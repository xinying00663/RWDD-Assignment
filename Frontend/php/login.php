<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "connect.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){
  $response=["success"=>false,"message"=>""];

  $email=$_POST["email"] ??"";
  $password=$_POST["password"] ??"";
  
  if(empty($email)|| empty($password)){
    $response["message"]="Please fill in all required fields.";
  }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    $response["message"]="Please enter a valid email address.";
  }else{
    try{
      $stmt=$pdo->prepare("SELECT UserID,Password,Email FROM users WHERE Email=?");
      $stmt->execute([$email]);
      $user=$stmt->fetch(PDO::FETCH_ASSOC);

      if($user&&$password===$user["Password"]){
        $lastLogin=date("Y-m-d H:i:s");
        $stmt=$pdo->prepare("UPDATE users SET Last_login=? WHERE UserID=?");
        $stmt->execute([$lastLogin,$user["UserID"]]);

        $_SESSION["user_id"]=$user["UserID"];
        $_SESSION["email"]=$user["Email"];
        $_SESSION["logged_in"]=true;

        echo "<script>
                        alert('Login successful!');
                        window.location.href = '../homePage.html';
                    </script>";
                    exit;

        // $response["success"]=true;
        // $response["message"]="Login successful.";
        // $response["user_id"]=$user["UserID"];
        // $response["redirectTo"]="../homePage.html";
      }else{
        echo "<script>
                            alert('Database error, please try again.');
                            window.history.back();
                        </script>";
                        exit;
      }
    }catch(PDOException $e){
      error_log("Database error:".$e->getMessage());
      $response["message"]="Error in processing your login. Please try again.";
    }
  }
  echo json_encode($response);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../styles/loginPage.css">
</head>
<body>
    <div class="card">
    <h2>Sign in</h2>

    <form id="loginForm" method="POST" action="php/login.php">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required />

      <label for="password">Password</label>
      <input id="password" name="password" type="password" required />

      <button class="primary">Sign in</button>
    </form>

    <button id="googleSignIn" class="google-btn" style="margin-top:10px;">
      <img src="../Pictures/loginPage/google.png" alt="" width="18" />
      Sign in with Google
    </button>

    <button class="secondary" type="button" onclick="window.location.href='../landingPage.html'">Cancel</button>

    <div class="row">
      <a href="#" id="forgotLink">Forgot password?</a>
      <a href="signup.php" id="signupLink">Create account</a>
    </div>

    <div id="msg" aria-live="polite"></div>
    <div class="small">By signing in you agree to our terms.</div>
  </div>
  <!-- <script src="../script/auth.js" defer></script> -->
</body>

</html>