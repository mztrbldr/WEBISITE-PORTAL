<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';


if (isset($_GET['action']) && $_GET['action'] === 'logout') {

    session_destroy();

    header('Location: signupPage.php');
    exit();
}
?>
