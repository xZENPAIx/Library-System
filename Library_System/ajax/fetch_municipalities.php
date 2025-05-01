<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['province'])) {
    echo json_encode([]);
    exit();
}

$province = $_GET['province'];
$municipalities = [];

try {
    $stmt = $pdo->prepare("SELECT DISTINCT municipality FROM address_tbl WHERE province = ? ORDER BY municipality");
    $stmt->execute([$province]);
    $municipalities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

echo json_encode($municipalities);
?>