<?php

    session_start();

    if (!isset($_SESSION['login_user'])) {
        $wert = "uebersicht";
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"> 
    <link rel="stylesheet" type="text/css" href="../webseite/style/zeiterfassung.css" />
    <link rel="icon" href="../webseite/doc/zeiterfassung.png">
    <title>Zeiterfassung</title>
</head>

<body>
    <div class="container">
        <div class="main">
            <!-- Logout Button -->
            <form action="../login/logout.php" method="post">
                <button class="abmeldeButton" type="submit" name="delete-button">
                    <img class="logoutBild" src="../webseite/doc/abmelden.png" alt="Abmelden">
                </button>
            </form>                   
        </div>
    </div>



    <header>
        <h2>Hallo, <?php echo $_SESSION['login_user']; ?></h2>
    </header>


    <main>
        <section id="tabelle">
        <table border="">
            <tr>
                <th>Datum</th>
                <th>Aktion</th>
                <th>Uhrzeit</th>
            </tr>
            <?php foreach ($eintraege as $eintrag): 
                $datum = date("d.m.Y", strtotime($eintrag['zeitpunkt']));
                $uhrzeit = date("H:i", strtotime($eintrag['zeitpunkt']));
            ?>
                <tr>
                    <td><?= $datum ?></td>
                    <td><?= $eintrag['aktion'] ?></td>
                    <td><?= $uhrzeit ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        </section>
        <section id="button-kalender">
            <section id="buttons">

            <form method="post">
                <button type="submit" name="aktion" value="Kommen">Kommen</button>
                <button type="submit" name="aktion" value="Gehen">Gehen</button>
            </form>

            </section>
            <section id="kalender-box">
                <iframe id="kalender" src="https://calendar.google.com/calendar/embed?src=3cb30da763a05d82a1000250416f2ed6fbcdca4aa0d86efc90f28128b747c051%40group.calendar.google.com&ctz=Europe%2FBerlin" style="border: 0" frameborder="0" scrolling="no"></iframe>
            </section>
        </section>
    </main>

    <aside>
        <section id="daten">
            <dl>
                <dt>ID</dt>
                <dd>32450896798023576</dd>
                <dt>Modell</dt>
                <dd>35h</dd>
                <dt>Saldo</dt>
                <dd>-5:32</dd>

            </dl>
        </section>
    </aside>

    <footer>
        <p>footer</p>
    </footer>
</body>

</html>
