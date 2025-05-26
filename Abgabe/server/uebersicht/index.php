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

    include("/home/admin/datenbank_verbindung.php");

    // Benutzer-ID anhand von Benutzername holen
    $benutzername = $_SESSION['login_user'];
    $stmt = $conn->prepare("SELECT id FROM user WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $stmt->bind_result($benutzer_id);
    $stmt->fetch();
    $stmt->close();

    // Aktion speichern, wenn ein Button geklickt wurde
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aktion'])) {
        $aktion = $_POST['aktion'];
        if (in_array($aktion, ['Kommen', 'Gehen'])) {
            $stmt = $conn->prepare("INSERT INTO zeiterfassung (benutzer_id, aktion) VALUES (?, ?)");
            $stmt->bind_param("is", $benutzer_id, $aktion);
            $stmt->execute();
            $stmt->close();
        }

        if ($aktion == 'Kommen') {
            $status = 'Anwesend';
        } else {
            $status = 'Abwesend';
        }
        $stmt = $conn->prepare("UPDATE user SET status = ? WHERE benutzername = ?");
        $stmt->bind_param("ss", $status, $benutzername);
        $stmt->execute();
        $stmt->close();
    }

    // Sprache
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sprache'])) {
        $_SESSION['lang'] = $_POST['sprache'];

        $stmt = $conn->prepare("UPDATE user SET lang = ? WHERE benutzername = ?");
        $stmt->bind_param("ss", $_SESSION['lang'], $benutzername);
        $stmt->execute();
        $stmt->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['passwortFormular'])) {
        $passwort = $_POST['passwortFormular'];
        $stmt = $conn->prepare("UPDATE user SET passwort = ? WHERE benutzername = ?");
        $stmt->bind_param("ss", $passwort, $benutzername);
        $stmt->execute();
        $stmt->close();
    }

    // Nachrichten
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get-notification'])) {

        $nachrichten = [];
        $stmt = $conn->prepare("SELECT * FROM notification WHERE benutzer_id = ?");
        $stmt->bind_param("i", $benutzer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $nachrichten[] = $row;
        }
        $stmt->close();
        echo json_encode($nachrichten);
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

    // Zweite Abfrage (Info)
    $eintraegeInfo = [];
    $stmt = $conn->prepare("SELECT sollArbeitszeit, status, fehlzeit, konto FROM user WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);  // Richtig binden!
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eintraegeInfo[] = $row;
    }
    $stmt->close();

    // Sprache aus Datenbank laden
    $stmt = $conn->prepare("SELECT lang FROM user WHERE benutzername = ?");
    $stmt->bind_param("s", $benutzername);
    $stmt->execute();
    $stmt->bind_result($lang);
    $stmt->fetch();
    $_SESSION['lang'] = $lang;
    $stmt->close();
    $conn->close();



    // Standardt

    if ($_SESSION['lang'] === 'en') 
    {
        include 'uebersicht_en.html';
    } 
    else 
    {
        include 'uebersicht_de.html';
    }
?>
