<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['municipality']) || !isset($_GET['province'])) {
    echo json_encode([]);
    exit();
}

$municipality = $_GET['municipality'];
$province = $_GET['province'];
$barangays = [];

try {
    $stmt = $pdo->prepare("SELECT DISTINCT brgy FROM address_tbl WHERE municipality = ? AND province = ? ORDER BY brgy");
    $stmt->execute([$municipality, $province]);
    $barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

echo json_encode($barangays);
?>