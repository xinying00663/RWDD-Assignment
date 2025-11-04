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
    $stmt = $pdo->query("SELECT c.*, u.Username 
                         FROM community c 
                         LEFT JOIN users u ON c.user_id = u.UserID 
                         ORDER BY c.Community_id DESC");
    $community_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Community posts fetch error: " . $e->getMessage());
    $community_posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Gardening Community</title>
    <link rel="stylesheet" href="styles/general.css?v=8">
    <link rel="stylesheet" href="styles/common.css?v=8">
    <link rel="stylesheet" href="styles/sidebar.css?v=8">
    <link rel="stylesheet" href="styles/mediaFeed.css?v=8">
    <link rel="stylesheet" href="styles/addButton.css?v=8">
    <link rel="stylesheet" href="styles/communityPage.css?v=8">
</head>
<body data-page="community">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="tabs-card">
            <div class="section-header">
                <h2>Gardens, swaps, and stories from your neighbours</h2>
                <p>Watch skill shares, explore project galleries, and meet growers who are transforming unused corners into thriving spaces.</p>
            </div>
            <div class="filter-controls" role="region" aria-label="Filter community stories">
                <label for="communityFilter">Show</label>
                <select id="communityFilter" class="media-filter" data-target="communityGrid">
                    <option value="all">Everything</option>
                    <option value="projects">Projects</option>
                    <option value="tips">Tips</option>
                </select>
            </div>
            <div class="media-grid" id="communityGrid">
                <?php if (empty($community_posts)): ?>
                    <div class="empty-state">
                        <p>No community posts have been shared yet. Be the first to share!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($community_posts as $post): ?>
                        <div class="media-card-wrapper">
                        <article class="media-card" data-category="<?php echo htmlspecialchars($post['Community_category']); ?>">
                            <a href="mediaDetail.html" class="media-card__link"
                               data-page="community"
                               data-title="<?php echo htmlspecialchars($post['Community_title']); ?>"
                               data-description="<?php echo htmlspecialchars($post['Community_summary']); ?>"
                               data-category="<?php echo htmlspecialchars($post['Community_category']); ?>"
                               data-media-type="<?php echo preg_match('/\.(mp4|mov|avi)$/i', $post['Community_media']) ? 'video' : 'image'; ?>"
                               data-media-src="<?php echo 'php/' . ltrim(htmlspecialchars($post['Community_media']), '/'); ?>"
                               data-alt="<?php echo htmlspecialchars($post['Community_title']); ?>"
                               data-uploader="<?php echo htmlspecialchars($post['Community_contributor'] ?: 'Anonymous'); ?>"
                               data-location="<?php echo htmlspecialchars($post['Community_location'] ?: ''); ?>">
                                <div class="card-media">
                                    <?php $media_path = 'php/' . ltrim(htmlspecialchars($post['Community_media']), '/'); ?>
                                    <?php if (preg_match('/\.(mp4|mov|avi)$/i', $media_path)): ?>
                                        <video src="<?php echo $media_path; ?>" muted loop playsinline loading="lazy"></video>
                                    <?php else: ?>
                                        <img src="<?php echo $media_path; ?>" alt="<?php echo htmlspecialchars($post['Community_title']); ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span class="media-indicator"><?php echo htmlspecialchars(ucfirst($post['Community_category'])); ?></span>
                                </div>
                                <div class="card-body">
                                    <span class="card-tag"><?php echo htmlspecialchars(ucfirst($post['Community_category'])); ?></span>
                                    <h3><?php echo htmlspecialchars($post['Community_title']); ?></h3>
                                    <p>
                                        <?php
                                        $summary = $post['Community_summary'] ?? '';
                                        echo htmlspecialchars(mb_strlen($summary) > 100 ? mb_substr($summary, 0, 100) . '…' : $summary);
                                        ?>
                                    </p>
                                    <div class="card-meta">
                                        <span class="uploader">By <?php echo htmlspecialchars($post['Community_contributor'] ?: $post['Username'] ?: 'Anonymous'); ?></span>
                                    </div>
                                </div>
                            </a>
                        </article>
                        <?php if ($post['user_id'] == $userId || $isAdmin): ?>
                            <form method="POST" action="php/deleteCommunity.php" class="media-card__delete" onsubmit="return confirm('Delete this post?');">
                                <input type="hidden" name="community_id" value="<?php echo (int)$post['Community_id']; ?>">
                                <button type="submit" aria-label="Delete community post">&times;</button>
                            </form>
                        <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <button type="button" class="add-button" aria-label="Share with the community" onclick="window.location.href='uploadCommunity.html'">+</button>
    </main>
    <script src="script/sidebar.js?v=3"></script>
    <script src="script/mediaFeed.js" defer></script>
</body>
</html>
