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
$role = $_SESSION['role'] ?? 'user';
if (!$userId) {
    http_response_code(401);
    echo "Authentication required.";
    exit;
}

// Only admins can add programs
if ($role !== 'admin') {
    http_response_code(403);
    echo "Admin privileges required.";
    exit;
}

$programName = $_POST['eventName'] ?? '';
$programLocation = $_POST['eventLocationSearch'] ?? '';
$programStartDate = $_POST['eventDate'] ?? '';
$programLat = $_POST['eventLocationLat'] ?? null;
$programLng = $_POST['eventLocationLng'] ?? null;
$programEndDate = $_POST['eventDateTo'] ?? '';
$programDescription = $_POST['eventDescription'] ?? '';
$coordinatorName = $_POST['coordinatorName'] ?? '';
$coordinatorEmail = $_POST['coordinatorEmail'] ?? '';
$coordinatorPhone = $_POST['coordinatorPhone'] ?? '';

$sectionTitles = $_POST['sectionTitle'] ?? [];
$sectionDescriptions = $_POST['sectionDescription'] ?? [];

$sections = [];
if (is_array($sectionTitles) || is_array($sectionDescriptions)) {
    $count = max(count((array)$sectionTitles), count((array)$sectionDescriptions));
    for ($i = 0; $i < $count; $i++) {
        $t = trim($sectionTitles[$i] ?? '');
        $d = trim($sectionDescriptions[$i] ?? '');
        if ($t !== '' || $d !== '') $sections[] = ['title' => $t, 'description' => $d];
    }
} else {
    if ($sectionTitles !== '' || $sectionDescriptions !== '') {
        $sections[] = ['title' => $sectionTitles, 'description' => $sectionDescriptions];
    }
}

// Basic validation
if ($programName === '' || $programStartDate === '' || $programEndDate === '') {
    http_response_code(400);
    echo "Required fields are missing.";
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not available.");
    }

    $pdo->beginTransaction();


    $query = "INSERT INTO program 
              (userID, Program_name, Program_location, Event_date_start, Event_date_end, 
               Program_description, Coordinator_name, Coordinator_email, Coordinator_phone,
               latitude, longitude, created_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $programName,
        $programLocation,
        $programStartDate,
        $programEndDate,
        $programDescription,
        $coordinatorName,
        $coordinatorEmail,
        $coordinatorPhone,
        $programLat,
        $programLng
    ]);

    $programId = (int)$pdo->lastInsertId();

    if (!empty($sections)) {
        $sectionQuery = "INSERT INTO program_sections (program_id, section_title, section_description) VALUES (?, ?, ?)";
        $sectionStmt = $pdo->prepare($sectionQuery);
        foreach ($sections as $s) {
            $sectionStmt->execute([
                $programId,
                $s['title'],
                $s['description']
            ]);
        }
    }

    $pdo->commit();

    header('Location: ../homePage.php');
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
    if (isset($sectionStmt)) $sectionStmt = null;
    $pdo = $pdo ?? null;
}
?>
