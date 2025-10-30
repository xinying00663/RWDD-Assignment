<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Only POST allowed";
    exit;
}

session_start(); 

include "connect.php";


$userId = $_SESSION['user_id'] ?? null;  
if (!$userId) {
    http_response_code(401);
    echo "Authentication required.";
    exit;
}

// Block admins from registering
$role = $_SESSION['role'] ?? 'user';
if ($role === 'admin') {
    http_response_code(403);
    echo "Admins cannot register for events.";
    exit;
}

$programId = $_POST['programId'] ?? null;
$CustomerName = $_POST['participantName'] ?? '';
$CustomerEmail = $_POST['participantEmail'] ?? '';
$CustomerPhone = $_POST['participantPhone'] ?? '';

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not available.");
    }
    if (!$programId) {
        throw new Exception("Program ID is missing.");
    }

    $pdo->beginTransaction();

    $query = "INSERT INTO program_customer
              (User_id, Program_id, Customer_name, Customer_email, Customer_phone)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $programId,
        $CustomerName,
        $CustomerEmail,
        $CustomerPhone
    ]);


    $pdo->commit();

    header('Location: ../programDetail.php?id=' . $programId . '&registered=success');
    exit;

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('DB error: ' . $e->getMessage());
    http_response_code(500);
    echo "Database error.";
    exit;
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo $e->getMessage();
    exit;
} finally {
    // PDO statements/connection cleanup
    if (isset($stmt)) $stmt = null;
    $pdo = $pdo ?? null;
}