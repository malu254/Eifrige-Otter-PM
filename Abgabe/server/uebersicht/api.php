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
    echo json_encode(['error' => 'Ungültiges JSON']);
    exit;
}

// Beispiel: Zugriff auf die Werte
$funktion = $data['function'] ?? null;

if ($funktion == "foo") {

	$nutzer_id = $data['nutzer_id'] ?? null;


    $stmt = $conn->prepare("SELECT benutzername FROM user WHERE id = $nutzer_id");
    $stmt->execute();
    $stmt->bind_result($benutzer_name);
    $stmt->fetch();
    $stmt->close();

	respond_json([
		"name" => $benutzer_name
	]);

}elseif ($funktion == "get_notifications") {
	$nutzer_id = $data["user_id"] ?? null;

	$nachrichten = sql_querry("SELECT * FROM notification WHERE benutzer_id = $nutzer_id");

	respond_json([
		"notifications" => $nachrichten
	]);

}elseif ($funktion == "get_id_by_name") {
	$nutzer_name = $data["user_name"] ?? null;
	$nutzer_name = "test";
	$nutzer_id = sql_querry("SELECT id FROM user WHERE benutzername = \"$nutzer_name\"");
	respond_json([
		"nutzer_id" => $nutzer_id[0]
	]);
}elseif ($funktion == "get_times") {
	$nutzer_id = $data["user_id"] ?? null;

}elseif ($funktion == "get_lang") {

}elseif ($funktion == "get_sollArbeitszeit") {

}elseif ($funktion == "get_status") {

}elseif ($funktion == "get_fehlzeit") {

}elseif ($funktion == "get_konto") {

}elseif ($funktion == "user_kommen") {

}elseif ($funktion == "user_gehen") {

}elseif ($funktion == "change_lang") {

}elseif ($funktion == "change_password") {

}
?>
