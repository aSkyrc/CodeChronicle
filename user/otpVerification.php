<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/mailer.php';


if (!isset($_SESSION)) {
    session_start();
}

// Session Timeout Logic
$timeout_duration = 600; // 10 minutes timeout (600 seconds)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    // If session has expired, destroy it and redirect
    session_unset();
    session_destroy();
    header("Location: signUp.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

// Check if the user has reached OTP verification directly without registration
if (!isset($_SESSION['email'])) {
    // Redirect the user back to the signUp.php if session data doesn't exist
    header("Location: signUp.php");
    exit();
}

// Get connection data
$connectionData = require $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
$connectionStatus = $connectionData['connectionStatus'];
$collections = $connectionData['collections'];

// Ensure the connection is established
if (!$connectionStatus) {
    die("Failed to connect to the database. Please try again later.");
}

// Get the `users` collection
$usersCollection = $collections['users'];

$otpErr = $successMessage = "";

// Function to generate a random OTP
function generateOtp() {
    return rand(100000, 999999); // 6-digit OTP
}

// Handle OTP verification and resend logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $enteredOtp = $_POST['otp'] ?? '';
    $action = $_POST['action'] ?? ''; // Check which action is being performed (verify or resend)

    // Retrieve the user from the database based on session email
    $user = $usersCollection->findOne(['email' => $_SESSION['email']]);


    if ($action === 'resend') {
        // Generate a new OTP
        $newOtp = generateOtp();
        $timestamp = time(); // Current timestamp

        // Update the OTP and timestamp in the database
        $usersCollection->updateOne(
            ['email' => $_SESSION['email']],  // Find user by email
            ['$set' => ['otp' => $newOtp, 'created_at' => $timestamp]] // Store OTP and timestamp
        );

        // Send the new OTP to the user's email
        sendOTPEmailUsingPHPMailer($_SESSION['email'], $newOtp);

        // Set success message for resend
        $successMessage = "A new OTP has been sent to your email.";
    } elseif ($action === 'verify') {
        // Check if the OTP entered matches the one stored in the database
        if ($user && $user['otp'] == $enteredOtp) {
            // Update OTP status to true, blank the OTP field, and update the timestamp
            $usersCollection->updateOne(
                ['email' => $_SESSION['email']],  // Find user by email
                [
                    '$set' => [
                        'otp-status' => true,    // Set otp-status to true
                        'otp' => '',             // Blank the OTP field
                        'created_at' => time(),  // Update the timestamp when OTP is verified
                        'first_login' => false   // Set first_login flag to false (as this is the first login after registration)
                    ]
                ]);

            // Refetch the user data to get updated OTP status and timestamp
            $user = $usersCollection->findOne(['email' => $_SESSION['email']]);


            unset($_SESSION['otp']);  // Clear OTP session data after verification

            // Set session flag to show registration success message
            $_SESSION['registration_success'] = true;

            // Set a session variable to ensure redirection only happens after modal interaction
            $_SESSION['show_success_modal'] = true;

            // Display the success alert before redirection
            echo "<script>
                    alert('You have successfully registered. You can log in now.');
                    setTimeout(function(){
                        window.location.href = 'signIn.php'; // Redirect to sign-in page
                    }, 1); 
                </script>";
            exit();  // Stop script execution here
        } else {
            $otpErr = "Invalid OTP.";
        }

    }  
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
    <link rel="stylesheet" href="../screen/css/OTP.css">
    <link rel="stylesheet" href="../screen/css/designLanding.css">
    <title>Code Chronicle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
    <div class="container">
        <div id="otpModal">
            <div class="modal-content">
                <div class="modal-logo">
                    <img src="../logos/CClogo.jpg" alt="Code Chronicle Logo">
                    <div class="modal-title">
                        <label class="brand-title">Code Chronicle</label><br>
                        <label class="brand-subtitle">Your Gateway to Programming Insights</label>
                    </div>
                </div>

                <div class="modal-header">
                    <h3>Enter Your Code</h3>
                    <br><br>
                    <h3 class="line">___________________________</h3>
                    <br><br>
                    <h5>We have sent a verification code to your email. Please check.</h5>
                </div>

                <form method="POST" action="otpVerification.php">
                    <div class="otp-wrapper">
                        <span class="error"><?php if (!empty($otpErr)) echo $otpErr; ?></span>
                        <span class="success"><?php if (!empty($successMessage)) echo $successMessage; ?></span>
                        <div class="otp-container">
                            <label for="otp" class="otp-label">OTP</label>
                            <input type="text" id="otp" name="otp" class="otp-input" placeholder="Enter OTP">
                            <button 
                                type="submit" 
                                name="action" 
                                value="resend" 
                                id="resendButton" 
                                class="resend-button" 
                                disabled>
                                Resend (60)
                            </button>
                        </div>
                    </div>

                    <div class="confirm-section">
                        <button type="submit" name="action" value="verify" id="confirmButton">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const resendButton = document.getElementById("resendButton");
        let countdown = 60; // Timer starts from 60 seconds

        // Disable the resend button initially and start the timer
        resendButton.disabled = true;

        const timer = setInterval(() => {
            if (countdown > 0) {
                resendButton.innerText = `Resend (${countdown--})`; // Update button text with timer
            } else {
                clearInterval(timer); // Stop the timer when it reaches 0
                resendButton.disabled = false; // Enable the button
                resendButton.innerText = "Resend"; // Reset button text
            }
        }, 1000); // Decrease the countdown every second
    });
</script>

</body>
</html>
