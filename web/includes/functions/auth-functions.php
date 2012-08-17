<?php
/*
    Uses lightopenid to login using google. 
*/
//Logging in with Google accounts requires setting special identity, so this example shows how to do it.
require_once 'includes/lightopenid/openid.php';

function doAuthentication($mysqli, $location) {
    if (!isset($mysqli)) {
        echo "Error NO MYSQL";
        exit(1);
    }
    if (!isset($location)) {
        $location = 'main.php';
    }
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
        if ($stmt = $mysqli->prepare("SELECT id, is_admin, show_tutorial FROM users WHERE email = ?")) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($user_id, $is_admin, $show_tutorial);
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
        $_SESSION['show_tutorial'] = $show_tutorial;
        header('Location: ' . $location);
    }
}
?>