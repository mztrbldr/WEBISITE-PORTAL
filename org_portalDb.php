<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "org_portal";

try {
    $connect = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
