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

// Fetch program IDs the user has already registered for
$registeredProgramIds = [];
try {
    $regQuery = "SELECT program_id FROM program_customer WHERE user_id = ?";
    $regStmt = $pdo->prepare($regQuery);
    $regStmt->execute([$userId]);
    $registeredProgramIds = $regStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log("DB query error for registered programs: " . $e->getMessage());
    // Non-critical error, so we can continue without this data.
}

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
                <?php
                    $isRegistered = in_array($program['ProgramID'], $registeredProgramIds);
                    $cardClass = $isRegistered ? 'highlight-card program-card is-registered' : 'highlight-card program-card';
                ?>
                <div class="program-card-wrapper">
                    <a href="programDetail.php?id=<?php echo esc($program['ProgramID']); ?>" class="<?php echo $cardClass; ?>">
                        <div class="program-card__meta">
                            <span class="program-card__tag">Community</span>
                            <?php if ($program['Event_date_start'] || $program['Event_date_end']): ?>
                            <span class="program-card__duration">
                                <?php 
                                    $dateRange = formatDateRange($program['Event_date_start'], $program['Event_date_end']);
                                    echo $dateRange ?: 'Date TBC';
                                ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc($program['Program_name']); ?></h3>
                        <p><?php echo esc($program['Program_location']); ?></p>
                        <div class="program-card__actions">
                            <?php if ($isRegistered): ?>
                                <span class="program-card__link program-card__link--signed">Signed Up</span>
                            <?php else: ?>
                                <span class="program-card__link">View Program</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php if ($isAdmin): ?>
                        <form method="POST" action="php/deleteProgram.php" class="program-card__delete" onsubmit="return confirm('Delete this program?');">
                            <input type="hidden" name="program_id" value="<?php echo (int)$program['ProgramID']; ?>">
                            <button type="submit" aria-label="Delete program">&times;</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($programs)): ?>
                    <div class="empty-state">
                        <p>No programs have been shared yet. Be the first to add one!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php if ($isAdmin): ?>
    <button type="button" class="add-button" aria-label="Add new program" 
        onclick="window.location.href='upload.html'">+</button>
    <?php endif; ?>
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
