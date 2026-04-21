<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "systemzarzadzania");

$result = $conn->query("SELECT title, deadline FROM tasks");

$events = [];

while($row = $result->fetch_assoc()) {
  $events[] = [
    "title" => $row['title'],
    "start" => $row['deadline']
  ];
}

echo json_encode($events);
?>