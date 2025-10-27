<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include "php/connect.php";

// Accept either session key name (some pages set user_id, others userId)
if (!isset($_SESSION['user_id'])) {
    header("Location: loginPage.html");
    exit();
}

$userId = $_SESSION['user_id'];

// Use correct column name (UserID) and alias columns to the keys used in the template
$query = "SELECT Full_Name AS fullName FROM users WHERE UserID = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);  
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch programs from database
try {
    $query = "SELECT ProgramID, Program_name, Program_location, Event_date_start, Event_date_end, Program_description, Coordinator_name, Coordinator_email, Coordinator_phone, latitude, longitude FROM program ORDER BY created_at DESC";
    $result = $pdo->query($query);
    // Fetch all results into an array
    $programs = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB query error: " . $e->getMessage());
    $programs = [];
}

// Format date helper function
function formatDateRange($start, $end) {
    if (!$start && !$end) return '';
    
    $fromDate = $start ? date('M j, Y', strtotime($start)) : '';
    $toDate = $end ? date('M j, Y', strtotime($end)) : '';
    
    if ($start && !$end) return "Starts {$fromDate}";
    if (!$start && $end) return "Until {$toDate}";
    return "{$fromDate} - {$toDate}";
}

// HTML escape helper
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Recycling Dashboard</title>
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/common.css">  
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/searchbar.css">
    <link rel="stylesheet" href="styles/mediaFeed.css">
    <link rel="stylesheet" href="styles/homePage.css">
    <link rel="stylesheet" href="styles/addButton.css">
</head>
<body data-page="recycling">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <section class="welcome-card">
            <h1>Welcome back, <span id="profileSummaryName"><?php echo $user["fullName"]; ?></span></h1>
        </section>
        <section class="tabs-card">
            <div class="section-header">
                <h2>Recycling Programs</h2>
                <p>Use the interactive map to see ongoing projects, connect with coordinators, and find the best time to volunteer. Switch to tips to pick up quick wins for your own plot.</p>
            </div>
            <div class="search-container">
                <label for="searchInput">Search places</label>
                <input id="searchInput" class="search-box" type="text" placeholder="Search programmes, gardens, or addresses...">
            </div>
            <div class="map-wrapper">
                <div id="map"></div>
            </div>
            <div class="section-header">
                <h2>Programs you can join</h2>
                <p>Select an initiative to learn more, meet the coordinator, and secure your spot.</p>
            </div>
            <div class="project-highlights program-grid" data-program-grid>
                <?php foreach ($programs as $program): ?>
                <a href="programDetail.php?id=<?php echo esc($program['ProgramID']); ?>" class="program-card">
                    <h3><?php echo esc($program['Program_name']); ?></h3>
                    <p class="muted"><?php echo esc($program['Program_location']); ?></p>
                    <p class="dates">
                        <?php echo formatDateRange($program['Event_date_start'], $program['Event_date_end']); ?>
                    </p>
                    <p>
                        <?php 
                        $desc = $program['Program_description'] ?? '';
                        echo esc(mb_strlen($desc) > 240 ? mb_substr($desc, 0, 240) . '…' : $desc);
                        ?>
                    </p>
                </a>
                <?php endforeach; ?>
                <?php if (empty($programs)): ?>
                    <div class="empty-state">
                        <p>No programs have been shared yet. Be the first to add one!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <button type="button" class="add-button" aria-label="Add new program" 
                onclick="window.location.href='upload.html'">+</button>
    </main>

    <script src="script/sidebar.js?v=2"></script>
    <script src="script/googleMap.js"></script>

    <!-- Google Maps API -->
    <script async
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX23VSgysvFGdvQRAadSkUkhIvF9-B6Mo&libraries=places&callback=initMap">
    </script>

    <script>
    // Programs with coords for client-side markers (safe JSON)
    window.serverPrograms = <?php echo json_encode($programs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;

    // function to add markers once map is available
    (function () {
        function addServerMarkers() {
            if (typeof google === 'undefined' || !window.map || !Array.isArray(window.serverPrograms)) return;
            window.serverPrograms.forEach(function(p) {
                var lat = parseFloat(p.latitude);
                var lng = parseFloat(p.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: window.map,
                        title: p.Program_name || ''
                    });
                }
            });
        }

        // expose so googleMap.js / initMap can call after it creates the map
        window.addServerMarkers = addServerMarkers;

        // also try to add markers after DOM content loaded (in case map created early)
        document.addEventListener('DOMContentLoaded', function () {
            var tries = 0;
            var t = setInterval(function () {
                tries++;
                if (window.map) {
                    addServerMarkers();
                    clearInterval(t);
                } else if (tries > 10) {
                    clearInterval(t);
                }
            }, 300);
        });
    })();
    </script>
</body>
</html>
