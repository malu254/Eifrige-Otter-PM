<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


    session_start();

    if (!isset($_SESSION['login_user'])) {
        $wert = "uebersicht/index.php";
        $seite = "Zeiterfassung";
        header("Location: ../login/login?data=$wert&site=$seite");
        exit();
    }

    if (isset($_GET['lang'])) 
    {
        $_SESSION['lang'] = $_GET['lang'];
    }

    $texts = include __DIR__ . "/../webseite/language/{$_SESSION['lang']}.php";

    include("/home/admin/datenbank_verbindung.php");
    
    $benutzername = $_SESSION['login_user'];

    // Benutzerinformationen einmalig laden
    $stmt = $conn->prepare("SELECT id, status, sollArbeitszeit, fehlzeit, konto, lang FROM user WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();

    // Danach Variablen setzen
    $benutzer_id = $userData['id'];
    $_SESSION['lang'] = $userData['lang'];
    $status = $userData['status'];
    $sollArbeitszeit = $userData['sollArbeitszeit'];
    $fehlzeit = $userData['fehlzeit'];
    $konto = $userData['konto'];

    $updates = [];
    $params = [];
    $types = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sprache
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sprache'])) {
            $_SESSION['lang'] = $_POST['sprache'];

            $stmt = $conn->prepare("UPDATE user SET lang = ? WHERE benutzername = ?");
            $stmt->bind_param("ss", $_SESSION['lang'], $benutzername);
            $stmt->execute();
        }

        // Neues Passwort
        if (isset($_POST['passwortFormular'])) {

            $stmt = $conn->prepare("UPDATE user SET passwort = ? WHERE benutzername = ?");
            $stmt->bind_param("ss", $_POST['passwortFormular'], $benutzername);
            $stmt->execute();
            $stmt->close();
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'password_success'
            ];
            header('Location: ' . $_SERVER['PHP_SELF']); // damit post formular "verschiwndet"
            exit;
        }
    }
    
    // Einträge für Zeitmanagement Tabelle holen
    $eintraege = [];
    $stmt = $conn->prepare("SELECT zeitpunkt, aktion FROM zeiterfassung WHERE benutzer_id = ? ORDER BY zeitpunkt DESC");
    $stmt->bind_param("i", $benutzer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eintraege[] = $row;
    }
    $stmt->close();

    include 'dashboard.html';
?>