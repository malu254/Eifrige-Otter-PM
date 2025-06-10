<?php


function respond_json($response)
{
	header('Content-Type: application/json');
	echo json_encode($response);
}

function sql_querry($querry)
{

	include("/home/admin/datenbank_verbindung.php");

	try {

		$stmt = $conn->prepare($querry);
		$stmt->execute();

		$result = $stmt->get_result();
		if (!$result) {
			return;
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

	// du kannst hier optional nach "gesehen = 0" filtern, wenn du nur ungesehene willst
	$nachrichten = sql_querry("SELECT * FROM notification WHERE benutzer_id = $nutzer_id AND gelesen = 0");

	respond_json([
		"notifications" => $nachrichten,
		"gelesen" => $gelesen
	]);

} elseif ($funktion == "mark_as_seen") {
	$notification_id = $data["notification_id"] ?? null;

	if ($notification_id !== null) {
		sql_querry("UPDATE notification SET gelesen = 1 WHERE id = $notification_id");
		respond_json(["success" => true]);
	} else {
		respond_json(["success" => false, "error" => "Keine ID"]);
	}

} elseif ($funktion == "get_current_user") {
	session_start();
	$user_name = $_SESSION["login_user"];
	$result = sql_querry("SELECT * FROM user WHERE benutzername = \"$user_name\"");
	respond_json([
		"user" => $result[0]
	]);
} elseif ($funktion == "get_id_by_name") {
	$nutzer_name = $data["user_name"] ?? null;
	$nutzer_id = sql_querry("SELECT id FROM user WHERE benutzername = \"$nutzer_name\"");
	respond_json([
		"user_id" => $nutzer_id[0]["id"]
	]);
} elseif ($funktion == "get_times") {
	$user_id = $data["user_id"] ?? null;

	$times = sql_querry("SELECT zeitpunkt, aktion FROM zeiterfassung WHERE benutzer_id = \"$user_id\" ORDER BY zeitpunkt DESC");
	respond_json([
		"times" => $times
	]);

} elseif ($funktion == "get_lang") {
	$user_id = $data["user_id"] ?? null;
	$lang = sql_querry("SELECT lang FROM user WHERE id = \"$user_id\"");
	respond_json([
		"lang" => $lang[0]
	]);

} elseif ($funktion == "get_sollArbeitszeit") {
	$user_id = $data["user_id"] ?? null;
	$arbeits_zeit = sql_querry("SELECT sollArbeitszeit FROM user WHERE id = \"$user_id\"");
	respond_json([
		"sollArbeitszeit" => $arbeits_zeit[0]["sollArbeitszeit"]
	]);

} elseif ($funktion == "get_status") {
	$user_id = $data["user_id"] ?? null;
	$status = sql_querry("SELECT status FROM user WHERE id = \"$user_id\"");
	respond_json([
		"status" => $status[0]["status"]
	]);
} elseif ($funktion == "get_fehlzeit") {
	$user_id = $data["user_id"] ?? null;
	$fehl_zeit = sql_querry("SELECT fehlzeit FROM user WHERE id = \"$user_id\"");
	respond_json([
		"fehlzeit" => $fehl_zeit[0]["fehlzeit"]
	]);

} elseif ($funktion == "get_konto") {
	$user_id = $data["user_id"] ?? null;
	$konto = sql_querry("SELECT konto FROM user WHERE id = \"$user_id\"");
	respond_json([
		"konto" => $konto[0]["konto"]
	]);

} elseif ($funktion == "user_kommen") {
	$user_id = $data["user_id"] ?? null;

	$result = sql_querry("SELECT status FROM user WHERE id = \"$user_id\"");
	if ($result[0]["status"] == 1) {
		respond_json([
			"error" => "users allready logged in"
		]);
		exit;
	}

	if (date("N") >= 6) {
		respond_json([
			"err" => "it is a weekend"
		]);
		exit;
	}

	$now = new DateTime();

	$result = sql_querry("SELECT geburtstag FROM user WHERE id = \"$user_id\"");
	$bday = new DateTime($result[0]["geburtstag"]);
	if ($now->getTimestamp() - $bday->getTimestamp() < 567648000) {
		$t = date("H:i");
		if ($t < "06:00" || $t > "22:00") {
			respond_json([
				"err" => "a young user cant login before 06:00 or after 22:00"
			]);
			exit;
		}
	}

	$t = date("Y-m-d H-i-s");

	$result = sql_querry("SELECT zeitpunkt FROM zeiterfassung WHERE benutzer_id = \"$user_id\" and aktion = \"Gehen\" ORDER BY zeitpunkt DESC LIMIT 1");
	if (sizeof($result) != 0) {
		$letzte_zeit = new DateTime($result[0]["zeitpunkt"]);

		$interval = $now->getTimestamp() - $letzte_zeit->getTimestamp();

		if ($interval < 11 * 3600) {
			respond_json([
				"err" => "time since last gehen too small"
			]);
			exit;
		}
	}

	sql_querry("insert into zeiterfassung (benutzer_id,aktion,zeitpunkt) values (\"$user_id\",\"Kommen\",\"$t\")");

	$result = sql_querry("UPDATE user SET status = 1 WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $response
	]);

} elseif ($funktion == "user_gehen") {
	$user_id = $data["user_id"] ?? null;

	$result = sql_querry("SELECT status FROM user WHERE id = \"$user_id\"");
	if ($result[0]["status"] == 0) {
		respond_json([
			"error" => "users allready gone in"
		]);
		exit;
	}

	$t = date("Y-m-d H-i-s");
	sql_querry("insert into zeiterfassung (benutzer_id,aktion,zeitpunkt) values (\"$user_id\",\"Gehen\",\"$t\")");

	$result = sql_querry("UPDATE user SET status = 0 WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $response
	]);

} elseif ($funktion == "change_lang") {
	$user_id = $data["user_id"] ?? null;
	$lang = $data["lang"] ?? null;

	$_SESSION['lang'] = $lang;

	$result = sql_querry("UPDATE user SET lang = \"$lang\" WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $result
	]);

} elseif ($funktion == "change_password") {
	$user_id = $data["user_id"] ?? null;
	$new_password = $data["new_password"] ?? null;
	$result = sql_querry("UPDATE user SET passwort = \"$new_password\" WHERE id = \"$user_id\"");
	respond_json([
		"respons" => $result
	]);

} elseif ($funktion == "validate_user") {
	include("/home/admin/datenbank_verbindung.php");
	$user_name = $data["user_name"] ?? null;
	$password = $data["password"] ?? null;


	$stmt = $conn->prepare("SELECT passwort FROM user WHERE benutzername = ?");
	$stmt->bind_param("s", $user_name);
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