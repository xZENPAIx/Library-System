<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['program_name'])) {
    echo json_encode([]);
    exit();
}

$programName = $_GET['program_name'];
$years = [];

try {
    $stmt = $pdo->prepare("SELECT DISTINCT year_level FROM program_tbl WHERE program_name = ? ORDER BY year_level");
    $stmt->execute([$programName]);
    $years = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

echo json_encode($years);
?>