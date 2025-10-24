<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Only POST allowed";
    exit;
}

include "connect.php";


$programName = $_POST['eventName'] ?? '';
$programLocation = $_POST['eventLocationSearch'] ?? '';
$programStartDate = $_POST['eventDate'] ?? '';
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
    // single values (old form) — keep as string
    if ($sectionTitles !== '' || $sectionDescriptions !== '') {
        $sections[] = ['title' => $sectionTitles, 'description' => $sectionDescriptions];
    }
}

// Basic validation (you can expand)
if ($programName === '' || $programStartDate === '' || $programEndDate === '') {
    http_response_code(400);
    echo "Required fields are missing.";
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO program 
        (Program_name, Program_location, Event_date_start, Event_date_end, Program_description, Coordinator_name, Coordinator_email, 
        Coordinator_phone, Section_title, Section_description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        'ssssssssss',
        $programName,
        $programLocation,
        $programStartDate,
        $programEndDate,
        $programDescription,
        $coordinatorName,
        $coordinatorEmail,
        $coordinatorPhone,
        $section_titles_json,
        $sections_json
    );
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header('Location: ../homePage.php');
        // optionally redirect: header('Location: ../homePage.html');
    } else {
        http_response_code(500);
        echo "Insert failed.";
    }

    $stmt->close();
    $db->close();
} catch (mysqli_sql_exception $e) {
    error_log('DB query error: ' . $e->getMessage());
    http_response_code(500);
    echo "Database error.";
    exit;
}
?>