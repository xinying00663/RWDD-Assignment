<?php

include "connect.php";

session_start();
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["action"])){
  $response=["success"=>false,"message"=>""];

  if($_POST["action"]==="login"){
    $email=filter_var($_POST["email"] ??"",FILTER_VALIDATE_EMAIL);
    $password=$_POST["password"] ??"";
  
    if(empty($email)|| empty($password)){
      $response["message"]="Please fill in all required fields.";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
      $response["message"]="Please enter a valid email address.";
    }else{
      try{
        $stmt=$pdo->prepare("SELECT UserID,Password,Email FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user=$stmt->fetch(PDO::FETCH_ASSOC);

        if($user&&password_verify($password,$user["Password"])){
          $lastLogin=date("Y-m-d H:i:s");
          $stmt=$pdo->prepare("UPDATE users SET Last_login=? WHERE UserID=?");
          $stmt->execute([$lastLogin,$user["UserID"]]);

          $_SESSION["user_id"]=$user["UserID"];
          $_SESSION["email"]=$user["Email"];
          $_SESSION["logged_in"]=true;

          $response["success"]=true;
          $response["message"]="Your account has been created successfully.";
          $response["user_id"]=$user["UserID"];
          $response["redirect"]="homePage.html";
        }else{
          $response["message"]="Invalid email or password.";
        }
      }catch(PDOException $e){
            error_log("Database error:".$e->getMessage());
            $response["message"]="Error in processing your login. Please try again.";
      }
    }
  }
  elseif($_POST["action"]=="forgot password"){
    $email=filter_var($_POST["email"] ??"",FILTER_VALIDATE_EMAIL);

    if(empty($email)||!filter_var($email,FILTER_VALIDATE_EMAIL)){
      $response["message"]="Please enter a valid email address.";
    }else{
      try{
        $stmt=$pdo->prepare("SELECT UserID FROM users WHERE Email=?");
        $stmt->execute([$email]);
        $user=$stmt->fetch();

        if($user){
          $reset_token=bin2hex(random_bytes(32));
          $expiry=date("Y-m-d H:i:s",time()+3600); // 1hour expiry

          $stmt=$pdo->prepare("UPDATE users SET reset_token=?,reset_expiry=? WHERE Email=?");
          $stmt->execute([$resetToken,$expiry,$email]);

          $response["success"]=true;
          $response["message"]="Password reset instructions have been sent to your email";
        }else{
          $response["message"]="No account found from the email address.";
        }
      }catch(PDOException $e){
        error_log("Database error:".$e->getMessage());
        $response["message"]="Error in processing your request. Please try again.";
      }
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
    <title>Login</title>
    <link rel="stylesheet" href="styles/loginPage.css">
</head>
<body>
    <div class="card">
    <h2>Sign in</h2>

    <form id="loginForm">
      <label for="email">Email</label>
      <input id="email" type="email" required />

      <label for="password">Password</label>
      <input id="password" type="password" required />

      <button class="primary" type="submit">Sign in</button>
    </form>

    <button id="googleSignIn" class="google-btn" style="margin-top:10px;">
      <img src="Pictures/loginPage/google.png" alt="" width="18" />
      Sign in with Google
    </button>

    <button class="secondary" type="button" onclick="window.location.href='landingPage.html'">Cancel</button>

    <div class="row">
      <a href="#" id="forgotLink">Forgot password?</a>
      <a href="signup.html" id="signupLink">Create account</a>
    </div>

    <div id="msg" aria-live="polite"></div>
    <div class="small">By signing in you agree to our terms.</div>
  </div>
</body>

</html>