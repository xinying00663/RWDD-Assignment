<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "php/connect.php";

// Block admins from swapping
$role = $_SESSION['role'] ?? 'user';
if ($role === 'admin') {
    echo '<script>alert("Admins cannot swap items."); window.location.href="swapPage.php";</script>';
    exit;
}

// Resolve selected item (GET) for display
$itemId = 0;
if (isset($_GET['id'])) {
    $itemId = (int)$_GET['id'];
} elseif (isset($_GET['item_id'])) {
    $itemId = (int)$_GET['item_id'];
}

$itemData = null;
if ($itemId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT i.ItemID, i.UserID, i.Title, i.Category, i.Description, i.Image_path, i.Status, u.Username AS OwnerUsername
                               FROM items i
                               LEFT JOIN users u ON i.UserID = u.UserID
                               WHERE i.ItemID = ? LIMIT 1");
        $stmt->execute([$itemId]);
        $itemData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Fetch item for confirm error: ' . $e->getMessage());
    }
}

$isOwner = false;
if ($itemData && isset($_SESSION['user_id'])) {
    $isOwner = ((int)$itemData['UserID'] === (int)$_SESSION['user_id']);
}

// Handle swap request submission
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["swap_request"])){
    $itemID=$_POST["item_id"] ?? $_GET["item_id"] ?? $_GET['id'] ?? 0;
    $offerTitle=$_POST["offer_title"] ??"";
    $offerDescription=$_POST["offer_description"] ??"";
    $offerNotes=$_POST["offer_notes"] ??"";
    $requesterID=$_SESSION["user_id"] ?? NULL;

    $offerImagePath="";
    if(isset($_FILES["offerMedia"])&& $_FILES["offerMedia"]["error"]===0){
        $target_dir="upload/offers/";
        if(!is_dir($target_dir)){
            mkdir($target_dir,0777,true);
        }
        $image_name=time().'_offer_'.basename($_FILES["offerMedia"]["name"]);
        $offerImagePath=$target_dir.$image_name;
        if(!move_uploaded_file($_FILES["offerMedia"]["tmp_name"], $offerImagePath)){
            echo '<script>alert("Failed to upload image. Please try again.")</script>';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } else {
        echo '<script>alert("Please upload an image of your offer item.")</script>';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try{
        $sql="SELECT UserID,Title from items WHERE ItemID=:ItemID and status='Available'";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([":ItemID"=>$itemID]);
        $item=$stmt->fetch(PDO::FETCH_ASSOC);

        if($item){
            $ownerID=$item["UserID"];
            $itemTitle=$item["Title"];

            // Prevent self-request (requester is the owner)
            if ((int)$ownerID === (int)$requesterID) {
                echo '<script>alert("You cannot send a swap request to your own listing.")</script>';
                header('Location: swapPage.php');
                exit;
            }

            $sql="SELECT ExchangeID FROM exchange WHERE ItemID=:ItemID AND RequesterID=:RequesterID AND status='pending'";
            $stmt=$pdo->prepare($sql);
            $stmt->execute(["ItemID"=>$itemID,"RequesterID"=>$requesterID]);

            if($stmt->rowCount()==0){
                $sql="INSERT INTO exchange(ItemID,RequesterID,OwnerID,Offer_title,Offer_description,Offer_notes,Offer_image,status,Exchange_timestamp)Values(:ItemID,:RequesterID,:OwnerID,:Offer_title,:Offer_description,:Offer_notes,:Offer_image,'pending',NOW())";
                $stmt=$pdo->prepare($sql);
                $stmt->execute([":ItemID"=>$itemID,":RequesterID"=>$requesterID,":OwnerID"=>$ownerID,":Offer_title"=>$offerTitle,":Offer_description"=>$offerDescription,":Offer_notes"=>$offerNotes,":Offer_image"=>$offerImagePath]);
                
                $exchangeID = $pdo->lastInsertId();
                
                // Get requester's username
                $stmt = $pdo->prepare("SELECT Username FROM users WHERE UserID = ?");
                $stmt->execute([$requesterID]);
                $requesterUser = $stmt->fetch(PDO::FETCH_ASSOC);
                $requesterName = $requesterUser['Username'] ?? 'Someone';
                
                // Create notification for the owner
                $notificationMessage = "Hi, " . $requesterName . " wants to swap with you using \"" . $offerTitle . "\" for your item \"" . $itemTitle . "\"";
                $sql = "INSERT INTO notifications (UserID, Message, ExchangeID, Is_read, Notification_Timestamp) VALUES (:UserID, :Message, :ExchangeID, 0, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([":UserID" => $ownerID, ":Message" => $notificationMessage, ":ExchangeID" => $exchangeID]);
                
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
    header('Location: swapPage.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Swap</title>
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/uploadPage.css">
    <link rel="stylesheet" href="styles/swapConfirm.css">
</head>
<body data-page="swap-confirm">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card swap-confirm-card" aria-labelledby="swapConfirmHeading">
            <div class="section-header">
                <h2 id="swapConfirmHeading">Swap for <span><?php echo htmlspecialchars($itemData['Title'] ?? 'this item'); ?></span></h2>
                <p>Share what you can offer in return so the owner can review and confirm the swap.</p>
            </div>
            <div class="swap-confirm__content">
                <article class="swap-confirm__item" aria-live="polite">
                    <div class="swap-confirm__media">
                        <?php 
                        $imageRel = null;
                        if (!empty($itemData['Image_path'])) {
                            $imageRel = 'php/' . ltrim($itemData['Image_path'], '/');
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imageRel ?: 'Pictures/landingPage/swap-item-pic.jpg'); ?>" alt="<?php echo htmlspecialchars($itemData['Title'] ?? 'Selected swap item'); ?>">
                    </div>
                    <div class="swap-confirm__details">
                        <span class="swap-confirm__tag"><?php echo htmlspecialchars($itemData['Category'] ?? 'Swap item'); ?></span>
                        <h3><?php echo htmlspecialchars($itemData['Title'] ?? 'Selected swap item'); ?></h3>
                        <p><?php echo htmlspecialchars($itemData['Description'] ?? 'Choose a listing from the swap feed to see the details here.'); ?></p>
                        <?php if (!empty($itemData['OwnerUsername'])): ?>
                            <div class="card-meta">
                                Listed by <?php echo htmlspecialchars($itemData['OwnerUsername']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php if ($itemData && $isOwner): ?>
                    <div class="upload-form warning-box">
                        <strong>This is your listing.</strong>
                        <p>You can't send a swap request to your own item. Go back to the swap feed to view other listings.</p>
                        <div class="upload-form__actions">
                            <button type="button" class="button-cancel" onclick="window.location.href='swapPage.php'">Back to swap feed</button>
                        </div>
                    </div>
                <?php else: ?>
                <form id="swapOfferForm" class="upload-form swap-confirm__form" action="swapConfirm.php?item_id=<?php echo (int)($itemData['ItemID'] ?? $itemId); ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="swap_request" value="1">
                    <input type="hidden" name="item_id" value="<?php echo (int)($itemData['ItemID'] ?? $itemId); ?>">
                    <div class="upload-form__grid">
                        <div class="input-group input-group--full">
                            <label for="offerMedia">Upload your item photo</label>
                            <input type="file" id="offerMedia" name="offerMedia" accept="image/*" required>
                            <p class="input-help">Share a clear image so your neighbour can confirm the swap quickly.</p>
                        </div>
                        <div class="input-group">
                            <label for="offerTitle">Your item name</label>
                            <input type="text" id="offerTitle" name="offer_title" placeholder="e.g. Handwoven market tote" required>
                        </div>
                        <div class="input-group input-group--full">
                            <label for="offerDescription">Describe your item</label>
                            <textarea id="offerDescription" name="offer_description" rows="5" placeholder="Explain the condition, pick-up timing, or what makes it a good swap." required></textarea>
                        </div>
                        <div class="input-group input-group--full">
                            <label for="offerNotes">Message to the owner (optional)</label>
                            <textarea id="offerNotes" name="offer_notes" rows="4" placeholder="Add extra context or propose a meeting spot."></textarea>
                        </div>
                    </div>
                    <div class="upload-form__footer">
                        <p class="helper-text">We'll notify the owner of <span><?php echo htmlspecialchars($itemData['Title'] ?? 'this item'); ?></span> once you confirm.</p>
                        <div class="upload-form__actions">
                            <button type="button" class="button-cancel" onclick="window.location.href='swapPage.php'">Back to swap feed</button>
                            <button type="submit">Confirm swap request</button>
                        </div>
                    </div>
                    <div class="swap-confirm__success" id="swapConfirmSuccess" role="status" aria-live="polite" tabindex="-1">
                        <strong>Swap request sent!</strong>
                        <p>The owner will review your offer and respond soon.</p>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script src="script/sidebar.js?v=2"></script>
</body>
</html>
