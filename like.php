<?php
include("system/auth.php");
require('system/db.php');

// Check if 'like' or 'unlike' GET parameter is set
if (isset($_GET['like']) || isset($_GET['unlike'])) {
    $postId = isset($_GET['like']) ? $_GET['like'] : $_GET['unlike'];
    $username = $_SESSION['username'];

    if (isset($_GET['like'])) {
        // User hasn't liked this post yet, insert the like
        $likeQuery = "INSERT INTO likes (post_id, username) VALUES ('$postId', '$username')";
        mysqli_query($con, $likeQuery);
    } elseif (isset($_GET['unlike'])) {
        // User has already liked the post, remove the like
        $unlikeQuery = "DELETE FROM likes WHERE post_id = '$postId' AND username = '$username'";
        mysqli_query($con, $unlikeQuery);
    }

    // Redirect based on the user's role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}
?>





