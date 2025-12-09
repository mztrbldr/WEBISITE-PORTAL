<?php
session_start();

include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';

if (isset($_GET['file'])) {
    $file = 'uploads/' . basename($_GET['file']);
    
    error_log("Attempting to download file: " . $file); 
    

    if (file_exists($file)) {

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        error_log("File does not exist: " . $file); 
        echo 'File does not exist.';
    }
} else {
    echo 'No file specified.';
}
?>
