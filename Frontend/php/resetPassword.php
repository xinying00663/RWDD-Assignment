<?php
// reset_password.php
require 'connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 1. Look up the token in the database
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > UTC_TIMESTAMP()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch();

    if ($reset_request) {
        // 2. Token is valid. Show the password reset form.
        ?>
        <form action="updatePassword.php" method="post">
            <h2>Set a New Password</h2>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="password" name="new_password" placeholder="New Password (min 8 characters)" minlength="8" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" minlength="8" required>
            <button type="submit">Reset Password</button>
        </form>
        <?php
    } else {
        echo "The password reset link is invalid or has expired.";
    }
} else {
    echo "Invalid request.";
}
