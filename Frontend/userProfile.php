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
                    <strong>5</strong>
                </article>
                <article>
                    <span class="label">Projects Shared</span>
                    <strong>10</strong>
                </article>
                <article>
                    <span class="label">Tips Shared</span>
                    <strong>7</strong>
                </article>
            </div>
        </section>
            <section class="tabs">
                <button class="tab-btn active" type="button" data-target="programs">Programs</button>
                <button class="tab-btn" type="button" data-target="posts">My Posts</button>
            </section>
            <section id="programs" class="profile-content active">
                <div class="profile-card">
                    <h2>Upcoming</h2>
                    <div class="activity-feed">
                        <article class="activity-item">
                            <span class="time">Yesterday</span>
                            <strong>Shared zero-waste workshop toolkit</strong>
                            <p>Uploaded facilitation slides and translation notes for community partners.</p>
                        </article>
                        <article class="activity-item">
                            <span class="time">Mon</span>
                            <strong>Closed Herb Lab harvest recap</strong>
                            <p>Logged 38 kg of produce redistributed and tagged photo album for social media.</p>
                        </article>
                        <article class="activity-item">
                            <span class="time">Sat</span>
                            <strong>Mentored new compost leads</strong>
                            <p>Trained 4 volunteers on aeration schedule and safety gear requirements.</p>
                        </article>
                    </div>
                </div>
                <div class="profile-card">
                    <h2>Recent activity</h2>
                    <div class="activity-feed">
                        <article class="activity-item">
                            <span class="time">Yesterday</span>
                            <strong>Shared zero-waste workshop toolkit</strong>
                            <p>Uploaded facilitation slides and translation notes for community partners.</p>
                        </article>
                        <article class="activity-item">
                            <span class="time">Mon</span>
                            <strong>Closed Herb Lab harvest recap</strong>
                            <p>Logged 38 kg of produce redistributed and tagged photo album for social media.</p>
                        </article>
                        <article class="activity-item">
                            <span class="time">Sat</span>
                            <strong>Mentored new compost leads</strong>
                            <p>Trained 4 volunteers on aeration schedule and safety gear requirements.</p>
                        </article>
                    </div>
                </div>

            </section>
            <section id="posts" class="profile-content">
                <div class="profile-card">
                    <div class="profile-card__header">
                        <h2>My Posts</h2>
                        <p>Check the projects, tips, and swaps you have shared with neighbours.</p>
                    </div>
                    <div class="activity-feed" data-profile-posts></div>
                    <p class="empty-state" data-profile-posts-empty hidden>You have not posted anything yet. Share your first story from the upload pages to see it here.</p>
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
    <!-- <script src="script/userProfile.js" defer></script> -->
</body>
</html>
