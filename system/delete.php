<?php
require('db.php');
$id=$_REQUEST['id'];
$query = "DELETE FROM posts WHERE post_id=$id"; 
$result = mysqli_query($con,$query);
header("Location: ../index.php"); 
?>