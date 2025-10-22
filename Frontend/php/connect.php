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
    echo '<script>console.log("Database connected successfully");</script>';
}catch(PDOException $e){
    error_log("Database connection failed:".$e->getMessage());
    die('<script>alert("Connection failed:Please check your database connection again!");</script>');
}
?>