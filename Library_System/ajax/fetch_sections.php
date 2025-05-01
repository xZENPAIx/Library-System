<?php
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['program_name']) || !isset($_GET['year_level'])) {
    echo json_encode([]);
    exit();
}

$programName = $conn->real_escape_string($_GET['program_name']);
$yearLevel = $conn->real_escape_string($_GET['year_level']);
$sections = [];

$query = "SELECT DISTINCT section FROM program_tbl WHERE program_name = ? AND year_level = ? ORDER BY section";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $programName, $yearLevel);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode($sections);
$stmt->close();
?>