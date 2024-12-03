<?php
include("system/auth.php");
require('system/db.php');

// Ensure only admins access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Fetch the post ID from the URL
$post_id = $_GET['id'];

// Delete the post from the database
$query = "DELETE FROM posts WHERE post_id = '$post_id'";
if (mysqli_query($con, $query)) {
    header('Location: admin_dashboard.php'); // Redirect to admin dashboard after deletion
    exit();
} else {
    echo "Error deleting post: " . mysqli_error($con);
}
?>

