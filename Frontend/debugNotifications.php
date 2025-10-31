<?php
// Debug script to test notification system
session_start();
include "php/connect.php";

echo "<h2>Notification System Debug</h2>";

// Check if user is logged in
echo "<h3>1. Session Check:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in: UserID = " . $_SESSION['user_id'] . "<br>";
    $userID = $_SESSION['user_id'];
} else {
    echo "❌ No user logged in. Please login first.<br>";
    echo '<a href="loginPage.html">Go to Login</a>';
    exit;
}

// Check if notifications table has ExchangeID column
echo "<h3>2. Database Schema Check:</h3>";
try {
    $stmt = $pdo->query("DESCRIBE notifications");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasExchangeID = false;
    echo "<table border='1' style='border-collapse:collapse;'><tr><th>Column</th><th>Type</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
        if ($col['Field'] === 'ExchangeID') {
            $hasExchangeID = true;
        }
    }
    echo "</table>";
    
    if ($hasExchangeID) {
        echo "✅ ExchangeID column exists<br>";
    } else {
        echo "❌ ExchangeID column missing! Run update_notifications.sql<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error checking schema: " . $e->getMessage() . "<br>";
}

// Check if there are any notifications for this user
echo "<h3>3. Notifications Check:</h3>";
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE UserID = ?");
    $stmt->execute([$userID]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "✅ Found {$count} notification(s) for this user<br><br>";
        
        // Display notifications
        $stmt = $pdo->prepare("SELECT n.*, e.status FROM notifications n 
                               LEFT JOIN exchange e ON n.ExchangeID = e.ExchangeID 
                               WHERE n.UserID = ? 
                               ORDER BY n.Notification_Timestamp DESC");
        $stmt->execute([$userID]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ID</th><th>Message</th><th>ExchangeID</th><th>Is Read</th><th>Timestamp</th><th>Exchange Status</th></tr>";
        foreach ($notifications as $n) {
            echo "<tr>";
            echo "<td>{$n['NotificationID']}</td>";
            echo "<td>" . htmlspecialchars($n['Message']) . "</td>";
            echo "<td>{$n['ExchangeID']}</td>";
            echo "<td>" . ($n['Is_read'] ? 'Read' : 'Unread') . "</td>";
            echo "<td>{$n['Notification_Timestamp']}</td>";
            echo "<td>{$n['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No notifications found for UserID {$userID}<br>";
        echo "This is normal if no one has sent you a swap request yet.<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error fetching notifications: " . $e->getMessage() . "<br>";
}

// Check if there are any exchanges
echo "<h3>4. Exchange Records Check:</h3>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exchange");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($count > 0) {
        echo "✅ Found {$count} exchange(s) in database<br><br>";
        
        $stmt = $pdo->query("SELECT e.*, 
                            i.Title as ItemTitle,
                            req.Username as RequesterName,
                            own.Username as OwnerName
                            FROM exchange e
                            LEFT JOIN items i ON e.ItemID = i.ItemID
                            LEFT JOIN users req ON e.RequesterID = req.UserID
                            LEFT JOIN users own ON e.OwnerID = own.UserID
                            ORDER BY e.Exchange_timestamp DESC
                            LIMIT 10");
        $exchanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse:collapse;'>";
        echo "<tr><th>ExchangeID</th><th>Item</th><th>Requester</th><th>Owner</th><th>Offer</th><th>Status</th><th>Time</th></tr>";
        foreach ($exchanges as $ex) {
            echo "<tr>";
            echo "<td>{$ex['ExchangeID']}</td>";
            echo "<td>" . htmlspecialchars($ex['ItemTitle']) . "</td>";
            echo "<td>{$ex['RequesterName']}</td>";
            echo "<td>{$ex['OwnerName']}</td>";
            echo "<td>" . htmlspecialchars($ex['Offer_title']) . "</td>";
            echo "<td>{$ex['status']}</td>";
            echo "<td>{$ex['Exchange_timestamp']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No exchange records found<br>";
        echo "Create a swap request to test the system.<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error fetching exchanges: " . $e->getMessage() . "<br>";
}

// Test the API endpoint
echo "<h3>5. API Test:</h3>";
echo "<button onclick='testAPI()'>Test getNotifications.php API</button>";
echo "<pre id='apiResult'></pre>";

echo "<script>
function testAPI() {
    fetch('php/getNotifications.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
        })
        .catch(error => {
            document.getElementById('apiResult').textContent = 'Error: ' + error;
        });
}
</script>";

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<a href='swapPage.php'>Go to Swap Page (to create a swap request)</a><br>";
echo "<a href='inboxPage.html'>Go to Inbox Page</a><br>";
echo "<a href='userProfile.php'>Go to Profile</a><br>";
?>
