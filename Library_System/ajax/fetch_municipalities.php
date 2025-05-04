<?php
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['province'])) {
    echo json_encode([]);
    exit();
}

$province = $conn->real_escape_string($_GET['province']);
$municipalities = [];

$query = "SELECT DISTINCT municipality FROM address_tbl WHERE province = ? ORDER BY municipality";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $province);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $municipalities[] = $row;
}

echo json_encode($municipalities);
$stmt->close();
?>