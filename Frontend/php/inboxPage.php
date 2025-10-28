<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connect.php";
// echo "Database connected successfully!";

session_start();

// $userID = $_SESSION['user_id'] ?? null;  
// if (!$userID) {
//     http_response_code(401);
//     echo "Authentication required.";
//     window.location.href="login.php";
//     exit;
// }

// Handle message sending
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["send_message"])){
    $conversationID=$_POST["conversation_id"] ?? NULL;
    $messageContent=$_POST["message_content"] ??"";

    if(!$conversationID || !$messageContent){
        echo json_encode(["status" => "error", "message" => "Missing conversationID and message content."]);
        exit();
    }

    try{
        $sql="INSERT INTO message(ConversationID,SenderID,Message_Content,Chat_Timestamp)VALUES(:ConversationID,:SenderID,:Message_Content,NOW())";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([":ConversationID"=>$conversationID,":SenderID"=>$userID,":Message_Content"=>$messageContent]);

        $sql="UPDATE conversation SET Last_Updated=NOW() WHERE ConversationID=:ConversationID";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([":ConversationID"=>$conversationID]);

        echo json_encode(["status" => "success"]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

// Handle swap request response
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["swap_action"])){
    $exchangeID=$_POST["exchange_id"] ?? NULL;
    $action=$_POST["action"] ??"";
    $conversationID=$_POST["conversation_id"] ?? NULL;

    if(!$exchangeID || !$conversationID){
        echo json_encode(["status" => "error", "message" => "Missing conversationID and exchangeID."]);
        exit();
    }

    try{
        $sql="UPDATE exchange SET Status=:Status WHERE ExchangeID=:ExchangeID";
        $stmt=$pdo->prepare($sql);
        $status=($action=="accept")?"Accepted":"Rejected";
        $stmt->execute([":Status"=>$status,":ExchangeID"=>$exchangeID]);

        $sql = "SELECT RequesterID FROM exchange WHERE ExchangeID = :ExchangeID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ExchangeID" => $exchangeID]);
        $exchange = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exchange) {
            $requesterID = $exchange['RequesterID'];

            $sql = "INSERT INTO notification (UserID, Message, Is_read, Notification_Timestamp) 
                    VALUES (:UserID, :Message, 0, NOW())";
            $stmt = $pdo->prepare($sql);
            $message = ($action == "accept") 
                ? "Your swap request has been accepted!" 
                : "Your swap request has been declined.";
        $stmt->execute([":Message" => $message, ":UserID" => $requesterID]);
        
        // Add system message to conversation
        $system_message = "Swap request has been " . strtolower($status) . " by the item owner.";
        $sql = "INSERT INTO message (ConversationID, SenderID, Message_Content, Chat_Timestamp, is_system) 
                VALUES (:ConversationID, 0, :Message_Content, NOW(), 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":Conversation" => $conversationID,
            ":Message_Content" => $system_message
        ]);
        
        // Redirect back to the same conversation
        header("Location: inboxPage.php?ConversationID=" . $conversationID);
        exit();
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}

