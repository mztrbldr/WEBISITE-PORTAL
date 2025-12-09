<?php
session_start();
require 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars(trim($_POST['reset_email']));
    $new_password = htmlspecialchars(trim($_POST['reset_password']));

    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=reset_password");
        exit();
    }

    try {
        $stmt = $connect->prepare("SELECT * FROM user_profile WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update_stmt = $connect->prepare("UPDATE user_profile SET password = :password WHERE email = :email");
            $update_stmt->bindParam(':password', $hashed_password);
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Your password has been reset successfully.";
                header("Location: /TRIAL/org_portal/pages/signupPage.php?action=login");
                exit();
            } else {
                $_SESSION['error'] = "Error updating password. Please try again.";
                header("Location: /TRIAL/org_portal/pages/signupPage.php?action=reset_password");
                exit();
            }
        } else {
            $_SESSION['error'] = "No user found with that email address.";
            header("Location: /TRIAL/org_portal/pages/signupPage.php?action=reset_password");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}
?>
