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
        if (isset($_POST['sprache'])) {
            $updates[] = "lang = ?";
            $params[] = $_POST['sprache'];
            $types .= 's';
            $_SESSION['lang'] = $_POST['sprache'];
        }

        if (isset($_POST['passwortFormular'])) {
            $updates[] = "passwort = ?";
            $params[] = $_POST['passwortFormular'];
            $types .= 's';
        }

        if (isset($_POST['aktion']) && in_array($_POST['aktion'], ['Kommen', 'Gehen', 'Frei'])) {
            $aktion = $_POST['aktion'];
            $stmt = $conn->prepare("INSERT INTO zeiterfassung (benutzer_id, aktion) VALUES (?, ?)");
            $stmt->bind_param("is", $benutzer_id, $aktion);
            $stmt->execute();
            $stmt->close();

            $status = ($aktion == 'Kommen') ? 'Anwesend' : 'Abwesend';
            $updates[] = "status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        // Nur wenn es Änderungen gibt
        if (!empty($updates)) {
            $query = "UPDATE user SET " . implode(', ', $updates) . " WHERE benutzername = ?";
            $params[] = $benutzername;
            $types .= 's';
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
    }



    // Nachrichten
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get-notification'])) {
        header('Content-Type: application/json');
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
        exit(); // Ganz wichtig!
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