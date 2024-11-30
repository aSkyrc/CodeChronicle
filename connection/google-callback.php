<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';

// Start session
session_start();

if (isset($_GET['code'])) {
    try {
        // Fetch the token from the Google Client
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Validate token and check if it's valid
        if (isset($token['error'])) {
            throw new Exception("Error fetching access token: " . $token['error']);
        }

        // Check if the access token was successfully obtained
        if (!isset($token['access_token']) || empty($token['access_token'])) {
            throw new Exception('Access token is missing or empty');
        }

        // Set access token for future requests
        $client->setAccessToken($token['access_token']);
        
        // Validate that the client is successfully authenticated with Google
        if (!$client->getAccessToken()) {
            throw new Exception('Google OAuth authentication failed');
        }

        // Get user profile info from Google
        $oauth2 = new Google\Service\Oauth2($client);
        $userInfo = $oauth2->userinfo->get();

        // Check if user data is retrieved successfully
        if (!$userInfo) {
            throw new Exception('Failed to retrieve user information from Google');
        }

        // Store necessary user data in session
        $_SESSION['user'] = [
            'username' => $userInfo->name,
            'email' => $userInfo->email,
            'picture' => $userInfo->picture,
            'token' => $token['access_token'],
        ];

        // Connect to MongoDB
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';

        // Select collections for checking existing users
        $googleUsersCollection = $db->selectCollection('google-users');
        $usersCollection = $db->selectCollection('users');

        // Check if the email exists in 'google-users' or 'users'
        $existingInGoogleUsers = $googleUsersCollection->findOne(['email' => $userInfo->email]);
        $existingInUsers = $usersCollection->findOne(['email' => $userInfo->email]);

        if ($existingInGoogleUsers) {
            // If user exists in 'google-users' collection, don't insert again
            // Update the session data if needed
            $_SESSION['user']['first_login'] = $existingInGoogleUsers['first_login']; 
            $_SESSION['user_id'] = $existingInGoogleUsers['_id']; // Save user ID from MongoDB

            // Redirect to the appropriate page based on the 'first_login' field
            if ($existingInGoogleUsers['first_login'] === true) {
                // Add success alert and redirect to the user selection role page
                echo "<script>alert('Login successful!'); window.location.href = '/Code Chronicle/user/userSelectionRole.php';</script>";
                exit;
            } else {
                // Add success alert and redirect to the user selection role page
                echo "<script>alert('Your Google account has been successfully connected!'); window.location.href = '/CodeChronicle/user/userSelectionRole.php';</script>";
                exit;
            }
        }

        // If the user does not exist, insert new user into the 'google-users' collection
        $googleUsersCollection->insertOne([
            'username' => $userInfo->name,
            'email' => $userInfo->email,
            'picture' => $userInfo->picture,
            'token' => $token['access_token'],
            'created_at' => time(),
            'first_login' => false,  // First-time login
        ]);

        // Store user ID in session
        $userRecord = $googleUsersCollection->findOne(['email' => $userInfo->email]);
        $_SESSION['user_id'] = $userRecord['_id'];  // Store the user's MongoDB ID in the session

        // Add success alert and redirect to the appropriate page based on first login status
        if ($userRecord['first_login'] === true) {
            // If it's the first login, redirect to homepage.php
            echo "<script>alert('Login successful!'); window.location.href = '/Code Chronicle/user/homepage.php';</script>";
        } else {
            // Otherwise, redirect to userSelectionRole.php
            echo "<script>alert('Your Google account has been successfully connected!'); window.location.href = '/Code Chronicle/user/userSelectionRole.php';</script>";
        }
        exit;

    } catch (Exception $e) {
        // Handle errors more gracefully
        echo 'Error: ' . $e->getMessage();
        exit;
    }
} elseif (isset($_SESSION['user'])) {
    // If session exists (i.e., already logged in), re-verify the user's session
    $userInfo = $_SESSION['user'];

    // Check if the access token is valid (this checks for token expiration)
    if ($client->isAccessTokenExpired()) {
        // Token expired, redirect to Google OAuth to re-authenticate
        unset($_SESSION['user']); // Optionally clear the session
        header('Location: google-login.php'); // Redirect to Google OAuth page
        exit;
    }

    // Connect to MongoDB
    $googleUsersCollection = $db->selectCollection('google-users');

    // Check if the email exists in the 'google-users' collection
    $existingInGoogleUsers = $googleUsersCollection->findOne(['email' => $userInfo['email']]);

    if ($existingInGoogleUsers) {
        // Redirect to the appropriate page based on the 'first_login' field
        if ($existingInGoogleUsers['first_login'] === true) {
            // If it's the first login, redirect to homepage.php
            header('Location: /Code Chronicle/user/homepage.php');
        } else {
            // Otherwise, redirect to userSelectionRole.php
            header('Location: /Code Chronicle/user/userSelectionRole.php');
        }
        exit;
    } else {
        echo 'Error: User not found in database!';
        exit;
    }
} else {
    echo 'Error during authentication!';
}
