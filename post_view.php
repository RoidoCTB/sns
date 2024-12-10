<?php
require("system/db.php");
include("system/auth.php");

$id = $_REQUEST['id'];

$postViewQuery = "SELECT * FROM posts where post_id = '$id'";
$pvqC = mysqli_query($con, $postViewQuery);
$pvqR = mysqli_fetch_assoc($pvqC);

$user = $pvqR['submittedby'];
$dp = "";
$userQuery = "SELECT * from users where username='" . $user . "'";
$userResult = mysqli_query($con, $userQuery) or die(mysqli_error($con));
$userRows = mysqli_fetch_assoc($userResult);
if ($userRows['prof_pic'] == null) $dp = "uploads/avatars/noimg.jpg";
else $dp = $userRows['prof_pic'];

$profile_picture = "";
$dpQ = "SELECT prof_pic FROM users WHERE username='" . $_SESSION['username'] . "'";
$dpR = mysqli_query($con, $dpQ);
$dpRR = mysqli_fetch_assoc($dpR);
if ($dpRR['prof_pic'] == null)
    $profile_picture = "uploads/avatars/noimg.jpg";
else $profile_picture = $dpRR['prof_pic'];

$disp = "Select display_name from users WHERE username='" . $_SESSION['username'] . "'";
$dispR = mysqli_query($con, $disp);
$DRDR = mysqli_fetch_assoc($dispR);
$naymu = $DRDR['display_name'];

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
    <title><?php echo $pvqR['submittedby']; ?>'s post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/w3.css" />
    <link rel="stylesheet" href="css/w3-1.css" />
</head>

<body class="bg-gray-100">

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


    <!-- page layout -->
    <div class="flex justify-between p-8">
        <!-- Profile Sidebar -->
        <div class="w-1/4 bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-xl font-semibold mb-4">Posted by:</h3>
            <div class="flex items-center space-x-4 mb-4">
                <a href="profile.php?user=<?php echo $userRows['username']; ?>">
                    <img src="<?php echo $dp ?>" class="rounded-full h-32 w-32" alt="Avatar">
                </a>
                <div>
                    <p class="text-lg font-semibold"><?php echo $userRows['display_name']; ?></p>
                    <p class="text-gray-500"><?php echo $userRows['username']; ?></p>
                </div>
            </div>

            <div class="border-t pt-4">
                <?php
                $userCTRQ = "SELECT COUNT(DISTINCT submittedby) AS ctr FROM comments WHERE post_id='$id'";
                $uCC = mysqli_query($con, $userCTRQ);
                $uCR = mysqli_fetch_assoc($uCC);

                $userLIST = "SELECT DISTINCT submittedby FROM comments WHERE post_id='$id' ORDER BY submittedby ASC";
                $uLC = mysqli_query($con, $userLIST);
                ?>
                <p><b>Thread participants: (<?php echo $uCR['ctr']; ?>)</b></p>
                <ul class="space-y-2 mt-4">
                    <?php while ($ulR = mysqli_fetch_assoc($uLC)) {
                        $p = "";
                        $q = "SELECT display_name, prof_pic FROM users WHERE username='" . $ulR['submittedby'] . "'";
                        $qC = mysqli_query($con, $q);
                        $qR = mysqli_fetch_assoc($qC);
                        if ($qR['prof_pic'] == null) $p = "uploads/avatars/noimg.jpg";
                        else $p = $qR['prof_pic'];
                    ?>
                        <li class="flex items-center space-x-2">
                            <img src="<?php echo $p; ?>" width="32" height="32" class="rounded-full" alt="Avatar">
                            <a href="profile.php?user=<?php echo $ulR['submittedby']; ?>" class="text-blue-500"><?php echo $qR['display_name']; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Post View -->
        <div class="w-3/4 bg-white p-6 rounded-lg shadow-md">
            <?php
            $count = 1;
            $sel_query = "Select * from posts where post_id='$id' ORDER BY post_date desc;";
            $result = mysqli_query($con, $sel_query);
            while ($row = mysqli_fetch_assoc($result)) {
                $pid = $row['post_id'];
                $p = "";
                $a = $row['submittedby'];
                $q = "SELECT display_name,prof_pic FROM users WHERE username='$a';";
                $r = mysqli_query($con, $q);
                $n = mysqli_fetch_assoc($r);
                if ($n['prof_pic'] == null)
                    $p = "uploads/avatars/noimg.jpg";
                else $p = $n['prof_pic'];
            ?>
                <div class="bg-white p-4 rounded-lg shadow mb-6" id="<?php echo $pid; ?>">
                    <!-- post header -->
                    <div class="flex items-center space-x-4 mb-4">
                        <a href="profile.php?user=<?php echo $row['submittedby'] ?>">
                            <img src="<?php echo $p ?>" class="rounded-full h-14 w-14" alt="Avatar">
                        </a>
                        <div>
                            <p class="font-semibold text-lg"><?php echo $n['display_name']; ?></p>
                            <p class="text-gray-500"><?php echo $row['submittedby']; ?></p>
                        </div>
                    </div>

                    <!-- Post Content -->
                    <div>
                        <p class="text-lg"><?php echo $row['post']; ?></p>
                        <div class="text-sm text-gray-500 mt-2">
                            <p>
                                <?php echo TimeAgo($row['post_date'], date("Y-m-d H:i:s")); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- Comments Section -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <h3 class="text-xl font-semibold mb-4">Comments</h3>
                <?php
                $commentQuery = "SELECT * FROM comments WHERE post_id='$id' ORDER BY comment_date";
                $commentResult = mysqli_query($con, $commentQuery);
                while ($comment = mysqli_fetch_assoc($commentResult)) {
                    $commentAuthor = $comment['submittedby'];
                    $commentText = $comment['comment'];
                    $commentDate = $comment['comment_date'];
                    $commentUserQuery = "SELECT * FROM users WHERE username='$commentAuthor'";
                    $commentUserResult = mysqli_query($con, $commentUserQuery);
                    $commentUser = mysqli_fetch_assoc($commentUserResult);
                    $commentAvatar = $commentUser['prof_pic'] ?: 'uploads/avatars/noimg.jpg';
                ?>
                    <div class="flex items-start space-x-4 mb-4">
                        <img src="<?php echo $commentAvatar; ?>" class="rounded-full h-10 w-10" alt="Avatar">
                        <div class="flex-1">
                            <p class="font-semibold"><?php echo $commentUser['display_name']; ?></p>
                            <p class="text-gray-600"><?php echo $commentText; ?></p>
                            <p class="text-xs text-gray-500"><?php echo TimeAgo($commentDate, date("Y-m-d H:i:s")); ?></p>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>

</body>

</html>


