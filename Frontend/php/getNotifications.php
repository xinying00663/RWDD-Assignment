<?php
// Disable HTML error output for API
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Set JSON header first
header('Content-Type: application/json');

// Include database connection
try {
    include "connect.php";
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Check authentication
$userID = $_SESSION['user_id'] ?? null;
if (!$userID) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated', 'notifications' => [], 'unread_count' => 0]);
    exit;
}

try {
    // Fetch notifications for current user, ordered by newest first
    $sql = "SELECT n.NotificationID, n.Message, n.Is_read, n.Notification_Timestamp, n.ExchangeID,
            e.status AS ExchangeStatus
            FROM notifications n
            LEFT JOIN exchange e ON n.ExchangeID = e.ExchangeID
            WHERE n.UserID = :UserID
            ORDER BY n.Notification_Timestamp DESC
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":UserID" => $userID]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $sql = "SELECT COUNT(*) as unread_count FROM notifications WHERE UserID = :UserID AND Is_read = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":UserID" => $userID]);
    $unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount
    ]);

} catch (PDOException $e) {
    // Log error but return valid JSON
    error_log('Error fetching notifications: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage(), 'notifications' => [], 'unread_count' => 0]);
}
?>
