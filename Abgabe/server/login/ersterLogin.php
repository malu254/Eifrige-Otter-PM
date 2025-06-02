<?php

include("/home/admin/datenbank_verbindung.php");

session_start();

$empfangeneDaten = $_GET['data'];
$loginSeite = $_GET['site'];
$error = $_GET['error'];

$benutzername = $_SESSION['login_user'];

// Standard Sprache
if (!isset($_SESSION['lang']))
{
    $_SESSION['lang'] = 'de';
}

$texts = include __DIR__ . "/../webseite/language/{$_SESSION['lang']}.php";

// Überprüft, ob die Anfrage per POST gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $passwort = $_POST['passwortFormular'];
    
    $update_sql = "UPDATE user SET passwort='$passwort', ersterLogin='0' WHERE benutzername ='$benutzername'";
    mysqli_query($conn, $update_sql);

            
    // Basis-URL wird auf die aktuelle Server-Adresse gesetzt
    $basisURL = $_SERVER['SERVER_NAME'] . "/";
    $weiterleitung = "http://$basisURL$empfangeneDaten"; // Leitet den Benutzer zu der angegebenen URL weiter

    header("Location: $weiterleitung"); // Führt die Weiterleitung aus
    exit(); // Beendet das Skript
}

include 'ersterLogin.html';
?>