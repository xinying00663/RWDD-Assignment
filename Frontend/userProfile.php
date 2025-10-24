<?php
session_start();
include "php/connect.php";

// Accept either session key name (some pages set user_id, others userId)
if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.html");
    exit();
}

$userId = $_SESSION['user_id'];

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
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/common.css">  
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/searchbar.css">
    <link rel="stylesheet" href="styles/userProfile.css">
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
                        <button class="primary" type="button" data-action="edit-profile">Edit profile</button>
                        <button class="secondary" type="button" data-action="logout">Logout</button>
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
    <div class="profile-modal" data-profile-modal hidden>
        <div class="profile-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
            <header class="profile-modal__header">
                <h2 id="profileEditTitle">Edit profile</h2>
                <button class="profile-modal__close" type="button" data-action="close-modal" aria-label="Close edit profile">&times;</button>
            </header>
            <form class="profile-modal__form">
                <div class="profile-modal__grid">
                    <label class="profile-modal__field">
                        <span>Username</span>
                        <input type="text" name="username" autocomplete="nickname" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Full name</span>
                        <input type="text" name="fullName" autocomplete="name" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Gender</span>
                        <select name="gender" required>
                            <option value="">Select</option>
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                            <option value="other">Prefer Not to Say</option>
                        </select>
                    </label>
                    <label class="profile-modal__field">
                        <span>Email</span>
                        <input type="email" name="email" autocomplete="email" required>
                    </label>
                    <label class="profile-modal__field">
                        <span>Phone number</span>
                        <input type="tel" name="phone" autocomplete="tel">
                    </label>
                    <label class="profile-modal__field">
                        <span>Location</span>
                        <input type="text" name="location" autocomplete="address-level2">
                    </label>
                    <label class="profile-modal_field profile-modal_field--wide">
                        <span>Bio</span>
                        <textarea name="bio" rows="4" placeholder="Share a bit about your sustainability journey..."></textarea>
                    </label>
                </div>
                <p class="profile-modal__error" data-modal-error aria-live="polite"></p>
                <div class="profile-modal__actions">
                    <button class="secondary" type="button" data-action="close-modal">Cancel</button>
                    <button class="primary" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>
    <script src="script/sidebar.js?v=2"></script>
    <!-- <script src="script/userProfile.js" defer></script> -->
</body>
</html>
