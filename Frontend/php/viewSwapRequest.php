<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "connect.php";

// Check authentication
$userID = $_SESSION['user_id'] ?? null;
if (!$userID) {
    echo '<script>alert("Please login first."); window.location.href="../loginPage.html";</script>';
    exit;
}

// Get exchange ID from GET parameter
$exchangeID = $_GET['exchange_id'] ?? 0;

if (!$exchangeID) {
    echo '<script>alert("Invalid request."); window.location.href="../inboxPage.html";</script>';
    exit;
}

// Fetch exchange details with item and requester info
try {
    $sql = "SELECT e.*, i.Title AS ItemTitle, i.Image_path AS ItemImage, 
        u.Username AS RequesterUsername, u.Email AS RequesterEmail, u.Phone_Number AS RequesterPhone, u.City_Or_Neighbourhood AS RequesterCity,
        owner.Username AS OwnerUsername, owner.Email AS OwnerEmail, owner.Phone_Number AS OwnerPhone, owner.City_Or_Neighbourhood AS OwnerCity
            FROM exchange e
            LEFT JOIN items i ON e.ItemID = i.ItemID
            LEFT JOIN users u ON e.RequesterID = u.UserID
            LEFT JOIN users owner ON e.OwnerID = owner.UserID
            WHERE e.ExchangeID = :ExchangeID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":ExchangeID" => $exchangeID]);
    $exchange = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exchange) {
        echo '<script>alert("Exchange not found."); window.location.href="../inboxPage.html";</script>';
        exit;
    }

    // Determine role relative to this exchange
    $isOwner = ((int)$exchange['OwnerID'] === (int)$userID);
    $isRequester = ((int)$exchange['RequesterID'] === (int)$userID);

    // Verify that the current user is part of this exchange
    if (!$isOwner && !$isRequester) {
        echo '<script>alert("You are not authorized to view this request."); window.location.href="../inboxPage.html";</script>';
        exit;
    }

    // Mark related notifications as read for this user
    $sql = "UPDATE notifications SET Is_read = 1 WHERE ExchangeID = :ExchangeID AND UserID = :UserID";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([":ExchangeID" => $exchangeID, ":UserID" => $userID]);

} catch (PDOException $e) {
    error_log('Error fetching exchange: ' . $e->getMessage());
    echo '<script>alert("Error loading swap request."); window.location.href="../inboxPage.html";</script>';
    exit;
}

// Handle Accept/Reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'accept' || $action === 'reject') {
        include "handleSwapDecision.php";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swap Request Details</title>
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/swapRequestDetail.css">
</head>
<body data-page="inbox">
    <main>
        <div class="swap-details-container">
            <div class="swap-card">
                <h2>Swap Request Details</h2>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-<?php echo htmlspecialchars($exchange['status']); ?>">
                        <?php echo htmlspecialchars(ucfirst($exchange['status'])); ?>
                    </span>
                </div>

                <div class="swap-info-grid">
                    <!-- Your Item Section -->
                    <div class="info-section">
                        <h3>Your Item</h3>
                        <div class="info-item">
                            <span class="info-label">Item Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($exchange['ItemTitle']); ?></span>
                        </div>
                        <?php if (!empty($exchange['ItemImage'])): ?>
                            <?php
                                // Item images are saved under php/upload/swapItems and stored as 'upload/swapItems/...'
                                // Since this page is in php/, use the stored path directly (no extra 'php/' prefix)
                                $itemImgSrc = $exchange['ItemImage'];
                            ?>
                            <img src="<?php echo htmlspecialchars($itemImgSrc); ?>" alt="Your item" class="swap-image">
                        <?php endif; ?>
                    </div>

                    <!-- Requester's Offer Section -->
                    <div class="info-section">
                        <h3>They are Offering</h3>
                        <div class="info-item">
                            <span class="info-label">Requester:</span>
                            <span class="info-value"><?php echo htmlspecialchars($exchange['RequesterUsername']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Offer Item:</span>
                            <span class="info-value"><?php echo htmlspecialchars($exchange['Offer_title']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Description:</span>
                            <span class="info-value"><?php echo htmlspecialchars($exchange['Offer_description']); ?></span>
                        </div>
                        <?php if (!empty($exchange['Offer_notes'])): ?>
                        <div class="info-item">
                            <span class="info-label">Additional Notes:</span>
                            <span class="info-value"><?php echo htmlspecialchars($exchange['Offer_notes']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($exchange['Offer_image'])): ?>
                            <?php
                                // Offer images are saved under Frontend/upload/offers and stored as 'upload/offers/...'
                                // Since this page is in php/, prepend '../' to reach the correct folder
                                $offerImgPath = $exchange['Offer_image'];
                                $offerImgSrc = (strpos($offerImgPath, 'upload/') === 0) ? ('../' . $offerImgPath) : $offerImgPath;
                            ?>
                            <img src="<?php echo htmlspecialchars($offerImgSrc); ?>" alt="Offer item" class="swap-image">
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($exchange['status'] === 'pending' && $isOwner): ?>
                <div class="action-buttons">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="accept">
                        <input type="hidden" name="exchange_id" value="<?php echo $exchangeID; ?>">
                        <button type="submit" class="btn-accept" onclick="return confirm('Are you sure you want to accept this swap? Your item will be marked as exchanged.');">Accept Swap</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="exchange_id" value="<?php echo $exchangeID; ?>">
                        <button type="submit" class="btn-reject" onclick="return confirm('Are you sure you want to reject this swap?');">Reject Swap</button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($exchange['status'] === 'pending' && $isRequester): ?>
                    <p class="swap-status-message pending">Your request is pending the owner's decision.</p>
                <?php endif; ?>

                <?php if ($exchange['status'] === 'accepted'): ?>
                    <div class="contact-card">
                        <?php if ($isRequester): ?>
                            <h3>Swap accepted! Contact the owner to arrange meetup</h3>
                            <div class="contact-grid">
                                <div class="contact-item">
                                    <span class="label">Owner</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['OwnerUsername']); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Phone</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['OwnerPhone'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Email</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['OwnerEmail'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Area</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['OwnerCity'] ?? ''); ?></span>
                                </div>
                            </div>
                            <p style="margin-top:12px;color:#476052;">Tip: suggest a safe, public meetup location and time that works for both of you.</p>
                        <?php elseif ($isOwner): ?>
                            <h3>You've accepted. Share your preferred meetup details with the requester</h3>
                            <div class="contact-grid">
                                <div class="contact-item">
                                    <span class="label">Requester</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['RequesterUsername']); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Phone</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['RequesterPhone'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Email</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['RequesterEmail'] ?? 'Not provided'); ?></span>
                                </div>
                                <div class="contact-item">
                                    <span class="label">Area</span>
                                    <span class="value"><?php echo htmlspecialchars($exchange['RequesterCity'] ?? ''); ?></span>
                                </div>
                            </div>
                            <p style="margin-top:12px;color:#476052;">Tip: propose 2-3 timeslots and a nearby public place. You can also exchange via phone or email above.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($exchange['status'] === 'declined' && $isRequester): ?>
                    <p class="swap-status-message declined">Your swap request was declined. The item remains available on the swap page.</p>
                <?php endif; ?>

                <div class="action-buttons" style="margin-top: 20px;">
                    <a href="../inboxPage.html" class="btn-back">Back to Inbox</a>
                </div>
            </div>
        </div>
    </main>
    <script src="../script/sidebar.js?v=2"></script>
</body>
</html>
