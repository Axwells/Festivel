<?php

session_start();
require "controler/controler.php";

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'home' :
            home();
            break;
        case 'programme' :
            programme();
            break;
        case 'register' :
            register();
            break;
        case 'logout' :
            logout();
            break;
        case 'login' :
            login($_POST);
            break;
        default :
            home();
    }
} else {
    home();
}