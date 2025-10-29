<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "php/connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.html");
    exit();
}

// Determine admin role, with DB fallback if session role not set
$isAdmin = false;
if (isset($_SESSION['role'])) {
    $isAdmin = ($_SESSION['role'] === 'admin');
} else {
    try {
        $roleStmt = $pdo->prepare("SELECT Role FROM users WHERE UserID = ?");
        $roleStmt->execute([$_SESSION['user_id']]);
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
    $stmt = $pdo->query("SELECT Energy_id, Energy_title, Energy_category, Energy_contributor, Energy_summary, Energy_media FROM energy ORDER BY Energy_id DESC");
    $energy_tips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Energy tips fetch error: " . $e->getMessage());
    $energy_tips = []; // Default to an empty array on error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Energy Playbook</title>
    <link rel="stylesheet" href="styles/general.css?v=6">
    <link rel="stylesheet" href="styles/common.css?v=6">
    <link rel="stylesheet" href="styles/sidebar.css?v=6">
    <link rel="stylesheet" href="styles/mediaFeed.css?v=6">
    <link rel="stylesheet" href="styles/addButton.css?v=6">
    <link rel="stylesheet" href="styles/energyPage.css?v=6">
</head>
<body data-page="energy">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card">
            <div class="section-header">
                <h2>Energy wins you can try this week</h2>
                <p>Browse quick tutorials and community-tested ideas to trim your bill without expensive upgrades. Filter by the area you need help with and dive deeper for step-by-step guides.</p>
            </div>
            <div class="filter-controls" role="region" aria-label="Filter energy resources">
                <label for="energyFilter">Show</label>
                <select id="energyFilter" class="media-filter" data-target="energyGrid">
                    <option value="all">Everything</option>
                    <option value="tutorial">Tutorial videos</option>
                    <option value="habit">Daily habits</option>
                    <option value="planning">Planning &amp; checklists</option>
                </select>
            </div>
            <div class="media-grid" id="energyGrid">
                <?php if (empty($energy_tips)): ?>
                    <div class="empty-state">
                        <p>No energy tips have been shared yet. Be the first to add one!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($energy_tips as $tip): ?>
                        <div class="media-card-wrapper">
                        <article class="media-card" data-category="<?php echo htmlspecialchars($tip['Energy_category']); ?>">
                            <a href="mediaDetail.html" class="media-card__link"
                               data-page="energy"
                               data-title="<?php echo htmlspecialchars($tip['Energy_title']); ?>"
                               data-description="<?php echo htmlspecialchars($tip['Energy_summary']); ?>"
                               data-category="<?php echo htmlspecialchars($tip['Energy_category']); ?>"
                               data-media-type="<?php echo preg_match('/\.(mp4|mov|avi)$/i', $tip['Energy_media']) ? 'video' : 'image'; ?>"
                               data-media-src="<?php echo 'php/' . ltrim(htmlspecialchars($tip['Energy_media']), '/'); ?>"
                               data-alt="<?php echo htmlspecialchars($tip['Energy_title']); ?>"
                               data-uploader="<?php echo htmlspecialchars($tip['Energy_contributor'] ?: 'Anonymous'); ?>">
                                <div class="card-media">
                                    <?php $media_path = 'php/' . ltrim(htmlspecialchars($tip['Energy_media']), '/'); ?>
                                    <?php if (preg_match('/\.(mp4|mov|avi)$/i', $media_path)): ?>
                                        <video src="<?php echo $media_path; ?>" muted loop playsinline loading="lazy"></video>
                                    <?php else: ?>
                                        <img src="<?php echo $media_path; ?>" alt="<?php echo htmlspecialchars($tip['Energy_title']); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="media-indicator"><?php echo htmlspecialchars(ucfirst($tip['Energy_category'])); ?></span>
                                </div>
                                <div class="card-body">
                                    <span class="card-tag"><?php echo htmlspecialchars(ucfirst($tip['Energy_category'])); ?></span>
                                    <h3><?php echo htmlspecialchars($tip['Energy_title']); ?></h3>
                                    <p>
                                        <?php
                                        $summary = $tip['Energy_summary'] ?? '';
                                        echo htmlspecialchars(mb_strlen($summary) > 100 ? mb_substr($summary, 0, 100) . '…' : $summary);
                                        ?>
                                    </p>
                                    <div class="card-meta">
                                        <span class="uploader">By <?php echo htmlspecialchars($tip['Energy_contributor'] ?: 'Anonymous'); ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                        <?php if ($isAdmin): ?>
                            <form method="POST" action="php/deleteEnergy.php" class="media-card__delete" onsubmit="return confirm('Delete this tip?');">
                                <input type="hidden" name="energy_id" value="<?php echo (int)$tip['Energy_id']; ?>">
                                <button type="submit" aria-label="Delete energy tip">&times;</button>
                            </form>
                        <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php if ($isAdmin): ?>
        <button type="button" class="add-button" aria-label="Submit energy tip" onclick="window.location.href='uploadEnergy.html'">+</button>
        <?php endif; ?>
    </main>
    <script src="script/sidebar.js?v=2"></script>
    <script src="script/energyPage.js" defer></script>
    <script src="script/mediaFeed.js" defer></script>
</body>
</html>
