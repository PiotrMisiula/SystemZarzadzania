<?php 
session_start();

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Brak user_id"]);
    exit;
}

if (!isset($data['id']) || !isset($data['title'])) {
    echo json_encode(["status" => "error", "message" => "Brak wymaganych danych"]);
    exit;
}

$id_event = $conn->real_escape_string($data['id']);
$title = $conn->real_escape_string($data['title']);
$desc = isset($data['description']) ? $data['description'] : '';
$startDate = $data['start'];   
$deadline = $data['end'];
$color = $conn->real_escape_string($data['color']);
$create_by = $_SESSION['user_id'];
$priority = isset($data['priority']) ? $data['priority'] : '';
$status = isset($data['status']) ? $data['status'] : 'todo';

$stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, start_date = ?, deadline = ?, backgroundColor = ? WHERE id = ? AND created_by = ?");
$stmt->bind_param("sssssssii", $title, $desc, $status, $priority, $startDate, $deadline, $color, $id_event, $create_by);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "message" => "Zadanie zaktualizowane"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}

$stmt->close();
$conn->close();

?>