<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["status" => "error", "message" => "Brak user_id"]);
  exit;
}

$result = $conn->query("SELECT * FROM tasks WHERE deadline IS NOT NULL AND created_by = $_SESSION[user_id]");

$events = [];

while ($row = $result->fetch_assoc()) {
  $events[] = [
    "id" => $row['id'],
    "title" => $row['title'],
    "description" => $row['description'],
    "start" => $row['start_date'] ?? $row['deadline'],
    "end" => $row['deadline'],
    "status" => $row['status'],
    "priority" => $row['priority'],
    "backgroundColor" => $row['backgroundColor']
  ];
}

echo json_encode($events);
