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

$swapTitle = $_POST['swapTitle'] ?? '';
$swapCategory = $_POST['swapCategory'] ?? '';
$swapCondition = $_POST['swapCondition'] ?? '';
$swapPreferred = $_POST['swapPreferred'] ?? '';
$swapDetails = $_POST['swapDetails'] ?? '';

if ($swapTitle === '' || $swapCategory === '') {
    http_response_code(400);
    echo "Required fields are missing.";
    exit;
}

$swapMedia = NULL;
if (isset($_FILES["swapMedia"]) && $_FILES["swapMedia"]["error"] === 0) {
    if ($_FILES["swapMedia"]["size"] > 1000000000) {
        echo '<script>
                alert("File size exceeds the 250MB limit.");
                window.history.back();
            </script>';
        exit;
    } else {
        $upload_dir = "upload/swapItems/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["swapMedia"]["name"], PATHINFO_EXTENSION));
        $allowed_extension = ["jpg", "jpeg", "png", "gif", "mp4", "mov", "avi"];

        if (in_array($file_extension, $allowed_extension)) {
            $filename = uniqid() . "." . $file_extension;
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES["swapMedia"]["tmp_name"], $target_path)) {
                $swapMedia = $target_path;
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

    $query = "INSERT INTO items (UserID, Title, Category, Description, Item_condition, Preferred_exchange, Image_path, Status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $swapTitle,
        $swapCategory,
        $swapDetails,
        $swapCondition,
        $swapPreferred,
        $swapMedia
    ]);

    $pdo->commit();

    header('Location: ../swapPage.php');
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