// Handle creating conversation from swap request (this should be in your swapConfirm.php)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_swap_conversation"])) {
    $itemID = $_POST["item_id"] ?? null;
    $requesterID = $_SESSION["user_id"] ?? null;
    
    if (!$itemID || !$requesterID) {
        echo json_encode(["status" => "error", "message" => "Missing item ID or user not logged in"]);
        exit();
    }

    try {
        // Get item owner
        $sql = "SELECT UserID, Title FROM items WHERE ItemID = :ItemID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([":ItemID" => $itemID]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$item) {
            echo json_encode(["status" => "error", "message" => "Item not found"]);
            exit();
        }
        
        $ownerID = $item['UserID'];
        $itemTitle = $item['Title'];
        
        // Check if conversation already exists
        $sql = "SELECT ConversationID FROM conversation 
                WHERE (User1ID = :User1ID AND User2ID = :User2ID) 
                OR (User1ID = :User2ID AND User2ID = :User1ID)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":User1ID" => $requesterID,
            ":User2ID" => $ownerID
        ]);
        
        $existingConversation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingConversation) {
            $conversationID = $existingConversation['ConversationID'];
        } else {
            // Create new conversation
            $sql = "INSERT INTO conversation (User1ID, User2ID, Last_Updated, ItemID) 
                    VALUES (:User1ID, :User2ID, NOW(), :ItemID)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":User1ID" => $requesterID,
                ":User2ID" => $ownerID,
                ":ItemID" => $itemID
            ]);
            $conversationID = $pdo->lastInsertId();
            
            // Add initial system message
            $initialMessage = "Conversation started about swapping: " . $itemTitle;
            $sql = "INSERT INTO message (ConversationID, SenderID, Message_Content, Chat_Timestamp, is_system) 
                    VALUES (:ConversationID, 0, :Message_Content, NOW(), 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":ConversationID" => $conversationID,
                ":Message_Content" => $initialMessage
            ]);
        }
        
        echo json_encode(["status" => "success", "conversation_id" => $conversationID]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit();
    }
}
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGo Inbox</title>
    <link rel="stylesheet" href="../styles/general.css">
    <link rel="stylesheet" href="../styles/common.css">  
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/searchbar.css">
    <link rel="stylesheet" href="../styles/inboxPage.css">
</head>
<body>
    <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false">
        <span class="sidebar-toggle__icon" aria-hidden="true"></span>
        <span class="sr-only">Toggle navigation</span>
    </button>
    <div class="sidebar-backdrop" id="sidebarBackdrop" hidden></div>
    <header id="sidebar" class="sidebar" aria-label="Primary navigation">
        <div class="logo">
            <a href="homePage.php">
                <img src="../Pictures/logo.jpeg" alt="EcoGo Logo">
            </a>
        </div>
        <nav>
            <a href="../homePage.php">
                <img src="../Pictures/sidebar/recycle-sign.png" alt="Home Icon">
                <p>Recycling Program</p>
            </a>
            <a href="../energyPage.php">
                <img src="../Pictures/sidebar/lamp.png" alt="Energy Icon">
                <p>Energy Conservation Tips</p>
            </a>
            <a href="../communityPage.html">
                <img src="Pictures/sidebar/garden.png" alt="Community Icon">
                <p>Gardening Community</p>
            </a>
            <a href="swapPage.html">
                <img src="../Pictures/sidebar/swap.png" alt="Swap Icon">
                <p>Swap Items</p>
            </a>
            <a href="../inboxPage.html" class="active">
                <img src="../Pictures/sidebar/inbox.png" alt="Inbox Icon">
                <p>Inbox</p>
            </a>
        </nav>
        <div class="profile">
            <a href="../userProfile.html">
                <img src="../Pictures/sidebar/user.png" alt="Profile Icon">
                <p>User Profile</p>
            </a>
        </div>
    </header>
    <main>
        <section class="tabs-card inbox-layout">
            <div class="inbox-topbar">
                <div class="search-container">
                    <label for="message-search">Search messages</label>
                    <input id="message-search" type="text" placeholder="Search by project, name, or keyword...">
                </div>
            </div>
            <div class="inbox-columns">
                <section class="conversation-list">
                    <div class="list-header">
                        <h2>Threads</h2>
                        <span class="status-pill new">3 new</span>
                    </div>
                    <div class="filter-pills">
                        <button class="active">All</button>
                        <button>Messages</button>
                        <button>Notifications</button>
                    </div>
                    <div class="conversation-items" id="conversationItems">
                        <!-- Conversation items will be dynamically inserted here -->
                    </div>
                </section>
                <section class="chatbox" id="chatbox">
                    <div class="chatbox-header">
                        <button onclick="goBack()">&larr; Back</button>
                        <h2 id="chatTitle">Chat</h2>
                    </div>

                    <div class="chat-messages" id="chatMessages"></div>

                    <div class="chat-input">
                        <input type="text" id="messageInput" placeholder="Type a message...">
                        <button onclick="sendMessage()">Send</button>
                    </div>
                </section>
            </div>
        </section>
    </main>
   <!-- <script src="script/sidebar.js" defer></script>
   <script src="script/inboxPage.js" defer></script> -->

</body>
</html>
