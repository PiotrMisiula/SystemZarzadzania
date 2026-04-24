<?php
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Brak user_id"]);
    exit;
}

if (!$data || !isset($data['title'], $data['start'])) {
    echo json_encode(["status" => "error", "message" => "Brak wymaganych danych"]);
    exit;
}

$title = $conn->real_escape_string($data['title']);
$desc = $conn->real_escape_string($data['description']);
$startDate = $data['start'];   
$deadline = $data['end'];
$color = $conn->real_escape_string($data['color']);
$create_by = $_SESSION['user_id'];
$priority = $data['priority'];

$stmt = $conn->prepare("INSERT INTO tasks (created_by, title, description, priority, start_date, deadline, created_at, backgroundColor) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
$stmt->bind_param("issssss", $create_by, $title, $desc, $priority, $startDate, $deadline, $color);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
    exit;    
}

echo json_encode(["status" => "ok"]);
?>