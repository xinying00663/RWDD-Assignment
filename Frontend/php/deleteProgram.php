<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Only POST allowed';
    exit;
}

session_start();
include 'connect.php';

$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'user';
if (!$userId) {
    http_response_code(401);
    echo 'Authentication required.';
    exit;
}
if ($role !== 'admin') {
    http_response_code(403);
    echo 'Admin privileges required.';
    exit;
}

$programId = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
if ($programId <= 0) {
    http_response_code(400);
    echo 'Invalid program id.';
    exit;
}

try {
    $pdo->beginTransaction();

    // Delete related sections first if no ON DELETE CASCADE
    $stmt = $pdo->prepare('DELETE FROM program_sections WHERE program_id = ?');
    $stmt->execute([$programId]);

    // Delete the program
    $stmt = $pdo->prepare('DELETE FROM program WHERE ProgramID = ?');
    $stmt->execute([$programId]);

    $pdo->commit();

    header('Location: ../homePage.php');
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Delete program error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Database error.';
    exit;
}
