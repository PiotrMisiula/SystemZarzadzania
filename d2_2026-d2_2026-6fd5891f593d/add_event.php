<?php
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Brak user_id"]);
    exit;
}

if (!$data || !isset($data['title'], $data['date'])) {
    echo json_encode(["status" => "error", "message" => "Brak wymaganych danych"]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$priority = "low";

$stmt = $conn->prepare("INSERT INTO tasks (created_by, title, description, priority, deadline, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("issss", $_SESSION['user_id'], $data['title'], $data['description'], $priority, $data['date']);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
    exit;    
}

echo json_encode(["status" => "ok"]);
?>