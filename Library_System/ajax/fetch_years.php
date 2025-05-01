<?php
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['program_name'])) {
    echo json_encode([]);
    exit();
}

$programName = $conn->real_escape_string($_GET['program_name']);
$years = [];

$query = "SELECT DISTINCT year_level FROM program_tbl WHERE program_name = ? ORDER BY year_level";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $programName);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $years[] = $row;
}

echo json_encode($years);
$stmt->close();
?>