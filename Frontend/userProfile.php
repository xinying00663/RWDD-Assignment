<?php
session_start();
include "php/connect.php";

// Accept either session key name (some pages set user_id, others userId)
if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Check if edit mode is requested
$showModal = isset($_GET['edit']) && $_GET['edit'] === 'true';

// Handle form submission
$updateSuccess = false;
$updateError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $updateQuery = "UPDATE users SET 
            Username = ?,
            Full_Name = ?,
            Gender = ?,
            Email = ?,
            Phone_Number = ?,
            City_Or_Neighbourhood = ?,
            Additional_info = ?
        WHERE UserID = ?";
        
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([
            $_POST['username'],
            $_POST['fullName'],
            $_POST['gender'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['location'],
            $_POST['bio'],
            $userId
        ]);
        
        $updateSuccess = true;
        // Redirect to remove POST data
        header("Location: userProfile.php?updated=true");
        exit();
    } catch (PDOException $e) {
        $updateError = "Error updating profile: " . $e->getMessage();
    }
}

// Use correct column name (UserID) and alias columns to the keys used in the template
$query = "SELECT
    Username AS username,
    Full_Name AS fullName,
    Gender AS gender,
    Email AS email,
    Phone_Number AS phone,
    City_Or_Neighbourhood AS location,
    Additional_info AS bio
FROM users WHERE UserID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get statistics
// Programs Joined
$programsJoinedQuery = "SELECT COUNT(*) as count FROM program_customer WHERE User_id = ?";
$programsJoinedStmt = $pdo->prepare($programsJoinedQuery);
$programsJoinedStmt->execute([$userId]);
$programsJoined = $programsJoinedStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Projects Shared (community posts with category 'projects')
$projectsSharedQuery = "SELECT COUNT(*) as count FROM community WHERE user_id = ? AND Community_category = 'projects'";
$projectsSharedStmt = $pdo->prepare($projectsSharedQuery);
$projectsSharedStmt->execute([$userId]);
$projectsShared = $projectsSharedStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Tips Shared (community posts with category 'tips')
$tipsSharedQuery = "SELECT COUNT(*) as count FROM community WHERE user_id = ? AND Community_category = 'tips'";
$tipsSharedStmt = $pdo->prepare($tipsSharedQuery);
$tipsSharedStmt->execute([$userId]);
$tipsShared = $tipsSharedStmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Get upcoming registered programs (future or ongoing events)
// First, let's check all registered programs without date filter
$allRegisteredQuery = "SELECT pc.Customer_id, pc.User_id, pc.Program_id, p.ProgramID, p.Program_name 
                       FROM program_customer pc 
                       LEFT JOIN program p ON p.ProgramID = pc.Program_id 
                       WHERE pc.User_id = ?";
$allRegisteredStmt = $pdo->prepare($allRegisteredQuery);
$allRegisteredStmt->execute([$userId]);
$allRegistered = $allRegisteredStmt->fetchAll(PDO::FETCH_ASSOC);

echo "<!-- Debug: All registered programs (no filter) = " . print_r($allRegistered, true) . " -->\n";

$upcomingProgramsQuery = "SELECT p.ProgramID, p.Program_name, p.Program_location, p.Event_date_start, p.Event_date_end 
                          FROM program p 
                          INNER JOIN program_customer pc ON p.ProgramID = pc.Program_id 
                          WHERE pc.User_id = ? AND (p.Event_date_start >= CURDATE() OR p.Event_date_end >= CURDATE() OR p.Event_date_start IS NULL OR p.Event_date_end IS NULL)
                          ORDER BY p.Event_date_start ASC";
$upcomingProgramsStmt = $pdo->prepare($upcomingProgramsQuery);
$upcomingProgramsStmt->execute([$userId]);
$upcomingPrograms = $upcomingProgramsStmt->fetchAll(PDO::FETCH_ASSOC);

// Debug output
echo "<!-- Debug: userId = " . $userId . " -->\n";
echo "<!-- Debug: upcomingPrograms count = " . count($upcomingPrograms) . " -->\n";
echo "<!-- Debug: upcomingPrograms = " . print_r($upcomingPrograms, true) . " -->\n";

