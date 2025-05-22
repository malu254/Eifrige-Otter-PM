<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

    session_start();

    if (!isset($_SESSION['login_user'])) {
        $wert = "leitung/index.php";
        $seite = "Leitung";
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

    if ($benutzername !== 'admin') {
            $wert = "leitung/index.php";
            $seite = "Leitung";
            $error = "Kein Zugriff";
            header("Location: ../login/login.php?data=" . urlencode($wert) . "&site=" . urlencode($seite) . "&error=" . urlencode($error));
            exit();
    }

    // Post's
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        if (isset($_POST['benutzername_loeschen'])) {
        $benutzername_loeschen = $_POST['benutzername_loeschen'];

        // Benutzer-ID ermitteln
        $stmt_id = $conn->prepare("SELECT id FROM user WHERE benutzername = ?");
        $stmt_id->bind_param("s", $benutzername_loeschen);
        $stmt_id->execute();
        $stmt_id->bind_result($benutzer_id);
        $stmt_id->fetch();
        $stmt_id->close();

        if (!empty($benutzer_id)) {
            // Zeiterfassung des Benutzers löschen
            $stmt2 = $conn->prepare("DELETE FROM zeiterfassung WHERE benutzer_id = ?");
            $stmt2->bind_param("i", $benutzer_id);
            $stmt2->execute();
            $stmt2->close();

            // Benutzer in Tabelle löschen
            $stmt = $conn->prepare("DELETE FROM user WHERE benutzername = ?");
            $stmt->bind_param("s", $benutzername_loeschen);
            $stmt->execute();
            $stmt->close();
        }
    }


        if (isset($_POST['benutzerHinzufuegen'])) {
            $benutzer = $_POST['benutzernameEingabe'];
            $passwort = $_POST['passwortEingabe'];
            $geburtstag = $_POST['gebEingabe'];
            $sollArbeitszeit = $_POST['sollArbeitszeitEingabe'];
            

            if (!empty($benutzer)) { // hier auf $benutzer prüfen
                // Optional: prüfen, ob Benutzer bereits existiert
                $stmt = $conn->prepare("SELECT id FROM user WHERE benutzername = ?");
                $stmt->bind_param("s", $benutzer);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 0) {
                    $stmt->close();

                    // Benutzer einfügen
                    $stmt = $conn->prepare("INSERT INTO user (benutzername, passwort, geburtstag, sollArbeitszeit) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $benutzer, $passwort, $geburtstag, $sollArbeitszeit);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt->close();
                    // Benutzer existiert bereits – hier könntest du eine Nachricht anzeigen
                }
            }
        }

    }

    // Einträge für Tabelle holen
    $eintraege = [];
    $stmt = $conn->prepare("SELECT benutzername, status,sollArbeitszeit, fehlzeit, geburtstag FROM user");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eintraege[] = $row;
    }
    $stmt->close();
    $conn->close();

    include 'leitung.html';
?>