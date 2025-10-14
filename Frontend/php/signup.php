<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connect.php";

session_start();
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $email=filter_var($_POST["Email"] ??"",FILTER_VALIDATE_EMAIL);
    $password=$_POST["Password"] ??"";
    $confirmPassword=$_POST["Confirm_Password"] ??"";
    $terms=isset($_POST["Terms"]) ? true : false;
    
    $response=["success"=>false,"message"=>""];
    
   //validate inputs
    if(empty($email)|| empty($password)|| empty($confirmPassword)){
        $response["message"]="All fields are required.";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $response["message"]="Please enter a valid email address.";
    }elseif(strlen($password)<8){
        $response["message"]="Password must be at least 8 characters long.";
    }elseif($password!==$confirmPassword){
        $response["message"]="Passwords do not match.";
    }elseif(!$terms){
        $response["message"]="You must agree to the terms and conditions.";
    }else{
        try{
            $stmt=$pdo->prepare("SELECT UserID FROM users WHERE Email=?");
            $stmt->execute([$email]);
            if($stmt->fetch()){
                $response["message"]="An account with this email already exists.";
            }else{
                $hashedPassword=password_hash($password,PASSWORD_DEFAULT);
                $joinDate=date("Y-m-d H:i:s");
                $stmt=$pdo->prepare("INSERT INTO users(Email,Password,Join_date,Last_login) VALUES(?,?,?,?)");
                $stmt->execute([$email,$hashedPassword,$joinDate,$joinDate]);

                $userID=$pdo->lastInsertID();

                $_SESSION["user_id"]=$userID;
                $_SESSION["Email"]=$email;
                $_SESSION["logged_in"]=true;

                $response["success"]=true;
                $response["message"]="Your account has been created successfully.";
                $response["user_id"]=$userID;
                $response["redirect"]="profileSetup.html";
            }
        }catch(PDOException $e){
            error_log("Database error:".$e->getMessage());
            $response["message"]="Error in processing your registration. Please try again.";
        }
    }
    header("Content-Type:application/json");
    echo json_encode($response);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join EcoGo</title>
    <link rel="stylesheet" href="../styles/auth.css">
</head>
<body class="auth-body">
    <main class="auth-shell">
        <section class="auth-card">
            <header>
                <a class="brand" href="../landingPage.html">
                    <img src="../Pictures/logo.jpeg" alt="EcoGo logo">
                    EcoGo Collective
                </a>
                <h1>Create your EcoGo account</h1>
                <p>Start tracking your sustainability journey, join neighbourhood initiatives, and swap resources with people nearby.</p>
            </header>
            <form id="signUpForm" class="auth-form" method="POST" action="php/signup.php" novalidate>
                <div class="form-row">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="you@example.com" required>
                </div>
                <div class="inline two">
                    <div class="form-row">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" minlength="8" required>
                    </div>
                    <div class="form-row">
                        <label for="confirmPassword">Confirm password</label>
                        <input id="confirmPassword" name="confirmPassword" type="password" minlength="8" required>
                    </div>
                </div>
                <div class="checkbox-row">
                    <label>
                        <input id="terms" name="terms" type="checkbox" required>
                        I agree to the EcoGo community guidelines and privacy policy.
                    </label>
                </div>
                <p id="signUpError" class="auth-error" aria-live="polite"></p>
                <div class="actions">
                    <button type="submit">Create account</button>
                    <footer>Already have an account? <a href="loginPage.php">Log in</a></footer>
                </div>
            </form>
        </section>
        <aside class="auth-side">
            <h2>Why join EcoGo?</h2>
            <ul>
                <li>Get curated energy and gardening tips tailored to your living space.</li>
                <li>Access the swap library to exchange tools, seedlings, and reuseable goods.</li>
                <li>Track your impact and celebrate milestones with your neighbours.</li>
            </ul>
        </aside>
    </main>
<!-- <script src="../script/auth.js" defer></script> -->
</body>
</html>