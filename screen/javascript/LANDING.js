document.addEventListener("DOMContentLoaded", () => {
    // Navigation for "Create Now!" link in Signin page
    const createAccountLink = document.querySelector(".signuplink a");
    if (createAccountLink) {
        createAccountLink.addEventListener("click", () => {
            window.location.href = "signUp.php"; // Redirect to signup.php
        });
    }

    // Navigation for "Already have an account? Sign In" link in Signup page
    const signInLink = document.querySelector(".signup-link a");
    if (signInLink) {
        signInLink.addEventListener("click", () => {
            window.location.href = "signin.php"; // Redirect to signin.php
        });
    }
});
