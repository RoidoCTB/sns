<?php
// Start a session
session_start();

// Initialize status message
$status = "";

// Connect to the database
require('system/db.php');

// Check if the user is submitting the login form
if (isset($_POST['login']) && $_POST['login'] == 'details') {

    // Get user input, remove backslashes and escape special characters to prevent SQL injection
    $usernameLogin = stripslashes($_REQUEST['usernameLogin']); 
    $usernameLogin = mysqli_real_escape_string($con, $usernameLogin); 

    // Get password input and escape special characters
    $passwordLogin = stripslashes($_REQUEST['passwordLogin']);
    $passwordLogin = mysqli_real_escape_string($con, $passwordLogin);

    // Query the database to check if the username/email and password match
    $queryLogin = "SELECT * FROM `users` WHERE (username='$usernameLogin' OR email='$usernameLogin')";
    $resultLogin = mysqli_query($con, $queryLogin) or die(mysqli_error($con));

    // Check if there is exactly one matching user
    $rowsLogin = mysqli_num_rows($resultLogin);
    // Check if there is exactly one matching user
if ($rowsLogin == 1) {
    // Fetch the user data
    $row = mysqli_fetch_assoc($resultLogin);
    
    // Verify the password using password_verify() and hashed password
    if (password_verify($passwordLogin, $row['password'])) {
        // Set session variables after successful login
        $_SESSION["username"] = $row["username"];
        $_SESSION["display_name"] = $row["display_name"];
        $_SESSION["role"] = $row["role"]; // Store the user's role in the session
        
        // Redirect the user based on their role
        if ($_SESSION["role"] == 'admin') {
            header("Location: admin_dashboard.php"); // Redirect to the admin dashboard
        } else {
            header("Location: index.php"); // Redirect to the user home page (feed)
        }
        exit();  // Ensure no further code is executed after the redirect
    } else {
        // If password does not match, show an error message
        $status = "Incorrect username/password.";
    }
} else {
    // If no matching user found, show an error message
    $status = "Username or email does not exist.";
}

}

// Check if the user is submitting the registration form
else if (isset($_POST['register']) && $_POST['register'] == 'details') {
    // Initialize status message for registration
    $status = "";
    
    // Get user input and escape special characters
    $usernameRegister = stripslashes($_REQUEST['usernameRegister']);
    $usernameRegister = mysqli_real_escape_string($con, $usernameRegister);

    $emailRegister = stripslashes($_REQUEST['emailRegister']);
    $emailRegister = mysqli_real_escape_string($con, $emailRegister);

    $passwordRegister = stripslashes($_REQUEST['passwordRegister']);
    $passwordRegister = mysqli_real_escape_string($con, $passwordRegister);

    $display_nameRegister = stripslashes($_REQUEST['display_nameRegister']);
    $display_nameRegister = mysqli_real_escape_string($con, $display_nameRegister);
    
    // Get current date and time for registration
    $trn_date = date("Y-m-d H:i:s");

    // Hash the password securely using password_hash()
    $hashedPassword = password_hash($passwordRegister, PASSWORD_DEFAULT);

    // Insert new user data into the database
    $queryR = "INSERT INTO `users` (username, password, email, display_name, join_date) 
               VALUES ('$usernameRegister', '$hashedPassword', '$emailRegister', '$display_nameRegister', '$trn_date')";

    // Execute the query and check if registration is successful
    $resultR = mysqli_query($con, $queryR);
    if ($resultR) {
        // If successful, show a success message
        $status = "Account created! You can now <a href='login.php'>log in.</a><br>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Container for Login Form -->
    <div class="bg-white w-full max-w-md p-8 rounded-xl shadow-md">
        <!-- Status message -->
        <p class="text-red-500 text-center mb-4"><?php echo $status; ?></p>
        
        <!-- Login Form -->
        <form action="" method="post" name="login" class="space-y-4">
            <h1 class="text-2xl font-semibold text-center text-gray-800">Log In</h1>
            
            <input type="hidden" name="login" value="details" />

            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="text" name="usernameLogin" placeholder="Username or Email" required />
            
            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="password" name="passwordLogin" placeholder="Password" required />

            <button class="w-full bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Login
            </button>

            <p class="text-center text-sm text-gray-600">No account? <a href="#" onclick="showOther('register')" class="text-blue-600">Register here</a></p>
        </form>
    </div>

    <!-- Registration Form (Hidden by Default) -->
    <div id="register" class="bg-white w-full max-w-md p-8 rounded-xl shadow-md mt-8 hidden">
        <form action="" method="post" name="registerForm" class="space-y-4">
            <h1 class="text-2xl font-semibold text-center text-gray-800">Register</h1>

            <input type="hidden" name="register" value="details" />

            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="text" name="usernameRegister" placeholder="Username" required />

            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="email" name="emailRegister" placeholder="Email" required />

            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="password" name="passwordRegister" placeholder="Password" required />

            <input class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   type="text" name="display_nameRegister" placeholder="Display Name" required />

            <button class="w-full bg-green-600 text-white p-3 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                Register
            </button>

            <p class="text-center text-sm text-gray-600">Already have an account? <a href="#" onclick="showOther('register')" class="text-blue-600">Log in here</a></p>
        </form>
    </div>

    <script>
        // Toggle the visibility of the registration form
        function showOther(id) {
            document.getElementById(id).classList.toggle("hidden");
        }
    </script>

</body>

</html>