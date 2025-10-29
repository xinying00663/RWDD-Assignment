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

$communityId = isset($_POST['community_id']) ? (int)$_POST['community_id'] : 0;
if ($communityId <= 0) {
    http_response_code(400);
    echo 'Invalid community id.';
    exit;
}

try {
    // Fetch post details to check ownership
    $stmt = $pdo->prepare('SELECT user_id, Community_media FROM community WHERE Community_id = ?');
    $stmt->execute([$communityId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo 'Post not found.';
        exit;
    }

    // Check if user owns this post or is admin
    if ($post['user_id'] != $userId && $role !== 'admin') {
        http_response_code(403);
        echo 'You can only delete your own posts.';
        exit;
    }

    $pdo->beginTransaction();
    $del = $pdo->prepare('DELETE FROM community WHERE Community_id = ?');
    $del->execute([$communityId]);
    $pdo->commit();

    // Attempt to delete media file if exists
    if ($post && !empty($post['Community_media'])) {
        $normalized = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $post['Community_media']);
        if ($normalized && strpos($normalized, realpath(__DIR__ . '/../upload/community')) === 0) {
            @unlink($normalized);
        }
    }

    header('Location: ../communityPage.php');
    exit;

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Delete community error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Database error.';
    exit;
}
