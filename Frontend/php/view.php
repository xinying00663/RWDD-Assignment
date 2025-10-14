<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <center>
        <h1>View data from database:</h1>

        <table>
            <tr>
                <th>UserID</th>
                <th>Full_Name</th>
                <th>Birth_Date</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Username</th>
                <th>Password</th>
                <th>Phone_Number</th>
                <th>City_or_Neighbourhood</th>
                <th>Additional_info</th>
                <th>Join_date</th>
                <th>Last_login</th>
                <th>Reset_token</th>
                <th>Reset_expiry</th>
            </tr>
            <?php
                include("connect.php");
                try{
                    $sql="SELECT * FROM users";
                    $stmt=$pdo->query($sql);
                    $users=$stmt->fetchAll(PDO::FETCH_ASSOC);
                    if(empty($users)){
                        die("<script>alert('No data from database');<script>");
                    }else{
                        foreach($users as $row){
                            echo "<tr>";
                            echo "<td>".$row['UserID']."</td>";
                            echo "<td>".$row['users_Full_Name']."</td>";
                            echo "<td>".$row['Birth_Date']."</td>";
                            echo "<td>".$row['Gender']."</td>";
                            echo "<td>".$row['Email']."</td>";
                            echo "<td>".$row['Username']."</td>";
                            echo "<td>".substr($row['Password'],0,20)."</td>";
                            echo "<td>".$row['Phone_Number']."</td>";
                            echo "<td>".$row['City_or_Neighbourhood']."</td>";
                            echo "<td>".$row['Additional_info']."</td>";
                            echo "<td>".$row['Join_date']."</td>";
                            echo "<td>".$row['Last_login']."</td>";
                            echo "<td>".$row['Reset_token']."</td>";
                            echo "<td>".$row['Reset_expiry']."</td>";
                            echo "<tr>";
                        }
                    }
                }catch (PDOException $e){
                    error_log("Database connection failed:".$e->getMessage());
    die('<script>alert("Connection failed:Please check your database connection again!");</script>');
                }
            ?>
        </table>
    </center>
</body>
</html>