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

$EnergyTitle = $_POST['energyTitle'] ?? '';
$EnergyCategory = $_POST['energyCategory'] ?? '';
$EnergyContributor = $_POST['energyContributor'] ?? '';
$EnergyDuration = $_POST['energyDuration'] ?? '';
$EnergySummary = $_POST['energySummary'] ?? '';
$EnergyLink = $_POST['energyLink'] ?? '';

if ($EnergyTitle === '' || $EnergyCategory=== '') {
    http_response_code(400);
    echo "Required fields are missing.";
    exit;
}

$Energymedia=NULL;
if(isset($_FILES["energyMedia"])&& $_FILES["energyMedia"]["error"]===0){
        if($_FILES["energyMedia"]["size"]>262144000){
            echo '<script>
                    alert("File size exceeds the 250MB limit.");
                    window.history.back();
                </script>';
                exit;
        }else{
            $upload_dir="upload/energyGrid/";
            if(!is_dir($upload_dir)){
                mkdir($upload_dir,0777,true);
            }

            $file_extension=strtolower(pathinfo($_FILES["energyMedia"]["name"],PATHINFO_EXTENSION));
            $allowed_extension=["jpg","jpeg","png","gif","mp4","mov","avi"];

            if(in_array($file_extension,$allowed_extension)){
                $filename=uniqid().".".$file_extension;
                $target_path=$upload_dir.$filename;

                if(move_uploaded_file($_FILES["energyMedia"]["tmp_name"],$target_path)){
                    $EnergyMedia=$target_path;
                }else{
                    echo '<script>
                            alert("Failed to upload file.Please try again.");
                            window.history.back();
                        </script>';
                        exit;
                }
            }else{
                echo '<script>
                        alert("Invalid file type. Please upload again.");
                        window.history.back();
                    </script>';
                    exit;
            }
        }
    }else{
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


    $query = "INSERT INTO energy 
              (user_id, Energy_title, Energy_category, Energy_contributor, Energy_duration, 
               Energy_media, Energy_summary, Energy_link)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $userId,
        $EnergyTitle,
        $EnergyCategory,
        $EnergyContributor,
        $EnergyDuration,
        $EnergyMedia,
        $EnergySummary,
        $EnergyLink
    ]);

    $pdo->commit();

    header('Location: ../uploadEnergy.html');
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


?>