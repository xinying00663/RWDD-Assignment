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

$communityTitle = $_POST['communityTitle'] ?? '';
$communityCategory = $_POST['communityCategory'] ?? '';
$communityContact = $_POST['communityContact'] ?? '';
$communityLocation = $_POST['communityLocation'] ?? '';
$communitySummary = $_POST['communitySummary'] ?? '';
$communityLink = $_POST['communityLink'] ?? '';

if ($communityTitle === '' || $communityCategory === '') {
    http_response_code(400);
    echo "Required fields are missing.";
    exit;
}

$communityMedia = NULL;
if (isset($_FILES["communityMedia"]) && $_FILES["communityMedia"]["error"] === 0) {
    if ($_FILES["communityMedia"]["size"] > 1000000000) {
        echo '<script>
                alert("File size exceeds the 1GB limit.");
                window.history.back();
            </script>';
        exit;
    } else {
        $upload_dir = "upload/community/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["communityMedia"]["name"], PATHINFO_EXTENSION));
        $allowed_extension = ["jpg", "jpeg", "png", "gif", "mp4", "mov", "avi"];

        if (in_array($file_extension, $allowed_extension)) {
            $filename = uniqid() . "." . $file_extension;
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES["communityMedia"]["tmp_name"], $target_path)) {
                $communityMedia = $target_path;
            } else {
                echo '<script>
                        alert("Failed to upload file. Please try again.");
                        window.history.back();
                    </script>';
                exit;
            }
        } else {
            echo '<script>
                    alert("Invalid file type. Please upload again.");
                    window.history.back();
                </script>';
            exit;
        }
    }
} else {
    echo '<script>
            alert("No file uploaded. Please select a file to upload.");
            window.history.back();
        </script>';
    exit;
}

try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not available.");
    }

    $pdo->beginTransaction();

    $query = "INSERT INTO community (user_id, Community_title, Community_category, Community_contributor, Community_location, Community_media, Community_summary, Community_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $communityTitle,
        $communityCategory,
        $communityContact,
        $communityLocation,
        $communityMedia,
        $communitySummary,
        $communityLink
    ]);

    $pdo->commit();

    header('Location: ../communityPage.php');
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
    if (isset($stmt)) $stmt = null;
    $pdo = $pdo ?? null;
}
?>
