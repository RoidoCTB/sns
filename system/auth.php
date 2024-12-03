<?php
/*
Author: Javed Ur Rehman
Website: http://www.allphptricks.com/
*/
?>

<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	if(!isset($_SESSION["username"]))
	{
		header("Location: login.php");
		exit(); 
	}
?>
