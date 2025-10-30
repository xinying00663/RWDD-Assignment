<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "php/connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Determine admin role, with DB fallback if session role not set
$isAdmin = false;
if (isset($_SESSION['role'])) {
    $isAdmin = ($_SESSION['role'] === 'admin');
} else {
    try {
        $roleStmt = $pdo->prepare("SELECT Role FROM users WHERE UserID = ?");
        $roleStmt->execute([$userId]);
        $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
        if ($roleRow) {
            $_SESSION['role'] = $roleRow['Role'] ?? 'user';
            $isAdmin = ($_SESSION['role'] === 'admin');
        }
    } catch (PDOException $e) {
        error_log('Role fetch error: ' . $e->getMessage());
    }
}

try {
    $stmt = $pdo->query("SELECT i.*, u.Username 
                         FROM items i 
                         LEFT JOIN users u ON i.UserID = u.UserID 
                         WHERE i.Status = 'Available'
                         ORDER BY i.ItemID DESC");
    $swap_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Swap items fetch error: " . $e->getMessage());
    $swap_items = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Swap Items</title>
    <link rel="stylesheet" href="styles/general.css?v=8">
    <link rel="stylesheet" href="styles/common.css?v=8">
    <link rel="stylesheet" href="styles/sidebar.css?v=8">
    <link rel="stylesheet" href="styles/searchbar.css?v=8">
    <link rel="stylesheet" href="styles/mediaFeed.css?v=8">
    <link rel="stylesheet" href="styles/swapPage.css?v=8">
    <link rel="stylesheet" href="styles/addButton.css?v=8">
</head>
<body data-page="swap">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card">
            <div class="section-header">
                <h2>Swap items from your neighbours</h2>
                <p>Browse the latest sustainable finds and trade for what you need without creating extra waste.</p>
            </div>
            <div class="filter-controls swap-controls" role="region" aria-label="Sort swap items">
                <label for="categoryFilter">Sort by category</label>
                <select id="categoryFilter" class="media-filter" data-target="swapItems">
                    <option value="all">Everything</option>
                    <option value="home-grown">Home-grown</option>
                    <option value="eco-friendly">Eco-friendly</option>
                </select>
            </div>
            <div class="media-grid" id="swapItems">
                <?php if (empty($swap_items)): ?>
                    <div class="empty-state">
                        <p>No swap items have been listed yet. Be the first to share!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($swap_items as $item): ?>
                        <div class="media-card-wrapper">
                        <article class="media-card swap-card" data-category="<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $item['Category']))); ?>">
                            <?php if (!$isAdmin): ?>
                            <a href="swapConfirm.php?id=<?php echo $item['ItemID']; ?>" class="media-card__link"
                               data-item-title="<?php echo htmlspecialchars($item['Title']); ?>"
                               data-item-description="<?php echo htmlspecialchars($item['Description']); ?>"
                               data-item-image="<?php echo 'php/' . ltrim(htmlspecialchars($item['Image_path']), '/'); ?>">
                            <?php else: ?>
                            <div class="media-card__link" style="cursor: default;">
                            <?php endif; ?>
                                <div class="card-media">
                                    <?php $media_path = 'php/' . ltrim(htmlspecialchars($item['Image_path']), '/'); ?>
                                    <?php if (preg_match('/\.(mp4|mov|avi)$/i', $media_path)): ?>
                                        <video src="<?php echo $media_path; ?>" muted loop playsinline loading="lazy"></video>
                                    <?php else: ?>
                                        <img src="<?php echo $media_path; ?>" alt="<?php echo htmlspecialchars($item['Title']); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="media-indicator"><?php echo htmlspecialchars($item['Category']); ?></span>
                                </div>
                                <div class="card-body">
                                    <span class="card-tag"><?php echo htmlspecialchars($item['Category']); ?></span>
                                    <h3><?php echo htmlspecialchars($item['Title']); ?></h3>
                                    <p>
                                        <?php
                                        $description = $item['Description'] ?? '';
                                        echo htmlspecialchars(mb_strlen($description) > 100 ? mb_substr($description, 0, 100) . '…' : $description);
                                        ?>
                                    </p>
                                    <?php if ($item['Item_condition'] || $item['Preferred_exchange']): ?>
                                        <div class="swap-card__meta">
                                            <?php if ($item['Item_condition']): ?>
                                                <span>Condition: <?php echo htmlspecialchars($item['Item_condition']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($item['Preferred_exchange']): ?>
                                                <span>Hoping for: <?php echo htmlspecialchars($item['Preferred_exchange']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <span class="swap-card__cta">Message to swap</span>
                                </div>
                            <?php if (!$isAdmin): ?>
                            </a>
                            <?php else: ?>
                            </div>
                            <?php endif; ?>
                        </article>
                        <?php if ($item['UserID'] == $userId || $isAdmin): ?>
                            <form method="POST" action="php/deleteSwap.php" class="media-card__delete" onsubmit="return confirm('Delete this swap item?');">
                                <input type="hidden" name="item_id" value="<?php echo (int)$item['ItemID']; ?>">
                                <button type="submit" aria-label="Delete swap item">&times;</button>
                            </form>
                        <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <button type="button" class="add-button" aria-label="List a swap item" onclick="window.location.href='uploadSwap.php'">+</button>
    </main>
    <script src="script/sidebar.js?v=3"></script>
    <script src="script/mediaFeed.js" defer></script>
</body>
</html>


