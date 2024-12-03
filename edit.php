<?php
// Include the database connection and authentication system
require('system/db.php');  // Connects to the database
include("system/auth.php");  // Checks if the user is authenticated

// Retrieve the logged-in user's profile information from the session
$profile = $_SESSION['username'];  // Get the current logged-in user's username

// Query to get the user's details from the database based on the username
$query = "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($con, $profile) . "'";
$result = mysqli_query($con, $query) or die(mysqli_error($con));  // Execute the query and check for errors
$row = mysqli_fetch_assoc($result);  // Fetch user details as an associative array

// Get the user ID for further updates
$id = $row['user_id'];  // User ID fetched from the database

// Initialize status variables
$detstat = $passstat = $dpstat = $emailstat = $uploadOk = "";

// Password change logic
if (isset($_POST['new']) && $_POST['new'] == 'security') {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if ($pass1 == $pass2) {
        $hashedPassword = password_hash($pass1, PASSWORD_DEFAULT);  
        $updatePass = "UPDATE users SET password = '$hashedPassword' WHERE user_id = '$id'";
        if (mysqli_query($con, $updatePass)) {
            $passstat = "Password Updated Successfully.";
        } else {
            $passstat = "Error updating password. Please try again.";
        }
    } else {
        $passstat = "Passwords do not match. Please try again.";
    }
}

// Profile and bio update logic
if (isset($_POST['new']) && $_POST['new'] == 'details') {
    $disp_name = stripslashes($_POST['name']);
    $disp_name = mysqli_real_escape_string($con, $disp_name);
    $disp_name = htmlspecialchars($disp_name);  

    $bio = stripslashes($_POST['bio']);
    $bio = mysqli_real_escape_string($con, $bio);
    $bio = htmlspecialchars($bio);  

    $updateDetails = "UPDATE users SET display_name = '$disp_name', prof_bio = '$bio' WHERE user_id = '$id'";
    if (mysqli_query($con, $updateDetails)) {
        $detstat = "Profile Updated Successfully.";
    } else {
        $detstat = "Error updating profile. Please try again.";
    }
}

// Email update logic
if (isset($_POST['new']) && $_POST['new'] == 'access') {
    $email = $_POST['email'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $updateEmail = "UPDATE users SET email = '$email' WHERE user_id = '$id'";
        if (mysqli_query($con, $updateEmail)) {
            $emailstat = "Email Updated Successfully.";
        } else {
            $emailstat = "Error updating email. Please try again.";
        }
    } else {
        $emailstat = "Invalid email format. Please enter a valid email.";
    }
}

// Profile Picture upload logic
if (isset($_POST['new']) && $_POST['new'] == 'picture') {
    $target_dir = "uploads/avatars/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $filename = basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $sanitizedFilename = uniqid("avatar_", true) . '.' . $imageFileType;
    $target_file = $target_dir . $sanitizedFilename;

    $errors = [];
    $uploadOk = 1;

    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check === false) {
        $errors[] = "File is not an image.";
        $uploadOk = 0;
    }
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        $errors[] = "File size must be less than 5MB.";
        $uploadOk = 0;
    }
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $dpstat = implode("<br>", $errors);
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $dpstat = "The file has been uploaded successfully.";

            $stmt = $con->prepare("UPDATE users SET prof_pic = ? WHERE user_id = ?");
            $stmt->bind_param("si", $target_file, $id);
            if ($stmt->execute()) {
                $dpstat .= " Profile picture updated successfully.";
            } else {
                $dpstat .= " Error updating profile picture in the database.";
            }
            $stmt->close();
        } else {
            $dpstat = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

    <!-- Navigation Bar -->
    <div class="w-full bg-gray-900 text-white p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold">Home</a>
            <div class="space-x-4">
                <a href="profile.php?user=<?php echo $_SESSION['username']; ?>" class="hover:underline"><?php echo $_SESSION['username']; ?></a>
                <a href="edit.php" class="hover:underline">Edit Profile</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto my-8 p-6 bg-white rounded-lg shadow-lg">

        <h1 class="text-3xl font-semibold text-center mb-6">Edit Profile</h1>

        <!-- Profile Details Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Update Profile Details</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="new" value="details">
                <div>
                    <label for="name" class="block text-gray-700">Profile Name</label>
                    <input type="text" name="name" id="name" placeholder="Display Name" value="<?php echo $row['display_name']; ?>" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="bio" class="block text-gray-700">Your Bio</label>
                    <input type="text" name="bio" id="bio" placeholder="Bio" value="<?php echo $row['prof_bio']; ?>" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md">Update Details</button>
                </div>
                <?php echo $detstat ? "<p class='text-green-500'>$detstat</p>" : ""; ?>
            </form>
        </div>

        <!-- Profile Picture Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Update Profile Picture</h2>
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="new" value="picture">
                <div>
                    <label for="fileToUpload" class="block text-gray-700">Select image to upload:</label>
                    <input type="file" name="fileToUpload" id="fileToUpload" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md">Upload Image</button>
                </div>
                <?php echo $dpstat ? "<p class='text-green-500'>$dpstat</p>" : ""; ?>
            </form>
        </div>

        <!-- Email Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Update Email</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="new" value="access">
                <div>
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email" value="<?php echo $row['email']; ?>" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md">Update Email</button>
                </div>
                <?php echo $emailstat ? "<p class='text-green-500'>$emailstat</p>" : ""; ?>
            </form>
        </div>

        <!-- Password Change Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Change Password</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="new" value="security">
                <div>
                    <label for="pass1" class="block text-gray-700">New Password</label>
                    <input type="password" name="pass1" id="pass1" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="pass2" class="block text-gray-700">Confirm Password</label>
                    <input type="password" name="pass2" id="pass2" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md">Change Password</button>
                </div>
                <?php echo $passstat ? "<p class='text-green-500'>$passstat</p>" : ""; ?>
            </form>
        </div>
    </div>

</body>
</html>