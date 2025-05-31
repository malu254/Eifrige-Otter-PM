<?php


function respond_json($response) {
	header('Content-Type: application/json');
	echo json_encode($response);
}

function sql_querry($querry) {

	include("/home/admin/datenbank_verbindung.php");

	try {

		$stmt = $conn->prepare($querry);
		$stmt->execute();

		$result = $stmt->get_result();
		if (!$result) {
			return ;
		}
		$rows = [];

		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		$stmt->close();
		return $rows;
	} catch (Throwable $e) {
		respond_json([
			"MySql Error" => $e->getMessage()
		]);
		exit;
	}
}


// Rohdaten aus dem POST-Body lesen
$json = file_get_contents('php://input');

// JSON in ein PHP-Array oder Objekt umwandeln
$data = json_decode($json, true);  // true = als assoziatives Array

if ($data === null) {
    // Fehler beim Decodieren
    http_response_code(400);
    echo json_encode(['error' => 'invalid JSON']);
	echo phpinfo();
    exit;
}

// Beispiel: Zugriff auf die Werte
$funktion = $data['function'] ?? null;

if ($funktion == "get_notifications") {
	$nutzer_id = $data["user_id"] ?? null;

	$nachrichten = sql_querry("SELECT * FROM notification WHERE benutzer_id = $nutzer_id");

	respond_json([
		"notifications" => $nachrichten
	]);

}elseif ($funktion == "get_current_user") {
	session_start();
	respond_json([
		"user_name" => $_SESSION["login_user"]
	]);
}elseif ($funktion == "get_id_by_name") {
	$nutzer_name = $data["user_name"] ?? null;
	$nutzer_id = sql_querry("SELECT id FROM user WHERE benutzername = \"$nutzer_name\"");
	respond_json([
		"user_id" => $nutzer_id[0]["id"]
	]);
}elseif ($funktion == "get_times") {
	$user_id = $data["user_id"] ?? null;

	$times = sql_querry("SELECT zeitpunkt, aktion FROM zeiterfassung WHERE benutzer_id = \"$user_id\" ORDER BY zeitpunkt DESC");
	respond_json([
		"times" => $times
	]);

}elseif ($funktion == "get_lang") {
	$user_id = $data["user_id"] ?? null;
	$lang = sql_querry("SELECT lang FROM user WHERE id = \"$user_id\"");
	respond_json([
		"lang" => $lang[0]
	]);

}elseif ($funktion == "get_sollArbeitszeit") {
	$user_id = $data["user_id"] ?? null;
	$arbeits_zeit = sql_querry("SELECT sollArbeitszeit FROM user WHERE id = \"$user_id\"");
	respond_json([
		"sollArbeitszeit" => $arbeits_zeit[0]["sollArbeitszeit"]
	]);

}elseif ($funktion == "get_status") {
	$user_id = $data["user_id"] ?? null;
	$status = sql_querry("SELECT status FROM user WHERE id = \"$user_id\"");
	respond_json([
		"status" => $status[0]["status"]
	]);
}elseif ($funktion == "get_fehlzeit") {
	$user_id = $data["user_id"] ?? null;
	$fehl_zeit = sql_querry("SELECT fehlzeit FROM user WHERE id = \"$user_id\"");
	respond_json([
		"fehlzeit" => $fehl_zeit[0]["fehlzeit"]
	]);

}elseif ($funktion == "get_konto") {
	$user_id = $data["user_id"] ?? null;
	$konto = sql_querry("SELECT konto FROM user WHERE id = \"$user_id\"");
	respond_json([
		"konto" => $konto[0]["konto"]
	]);

}elseif ($funktion == "user_kommen") {
	$user_id = $data["user_id"] ?? null;

	$result = sql_querry("UPDATE user SET status = 1 WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $response
	]);

}elseif ($funktion == "user_gehen") {
	$user_id = $data["user_id"] ?? null;
	$result = sql_querry("UPDATE user SET status = 0 WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $response
	]);

}elseif ($funktion == "change_lang") {
	$user_id = $data["user_id"] ?? null;
	$lang    = $data["lang"]    ?? null;

	$result = sql_querry("UPDATE user SET lang = \"$lang\" WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $result
	]);

}elseif ($funktion == "change_password") {
	$user_id = $data["user_id"] ?? null;
	$new_password = $data["new_password"] ?? null;
	$result = sql_querry("UPDATE user SET passwort = \"$new_password\" WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $result
	]);

}elseif ($funktion == "validate_user") {
		include("/home/admin/datenbank_verbindung.php");
        $user_name = $data["user_name"] ?? null;
        $password = $data["password"] ?? null;


        $stmt = $conn->prepare("SELECT passwort FROM user WHERE benutzername = ?");
        $stmt->bind_param("s",$user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
                if ($password === $row["passwort"]) {
                        respond_json(["is_valid" => True]);
                } else {
                        respond_json(["is_valid" => False]);
                }
        } else {
                respond_json(["is_valid" => False]);
        }

	
}
?>
