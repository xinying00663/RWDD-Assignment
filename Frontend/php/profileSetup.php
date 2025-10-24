<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "connect.php";

// check if the user has logged in
if(!isset($_SESSION["user_id"]) || !$_SESSION["logged_in"]){
    header("Location:login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $userID=$_SESSION["user_id"];
    $username=$_POST["username"] ??"";
    $fullName=$_POST["fullName"] ??"";
    $gender=$_POST["gender"] ??"";
    $phoneNumber=$_POST["phoneNumber"] ??"";
    $city=$_POST["city"] ??"";
    $additionalInfo=$_POST["skills"] ??"";
    
    $response=["success"=>false,"message"=>""];

    // validate inputs
    if(empty($username)||empty($fullName)||empty($gender)||empty($phoneNumber)||empty($city)){
        echo "<script>
                alert('All fields are required except additional info.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="All fields are required except additional info.";
    }else{
        try{
            $stmt=$pdo->prepare("SELECT UserID FROM users WHERE Username=? and UserID!=?");
            $stmt->execute([$username,$userID]);
            if($stmt->fetch()){
                echo "<script>
                        alert('The username already been used, please try again.');
                        window.history.back();
                    </script>";
                    exit;
                // $response["message"]="The username already been used, please try again.";
            }else{
                $genderMap=["male"=>"Male", "female"=>"Female","other"=>"Others"];

                $dbGender=$genderMap[$gender] ??"";

                $stmt=$pdo->prepare("SELECT Email FROM users WHERE UserID=?");
                $stmt->execute([$userID]);
                $user=$stmt->fetch();

                if(!$user){
                    $response["message"]="User not found.";
                }else{
                    // Update user profile
                    $stmt=$pdo->prepare("UPDATE users SET
                        Username= ?,
                        Full_Name= ?,
                        Gender= ?,
                        Phone_Number= ?,
                        City_Or_Neighbourhood= ?,
                        Additional_info= ?
                        WHERE UserID=?");

                    $success=$stmt->execute([
                        $username ?: NULL,
                        $fullName ?: NULL,
                        $dbGender ?: NULL,
                        $phoneNumber ?: NULL,
                        $city ?: NULL,
                        $additionalInfo ?: NULL,
                        $userID 
                    ]);

                    if($success && $stmt->rowCount()>0){
                        $_SESSION["Username"]=$username;
                        $_SESSION["Full_Name"]=$fullName;
                        echo "<script>
                                alert('Your profile has been setup successfully!');
                                window.location.href = '../homePage.html';
                            </script>";
                            exit;
                        // $response["success"]=true;
                        // $response["message"]="Your profile has been setup successfully.";
                        // $response["redirectTo"]="userProfile.html";
                    }else{
                            echo "<script>
                                    alert('No changes were made due to database error, please try again.');
                                    window.history.back();
                                </script>";
                                exit;
                        // $response["message"]="No changes were made due to database error, please try again.";
                    }
                }
            }

        }catch(PDOException $e){
            error_log("Database error:".$e->getMessage());
            echo "<script>
                    alert('Error in processing your registration. Please try again.');
                    window.history.back();
                </script>";
                exit;
            // $response["message"]="Error in processing your registration. Please try again.";
        }
    }
    // header("Content-Type:application/json");
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your EcoGo Profile</title>
    <link rel="stylesheet" href="../styles/auth.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card">
            <header>
                <a class="brand" href="homePage.html">
                    <img src="../Pictures/logo.jpeg" alt="EcoGo logo">
                    EcoGo Collective
                </a>
                <h1>Tell us about yourself</h1>
                <p>We use these details to recommend the right programs, swaps, and neighbours for you. You can update them anytime from your profile.</p>
            </header>
            <form id="profileForm" class="auth-form" method="POST" novalidate>
                <div class="form-row">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="Jamie Lim" required>
                </div>
                <div class="form-row">
                    <label for="fullName">Full name</label>
                    <input id="fullName" name="fullName" type="text" placeholder="Jamie Lim" required>
                </div>
                <div class="form-row">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Prefer Not to Say</option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="phoneNumber">Phone number</label>
                    <input id="phoneNumber" name="phoneNumber" type="tel" placeholder="+60 12-345 6789" required>
                </div>
                <div class="form-row">
                    <label for="city">City or neighbourhood</label>
                    <input id="city" name="city" type="text" placeholder="Kuala Lumpur" required>
                </div>
                <div class="form-row">
                    <label for="skills">Anything else we should know?</label>
                    <textarea id="skills" name="skills" rows="3" placeholder="Add notes about skills, availability, or accessibility needs..."></textarea>
                </div>
                <p id="profileError" class="auth-error" aria-live="polite"></p>
                <div class="actions">
                    <button type="submit">Save and explore EcoGo</button>
                </div>
            </form>
        </section>
        <aside class="auth-side">
            <h2>Next up</h2>
            <ul>
                <li>Browse energy tips and project videos customised to your goals.</li>
                <li>Find gardeners and eco-enthusiasts near your living space.</li>
                <li>Track your swaps, likes, and event attendance in one dashboard.</li>
            </ul>
        </aside>
    </main>
    <!-- <script src="../script/userProfile.js" defer></script> -->
</body>
</html>