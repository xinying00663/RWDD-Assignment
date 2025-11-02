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

// Get search query from URL parameter
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch programs from database with optional search filter
try {
    if (!empty($searchQuery)) {
        // Search by program name
        $query = "SELECT ProgramID, Program_name, Program_location, Event_date_start, Event_date_end, Program_description, Coordinator_name, Coordinator_email, Coordinator_phone, latitude, longitude 
                  FROM program 
                  WHERE Program_name LIKE ? 
                  ORDER BY created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['%' . $searchQuery . '%']);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Fetch all programs
        $query = "SELECT ProgramID, Program_name, Program_location, Event_date_start, Event_date_end, Program_description, Coordinator_name, Coordinator_email, Coordinator_phone, latitude, longitude 
                FROM program 
                ORDER BY created_at DESC";
        $result = $pdo->query($query);
        $programs = $result->fetchAll(PDO::FETCH_ASSOC);
    }
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
                <form method="GET" action="homePage.php" style="width: 100%;" onsubmit="return validateSearch();">
                    <label for="searchInput">Search places</label>
                    <input id="searchInput" class="search-box" type="text" name="search" 
                           placeholder="Search programmes, gardens, or addresses..." 
                           value="<?php echo esc($searchQuery); ?>"
                           onkeypress="if(event.key==='Enter'){this.form.submit();}">
                </form>
            </div>
            <script>
            function validateSearch() {
                const input = document.getElementById('searchInput');
                if (input.value.trim() === '') {
                    // If search is empty, redirect to show all programs
                    window.location.href = 'homePage.php';
                    return false;
                }
                return true;
            }
            </script>
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
                        <p><?php echo !empty($searchQuery) ? 'No programs found matching "' . esc($searchQuery) . '". Try a different search.' : 'No programs have been shared yet. Be the first to add one!'; ?></p>
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

    <!-- Google Maps API -->
    <script async
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX23VSgysvFGdvQRAadSkUkhIvF9-B6Mo&libraries=places&callback=initMap">
    </script>

    <script>
    // Initialize map with markers from PHP database
    function initMap() {
        // Center map on Kuala Lumpur
        const center = { lat: 3.1390, lng: 101.6869 };

        // Create the map
        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: center,
        });

        const bounds = new google.maps.LatLngBounds();
        let markerCount = 0;

        <?php foreach ($programs as $program): ?>
            <?php 
            $lat = floatval($program['latitude']);
            $lng = floatval($program['longitude']);
            if (!empty($lat) && !empty($lng)):
            ?>
            // Marker for: <?php echo esc($program['Program_name']); ?>
            
            (function() {
                const position = { lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?> };
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: <?php echo json_encode($program['Program_name']); ?>,
                    animation: google.maps.Animation.DROP
                });

                const infoContent = 
                    '<div style="padding:8px;max-width:250px;">' +
                    '<h3 style="margin:0 0 8px 0;color:#214235;font-size:1.1rem;"><?php echo addslashes(esc($program['Program_name'])); ?></h3>' +
                    '<p style="margin:4px 0;color:#6c7c74;"><strong> Location:</strong> <?php echo addslashes(esc($program['Program_location'])); ?></p>' +
                    '<p style="margin:4px 0;color:#6c7c74;"><strong> Date:</strong> <?php echo $program['Event_date_start'] ? date('M j, Y', strtotime($program['Event_date_start'])) : 'TBC'; ?></p>' +
                    '<a href="programDetail.php?id=<?php echo $program['ProgramID']; ?>" style="display:inline-block;margin-top:8px;padding:6px 12px;background:#2d8d60;color:#fff;text-decoration:none;border-radius:999px;font-size:0.9rem;">View Details</a>' 
                    +
                    '</div>';

                const infoWindow = new google.maps.InfoWindow({
                    content: infoContent
                });

                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });

                bounds.extend(position);
                markerCount++;
            })();
            
            <?php endif; ?>
        <?php endforeach; ?>

        // Fit map to show all markers
        if (markerCount > 0) {
            map.fitBounds(bounds);
            // Don't zoom in too much if only one marker
            if (markerCount === 1) {
                const listener = google.maps.event.addListener(map, "idle", function() { 
                    if (map.getZoom() > 15) map.setZoom(15); 
                    google.maps.event.removeListener(listener); 
                });
            }
        }

        console.log('Added ' + markerCount + ' markers to map from PHP database');
    }
    </script>
    <script src="script/typingEffect.js?v=2"></script>
</body>
</html>
