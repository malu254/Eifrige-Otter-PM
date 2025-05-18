<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include("/home/admin/datenbank_verbindung.php");


session_start();

$empfangeneDaten = $_GET['data'];

// Überprüft, ob die Anfrage per POST gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{

    // Sicheres Escapen der Benutzereingaben, um SQL-Injection zu verhindern
    $benutzername = mysqli_real_escape_string($conn, $_POST['benutzernameFormular']);
    $passwort = $_POST['passwortFormular'];
    $email = $_POST['emailFormular'];
    $geb = $_POST['gebFormular'];


    // SQL-Abfrage, um den Benutzer anhand des Benutzernamens zu überprüfen
    $sql = "SELECT * FROM user WHERE benutzername = '$benutzername'";
    $result = mysqli_query($conn, $sql);
    $count = mysqli_num_rows($result);

    if ($count == 1) 
    {
        $error = "Benutzername schon vergeben";
    } 
    else 
    {
        
        $stmt = $conn->prepare("INSERT INTO user (benutzername, passwort, email, geburtstag) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $benutzername, $passwort, $email, $geb);
        $stmt->execute();
        $stmt->close();

        $_SESSION['login_user'] = $benutzername; // Setzt den Benutzernamen in der Session
        
        $basisURL = $_SERVER['SERVER_NAME'] . "/";
        $weiterleitung = "http://$basisURL$empfangeneDaten"; // Leitet den Benutzer zu der angegebenen URL weiter
        
        header("Location: $weiterleitung"); // Führt die Weiterleitung aus
        exit(); // Beendet das Skript
    }
}

include 'registrieren.html';
?>