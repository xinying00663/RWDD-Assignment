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

$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
if ($itemId <= 0) {
    http_response_code(400);
    echo 'Invalid item id.';
    exit;
}

try {
    // Fetch item details to check ownership
    $stmt = $pdo->prepare('SELECT UserID, Image_path FROM items WHERE ItemID = ?');
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        http_response_code(404);
        echo 'Item not found.';
        exit;
    }

    // Check if user owns this item or is admin
    if ($item['UserID'] != $userId && $role !== 'admin') {
        http_response_code(403);
        echo 'You can only delete your own items.';
        exit;
    }

    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM items WHERE ItemID = ?');
    $del->execute([$itemId]);
    $pdo->commit();

    // Attempt to delete media file if exists
    if ($item && !empty($item['Image_path'])) {
        $normalized = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $item['Image_path']);
        if ($normalized && strpos($normalized, realpath(__DIR__ . '/../upload/swapItems')) === 0) {
            @unlink($normalized);
        }
    }

    header('Location: ../swapPage.php');
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Delete swap error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Database error.';
    exit;
}
