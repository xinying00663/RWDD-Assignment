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

$energyId = isset($_POST['energy_id']) ? (int)$_POST['energy_id'] : 0;
if ($energyId <= 0) {
    http_response_code(400);
    echo 'Invalid energy id.';
    exit;
}

try {
    // Fetch media path to optionally delete file
    $stmt = $pdo->prepare('SELECT Energy_media FROM energy WHERE Energy_id = ?');
    $stmt->execute([$energyId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM energy WHERE Energy_id = ?');
    $del->execute([$energyId]);
    $pdo->commit();

    // Attempt to delete media file if exists and inside allowed upload dir
    if ($row && !empty($row['Energy_media'])) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . $row['Energy_media'];
        // If stored path is relative like 'upload/energyGrid/filename.ext', resolve from php dir
        $normalized = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $row['Energy_media']);
        if ($normalized && strpos($normalized, realpath(__DIR__ . '/../upload/energyGrid')) === 0) {
            @unlink($normalized);
        }
    }

    header('Location: ../energyPage.php');
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Delete energy error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Database error.';
    exit;
}
