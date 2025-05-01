<?php
require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['program_name']) || !isset($_GET['year_level'])) {
    echo json_encode([]);
    exit();
}

$programName = $_GET['program_name'];
$yearLevel = $_GET['year_level'];
$sections = [];

try {
    $stmt = $pdo->prepare("SELECT DISTINCT section FROM program_tbl WHERE program_name = ? AND year_level = ? ORDER BY section");
    $stmt->execute([$programName, $yearLevel]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

echo json_encode($sections);
?>