<?php
require 'includes/functions/auth-functions.php';
require 'config.php';
session_start();

try {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno()) {
        die('Connection Error: ' . mysqli_connect_error());
    }
    if (!isset($_SESSION['user_id'])) {
        doAuthentication($mysqli, 'index.php');
    } else {
         header('Location: index.php');
    }
    $mysqli->close();
} catch(ErrorException $e) {
    echo $e->getMessage();
}
?>
