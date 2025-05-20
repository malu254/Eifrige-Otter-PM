<?php

    session_start();

    if (!isset($_SESSION['login_user'])) {
        $wert = "uebersicht/index.php";
        $seite = "Zeiterfassung";
        header("Location: ../login/login?data=$wert&site=$seite");
        exit();
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

    // Einträge für Tabelle holen
    $eintraege = [];
    $stmt = $conn->prepare("SELECT zeitpunkt, aktion FROM zeiterfassung WHERE benutzer_id = ? ORDER BY zeitpunkt DESC");
    $stmt->bind_param("i", $benutzer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eintraege[] = $row;
    }
    $stmt->close();
    $conn->close();

    include 'uebersicht.html';
?>
