<?php
// send_reset_link.php
require '../vendor/autoload.php'; // Path to PHPMailer if using Composer
require 'connect.php'; // Your database connection script

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['email'])) {
    $email = $_POST['email'];

    // 1. Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT UserId FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['UserId'];

        // 2. Generate and store a unique, secure token
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTime('now', new DateTimeZone('UTC')))
        ->modify('+1 hour')
        ->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $token, $expires]);

        // 3. Send the email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings...
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'creating4fun12@gmail.com';
            $mail->Password = 'kwmm fgap zvlq uhlu';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@yourwebsite.com', 'EcoGo Reset Password');
            $mail->addAddress($email);

            // Content
            $reset_link = "http://localhost/RWDD-Assignment/Frontend/php/resetPassword.php?token=$token";
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Hi,<br>Click this link to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();
            echo '<script>
                    alert("A password reset link has been sent to your email.");
                    window.location.href = "../landingPage.html";
                  </script>';
        } catch (Exception $e) {
            echo '<script>
                    alert("Failed to send email. Please try again later.");
                    window.history.back();
                  </script>';
        }
    } else {
        // Send a generic message to prevent revealing if an email exists
        echo "If an account with that email exists, a password reset link has been sent.";
    }
}
