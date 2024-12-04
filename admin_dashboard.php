<?php
include("system/auth.php");
require('system/db.php');

// Ensure only admins access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Initialize status message
$status = "";

// Handle post submission
if (isset($_POST['submitPost'])) {
    // Sanitize the input
    $postContent = stripslashes($_POST['adminPostContent']);
    $postContent = mysqli_real_escape_string($con, $postContent);

    // Get current date and time
    $postDate = date("Y-m-d H:i:s");

    // Handle image upload
    $imagePath = ""; // Default value if no image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = $_FILES['image']['name'];
        $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate image extension
        if (in_array(strtolower($imageExt), $allowedExts)) {
            $newImageName = uniqid() . '.' . $imageExt;
            $imageUploadPath = 'uploads/' . $newImageName;
            move_uploaded_file($imageTmpPath, $imageUploadPath);
            $imagePath = $imageUploadPath;
        }
    }

    // Insert post into the database, marking it as an admin post
    $query = "INSERT INTO posts (post, post_date, submittedby, is_admin_post, image_path) 
              VALUES ('$postContent', '$postDate', '{$_SESSION['username']}', 1, '$imagePath')";

    if (mysqli_query($con, $query)) {
        $status = "Post created successfully!";
    } else {
        $status = "Error: " . mysqli_error($con);
    }
}

// Fetch all posts, prioritize admin posts (is_admin_post = 1) at the top
$query = "SELECT * FROM posts ORDER BY is_admin_post DESC, post_date DESC";
$result = mysqli_query($con, $query);

// Fetch profile picture for the submittedby user
function getProfilePic($username) {
    global $con;
    $query = "SELECT prof_pic FROM users WHERE username = '$username'";
    $result = mysqli_query($con, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['prof_pic'];
    }
    return ''; // Return empty if no profile picture is found
}

// Fetch the like count for a post
function getLikeCount($postId) {
    global $con;
    $query = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = '$postId'";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['like_count'];
}

// Handle the like/unlike button action
if (isset($_GET['like']) || isset($_GET['unlike'])) {
    $postId = isset($_GET['like']) ? $_GET['like'] : $_GET['unlike'];
    $username = $_SESSION['username'];

    if (isset($_GET['like'])) {
        // Check if the user has already liked the post
        $checkQuery = "SELECT * FROM likes WHERE post_id = '$postId' AND username = '$username'";
        $checkResult = mysqli_query($con, $checkQuery);

        if (mysqli_num_rows($checkResult) == 0) {
            // User hasn't liked the post yet, so insert the like
            $likeQuery = "INSERT INTO likes (post_id, username) VALUES ('$postId', '$username')";
            mysqli_query($con, $likeQuery);
        }
    } elseif (isset($_GET['unlike'])) {
        // User has already liked the post, so remove the like
        $unlikeQuery = "DELETE FROM likes WHERE post_id = '$postId' AND username = '$username'";
        mysqli_query($con, $unlikeQuery);
    }

    // Redirect back to the page to refresh the like count
    header("Location: admin_dashboard.php");
    exit();
}

// Handle post deletion (if applicable)
if (isset($_GET['deletePost'])) {
    $postId = $_GET['deletePost'];

    // Ensure the post exists
    $deleteQuery = "DELETE FROM posts WHERE post_id = '$postId'";
    if (mysqli_query($con, $deleteQuery)) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $status = "Error deleting post: " . mysqli_error($con);
    }
}


// Handle post edit
if (isset($_POST['editPost'])) {
    $postId = $_POST['postId'];
    $newContent = stripslashes($_POST['editedContent']);
    $newContent = mysqli_real_escape_string($con, $newContent);

    // Update post content
    $updateQuery = "UPDATE posts SET post = '$newContent' WHERE post_id = '$postId'";
    if (mysqli_query($con, $updateQuery)) {
        $status = "Post updated successfully!";
    } else {
        $status = "Error updating post: " . mysqli_error($con);
    }
}

// Check if the query was successful
if (!$result) {
    die('Query failed: ' . mysqli_error($con));
}

