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
        
        if (isset($_POST['benutzername_loeschen']))
        {
            $benutzername_loeschen = $_POST['benutzername_loeschen'];
        
            // Benutzer
            $stmt = $conn->prepare("DELETE FROM user WHERE benutzername = ?");
            $stmt->bind_param("s", $benutzername_loeschen);
            $stmt->execute();
            $stmt->close();

            // Zeitmanagement
            $stmt2 = $conn->prepare("DELETE FROM zeitmanagement WHERE id = ?");
            $stmt2->bind_param("i", $benutzer_id);
            $stmt2->execute();
            $stmt2->close();
        }

        if (isset($_POST['benutzerHinzufuegen']))
        {
            $neuerBenutzer = trim($_POST['benutzernameEingabe']);

            if(!empty($neuerBenutzer)) {
                // Optional: prüfen, ob Benutzer bereits existiert
                $stmt = $conn->prepare("SELECT id FROM user WHERE benutzername = ?");
                $stmt->bind_param("s", $neuerBenutzer);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 0) {
                    $stmt->close();

                    // Benutzer einfügen (ggf. mit Platzhalterwerten für andere Felder)
                    $stmt = $conn->prepare("INSERT INTO user (benutzername) VALUES (?)");
                    $stmt->bind_param("s", $neuerBenutzer);
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
    $stmt = $conn->prepare("SELECT benutzername, email FROM user");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $eintraege[] = $row;
    }
    $stmt->close();
    $conn->close();

    include 'leitung.html';
?>