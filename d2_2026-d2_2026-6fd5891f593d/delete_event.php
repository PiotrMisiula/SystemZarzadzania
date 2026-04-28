<?php
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Brak user_id"]);
    exit;
}

if (!isset($data['id'])) {
    echo json_encode(["status" => "error", "message" => "Brak id zadania"]);
    exit;
}

$id_event = $conn->real_escape_string($data['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND created_by = ?");
$stmt->bind_param("si", $id_event, $user_id);

if  (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
    exit;
}

if ($stmt->affected_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Nie znaleziono zadania lub brak uprawnień"]);
    exit;
}

echo json_encode(["status" => "ok"]);

$stmt->close();
$conn->close();
?>