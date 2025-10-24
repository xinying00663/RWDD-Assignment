<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "connect.php";
// echo "Database connected successfully!";

session_start();

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $email=$_POST["email"] ??"";
    $password=$_POST["password"] ??"";
    $confirmPassword=$_POST["confirmPassword"] ??"";
    $userRole=$_POST["userRole"] ??"user"; // Default to 'user' if not set
    $terms=isset($_POST["terms"]) ? true : false;
    
    $response=["success"=>false,"message"=>""];
    
   //validate inputs
    if(empty($email)|| empty($password)|| empty($confirmPassword)){
        echo "<script>
                alert('All fields are required.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="All fields are required.";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        echo "<script>
                alert('Please enter a valid email address.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="Please enter a valid email address.";
    }elseif(strlen($password)<8){
        echo "<script>
                alert('Password must be at least 8 characters long.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="Password must be at least 8 characters long.";
    }elseif($password!==$confirmPassword){
        echo "<script>
                alert('Passwords do not match.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="Passwords do not match.";
    }elseif(!$terms){
        echo "<script>
                alert('You must agree to the terms and conditions.');
                window.history.back();
            </script>";
            exit;
        // $response["message"]="You must agree to the terms and conditions.";
    }elseif(!in_array($userRole, ['user', 'admin'])){
        echo "<script>
                alert('Please select a valid account type.');
                window.history.back();
            </script>";
            exit;
    }else{
        try{
            $stmt=$pdo->prepare("SELECT UserID FROM users WHERE Email=?");
            $stmt->execute([$email]);

            if($stmt->fetch()){
                echo "<script>
                        alert('An account with this email already exists.');
                        window.history.back();
                    </script>";
                    exit;
                // $response["message"]="An account with this email already exists.";
            }else{
                $hashedPassword=password_hash($password,PASSWORD_DEFAULT);
                $joinDate=date("Y-m-d H:i:s");

                $stmt=$pdo->prepare("INSERT INTO users(Email, Password, Role, Join_date, Last_login) VALUES(?,?,?,?,?)");
                if($stmt->execute([$email,$hashedPassword,$userRole,$joinDate,$joinDate])){
                    $userID=$pdo->lastInsertId();

                    $_SESSION["user_id"]=$userID;
                    $_SESSION["Email"]=$email;
                    $_SESSION["logged_in"]=true;

                    // method 1
                    // header("Location: ../loginPage.html");
                    // exit;

                    // method 2
                    echo "<script>
                        alert('Your account has been created successfully!');
                        window.location.href = '../profileSetup.html';
                    </script>";
                    exit;
                     
                    // $response["success"]=true;
                    // $response["message"]="Your account has been created successfully.";
                    // $response["user_id"]=$userID;
                    // $response["redirectTo"]="profileSetup.php";

                    // $response = [
                    //     "success" => true,
                    //     "message" => "Your account has been created successfully.",
                    //     "user_id" => $userID,
                    //     "redirectTo" => "login.php"
                    // ];

                } else{
                    // method 2
                      echo "<script>
                            alert('Database error, please try again.');
                            window.history.back();
                        </script>";
                        exit;
                    // $response["message"]="Database error, please try again.";
                }
            }
        }catch(PDOException $e){
            error_log("Database error:".$e->getMessage());
            $response["message"]="Error in processing your registration. Please try again.";
        }
    }
    // header("Content-Type:application/json");
    echo json_encode($response);
    exit;
}
?>