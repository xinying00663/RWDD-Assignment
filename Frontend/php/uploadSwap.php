<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connect.php";
// echo "Database connected successfully!";

session_start();

if($_SERVER["REQUEST_METHOD"]=="POST"){
    echo '<script>alert("Swap page request received");</script>';

    $title=$_POST["swapTitle"]??"";
    $category=$_POST["swapCategory"]??"";
    $itemCondition=$_POST["swapCondition"]??"";
    $preferredExchange=$_POST["swapPreferred"]??"";
    $description=$_POST["swapDetails"]??"";
    $category=$_POST["swapCategory"]??"";
    $userID=$_SESSION["user_id"];

    $image_path=NULL;
    if(isset($_FILES["swapMedia"])&& $_FILES["swapMedia"]["error"]===0){
        if($_FILES["swapMedia"]["size"]>262144000){
            echo '<script>
                    alert("File size exceeds the 250MB limit.");
                    window.history.back();
                </script>';
                exit;
        }else{
            $upload_dir="upload/swapItems/";
            if(!is_dir($upload_dir)){
                mkdir($upload_dir,0777,true);
            }

            $file_extension=strtolower(pathinfo($_FILES["swapMedia"]["name"],PATHINFO_EXTENSION));
            $allowed_extension=["jpg","jpeg","png","gif","mp4","mov","avi"];

            if(in_array($file_extension,$allowed_extension)){
                $filename=uniqid().".".$file_extension;
                $target_path=$upload_dir.$filename;

                if(move_uploaded_file($_FILES["swapMedia"]["tmp_name"],$target_path)){
                    $image_path=$target_path;
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
    
    if(!isset($error)){
        $stmt=$pdo->prepare("INSERT INTO items(ItemID,Title,Category,Description,Item_condition,Preferred_exchange,Image_path,Status,UserID) VALUES(?,?,?,?,?,?,?,?,?)");
        if($stmt->execute([$user_id,$title,$category,$description,$itemCondition,$preferredExchange,$image_path])){
            header("Location:swapPage.php?success=item_added");
            exit;
        }else{
            echo'<script>
                   alert("Database error,please try again.");
                   window.history.back();
                </script>';
                exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List a Swap Item</title>
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/uploadPage.css">
</head>
<body data-page="swap-upload">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card upload-card">
            <div class="section-header">
                <h2>List a swap item</h2>
                <p>Share a photo or short clip of what you'd like to swap and let neighbours know what you're hoping for in return.</p>
            </div>
            <form class="upload-form" action="#" method="post">
                <div class="upload-form__grid">
                    <div class="input-group">
                        <label for="swapTitle">Item name</label>
                        <input type="text" id="swapTitle" name="swapTitle" placeholder="e.g. Balcony herb starter kit" required>
                    </div>
                    <div class="input-group">
                        <label for="swapCategory">Category</label>
                        <select id="swapCategory" name="swapCategory" required>
                            <option value="home-grown">Home-grown</option>
                            <option value="eco-friendly">Eco-friendly</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="swapCondition">Condition</label>
                        <input type="text" id="swapCondition" name="swapCondition" placeholder="e.g. Rooted cuttings, lightly used">
                    </div>
                    <div class="input-group">
                        <label for="swapPreferred">Preferred exchange</label>
                        <input type="text" id="swapPreferred" name="swapPreferred" placeholder="e.g. Open to seeds or compost bins">
                    </div>
                    <div class="input-group input-group--full">
                        <label for="swapMedia">Upload media</label>
                        <input type="file" id="swapMedia" name="swapMedia" accept="image/*,video/*" required>
                        <p class="input-help">Select clear images or short videos (max 250&nbsp;MB) to help neighbours see the item.</p>
                    </div>
                    <div class="input-group input-group--full">
                        <label for="swapDetails">Item details</label>
                        <textarea id="swapDetails" name="swapDetails" rows="5" placeholder="Tell people what makes this item special and any pick-up info." required></textarea>
                    </div>
                </div>
                <div class="upload-form__footer">
                    <p class="helper-text">We'll publish your listing once it meets community swap guidelines.</p>
                    <div class="upload-form__actions">
                        <button type="button" class="button-cancel" onclick="window.location.href='swapPage.html'">Cancel</button>
                        <button type="submit">Post listing</button>
                    </div>
                </div>
            </form>
        </section>
    </main>
    <script src="script/sidebar.js?v=2"></script>
    <script src="script/uploadShared.js" defer></script>
    <script src="script/uploadSwap.js" defer></script>
</body>
</html>