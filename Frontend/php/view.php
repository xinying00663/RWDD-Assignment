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
                <th>BirthDate</th>
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
                $sql="SELECT *FROM users";
                $result=mysqli_query($dbConn,$sql);
                if(mysqli_num_rows($result)<=0){
                    die("<script>alert('No data from database');<script>");
                }
                while($rows=$mysqliquery_fetch_array($result)){
                    echo "<tr>";
                    echo "<td>".$rows['users_UserID']."</td>";
                    echo "<td>".$rows['users_Full_Name']."</td>";
                    echo "<td>".$rows['users_BirthDate']."</td>";
                    echo "<td>".$rows['users_Gender']."</td>";
                    echo "<td>".$rows['users_Email']."</td>";
                    echo "<td>".$rows['users_Username']."</td>";
                    echo "<td>".$rows['users_Password']."</td>";
                    echo "<td>".$rows['users_Phone_Number']."</td>";
                    echo "<td>".$rows['users_City_or_Neighbourhood']."</td>";
                    echo "<td>".$rows['users_Additional_info']."</td>";
                    echo "<td>".$rows['users_Join_date']."</td>";
                    echo "<td>".$rows['users_Last_login']."</td>";
                    echo "<td>".$rows['users_Reset_token']."</td>";
                    echo "<td>".$rows['users_Reset_expiry']."</td>";
                }
            ?>
        </table>
    </center>
</body>
</html>