// Function to display time difference
function TimeAgo($oldTime, $newTime) {
    $timeDifference = strtotime($newTime) - strtotime($oldTime);
    $units = [
        31536000 => "year",
        2592000 => "month",
        86400 => "day",
        3600 => "hour",
        60 => "minute",
        1 => "second"
    ];

    foreach ($units as $unitInSeconds => $unitName) {
        if ($timeDifference >= $unitInSeconds) {
            $unitCount = floor($timeDifference / $unitInSeconds);
            return "$unitCount $unitName" . ($unitCount > 1 ? "s" : "") . " ago";
        }
    }
    return "Just now";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Navigation Bar -->
    <div class="w-full bg-gray-900 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="admin_dashboard.php" class="text-xl font-bold">Admin Dashboard</a>
            <div class="space-x-4">
                <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="hover:underline"><?php echo $_SESSION['username']; ?></a>
                <a href="edit.php" class="hover:underline">Edit Profile</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto my-8">
        <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

        <!-- Status Message -->
        <?php if ($status) { ?>
            <div class="bg-green-200 text-green-700 p-4 mb-4 rounded">
                <?php echo $status; ?>
            </div>
        <?php } ?>

        <!-- Post Creation Form -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="font-semibold text-lg mb-4">Create a New Post</h2>
            <form action="admin_dashboard.php" method="post" enctype="multipart/form-data">
                <textarea name="adminPostContent" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Write your post here..." required></textarea>

                <!-- Image Upload -->
                <div class="flex items-center space-x-4 mt-4">
                    <div class="flex items-center justify-center border-2 border-gray-300 rounded-md p-2 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                        <input type="file" name="image" id="file" class="hidden" />
                        <label for="file" class="text-gray-600 text-sm font-semibold cursor-pointer">
                            <!-- Picture Icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l4-4m0 0l4 4m-4-4v12m9-8h.01M21 11V4a2 2 0 00-2-2H5a2 2 0 00-2 2v13a2 2 0 002 2h14a2 2 0 002-2v-7h-5l-3-3-3 3H3V11h18z" />
                            </svg>
                            <span class="sr-only">Upload Image</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end">
                <button type="submit" name="submitPost" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-all">Post</button>
                </div>
            </form>
        </div>

        <!-- Posts List -->
<?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <div class="flex items-center space-x-4">
            <img src="<?php echo getProfilePic($row['submittedby']); ?>" class="w-12 h-12 rounded-full object-cover" alt="User's profile picture">
            <div class="flex justify-between w-full">
                <div>
                    <h3 class="font-semibold text-lg"><?php echo $row['submittedby']; ?></h3>
                    <p class="text-sm text-gray-500"><?php echo TimeAgo($row['post_date'], date("Y-m-d H:i:s")); ?></p>
                </div>
                
                <!-- Admin Post Controls (next to date-time) -->
              <?php if ($row['submittedby'] === $_SESSION['username'] || $_SESSION['role'] === 'admin') { ?>
    <div class="space-x-2">
        <a href="admin_edit.php?post_id=<?php echo $row['post_id']; ?>" class="text-blue-500 hover:underline">Edit</a>
        <a href="?deletePost=<?php echo $row['post_id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
    </div>
<?php } ?>

            </div>
        </div>

        <p class="mt-4 text-lg"><?php echo nl2br($row['post']); ?></p>

        <!-- Image if available -->
        <?php if ($row['image_path']) { ?>
            <img src="<?php echo $row['image_path']; ?>" alt="Post Image" class="mt-4 h-auto rounded-lg mx-auto">
        <?php } ?>

        <?php
$username = $_SESSION['username']; // Get the logged-in user's username
$postId = $row['post_id']; // Get the post ID

// Check if the user has liked the post
$checkLikeQuery = "SELECT * FROM likes WHERE post_id = '$postId' AND username = '$username'";
$likeResult = mysqli_query($con, $checkLikeQuery);
$isLiked = mysqli_num_rows($likeResult) > 0; // true if liked, false otherwise
?>

<div class="flex justify-between items-center mt-4">
    <div class="space-x-4">
        <?php if ($isLiked): ?>
            <!-- Show "Unlike" button if the user has liked the post -->
            <a href="?unlike=<?php echo $postId; ?>" class="text-blue-500 hover:underline">Unlike</a>
        <?php else: ?>
            <!-- Show "Like" button if the user has not liked the post -->
            <a href="?like=<?php echo $postId; ?>" class="text-blue-500 hover:underline">Like</a>
        <?php endif; ?>
        
        <span class="text-sm"><?php echo getLikeCount($postId); ?> Likes</span>
    </div>
</div>


        <!-- Comments Section -->
        <div class="mt-6">
            <h4 class="font-semibold">Comments</h4>

            <!-- Add a new comment (Positioned below likes section) -->
            <form action="system/comment.php" method="post" class="mt-4 flex items-center space-x-4">
    <input type="text" name="commentContent" class="w-full p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Write a comment..." required />
    
    <!-- Add hidden inputs for required data -->
    <input type="hidden" name="page" value="index" /> <!-- or use dynamic value if necessary -->
    <input type="hidden" name="profile" value="<?php echo $_SESSION['username']; ?>" /> <!-- or replace with actual value -->
    <input type="hidden" name="to" value="<?php echo $row['post_id']; ?>" />
    <input type="hidden" name="submitter" value="<?php echo $_SESSION['username']; ?>" />
    
    <button type="submit" class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700 transition duration-300">Comment</button>
</form>


            <!-- Comments List -->
            <?php
            $commentQuery = "SELECT * FROM comments WHERE post_id = {$row['post_id']} ORDER BY comment_date DESC";
            $commentResult = mysqli_query($con, $commentQuery);
            while ($comment = mysqli_fetch_assoc($commentResult)) {
            ?>
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex items-center space-x-3">
                        <img src="<?php echo getProfilePic($comment['submittedby']); ?>" class="w-8 h-8 rounded-full" alt="User's profile picture">
                        <div>
                            <h5 class="font-semibold text-sm"><?php echo $comment['submittedby']; ?></h5>
                            <p class="text-xs text-gray-500"><?php echo TimeAgo($comment['comment_date'], date("Y-m-d H:i:s")); ?></p>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-700"><?php echo nl2br($comment['comment']); ?></p>

                    <!-- Admin/Owner Controls for Comments -->
                    <?php if ($comment['submittedby'] === $_SESSION['username'] || $_SESSION['role'] === 'admin') { ?>
                        <a href="system/cdel.php?comment_id=<?php echo $comment['comment_id']; ?>&post_id=<?php echo $row['post_id']; ?>" class="text-red-500 hover:underline mt-2">Delete Comment</a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>


</body>
</html>



<script src="script.js"></script>





