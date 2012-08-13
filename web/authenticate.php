<?php
/*
    Uses lightopenid to login using google. 
*/
//Logging in with Google accounts requires setting special identity, so this example shows how to do it.
require 'includes/lightopenid/openid.php';
require 'config.php';
session_start();
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die('Connection Error: ' . mysqli_connect_error());
}
if (isset( $_GET['robot'] )) {
    $_SESSION['robot'] = $_GET['robot'];
}
try {
    $openid = new LightOpenID($_SERVER['HTTP_HOST']);

    if(!$openid->mode) {
        //do the login
        
        //The google openid url
        $openid->identity = 'https://www.google.com/accounts/o8/id';
            
        //Get additional google account information about the user , name , email , country
        $openid->required = array('contact/email' , 'namePerson/first' , 'namePerson/last' , 'pref/language' , 'contact/country/home'); 
            
        //start discovery
        header('Location: ' . $openid->authUrl());
    } else if($openid->mode == 'cancel') {
        header('Location: index.php');
    } else if ($openid->validate()) {
        //User logged in
        $d = $openid->getAttributes();
        
        $first_name = $d['namePerson/first'];
        $last_name = $d['namePerson/last'];
        $email = $d['contact/email'];
        $language_code = $d['pref/language'];
        $country_code = $d['contact/country/home'];
        $user_id = NULL;
        $is_admin = 0;
        // See if there is an existing user record.
        if ($stmt = $mysqli->prepare("SELECT id, is_admin FROM users WHERE email = ?")) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($user_id, $is_admin);
            $stmt->fetch();
            $stmt->close();
        }
        // Create or update a user record.
        if (is_null($user_id) || $user_id <= 0) {
            // New user record.
            if ($stmt = $mysqli->prepare("INSERT INTO users (email, first_name, last_name, country, created, last_seen) values (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)")) {
                $stmt->bind_param('ssss', $email, $first_name, $last_name, $country_code);
                $stmt->execute();
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $stmt->close();
            }
        } else {
            // Existing user record.
            if ($stmt = $mysqli->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, country = ?, last_seen = CURRENT_TIMESTAMP where email = ?")) {
                $stmt->bind_param('sssss', $email, $first_name, $last_name, $country_code, $email);
                $stmt->execute();
                $_SESSION['user_id'] = $user_id;
                $stmt->close();
            }
        }
        $_SESSION['is_admin'] = $is_admin;
        // Handle robot number.
        $robot_number = 0;
        if (isset( $_SESSION['robot'] )) {
            $robot_number = $_SESSION['robot'];
        }
        $_SESSION['robot_number'] = $robot_number;
        $mysqli->query("DELETE from controllers WHERE user_id = " . $user_id);
        // Create or update the queue record.
        if ($result = $mysqli->query("SELECT id FROM queue WHERE user_id = " . $user_id )) {
            $row_cnt = $result -> num_rows;
            $result -> close();
            if ($row_cnt == 0) {
                if ($stmt = $mysqli -> prepare("INSERT INTO queue (created, last_active, user_id, robot_number) values (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?)")) {
                    $stmt -> bind_param('ii', $user_id, $robot_number);
                    $stmt -> execute();
                    $_SESSION['queue_id'] = $stmt -> insert_id;
                    $stmt -> close();
                }
            } else {
                if ($stmt = $mysqli->prepare("UPDATE queue SET last_active = CURRENT_TIMESTAMP, robot_number = ? where user_id = ?")) {
                    $stmt->bind_param('ii', $robot_number, $user_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        // Redirect the user to the control page.
        header('Location: main.php');
    }
    $mysqli->close();
} catch(ErrorException $e) {
    echo $e->getMessage();
}
?>
