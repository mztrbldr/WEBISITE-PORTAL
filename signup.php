<?php
session_start();
require 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';

$errorsu = '';
$name = '';
$lname = '';
$email = '';
$uni_id = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $name = $_POST['name'];
    $lname = $_POST['lname'];
    $email = $_POST['signup_email'];
    $uni_id = $_POST['signup_uni_id'];
    $password = $_POST['signup_password'];
    $confirm_password = $_POST['signup_confirm_password'];


    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match. Please try again.";
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=signup");
        exit(); 
    }


    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=signup");
        exit(); 
    }

    $stmt = $connect->prepare('SELECT * FROM user_profile WHERE uni_id = :uni_id OR email = :email');
    $stmt->bindParam(':uni_id', $uni_id);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        if ($existing_user['uni_id'] === $uni_id) {
            $_SESSION['error'] = "User with this Student Number is already signed up.";
        } elseif ($existing_user['email'] === $email) {
            $_SESSION['error'] = "Email address is already registered.";
        }
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=signup");
        exit(); 
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO user_profile (name, lname, email, uni_id, password) VALUES (:name, :lname, :email, :uni_id, :password)";
    $stmt = $connect->prepare($sql);

    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':uni_id', $uni_id);
    $stmt->bindParam(':password', $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Successfully signed up! You can now log in.";
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=login");
        exit(); 
    } else {
        $_SESSION['error_message'] = "Error: Could not execute the query."; 
        header("Location: /TRIAL/org_portal/pages/signupPage.php?action=signup");
        exit(); 
    }
}
?>
