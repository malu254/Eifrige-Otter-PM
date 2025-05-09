<?php

include("/home/admin/datenbank_verbindung.php");

session_start();

$empfangeneDaten = $_GET['data'];
$loginSeite = $_GET['site'];

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $loginSeite; ?> Anmeldung</title>
    <link rel="stylesheet" type="text/css" href="../webseite/style/login.css" />
</head>


<body>
    <form method="post">
        <div class="login_card">
                <label id="logo"><?php echo $loginSeite; ?> Anmeldung</label>
                <input type="text" maxlength="35" name="benutzernameFormular" placeholder="Benutzername" id="bName">
                <input type="password" name="passwortFormular" placeholder="Passwort" id="passwort">
                <input type="submit" name="submit" value="Anmelden" id="submit_button">
                <div style="font-size:11px; color:#cc0000; margin-top:10px"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></div>
        </div>
    </form>
</body>

</html

