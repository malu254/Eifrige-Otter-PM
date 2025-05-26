<?php
session_start(); // Hier wird eine PHP Session neu erstellt

// Wenn ein angemeldeter Benutzer existiert, weden alle Cookie Variablen gelöscht und die Sitzung zerstört
// Danach wird zur Anfangs- Übersichtsseite weitergeleitet und das Skript verlassen 
if(isset($_SESSION['login_user'])) {
    $lang = $_SESSION['lang'];
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['lang'] = $lang;
    header("Location: ../../uebersicht/index.php");
    exit;
}
?>
