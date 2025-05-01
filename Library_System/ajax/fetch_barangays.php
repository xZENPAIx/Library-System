<?php
require_once __DIR__ . '/../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['municipality']) || !isset($_GET['province'])) {
    echo json_encode([]);
    exit();
}

$municipality = $conn->real_escape_string($_GET['municipality']);
$province = $conn->real_escape_string($_GET['province']);
$barangays = [];

$query = "SELECT DISTINCT brgy FROM address_tbl WHERE municipality = ? AND province = ? ORDER BY brgy";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $municipality, $province);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $barangays[] = $row;
}

echo json_encode($barangays);
$stmt->close();
?>