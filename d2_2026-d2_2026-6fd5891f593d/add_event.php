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

$title = isset($data['title']) ? $data['title'] : '';
$desc = isset($data['description']) ? $data['description'] : '';
$startDate = $data['start'];
$deadline = $data['end'];
$color = isset($data['color']) ? $data['color'] : '#3b82f6';
$create_by = $_SESSION['user_id'];
$priority = $data['priority'];
$status = isset($data['status']) ? $data['status'] : 'todo';

$stmt = $conn->prepare("INSERT INTO tasks (created_by, title, description, status, priority, start_date, deadline, created_at, backgroundColor) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
$stmt->bind_param("isssssss", $create_by, $title, $desc, $status, $priority, $startDate, $deadline, $color);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
    exit;
}

echo json_encode(["status" => "ok"]);

$conn->close();
$stmt->close();
