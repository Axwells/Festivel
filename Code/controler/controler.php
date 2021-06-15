<?php

// Fonction qui permet d'afficher la page "home"
function home()
{
    require "view/home.php";
}

//------------------------------------- Register - Login -------------------------------------//

// Fonction qui permet de créer un nouvel utilisateur
function register($registerRequest)
{
//if a register request was submitted
    if (isset($registerRequest['inputUserEmailAddress']) && isset($registerRequest['inputUserPsw']) && isset($registerRequest['inputUserPswRepeat'])
        && isset($registerRequest['inputUserName']) && isset($registerRequest['inputFirstName']) && isset($registerRequest['inputUserName'])
        && isset($registerRequest['inputUserNumberPhone'])) {

        $userName = $registerRequest ['inputUserName'];
        $userFirstName = $registerRequest ['inputFirstName'];
        $userNumberPhone = $registerRequest ['inputUserNumberPhone'];
        $userEmailAddress = $registerRequest['inputUserEmailAddress'];
        $userPsw = $registerRequest['inputUserPsw'];
        $userPswRepeat = $registerRequest['inputUserPswRepeat'];

        if ($userPsw == $userPswRepeat) {
            require_once "model/usersManager.php";
            if (registerNewAccount($userEmailAddress, $userPsw, $userName, $userFirstName, $userNumberPhone)) {
                $userType = getUserType($userEmailAddress);
                createSession($userEmailAddress, $userType);
                $registerErrorMessage = null;
                require "view/home.php";
            } else {
                $registerErrorMessage = "L'inscription n'est pas possible avec les valeurs saisies !";
                require "view/register.php";
            }
        } else {
            $registerErrorMessage = "Les mots de passe ne sont pas similaires !";
            require "view/register.php";
        }
    } else {
        $registerErrorMessage = null;
        require "view/register.php";
    }
}

// Fonction qui permet de connecter avec un les informations d'un utilisateurs déjà créé
function login($loginRequest)
{
//if a login request was submitted
    if (isset($loginRequest['inputUserEmailAddress']) && isset($loginRequest['inputUserPsw'])) {
        //extract login parameters
        $userEmailAddress = $loginRequest['inputUserEmailAddress'];
        $userPsw = $loginRequest['inputUserPsw'];
        //try to check if user/psw are matching with the database
        require_once "model/usersManager.php";
        if (isLoginCorrect($userEmailAddress, $userPsw)) {
            $userType = getUserType($userEmailAddress);
            createSession($userEmailAddress, $userType);
            $_GET['loginError'] = false;
            require "view/home.php";
        } else { //if the user/psw does not match, login form appears again
            $_GET['loginError'] = true;
            require "view/login.php";
        }
    } else { //the user does not yet fill the form
        require "view/login.php";
    }
}

// Fonction qui permet de se déconnecter de la session ouverte
function logout()
{
    session_destroy();
    require "view/home.php";
}

// Fonction qui permet de créer une nouvelle session
function createSession($userEmailAddress, $userType)
{
    $_SESSION['userEmailAddress'] = $userEmailAddress;
    $_SESSION['userType'] = $userType;
}

//------------------------------------- Programme -------------------------------------//

// Fonction qui permet de d'afficher le programme
function programme()
{
    require_once "model/concertsManager.php";
    $concerts = getConcerts();
    $artists = getArtists();
    $days = getDays();

    require "view/programme.php";
}

//------------------------------------- Pannier -------------------------------------//

// Fonction qui permet de d'afficher le pannier
function panier()
{
    require_once "model/billetsManager.php";
    $reservations = getPanier();
    require_once "model/usersManager.php";
    $users = getUsers();
    require "view/panier.php";
}

//------------------------------------- Concert -------------------------------------//

// Fonction qui permet de d'afficher les concerts
function concert()
{
    $concertId = $_GET['concertId'];
    require "model/concertsManager.php";
    $concerts = getConcerts();
    $artists = getArtists();
    $days = getDays();
    $concertsScenes = getConcertsScenes();
    $scenes = getScenes();

    require "view/concert.php";
}

// Fonction qui permet de suprrimer un article dans le panier
function delCart(){
    $reservationId = $_GET['reservationId'];
    require_once "model/billetsManager.php";
    $delCart = supCartBDD($reservationId);

    panier();
}

// Fonction qui permet de supprimer un concert dans la base de données
function supConcert()
{
    $concertID = $_GET["concertId"];
    require_once "model/concertsManager.php";
    $supConcert = supConcertBDD($concertID);

    programme();
}

// Fonction qui permet d'ajouter un concert dans la base de données
function addConcert()
{
    $concertID = $_GET["concertId"];
    require_once "model/concertsManager.php";
    $addConcert = addConcertBDD($concertID);

    programme();
}

// Fonction qui permet d'afficher le formulaire des concerts
function formConcert()
{
    require "view/formConcert";
}

//------------------------------------- Billetterie -------------------------------------//

// Fonction qui permet d'afficher la billetterie
function billetterie()
{
    require "view/billetterie.php";
}

// Fonction qui permet de d'ajouter des billets au pannier (et sont écrit dans la BDD)
function buyBillet(){
    $name = $_GET['name'];
    $day = $_GET['day'];
    $vip = $_GET['vip'];
    $price = $_GET['price'];
    $userId = '';

    if ($day == '2021-06-26') {
        $day = 1;
    } elseif ($day == '2021-06-27'){
        $day = 2;
    } else {
        $day = [1,2];
    }

    require_once "model/usersManager.php";
    $users = getUsers();

    foreach ($users as $user) {
        if ($_SESSION['userEmailAddress'] == $user['email']){
            $userId = $user['id'];
        }
    }

    require_once "model/billetsManager.php";
    $reservations = getPanier();
    $reservationNumber = 1;
    foreach ($reservations as $reservation) {
        $reservationNumber += 1;
    }

    require_once "model/billetsManager.php";
    $result = addBilletBD($name, $day, $vip, $reservationNumber, $price, $userId);

    panier();

}

function confirmCart(){
    $userId = $_GET['userId'];

    $reservations = getPanier();
    foreach ($reservations as $reservation) {
        if ($reservation['users_id'] == $userId) {
            if ($reservation['days_id'] == 1) {
                $day = '2021-06-26';
            } else {
                $day = '2021-06-27';
            }
            $orders = confirmCartBD($reservation['name'], $reservation['vip'], $reservation['price'], $reservation['reservationNumber'], $day, $userId);
            $orders = supCartBDD($reservation['id']);
        }
    }


    require "view/home.php";
}