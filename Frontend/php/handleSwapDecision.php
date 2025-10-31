<?php
// This file is included by viewSwapRequest.php when user clicks Accept or Reject
// It handles the swap decision and sends notification back to requester

if (!isset($pdo) || !isset($exchange) || !isset($userID)) {
    exit;
}

$action = $_POST['action'] ?? '';
$exchangeID = $_POST['exchange_id'] ?? 0;

try {
    if ($action === 'accept') {
        // Update exchange status to accepted
        $sql = "UPDATE exchange SET status = 'accepted' WHERE ExchangeID = :ExchangeID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ExchangeID" => $exchangeID]);

        // Mark the item as Exchanged
        $sql = "UPDATE items SET Status = 'Exchanged' WHERE ItemID = :ItemID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ItemID" => $exchange['ItemID']]);

        // Send notification to requester
        $notificationMessage = "Great news! " . $exchange['OwnerUsername'] . " has accepted your swap request for \"" . $exchange['ItemTitle'] . "\"";
        $sql = "INSERT INTO notifications (UserID, Message, ExchangeID, Is_read, Notification_Timestamp) VALUES (:UserID, :Message, :ExchangeID, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":UserID" => $exchange['RequesterID'],
            ":Message" => $notificationMessage,
            ":ExchangeID" => $exchangeID
        ]);

        echo '<script>alert("Swap accepted! The item has been marked as exchanged."); window.location.href="../inboxPage.html";</script>';
        
    } elseif ($action === 'reject') {
        // Update exchange status to declined
        $sql = "UPDATE exchange SET status = 'declined' WHERE ExchangeID = :ExchangeID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ExchangeID" => $exchangeID]);

        // Send notification to requester
        $notificationMessage = "Sorry, " . $exchange['OwnerUsername'] . " has declined your swap request for \"" . $exchange['ItemTitle'] . "\"";
        $sql = "INSERT INTO notifications (UserID, Message, ExchangeID, Is_read, Notification_Timestamp) VALUES (:UserID, :Message, :ExchangeID, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":UserID" => $exchange['RequesterID'],
            ":Message" => $notificationMessage,
            ":ExchangeID" => $exchangeID
        ]);

        echo '<script>alert("Swap rejected. The item remains available."); window.location.href="../inboxPage.html";</script>';
    }
} catch (PDOException $e) {
    error_log('Error processing swap decision: ' . $e->getMessage());
    echo '<script>alert("Error processing your decision. Please try again."); window.location.href="../inboxPage.html";</script>';
}
exit;
?>
