<?php

    include("/home/admin/datenbank_verbindung.php");

    echo "Vor if";
    // Nachrichten
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['get-notification'])) {
        echo $_POST['get-notification'];
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

?>