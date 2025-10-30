<?php
// update_password.php
require 'connect.php';

// Set PHP timezone explicitly (use your preferred zone)

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    // 1) Get the token row WITHOUT checking expiry in SQL
    $stmt = $pdo->prepare("SELECT user_id, expires_at FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset_request) {
        echo "Token not found in DB.<br>";
        exit;
    }


    // 2) Parse expiry from DB and compare in PHP using same timezone
    // Important: assume DB stores DATETIME in the same logical timezone as you use below.
    // If DB stores UTC, use new DateTimeZone('UTC') instead.
    $dbExpires = new DateTime($reset_request['expires_at'], new DateTimeZone('UTC'));
    $now = new DateTime('now', new DateTimeZone('UTC'));


    echo "PHP now: " . $now->format('Y-m-d H:i:s') . "<br>";
    echo "DB expiry (as DateTime in UTC): " . $dbExpires->format('Y-m-d H:i:s') . "<br>";

    if ($dbExpires < $now) {
        echo "Token is expired according to PHP comparison.<br>";
        exit;
    }

    // 3) Check password match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.<br>";
        exit;
    }

    // 4) All good — update password
    $user_id = $reset_request['user_id'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE UserId = ?");
    $stmt->execute([$hashed_password, $user_id]);

    // 5) Delete token to prevent reuse
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);

    echo '<script>
    alert("Your password has been successfully reset.");
    window.location.href = "../loginPage.html";
    </script>';
} else {
    echo '<script>
    alert("Invalid request.");
    window.location.href = "../loginPage.html";
    </script>';
}
