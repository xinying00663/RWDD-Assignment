<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to escape HTML output
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Helper to format date ranges, similar to the JS version
function formatDateRange($start, $end) {
    if (!$start && !$end) return "Schedule to be confirmed";
    $startFmt = $start ? date('M j, Y', strtotime($start)) : '';
    $endFmt = $end ? date('M j, Y', strtotime($end)) : '';
    if ($start && $end) return "{$startFmt} - {$endFmt}";
    if ($start) return "Starts {$startFmt}";
    return "Until {$endFmt}";
}

include "php/connect.php";

$programId = $_GET['id'] ?? null;
$programData = null;
$userId = $_SESSION['user_id'] ?? null;
$userData = null;
$isRegistered = false;

// Fetch current user data
if ($userId) {
    try {
        $userStmt = $pdo->prepare("SELECT Username, Full_Name, Email, Phone_Number FROM users WHERE UserID = ?");
        $userStmt->execute([$userId]);
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('User fetch error: ' . $e->getMessage());
    }
}

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

echo "<!-- Debug: programId from URL: " . var_export($programId, true) . " -->\n";

if ($programId) {
    echo "<!-- Debug: programId is set, attempting to fetch from DB -->\n";
    try {
        // Fetch main program details
        $query = "SELECT 
                    ProgramID as id,
                    Program_name as name,
                    Program_description as description,
                    Program_location as location,
                    Event_date_start as startDate,
                    Event_date_end as endDate,
                    Coordinator_name as coordinatorName,
                    Coordinator_email as coordinatorEmail,
                    Coordinator_phone as coordinatorPhone
                  FROM program 
                  WHERE ProgramID = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$programId]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<!-- Debug: Program fetched: " . var_export($program, true) . " -->\n";

        if ($program) {
            echo "<!-- Debug: Program found, fetching sections -->\n";
            // Fetch custom sections
            $sectionQuery = "SELECT 
                                section_title as heading, 
                                section_description as details 
                             FROM program_sections 
                             WHERE program_id = ?";
            $sectionStmt = $pdo->prepare($sectionQuery);
            $sectionStmt->execute([$programId]);
            $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);
            $program['sections'] = $sections;
            $programData = $program; // Assign the fetched program data
            
            // Check if user is already registered
            if ($userId) {
                $regCheckStmt = $pdo->prepare("SELECT COUNT(*) as count FROM program_customer WHERE User_id = ? AND Program_id = ?");
                $regCheckStmt->execute([$userId, $programId]);
                $regCheck = $regCheckStmt->fetch(PDO::FETCH_ASSOC);
                $isRegistered = ($regCheck['count'] > 0);
            }
            
            echo "<!-- Debug: programData after sections: " . var_export($programData, true) . " -->\n";
        } else {
            echo "<!-- Debug: No program found with ID: " . var_export($programId, true) . " -->\n";
        }
    } catch (PDOException $e) {
        error_log("Program detail fetch error: " . $e->getMessage());
        echo "<!-- Debug: PDOException caught: " . $e->getMessage() . " -->\n";
    }
} else {
    echo "<!-- Debug: programId is NOT set in URL. -->\n";
}
echo "<!-- Debug: Final programData before rendering: " . var_export($programData, true) . " -->\n";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Program Details</title>
    <link rel="stylesheet" href="styles/general.css">
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <link rel="stylesheet" href="styles/programDetail.css">
</head>
<body data-page="recycling">
    <!-- Sidebar will be loaded here by sidebar.js -->
    <main>
        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
        <div class="success-message" style="background:#d4edda;color:#155724;padding:16px 24px;border-radius:12px;margin-bottom:20px;border:1px solid #c3e6cb;">
            ✓ Registration successful! The coordinator will contact you soon.
        </div>
        <?php endif; ?>
        
        <?php if ($programData): ?>
        <?php
            // Prepare variables for the view, providing fallbacks
            $title = esc($programData['name'] ?: 'Community Recycling Program');
            $summary = esc($programData['description'] ?: 'This organiser has not shared additional details yet.');
            $location = esc($programData['location'] ?: 'Location to be confirmed');
            $dateLabel = formatDateRange($programData['startDate'], $programData['endDate']);
            $coordinatorName = esc($programData['coordinatorName'] ?: 'Community Organiser');
            $coordinatorEmail = esc($programData['coordinatorEmail']);
            $coordinatorPhone = esc($programData['coordinatorPhone']);
        ?>
        <div id="programView" data-program-id="<?php echo esc($programData['id']); ?>">
            <section class="program-hero">
                <nav class="breadcrumbs" aria-label="Program breadcrumbs">
                    <a href="homePage.php">Recycling Programs</a>
                    <span aria-hidden="true">&gt;</span>
                    <span id="programBreadcrumb"><?php echo $title; ?></span>
                </nav>
                <div class="hero-badge" id="programBadge">Community submission</div>
                <h1 id="programTitle"><?php echo $title; ?></h1>
                <p class="hero-summary" id="programSummary"><?php echo $summary; ?></p>
                <div class="hero-meta">
                    <div class="hero-meta__item">
                        <span class="hero-meta__label">Duration</span>
                        <span class="hero-meta__value" id="programDuration"><?php echo $dateLabel; ?></span>
                    </div>
                    <div class="hero-meta__item">
                        <span class="hero-meta__label">Meet-ups</span>
                        <span class="hero-meta__value" id="programCommitment"><?php echo $dateLabel; ?></span>
                    </div>
                    <div class="hero-meta__item">
                        <span class="hero-meta__label">Location</span>
                        <span class="hero-meta__value" id="programLocation"><?php echo $location; ?></span>
                    </div>
                </div>
                <div class="hero-actions">
                    <?php if ($isAdmin): ?>
                        <button class="primary-cta" disabled style="opacity:0.6;cursor:not-allowed;">Admins cannot register</button>
                    <?php elseif ($isRegistered): ?>
                        <button class="primary-cta" disabled style="opacity:0.6;cursor:not-allowed;">Registered</button>
                    <?php else: ?>
                        <button class="primary-cta" onclick="showRegisterModal()">Register now</button>
                    <?php endif; ?>
                    <a class="secondary-cta" href="homePage.php">Back to programs</a>
                    <?php if ($isAdmin): ?>
                        <form method="POST" action="php/deleteProgram.php" style="display:inline-block" onsubmit="return confirm('Delete this program?');">
                            <input type="hidden" name="program_id" value="<?php echo esc($programData['id']); ?>">
                            <button type="submit" class="secondary-cta" style="background:#c62828;color:#fff;border:none;cursor:pointer;border-radius:999px;padding:10px 16px;">Delete program</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
            <section class="program-body">
                <article class="program-panel">
                    <h2>What you will do</h2>
                    <ul class="program-outcomes" id="programOutcomes">
                        <li><?php echo $summary; ?></li>
                    </ul>
                </article>
                <article class="program-panel">
                    <h2>What to know before you join</h2>
                    <div class="info-grid" id="programResources">
                        <div class="info-card"><h3>When</h3><p><?php echo $dateLabel; ?></p></div>
                        <div class="info-card"><h3>Where</h3><p><?php echo $location; ?></p></div>
                    </div>
                </article>
                <?php if (!empty($programData['sections'])): ?>
                    <?php foreach ($programData['sections'] as $section): ?>
                        <?php if (!empty($section['heading']) || !empty($section['details'])): ?>
                        <article class="program-panel program-panel--community">
                            <h2><?php echo esc($section['heading'] ?: 'Additional Details'); ?></h2>
                            <p><?php echo esc($section['details'] ?: 'More information coming soon.'); ?></p>
                        </article>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <section class="coordinator">
                <h2>Program coordinator</h2>
                <div class="coordinator-card">
                    <div class="coordinator-details">
                        <h3 id="coordinatorName"><?php echo $coordinatorName; ?></h3>
                        <p id="coordinatorRole">Community submission</p>
                    </div>
                    <ul class="coordinator-contact">
                        <li><span>Phone</span>
                            <?php if ($coordinatorPhone): ?>
                                <a id="coordinatorPhone" href="tel:<?php echo preg_replace('/\s+/', '', $coordinatorPhone); ?>"><?php echo $coordinatorPhone; ?></a>
                            <?php else: ?>
                                <a id="coordinatorPhone">To be shared after confirmation</a>
                            <?php endif; ?>
                        </li>
                        <li><span>Email</span>
                            <?php if ($coordinatorEmail): ?>
                                <a id="coordinatorEmail" href="mailto:<?php echo $coordinatorEmail; ?>"><?php echo $coordinatorEmail; ?></a>
                            <?php else: ?>
                                <a id="coordinatorEmail">Email not provided</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </section>
            
            <!-- Registration Confirmation Modal -->
            <div id="registerModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <h2>Confirm Registration</h2>
                    <p class="modal-intro">Please review your information before registering for <strong><?php echo $title; ?></strong>.</p>
                    <form id="confirmRegisterForm" action="php/programCustomer.php" method="POST">
                        <input type="hidden" name="programId" value="<?php echo esc($programData['id']); ?>">
                        
                        <div class="form-field">
                            <label>Full Name</label>
                            <input type="text" name="participantName" value="<?php echo esc($userData['Full_Name'] ?? $userData['Username'] ?? ''); ?>" readonly style="background:#f5f5f5;">
                        </div>
                        
                        <div class="form-field">
                            <label>Email</label>
                            <input type="email" name="participantEmail" value="<?php echo esc($userData['Email'] ?? ''); ?>" readonly style="background:#f5f5f5;">
                        </div>
                        
                        <div class="form-field">
                            <label>Phone Number</label>
                            <input type="tel" name="participantPhone" value="<?php echo esc($userData['Phone_Number'] ?? ''); ?>" readonly style="background:#f5f5f5;">
                        </div>
                        
                        <div class="modal-actions">
                            <button type="button" class="secondary-cta" onclick="closeRegisterModal()">Cancel</button>
                            <button type="submit" class="primary-cta">Confirm Registration</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <section class="program-register" id="register" style="display:none;">
                <h2>Reserve your spot</h2>
                <p class="register-intro">Complete the form and the coordinator will reach out with onboarding details for the <span id="registerProgramName"><?php echo $title; ?></span>.</p>
                <form class="register-form" id="programRegisterForm" action="php/programCustomer.php" method="post">
                    <input type="hidden" name="programId" value="<?php echo esc($programData['id']); ?>">
                    <div class="form-field">
                        <label for="participantName">Full name</label>
                        <input type="text" id="participantName" name="participantName" placeholder="e.g. Nur Aisyah" required>
                    </div>
                    <div class="form-field">
                        <label for="participantEmail">Email</label>
                        <input type="email" id="participantEmail" name="participantEmail" placeholder="you@example.com" required>
                    </div>
                    <div class="form-field">
                        <label for="participantPhone">Phone number</label>
                        <input type="tel" id="participantPhone" name="participantPhone" placeholder="e.g. +60 12-345 6789">
                    </div>
                    <div class="form-field form-field--checkbox form-field--full">
                        <input type="checkbox" id="consent" name="consent" required>
                        <label for="consent">I agree to follow the EcoGo volunteer guidelines and will be contacted by the coordinator.</label>
                    </div>
                    <button type="submit" class="primary-cta form-field--full">Submit registration</button>
                    <p class="form-status" id="formStatus" role="status" aria-live="polite"></p>
                </form>
            </section>
        </div>
        <?php else: ?>
        <section class="program-fallback" id="programFallback">
            <h1>We could not find that program</h1>
            <p>The link might be outdated. Browse the <a href="homePage.php">Recycling Programs</a> to pick an active initiative.</p>
        </section>
        <?php endif; ?>
    </main>
    <script src="script/sidebar.js?v=2"></script>
    <script>
        function showRegisterModal() {
            document.getElementById('registerModal').style.display = 'flex';
        }
        
        function closeRegisterModal() {
            document.getElementById('registerModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('registerModal');
            if (event.target === modal) {
                closeRegisterModal();
            }
        }
    </script>
</body>
</html>
