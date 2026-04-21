<?php
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

<div id="tasks-list" class="list">
	<div id="task-list-content">
		<h3>TEST</h3>
	</div>
 </div>