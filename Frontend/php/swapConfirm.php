<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connect.php";
// echo "Database connected successfully!";

session_start();

// Handle swap request submission
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["swap_request"])){
    $itemID=$_POST["item_id"] ?? $_GET["item_id"] ?? 0;
    $offerTitle=$_POST["offer_title"];
    $offerDescription=$_POST["offer_description"];
    $offerNotes=$_POST["offer_notes"];
    $requesterID=$_SESSION["user_id"];

    $offerImagePath="";
    if(isset($_FILES["offerMedia"])&& $_FILES["offerMedia"]["error"]===0){
        $target_dir="upload/offers/";
        if(!is_dir($target_dir)){
            mkdir($target_dir,0777,true);
        }
        $image_name=time().'_offer_'.basename($_FILES["offerMedia"]["name"]);
        $offer_image_path=$target_dir.$image_name;
        move_uploaded_file($_FILES["offerMedia"]["tmp_name"],$offer_image_path);

        try{
            $sql="SELECT UserID,Title from items WHERE id=:ItemID and status='Available'";
            $stmt=$pdo->prepare($sql);
            $stmt=$pdo->execute([":ItemID"=>$itemID]);
            $item=$stmt->fetch(PDO::FETCH_ASSOC);

            if($item){
                $ownerID=$item["UserID"];
                $itemTitle=$item["Title"];

                $sql="SELECT id FROM exchange WHERE ItemID=:ItemID AND RequesterID=:RequesterID AND Status='Pending'";
                $stmt=$pdo->prepare($sql);
                $stmt->execute(["ItemID"=>$itemID,"RequesterID"=>$requesterID]);

                if($stmt->rowCount()==0){
                    $sql="INSERT INTO exchange(ItemID,RequesterID,OwnerID,OfferID,Offer_Description,Offer_Notes,Offer_Image,Status,Exchange_Timestamp)Values(:ItemID,:RequesterID,:OwnerID,:OfferID,:Offer_Description,:Offer_Notes,:Offer_Image,'Pending',NOW())";
                    $stmt=$pdo->prepare($sql);
                    $stmt->execute([":ItemID"=>$itemID,":RequesterID"=>$requesterID,":OwnerID"=>$ownerID,":OfferID"=>$offerID,":Offer_Description"=>$offerDescription,":Offer_Notes"=>$offerNotes,":Offer_Image"=>$offerImage]);
                    echo '<script>alert("Swap request sent successfully!")</script>';
                }else{
                    echo '<script>alert("You have already sent a swap request for this item.")</script>';
                }
            }else{
                echo '<script>alert("The selected item is no longer available or not found.")</script>';
            }
        }catch(PDOException $e){
                echo '<script>alert("Error: '.$e->getMessage().'")</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Swap</title>
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/uploadPage.css">
    <link rel="stylesheet" href="../styles/swapConfirm.css">
</head>
<body data-page="swap-confirm">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card swap-confirm-card" aria-labelledby="swapConfirmHeading">
            <div class="section-header">
                <h2 id="swapConfirmHeading">Swap for <span id="selectedItemTitle">this item</span></h2>
                <p>Share what you can offer in return so the owner can review and confirm the swap.</p>
            </div>
            <div class="swap-confirm__content">
                <article class="swap-confirm__item" aria-live="polite">
                    <div class="swap-confirm__media">
                        <img id="selectedItemImage" src="../Pictures/landingPage/swap-item-pic.jpg" alt="Selected swap item">
                    </div>
                    <div class="swap-confirm__details">
                        <span class="swap-confirm__tag" id="selectedItemTag">Swap item</span>
                        <h3 id="selectedItemName">Selected swap item</h3>
                        <p id="selectedItemDescription">Choose a listing from the swap feed to see the details here.</p>
                    </div>
                </article>
                <form id="swapOfferForm" class="upload-form swap-confirm__form" action="#" method="post" enctype="multipart/form-data">
                    <div class="upload-form__grid">
                        <div class="input-group input-group--full">
                            <label for="offerMedia">Upload your item photo</label>
                            <input type="file" id="offerMedia" name="offerMedia" accept="image/*" required>
                            <p class="input-help">Share a clear image so your neighbour can confirm the swap quickly.</p>
                        </div>
                        <div class="input-group">
                            <label for="offerTitle">Your item name</label>
                            <input type="text" id="offerTitle" name="offerTitle" placeholder="e.g. Handwoven market tote" required>
                        </div>
                        <div class="input-group input-group--full">
                            <label for="offerDescription">Describe your item</label>
                            <textarea id="offerDescription" name="offerDescription" rows="5" placeholder="Explain the condition, pick-up timing, or what makes it a good swap." required></textarea>
                        </div>
                        <div class="input-group input-group--full">
                            <label for="offerNotes">Message to the owner (optional)</label>
                            <textarea id="offerNotes" name="offerNotes" rows="4" placeholder="Add extra context or propose a meeting spot."></textarea>
                        </div>
                    </div>
                    <div class="upload-form__footer">
                        <p class="helper-text">We'll notify the owner of <span id="selectedItemNameInline">this item</span> once you confirm.</p>
                        <div class="upload-form__actions">
                            <button type="button" class="button-cancel" onclick="window.location.href='swapPage.html'">Back to swap feed</button>
                            <button type="submit">Confirm swap request</button>
                        </div>
                    </div>
                    <div class="swap-confirm__success" id="swapConfirmSuccess" role="status" aria-live="polite" tabindex="-1">
                        <strong>Swap request sent!</strong>
                        <p>We'll let you know in your inbox when the owner responds.</p>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script src="script/sidebar.js?v=2"></script>
    <script src="script/swapConfirm.js" defer></script>
</body>
</html>
