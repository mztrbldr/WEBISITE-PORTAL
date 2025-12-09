<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORG PORTAL</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="container">

    <div class="front_logo">
        </div>
    </div>

    <div class="formdiv">
        
    <?php 
        $action = $_GET['action'] ?? 'signup'; 

        if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php elseif (isset($_SESSION['success'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <?php if ($action === 'signup'): ?>
            <form method="POST" action="signup.php">
                <h2>SIGN UP</h2>
                <label for="first_name">First Name</label>
                <input class="inputbox" type="text" id="first_name" placeholder="First Name" name="name" required>
                <br>
                <label for="last_name">Last Name</label>
                <input class="inputbox" type="text" id="last_name" placeholder="Last Name" name="lname" required>
                <br>
                <label for="email">E-Mail</label>
                <br>
                <input class="inputbox" type="email" id="email" placeholder="E-mail" name="signup_email" required>
                <br>
                <label for="uni_id">Student Number</label>
                <input class="inputbox" type="text" id="uni_id" placeholder="Student Number (ex. 03-2223-1100)" name="signup_uni_id" required>
                <br>
                <label for="signup_password">Password</label>
                <input class="inputbox" type="password" id="signup_password" name="signup_password" required>
                <br>
                <label for="signup_confirm_password">Confirm Password</label>
                <input class="inputbox" type="password" id="signup_confirm_password" name="signup_confirm_password" required>
                <br>
                <button class="signup_button" type="submit">SIGN UP</button>
                <a class="logInAcc_button" href="?action=login">Already have an account? Log In</a>
            </form>

        <?php elseif ($action === 'login'): ?>
            <form method="POST" action="login.php">
                <h2>LOG IN</h2>
                <label for="uni_id">Student Number</label>
                <input class="inputbox" type="text" name="login_uni_id" id="uni_id" placeholder="Student Number (ex. 03-2223-1100)" required>
                <br>
                <label for="login_password">Password</label>
                <input class="inputbox" type="password" id="login_password" name="login_password" required>
                <br>
                <button class="login_button" type="submit" name="LOGIN">LOG IN</button>
                <a class="forgotPw_button" href="?action=reset_password">Forgot Password?</a>
                <a class="createAcc_button" href="?action=signup">Create Account</a>
            </form>

        <?php elseif ($action === 'reset_password'): ?>
            <form method="POST" action="resetpw.php">
                <h2>RESET PASSWORD</h2>
                <label for="email">E-Mail</label>
                <br>
                <input class="inputbox" type="email" name="reset_email" id="email" placeholder="E-mail" required>
                <br>
                <label for="reset_password">New Password</label>
                <input class="inputbox" type="password" name="reset_password" id="reset_password" required>
                <br>
                <label for="reset_confirm_password">Confirm New Password</label>
                <input class="inputbox" type="password" name="reset_confirm_password" id="reset_confirm_password" required>
                <br>
                <button class="resetPw_button" type="submit">RESET PASSWORD</button>
                <a class="logInAccRs_button" href="?action=login">Remembered your password? Log In</a>
                <a class="createAcc_button" href="?action=signup">Create Account</a>
            </form>

        <?php else: ?>
            <p>Invalid action.</p>
        <?php endif; ?>

    </div>

</body>
</html>
