<?php
include("auth.php");
require('db.php');

// Ensure only admins access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['comment_id']) && isset($_GET['post_id'])) {
    $commentId = $_GET['comment_id'];
    $postId = $_GET['post_id']; // Optional: use to redirect to the post view after deletion

    // Using prepared statements to prevent SQL injection
    $deleteCommentQuery = "DELETE FROM comments WHERE comment_id = ?";
    if ($stmt = mysqli_prepare($con, $deleteCommentQuery)) {
        // Bind the parameter to the prepared statement
        mysqli_stmt_bind_param($stmt, "i", $commentId);

        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to the post or admin dashboard after deletion
            header("Location: ../admin_dashboard.php"); // Or use $postId to redirect to a specific post
            exit();
        } else {
            // Handle error if deletion fails
            echo "Error: Failed to delete comment. Please try again later.";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        // Handle error if preparing the statement fails
        echo "Error: Could not prepare the SQL statement.";
    }
} else {
    echo "No comment ID or post ID specified.";
}
?>


