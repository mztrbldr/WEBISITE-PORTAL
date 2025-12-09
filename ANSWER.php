<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';
date_default_timezone_set('Asia/Manila');


if (!isset($_SESSION['login_uni_id'])) {
    header('Location: login.php'); 
    exit;
}


$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer'], $_POST['question_id'])) {
    $answer = $_POST['answer'];
    $question_id = $_POST['question_id'];
    $admin_id = $_SESSION['login_uni_id']; 
    $posted = date('Y-m-d H:i:s'); 

    try {
     
        $stmt = $connect->prepare("INSERT INTO answers (ans, q_id, admin_id, posted) VALUES (?, ?, ?, ?)");
        $stmt->bindParam(1, $answer);
        $stmt->bindParam(2, $question_id);
        $stmt->bindParam(3, $admin_id);
        $stmt->bindParam(4, $posted);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Answer submitted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error submitting answer: ' . implode(", ", $stmt->errorInfo());
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error submitting answer: ' . $e->getMessage();
    }
}


header('Location: QUESTIONHUB.php');
exit;
?>
