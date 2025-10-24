<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include "php/connect.php";
// $host="localhost";
// $user="root";
// $password="";
// $dbname="uploadpost";

// try {
//     $db = new mysqli($host, $user, $password, $dbname);
//     $db->set_charset('utf8mb4');
// } catch (mysqli_sql_exception $e) {
//     error_log("DB connect error: " . $e->getMessage());
//     http_response_code(500);
//     echo "DB connect error: " . $e->getMessage();
//     exit;
// }

// Fetch programs from database
try {
    $query = "SELECT
    Full_Name AS fullName,
    FROM users WHERE UserID = ?";
    $sql = "SELECT * FROM program ORDER BY created_at DESC";
    $result = $pdo->query($sql);
    $programs = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $programs[] = $row;
    }
    $result->free();
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
            <h1>Welcome back, <span id="profileSummaryName"><?php echo esc($user["fullName"]); ?></span></h1>
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
                <article class="program-card">
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
                    <div class="coordinator-info">
                        <p class="coordinator">
                            Coordinator: <?php echo esc($program['Coordinator_name'] ?: '—'); ?>
                        </p>
                        <?php if ($program['Coordinator_email'] || $program['Coordinator_phone']): ?>
                        <div class="contact-details">
                            <?php if ($program['Coordinator_email']): ?>
                            <p>Email: <?php echo esc($program['Coordinator_email']); ?></p>
                            <?php endif; ?>
                            <?php if ($program['Coordinator_phone']): ?>
                            <p>Phone: <?php echo esc($program['Coordinator_phone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
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
    window.serverPrograms = <?php echo json_encode($programs_for_js, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;

    // function to add markers once map is available
    (function () {
        function addServerMarkers() {
            if (typeof google === 'undefined' || !window.map || !Array.isArray(window.serverPrograms)) return;
            window.serverPrograms.forEach(function(p) {
                var lat = parseFloat(p.eventLocationLat);
                var lng = parseFloat(p.eventLocationLng);
                if (!isNaN(lat) && !isNaN(lng)) {
                    new google.maps.Marker({
                        position: { lat: lat, lng: lng },
                        map: window.map,
                        title: p.eventName || ''
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
