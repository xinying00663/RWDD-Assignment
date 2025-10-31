<?php
$host="localhost";
$user="root";
$password="";
$dbname="ecogo";

//Connect to database
try{
    $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$user,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    // Database connected successfully (removed echo to prevent breaking JSON APIs)
}catch(PDOException $e){
    error_log("Database connection failed:".$e->getMessage());
    // Only show error for non-API pages
    if (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
        die('<script>alert("Connection failed:Please check your database connection again!");</script>');
    } else {
        die();
    }
}
?>
