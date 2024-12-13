
<?php
session_start();  // Start the session

// Debugging line to check session status
if (!isset($_SESSION['username'])) {
    // If no session is found, redirect to login page
    header("Location: login.php");
    exit();
}

// Rest of your code goes here
include("system/auth.php");
require('system/db.php');

// Fetch user data and other operations
$query = "SELECT display_name, prof_pic FROM users WHERE username = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 's', $_SESSION['username']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $naymu, $profile_picture);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Default profile picture if none is set
if (empty($profile_picture)) {
    $profile_picture = "uploads/avatars/noimg.jpg";
}


$status = "";
if (isset($_POST['new']) && $_POST['new'] == 'status') {
    // Sanitize and insert the new post
    $post = trim($_POST['post']);
    $post = mysqli_real_escape_string($con, $post);
    $post = nl2br($post); // Converts newlines to <br> tags

    $trn_date = date("Y-m-d H:i:s");
    $rating = 0;
    $submittedby = $_SESSION["username"];
    $imagePath = ''; // Default, no image uploaded

    // Handle file upload if an image is selected
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
        } else {
            $status = "Invalid image type. Please upload a jpg, jpeg, png, or gif image.";
        }
    }

    // Insert post into the database
    $insert_query = "INSERT INTO posts (post_date, post, submittedby, rating, image_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($stmt, 'sssis', $trn_date, $post, $submittedby, $rating, $imagePath);
    if (mysqli_stmt_execute($stmt)) {
        $status = "New Post Uploaded Successfully.";
    } else {
        $status = "Error uploading post: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
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
    <title>Feed</title>
    <script src="https://cdn.tailwindcss.com" src=></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Navigation Bar -->
<div class="w-full bg-gray-900 text-white p-4">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
       
        <div class="flex items-center">
            <i class="fas fa-users text-3xl text-blue-500 mr-2"></i>
            <a href="index.php" class="text-xl font-bold">Home</a>
        </div>

        <div class="space-x-4">
            <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="hover:underline"><?php echo $_SESSION['username']; ?></a>
            <a href="edit.php" class="hover:underline">Edit Profile</a>
            <a href="logout.php" class="hover:underline">Logout</a>
        </div>
    </div>
</div>

    <div class="max-w-6xl mx-auto my-8">
        <div class="grid grid-cols-4 gap-8">
            
            <!-- Profile Section -->
            <div class="col-span-1 bg-white shadow-lg rounded-lg p-6 text-center">
                <a href="profile.php?user=<?php echo $_SESSION['username']; ?>">
                    <img src="<?php echo $profile_picture ?>" class="rounded-full h-32 w-32 mx-auto" alt="Avatar">
                </a>
                <div class="mt-4">
                    <p class="text-xl font-semibold"><?php echo $naymu; ?></p>
                    <p class="text-gray-500"><?php echo $_SESSION['username']; ?></p>
                    <a href="edit.php" class="text-blue-500 hover:underline">Edit Profile</a>
                </div>
            </div>

            <!-- Post Section -->
            <div class="col-span-3 bg-white shadow-lg rounded-lg p-6">
            <form method="post" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded-md shadow-lg">
    <input type="hidden" name="new" value="status">
    
    <!-- Post Textarea -->
    <textarea name="post" class="w-full p-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="What's on your mind?"></textarea>

    <!-- File Upload with Picture Icon on the Far Left -->
    <div class="flex items-center space-x-4">
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

    <!-- Image Preview Section -->
    <div id="image-preview-container" class="mt-4"></div>

    <!-- Post Button Container with Flex -->
    <div class="flex justify-end">
        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition-all">Post</button>
    </div>
</form>

                <p class="text-green-500 mt-2"><?php echo $status; ?></p>
            </div>
        </div>

        <div class="mt-8">
    <?php
    $sel_query = "SELECT * FROM posts ORDER BY is_admin_post DESC, post_date DESC";
    $result = mysqli_query($con, $sel_query);
    while ($row = mysqli_fetch_assoc($result)) {
        $pid = $row['post_id'];
        $submittedby = $row['submittedby'];
        $userQuery = "SELECT display_name, prof_pic FROM users WHERE username='$submittedby'";
        $userResult = mysqli_query($con, $userQuery);
        $userData = mysqli_fetch_assoc($userResult);
    
        $profilePic = empty($userData['prof_pic']) ? "uploads/avatars/noimg.jpg" : $userData['prof_pic'];
    
        // Get like count for the post
        $likeCountQuery = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = '$pid'";
        $likeCountResult = mysqli_query($con, $likeCountQuery);
        $likeCount = mysqli_fetch_assoc($likeCountResult)['like_count'];
    
        // Check if the current user has liked the post
        $checkLikeQuery = "SELECT * FROM likes WHERE post_id = '$pid' AND username = '{$_SESSION['username']}'";
        $checkLikeResult = mysqli_query($con, $checkLikeQuery);
        $isLiked = (mysqli_num_rows($checkLikeResult) > 0);
    ?>
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6 relative">
            <div class="flex items-center space-x-4">
                <a href="profile.php?user=<?php echo $submittedby; ?>">
                    <img src="<?php echo $profilePic; ?>" class="rounded-full h-12 w-12" alt="Avatar">
                </a>
                <div>
                    <p class="font-semibold"><?php echo $userData['display_name']; ?>
                        <!-- Show 'Admin' if the post is an admin post -->
                        <?php if ($row['is_admin_post'] == 1) { ?>
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full ml-2">Admin</span>
                        <?php } ?>
                    </p>
                    <p class="text-sm text-gray-500"><?php echo $submittedby; ?></p>
                    <p class="text-sm text-gray-500 mr-4"><?php echo TimeAgo($row['post_date'], date("Y-m-d H:i:s")); ?></p>
                </div>
            </div>
    
            <div class="mt-4">
                <p><?php echo nl2br(htmlspecialchars($row['post'], ENT_QUOTES, 'UTF-8')); ?></p>
                <?php if ($row['image_path']) { ?>
                    <img src="<?php echo $row['image_path']; ?>" alt="Uploaded File" class="mt-4 h-auto rounded-lg mx-auto">
                <?php } ?>
            </div>
    
            <!-- Edit and Delete Buttons (Top-right corner) -->
            <?php if ($row['submittedby'] == $_SESSION['username']) { ?>
                <div class="absolute top-0 right-0 flex items-center space-x-4 mt-4 mr-4">
                    <!-- Edit Button -->
                    <a href="edit_post.php?id=<?php echo $pid; ?>" class="text-blue-500 hover:underline">Edit</a>
                    <!-- Delete Button -->
                    <a href="system/delete.php?id=<?php echo $pid; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                </div>
            <?php } ?>
    
            <!-- Like Section Below Post Content -->
            <div class="mt-4 flex items-center justify-start space-x-4">
                <!-- Like/Unlike Button -->
                <a href="like.php?<?php echo $isLiked ? 'unlike' : 'like'; ?>=<?php echo $pid; ?>" class="text-blue-500 hover:underline">
                    <?php echo $isLiked ? 'Unlike' : 'Like'; ?>
                </a>
                <!-- Like Counter -->
                <span class="text-sm text-gray-600"><?php echo $likeCount; ?> Likes</span>
            </div>
    
            <div class="mt-4">
                <form action="system/comment.php" method="post" class="flex items-center space-x-4">
                    <input type="hidden" name="page" value="index">
                    <input type="hidden" name="to" value="<?php echo $pid; ?>">
                    <input type="hidden" name="submitter" value="<?php echo $_SESSION['username']; ?>">
                    <input type="text" name="commentContent" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Write a comment...">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md">Comment</button>
                </form>
            </div>
    
       <!-- Comments Section -->
<div class="mt-4">
    <?php
    $commentQuery = "SELECT * FROM comments WHERE post_id=$pid LIMIT 3";
    $commentResult = mysqli_query($con, $commentQuery);
    while ($comment = mysqli_fetch_assoc($commentResult)) {
        $commentUser = $comment['submittedby'];
        $commentUserQuery = "SELECT display_name, prof_pic, role FROM users WHERE username='$commentUser'"; // Include role in the query
        $commentUserResult = mysqli_query($con, $commentUserQuery);
        $commentUserData = mysqli_fetch_assoc($commentUserResult);

        $commentProfilePic = empty($commentUserData['prof_pic']) ? "uploads/avatars/noimg.jpg" : $commentUserData['prof_pic'];
    ?>
        <div class="flex items-start space-x-4 mb-4">
            <img src="<?php echo $commentProfilePic; ?>" class="rounded-full h-8 w-8" alt="Avatar">
            <div>
                <p class="font-semibold">
                    <?php echo $commentUserData['display_name']; ?>
                    <!-- Show 'Admin' badge if the user is an admin -->
                    <?php if ($commentUserData['role'] == 'admin') { ?>
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full ml-2">Admin</span>
                    <?php } ?>
                </p>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($comment['comment'], ENT_QUOTES, 'UTF-8')); ?></p>
                <p class="text-sm text-gray-500"><?php echo TimeAgo($comment['comment_date'], date("Y-m-d H:i:s")); ?></p>
            </div>
        </div>
    <?php } ?>
    <a href="post_view.php?id=<?php echo $pid; ?>" class="text-blue-500 hover:underline">View all comments</a>
</div>

        </div>
    <?php } ?>
</body>
</html>
<script src="script.js"></script>









