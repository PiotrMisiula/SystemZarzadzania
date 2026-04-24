<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$result = $conn->query("SELECT * FROM tasks WHERE deadline IS NOT NULL");

$events = [];

while($row = $result->fetch_assoc()) {
  $events[] = [
    "id" => $row['id'],
    "title" => $row['title'],
    "description" => $row['description'],
    "start" => $row['start_date'] ?? $row['deadline'],
    "end" => $row['deadline'],
    "backgroundColor" => $row['backgroundColor']
  ];
}

echo json_encode($events);
?>