// Get user's community posts
$userPostsQuery = "SELECT Community_id, Community_title, Community_category, Community_media, Community_summary, created_at 
                   FROM community 
                   WHERE user_id = ? 
                   ORDER BY created_at DESC";
$userPostsStmt = $pdo->prepare($userPostsQuery);
$userPostsStmt->execute([$userId]);
$userPosts = $userPostsStmt->fetchAll(PDO::FETCH_ASSOC);

// Debug output
echo "<!-- Debug: userPosts count = " . count($userPosts) . " -->\n";
echo "<!-- Debug: userPosts = " . print_r($userPosts, true) . " -->\n";

// Helper function to format dates
function formatDate($date) {
    if (!$date) return '';
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 86400) return 'Today';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return date('D', $timestamp); // Day name like "Mon"
    return date('M j, Y', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Profile</title>
    <link rel="stylesheet" href="styles/general.css?v=5">
    <link rel="stylesheet" href="styles/common.css?v=5">
    <link rel="stylesheet" href="styles/sidebar.css?v=5">
    <link rel="stylesheet" href="styles/searchbar.css?v=5">
    <link rel="stylesheet" href="styles/userProfile.css?v=5">
</head>
<body data-page="user-profile">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="profile-hero">
            <div class="identity">
                <div class="profile-avatar"></div>
                <div>
                    <h1 class="username"><?php echo $user['username']; ?></h1>
                    <p class="fullName"><?php echo $user['fullName']; ?></p>
                    <p class="gender"><?php echo $user['gender']; ?></p>
                    <p class="value"><?php echo $user['email']; ?></p>
                    <p class="value"><?php echo $user['phone']; ?></p>
                    <p class="location"><?php echo $user['location']; ?></p>
                    <p class="bio"><?php echo $user['bio']; ?></p>
                    <div class="hero-actions">
                        <a href="userProfile.php?edit=true" class="primary">Edit profile</a>
                        <a href="php/logout.php" class="secondary">Logout</a>
                    </div>
                </div>
            </div>
            
            <div class="impact-stats">
                <article>
                    <span class="label">Programs Joined</span>
                    <strong><?php echo $programsJoined; ?></strong>
                </article>
                <article>
                    <span class="label">Projects Shared</span>
                    <strong><?php echo $projectsShared; ?></strong>
                </article>
                <article>
                    <span class="label">Tips Shared</span>
                    <strong><?php echo $tipsShared; ?></strong>
                </article>
            </div>
        </section>
            <section class="tabs">
                <button class="tab-btn active" type="button" data-target="programs">Programs</button>
                <button class="tab-btn" type="button" data-target="posts">My Posts</button>
            </section>
            <section id="programs" class="profile-content active">
    <div class="profile-card">
        <h2>Registered Events</h2>
        <div class="activity-feed">
            <?php if (count($allRegistered) > 0): ?>
                <?php foreach ($allRegistered as $program): ?>
                    <article class="activity-item">
                        <strong><a href="programDetail.php?id=<?php echo $program['ProgramID']; ?>" style="color:#2d8d60;text-decoration:none;"><?php echo htmlspecialchars($program['Program_name']); ?></a></strong>
                        <p>
                            <?php 
                            $dateStart = $program['Event_date_start'] ?? '';
                            $dateEnd = $program['Event_date_end'] ?? '';
                            if ($dateStart && $dateEnd) {
                                echo date('M j, Y', strtotime($dateStart)) . ' - ' . date('M j, Y', strtotime($dateEnd));
                            } elseif ($dateStart) {
                                echo 'Starts ' . date('M j, Y', strtotime($dateStart));
                            }
                            ?>
                        </p>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty-state">You haven't registered for any programs yet. <a href="homePage.php" style="color:#2d8d60;">Browse programs</a></p>
            <?php endif; ?>
        </div>
    </div>
</section>
            <section id="posts" class="profile-content">
                <div class="profile-card">
                    <div class="profile-card__header">
                        <h2>My Posts</h2>
                        <p>Check the projects, tips, and swaps you have shared with neighbours.</p>
                    </div>
                    <div class="activity-feed">
                        <?php if (count($userPosts) > 0): ?>
                            <?php foreach ($userPosts as $post): ?>
                                <article class="activity-item" style="position:relative;display:flex;gap:16px;">
                                    <?php if ($post['Community_media']): ?>
                                        <div style="width:80px;height:80px;flex-shrink:0;border-radius:8px;overflow:hidden;background:#f5f5f5;">
                                            <?php 
                                            $media_path = 'php/' . ltrim(htmlspecialchars($post['Community_media']), '/');
                                            $ext = strtolower(pathinfo($post['Community_media'], PATHINFO_EXTENSION));
                                            if (in_array($ext, ['mp4', 'mov', 'avi'])): 
                                            ?>
                                                <video style="width:100%;height:100%;object-fit:cover;">
                                                    <source src="<?php echo $media_path; ?>">
                                                </video>
                                            <?php else: ?>
                                                <img src="<?php echo $media_path; ?>" alt="Post media" style="width:100%;height:100%;object-fit:cover;">
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex:1;">
                                        <span class="time"><?php echo formatDate($post['created_at']); ?></span>
                                        <strong><?php echo htmlspecialchars($post['Community_title']); ?></strong>
                                        <span style="display:inline-block;margin-left:8px;padding:2px 8px;background:rgba(45,141,96,0.1);color:#2d8d60;border-radius:12px;font-size:0.75rem;font-weight:600;">
                                            <?php echo ucfirst($post['Community_category']); ?>
                                        </span>
                                        <p><?php echo htmlspecialchars($post['Community_summary']); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-state">You have not posted anything yet. Share your first story from the <a href="uploadCommunity.php" style="color:#2d8d60;">upload page</a> to see it here.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
    </main>
    <div class="profile-modal" data-profile-modal <?php echo $showModal ? '' : 'hidden'; ?>>
        <div class="profile-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
            <header class="profile-modal__header">
                <h2 id="profileEditTitle">Edit profile</h2>
                <a href="userProfile.php" class="profile-modal__close" aria-label="Close edit profile">&times;</a>
            </header>
            <form class="profile-modal__form" method="POST" action="userProfile.php">
                <div class="profile-modal__grid">
                    <label class="profile-modal__field">
                        <span>Username</span>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" autocomplete="nickname" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Full name</span>
                        <input type="text" name="fullName" value="<?php echo htmlspecialchars($user['fullName']); ?>" autocomplete="name" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Gender</span>
                        <select name="gender" required>
                            <option value="">Select</option>
                            <option value="female" <?php echo $user['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="male" <?php echo $user['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="other" <?php echo $user['gender'] === 'other' ? 'selected' : ''; ?>>Prefer Not to Say</option>
                        </select>
                    </label>
                    <label class="profile-modal__field">
                        <span>Email</span>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" autocomplete="email" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Phone number</span>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" autocomplete="tel">
                    </label>
                    <label class="profile-modal__field">
                        <span>Location</span>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" autocomplete="address-level2">
                    </label>
                    <label class="profile-modal_field profile-modal_field--wide">
                        <span>Bio</span>
                        <textarea name="bio" rows="4" placeholder="Share a bit about your sustainability journey..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </label>
                </div>
                <?php if ($updateError): ?>
                    <p class="profile-modal__error" style="color: red;"><?php echo htmlspecialchars($updateError); ?></p>
                <?php endif; ?>
                <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
                    <p class="profile-modal__success" style="color: green;">Profile updated successfully!</p>
                <?php endif; ?>
                <input type="hidden" name="update_profile" value="1">
                <div class="profile-modal__actions">
                    <a href="userProfile.php" class="secondary">Cancel</a>
                    <button class="primary" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
    <script src="script/sidebar.js?v=2"></script>
    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.profile-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                const target = this.getAttribute('data-target');
                document.getElementById(target).classList.add('active');
            });
        });
    </script>
    <!-- <script src="script/userProfile.js" defer></script> -->
</body>
</html>
