<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "connect.php";

if($_SERVER["REQUEST_METHOD"]=="POST"){
  echo '<script>alert("Login request received");</script>';
  // $response=["success"=>false,"message"=>""];

  $email=$_POST["email"] ??"";
  $password=$_POST["password"] ??"";
  
  if(empty($email)|| empty($password)){
    echo "<script>
            alert('Please fill in all required fields.');
            window.history.back();
        </script>";
        exit;
    //$response["message"]="Please fill in all required fields.";
  }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
    echo "<script>
            alert('Please enter a valid email address.');
            window.history.back();
        </script>";
        exit;
  }else{
    try{
      $stmt=$pdo->prepare("SELECT UserID,Password,Email FROM users WHERE Email=?");
      $stmt->execute([$email]);
      $user=$stmt->fetch(PDO::FETCH_ASSOC);

      if($user&& $password===$user["Password"]){
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
      }else{
        echo "<script>
                alert('Invalid email or password.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="Invalid email or password.";
      }
    }catch(PDOException $e){
      error_log("Database error:".$e->getMessage());
      echo "<script>
              alert('Error in processing your login. Please try again.');
              window.history.back();
          </script>";
          exit;
      //$response["message"]="Error in processing your login. Please try again.";
    }
  }
  // header("Content-Type:application/json");
  echo json_encode($response);
  exit;
}
?>
