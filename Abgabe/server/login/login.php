<?php

include("/home/admin/datenbank_verbindung.php");


session_start();

$empfangeneDaten = $_GET['data'];
$loginSeite = $_GET['site'];
$error = $_GET['error'];

$texts = include __DIR__ . "/../webseite/language/{$_SESSION['lang']}.php";

// Überprüft, ob die Anfrage per POST gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{

    // Sicheres Escapen der Benutzereingaben, um SQL-Injection zu verhindern
    $benutzername = mysqli_real_escape_string($conn, $_POST['benutzernameFormular']);
    $passwort = $_POST['passwortFormular'];


    // SQL-Abfrage, um den Benutzer anhand des Benutzernamens zu überprüfen
    $sql = "SELECT * FROM user WHERE benutzername = '$benutzername'";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_num_rows($result);

    if ($count == 1) 
    {
        $row = mysqli_fetch_assoc($result);
        $db_passwort = $row["passwort"];

        // Passwortüberprüfung mit password_verify
        if ($passwort == $db_passwort)
        {
            $_SESSION['login_user'] = $benutzername; // Setzt den Benutzernamen in der Session

            // Aktualisiert den letzten Login-Zeitpunkt des Benutzers
            $update_sql = "UPDATE user SET letzter_login=NOW() WHERE benutzername ='$benutzername'";
            mysqli_query($conn, $update_sql);
            
            // Basis-URL wird auf die aktuelle Server-Adresse gesetzt
            $basisURL = $_SERVER['SERVER_NAME'] . "/";
            $weiterleitung = "http://$basisURL$empfangeneDaten"; // Leitet den Benutzer zu der angegebenen URL weiter

            header("Location: $weiterleitung"); // Führt die Weiterleitung aus
            exit(); // Beendet das Skript
        }
        else 
        {
            // Wenn die Eingaben falsch sind, wird eine Fehlermeldung angezeigt
            $error = "Falsches Passwort!";
        }
    } 
    else 
    {
        // Wenn die Eingaben falsch sind, wird eine Fehlermeldung angezeigt
        $error = "Falscher Benutzername!";
    }
}

include 'login.html';

?>