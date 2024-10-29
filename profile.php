<?php
    include("useraccount.php");
    require('connection.php');
    if(isset($_SESSION['uid']))
    {
    $userid=$_SESSION['uid'];
    $query="select * from registration where lid='$userid'";
    $re=mysqli_query($con,$query);
    $row=mysqli_fetch_array($re);
    }
    else{
        header("location:index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Profile</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .content {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .icon {
            text-align: right;
            margin-bottom: 20px;
        }

        .icon a {
            color: #555;
            font-size: 20px;
            text-decoration: none;
        }

        .icon a:hover {
            color: #000;
        }

        label {
            font-size: 18px;
            color: #777;
            font-weight: bold;
            display: inline-block;
            width: 150px;
            margin-bottom: 10px;
        }

        h4 {
            display: inline-block;
            font-size: 18px;
            color: #333;
            margin: 0;
            vertical-align: middle;
        }

        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
        }

        h3 {
            margin-left: 10px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            font-size: 24px;
        }

        .profile {
            margin-left: 20px;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="icon"><a href="editprofile.php"><i class="fa fa-edit"></i></a></div>
    <div class="profile">
        <label>Name:</label><h4><?php echo $row['name'];?></h4><br>
        <label>Email:</label><h4><?php echo $row['email'];?></h4><br>
        <label>Phone number:</label><h4><?php echo $row['phone'];?></h4><br>
        <hr>
        <h3>Address</h3>
        <?php
        $sql = "select * from registration where lid='$userid'";
        $res = mysqli_query($con, $sql);
        while($row1 = mysqli_fetch_array($res))
        {
            if($row1['address']) {
                echo '<label>Address:</label><h4>' . $row1['address'] . '</h4><br>';
            }
            if($row1['landmark']) {
                echo '<label>Landmark:</label><h4>' . $row1['landmark'] . '</h4><br>';
            }
            if($row1['roadname']) {
                echo '<label>Roadname:</label><h4>' . $row1['roadname'] . '</h4><br>';
            }
            if($row1['district']) {
                echo '<label>District:</label><h4>' . $row1['district'] . '</h4><br>';
            }
            if($row1['state']) {
                echo '<label>State:</label><h4>' . $row1['state'] . '</h4><br>';
            }
            if($row1['pincode']) {
                echo '<label>Pincode:</label><h4>' . $row1['pincode'] . '</h4><br>';
            }
            echo '<hr>';
        }
        ?>
    </div>
</div>
</body>
</html>
