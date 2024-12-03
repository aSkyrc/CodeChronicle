<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/mailer.php';

if (!isset($_SESSION)) {
    session_start();
}

// Get connection data
$connectionData = require $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
$connectionStatus = $connectionData['connectionStatus'];
$collections = $connectionData['collections'];

// Ensure the connection is established
if (!$connectionStatus) {
    die("Failed to connect to the database. Please try again later.");
}

// Get the `users` and `google-users` collections
$usersCollection = $collections['users'];
$googleUsersCollection = $collections['google-users']; // New collection for Google users

// Initialize error messages
$usernameErr = $emailErr = $passwordErr = $confirmPasswordErr = $otpErr = "";
$successMessage = "";

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signup'])) {
        // Get form data
        $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $confirmPassword = htmlspecialchars($_POST['confirm-password'] ?? '', ENT_QUOTES, 'UTF-8');

        // Form validation
        if (empty($username)) {
            $usernameErr = "Username is required.";
        } elseif (empty($email)) {
            $emailErr = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format.";
        } elseif (empty($password)) {
            $passwordErr = "Password is required.";
        } elseif ($password !== $confirmPassword) {
            $confirmPasswordErr = "Passwords do not match.";
        } else {
            // Check if username exists with otp-status true
            $existingUser = $usersCollection->findOne(['username' => $username, 'otp-status' => true]);
            if ($existingUser) {
                $usernameErr = "Username is already taken.";
            }

            // Check if email exists with otp-status true in users collection
            $existingEmail = $usersCollection->findOne(['email' => $email, 'otp-status' => true]);
            // Check if email exists in google-users collection
            $existingGoogleEmail = $googleUsersCollection->findOne(['email' => $email]);
            if ($existingEmail || $existingGoogleEmail) {
                $emailErr = "Email is already taken.";
            }

            // Check if both username and email already exist
            if (empty($usernameErr) && empty($emailErr)) {
                $existingUser = $usersCollection->findOne(['username' => $username, 'otp-status' => false]);
                $existingEmail = $usersCollection->findOne(['email' => $email, 'otp-status' => false]);
                $existingGoogleEmail = $googleUsersCollection->findOne(['email' => $email]);

                if ($existingUser && $existingEmail) {
                    $usernameErr = "Username and email are already taken.";
                    $emailErr = "Username and email are taken.";
                }

                // If email exists with otp-status false in users collection or google-users collection, delete the old record
                if ($existingEmail && $existingEmail['otp-status'] === false) {
                    $usersCollection->deleteOne(['email' => $email, 'otp-status' => false]);
                }

                if ($existingGoogleEmail) {
                    $googleUsersCollection->deleteOne(['email' => $email]);
                }
            }

            // If no errors, generate OTP and send email
            if (empty($usernameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
                // Check if username or email already exists with otp-status false
                $existingUser = $usersCollection->findOne(['username' => $username, 'otp-status' => false]);
                if ($existingUser) {
                    // If user exists and OTP status is false, delete the old user
                    $usersCollection->deleteOne(['username' => $username, 'otp-status' => false]);
                }

                $existingEmail = $usersCollection->findOne(['email' => $email, 'otp-status' => false]);
                if ($existingEmail) {
                    // If email exists and OTP status is false, delete the old user
                    $usersCollection->deleteOne(['email' => $email, 'otp-status' => false]);
                }

                // Store data in session for use in OTP verification
                $_SESSION['username'] = $username;
                $_SESSION['password'] = $password;
                $_SESSION['email'] = $email;

                // Generate OTP (6 digit code)
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;  // Store OTP in session temporarily

                // Store the user with OTP and OTP status in the database
                $userData = [
                    'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
                    'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'picture' => '../logos/userDefault.png',
                    'otp' => $otp,
                    'otp-status' => false
                ];
                $usersCollection->insertOne($userData);

                // Send OTP to the user's email
                if (sendOTPEmail($email, $otp)) {
                    echo "Email sent successfully.";
                    $_SESSION['otp_sent'] = true;
                    header('Location: otpVerification.php'); // Redirect to OTP page
                    exit();
                } else {
                    $emailErr = "Failed to send OTP email. Please try again.";
                }
            }
        }
    }
}

// Function to send OTP via email
function sendOTPEmail($email, $otp) {
    return sendOTPEmailUsingPHPMailer($email, $otp);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="shortcut icon" href="../logos/CClogo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="../screen/css/userSelectionCategory.css">
    <link rel="stylesheet" href="../screen/css/userSelectionRole.css">
    <link rel="stylesheet" href="../screen/css/otp.css">
    <link rel="stylesheet" href="../screen/css/designLanding.css">
    <title>Code Chronicle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
   
</head>
<body>
    <div class="container">
        <!-- Sign Up Form -->
        <div id="signup-form-content">
            <div class="logo">
                <img src="../logos/CClogo.jpg" alt="Code Chronicle Logo">
                <div class="cc">
                    <label>Code Chronicle</label>
                </div>
                <div class="cc2">
                    <label>Your Gateway to Programming Insights</label>
                </div>
            </div>

            <!-- Display validation errors here -->
            <div class="text">
                <label>Register Form</label>
                <br>
                <label class="line">____________________________</label>
                
                <!-- Display error messages -->
                <?php if (!empty($usernameErr) || !empty($emailErr) || !empty($passwordErr) || !empty($confirmPasswordErr)) { ?>
                    <div class="error-messages">
                        <?php
                        if (!empty($usernameErr)) {
                            echo "<p class='error'>" . htmlspecialchars($usernameErr, ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                        if (!empty($emailErr)) {
                            echo "<p class='error'>" . htmlspecialchars($emailErr, ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                        if (!empty($passwordErr)) {
                            echo "<p class='error'>" . htmlspecialchars($passwordErr, ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                        if (!empty($confirmPasswordErr)) {
                            echo "<p class='error'>" . htmlspecialchars($confirmPasswordErr, ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>

            <form method="POST" id="signup-form">
                <div class="signup-inputbox">
                    <div class="signup-inputtext">
                        <label for="username">Username</label>
                        <input class="username" type="text" id="username" name="username" placeholder="Enter Username">
                    </div>

                    <div class="signup-inputtext">
                        <label for="password">Password</label>
                        <input class="password" type="password" id="password" name="password" placeholder="Enter Password">
                    </div>

                    <div class="signup-inputtext">
                        <label for="email">Email</label>
                        <input class="email" type="email" id="email" name="email" placeholder="Enter Email">
                    </div>

                    <div class="signup-inputtext">
                        <label for="confirm-password">Confirm Password</label>
                        <input class="cpassword" type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password">
                    </div>

                    <div class="signup-button">
                        <!-- Change the button type to "submit" -->
                        <button type="submit" name="signup">Sign Up</button>
                    </div>
                </div>
            </form>

            <div class="signup-link">
                <label>Already have an account?</label>
                <a href="#">Sign In</a>
            </div>
        </div>
    </div>

    
    <script src="../screen/javascript/LANDING.js"></script>

    <script>
        window.addEventListener('beforeunload', function() {
            // Send request to remove session data and database entry if the user leaves the page
            <?php 
                // Make sure session data is cleared when the user leaves
                session_unset();
                session_destroy();
                // Code to remove the user from the database
                $usersCollection->deleteOne(['email' => $_SESSION['email']]);
            ?>
        });
        

    </script>
</body>
</html>
