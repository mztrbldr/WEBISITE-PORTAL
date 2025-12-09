<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uni_id = $_POST['login_uni_id'] ?? '';
    $password = $_POST['login_password'] ?? '';

    try {
        $stmt = $connect->prepare('SELECT * FROM user_profile WHERE uni_id = :uni_id');
        $stmt->bindParam(':uni_id', $uni_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['login_uni_id'] = $user['uni_id'];
                $_SESSION['role'] = $user['role'];
                header('Location: /TRIAL/org_portal/pages/DASHBOARD.php');
                exit;

            } else {
                $_SESSION['error'] = 'Incorrect password.';
                header("Location: /TRIAL/org_portal/pages/signupPage.php?action=login");
                exit();
            }
        } else {
            $_SESSION['error'] = 'User not found.';
            header("Location: /TRIAL/org_portal/pages/signupPage.php?action=login");
            exit(); 
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    }
}
?>
