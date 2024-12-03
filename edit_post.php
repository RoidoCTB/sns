<?php
session_start();
include('system/db.php'); // Include your DB connection

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch the post ID from the URL
$post_id = $_GET['id'];

// Fetch the post details from the database
$query = "SELECT * FROM posts WHERE post_id = '$post_id' AND submittedby = '" . mysqli_real_escape_string($con, $_SESSION['username']) . "'";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    die("Post not found or you're not authorized to edit this post.");
}

$row = mysqli_fetch_assoc($result);
$post_content = $row['post']; // The current content of the post
$current_image = $row['image_path']; // The current image path

// Handle form submission (update post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated_post = mysqli_real_escape_string($con, $_POST['post_content']);  // Sanitize the input
    $imagePath = $current_image; // Default: use current image if no new one is uploaded

    // Handle file upload if a new image is selected
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
            $imagePath = $imageUploadPath; // Update the image path to the new image
        }
    }

    // Update the post in the database with the new content and image path
    $update_query = "UPDATE posts SET post = '$updated_post', image_path = '$imagePath' WHERE post_id = '$post_id'";
    if (mysqli_query($con, $update_query)) {
        header('Location: index.php'); // Redirect to the feed page after editing
        exit();
    } else {
        echo "Error updating post: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Navigation Bar -->
    <div class="w-full bg-gray-900 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold">Home</a>
            <div class="space-x-4">
                <a href="profile.php?user=<?php echo htmlspecialchars($_SESSION['username']); ?>" class="hover:underline"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                <a href="edit.php" class="hover:underline">Edit Profile</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto my-8 bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold mb-4">Edit Your Post</h2>
        
        <form action="edit_post.php?id=<?php echo htmlspecialchars($post_id); ?>" method="post" enctype="multipart/form-data">
            <textarea name="post_content" class="w-full p-4 border border-gray-300 rounded-md" rows="6"><?php echo htmlspecialchars($post_content); ?></textarea>
            
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

            <!-- Image Preview Section -->
            <div id="image-preview-container" class="mt-4">
                <?php if ($current_image): ?>
                    <img src="<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" class="mt-4 w-32 h-32 object-cover rounded-md mx-auto">
                <?php endif; ?>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md mt-4">Save Changes</button>
        </form>
    </div>

</body>
</html>

<script src="script.js"></script>


