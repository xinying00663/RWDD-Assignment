<?php
// update_password.php
require 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Verify token and check for expiration
    $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch();

    if ($reset_request && $new_password === $confirm_password) {
        $user_id = $reset_request['user_id'];
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 2. Update the user's password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE UserId = ?");
        $stmt->execute([$hashed_password, $user_id]);

        // 3. Delete the token to prevent reuse
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        echo "Your password has been successfully reset.";
    } else {
        echo "Password reset failed. Please try again.";
    }
}
