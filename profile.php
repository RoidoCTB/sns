<?php
require("system/db.php");
include("system/auth.php");

$disp = "Select display_name from users WHERE username='" . $_SESSION['username'] . "'";
$dispR = mysqli_query($con, $disp);
$DRDR = mysqli_fetch_assoc($dispR);
$naymu = $DRDR['display_name'];

$user = $_REQUEST['user'];

$dp = "";
$userQuery = "SELECT * from users where username='" . $user . "'";
$userResult = mysqli_query($con, $userQuery) or die(mysqli_error());
$userRows = mysqli_fetch_assoc($userResult);
if ($userRows['prof_pic'] == null) $dp = "uploads/avatars/noimg.jpg";
else $dp = $userRows['prof_pic'];

$postCountQuery = "SELECT COUNT(post_id) AS postCTR FROM posts WHERE submittedby='" . $user . "'";
$pcqResult = mysqli_query($con, $postCountQuery) or die(mysqli_error());
$pcqRows = mysqli_fetch_assoc($pcqResult);

$commentCountQuery = "SELECT COUNT(comment_id) AS commentCTR FROM comments WHERE submittedby='$user'";
$ccqResult = mysqli_query($con, $commentCountQuery) or die(mysqli_error());
$ccqRows = mysqli_fetch_assoc($ccqResult);

$profile_picture = "";
$dpQ = "SELECT prof_pic FROM users WHERE username='" . $_SESSION['username'] . "'";
$dpR = mysqli_query($con, $dpQ);
$dpRR = mysqli_fetch_assoc($dpR);
if ($dpRR['prof_pic'] == null)
    $profile_picture = "uploads/avatars/noimg.jpg";
else $profile_picture = $dpRR['prof_pic'];

function TimeAgo($oldTime, $newTime)
{
    $timeCalc = strtotime($newTime) - strtotime($oldTime);
    if ($timeCalc < 0) {
        $timeCalc = "FROM THE DISTANT FUTURE";
    } else if ($timeCalc >= (60 * 60 * 24 * 30 * 12 * 2)) {
        $timeCalc = intval($timeCalc / 60 / 60 / 24 / 30 / 12) . " years ago";
    } else if ($timeCalc >= (60 * 60 * 24 * 30 * 12)) {
        $timeCalc = intval($timeCalc / 60 / 60 / 24 / 30 / 12) . " year ago";
    } else if ($timeCalc >= (60 * 60 * 24 * 30 * 2)) {
        $timeCalc = intval($timeCalc / 60 / 60 / 24 / 30) . " months ago";
    } else if ($timeCalc >= (60 * 60 * 24 * 30)) {
        $timeCalc = intval($timeCalc / 60 / 60 / 24 / 30) . " month ago";
    } else if ($timeCalc >= (60 * 60 * 24 * 2)) {
        $timeCalc = intval($timeCalc / 60 / 60 / 24) . " days ago";
    } else if ($timeCalc >= (60 * 60 * 24)) {
        $timeCalc = " Yesterday";
    } else if ($timeCalc >= (60 * 60 * 2)) {
        $timeCalc = intval($timeCalc / 60 / 60) . " hours ago";
    } else if ($timeCalc >= (60 * 60)) {
        $timeCalc = intval($timeCalc / 60 / 60) . " hour ago";
    } else if ($timeCalc >= 60 * 2) {
        $timeCalc = intval($timeCalc / 60) . " minutes ago";
    } else if ($timeCalc >= 60) {
        $timeCalc = intval($timeCalc / 60) . " minute ago";
    } else if ($timeCalc > 0) {
        $timeCalc .= " seconds ago";
    } else if ($timeCalc == 0) {
        $timeCalc = "Just now";
    } else $timeCalc = "Unknown date";
    return $timeCalc;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $user; ?>'s userpage!</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Top Navbar -->
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
    <!-- End Top Navbar -->

    <div class="container mx-auto my-6 px-6">

        <?php if ($userRows == false) { ?>
            <div class="text-center">
                <h1 class="text-2xl font-bold">User <i><?php echo $user; ?></i> not found!</h1>
                <p class="text-lg mt-2">Maybe you had a typo while typing the user's URL! Check the name after "<i>profile.php?user=</i>"</p>
                <a href="index.php" class="text-blue-600 mt-4 inline-block">Go back to Home</a>
            </div>
        <?php } else { ?>
            <div class="flex justify-between">
                <!-- Profile Sidebar -->
                <div class="w-1/4 bg-white shadow-md rounded-lg p-6 sticky top-0">
                    <div class="flex justify-center mb-4">
                        <img src="<?php echo $dp ?>" alt="Profile Avatar" class="w-32 h-32 rounded-full">
                    </div>
                    <div class="text-center">
                        <p class="text-xl font-semibold"><?php echo $userRows['display_name'] ?></p>
                        <p class="text-sm text-gray-600"><?php echo $userRows['username']; ?></p>
                    </div>
                    <?php if ($userRows['username'] == $_SESSION['username']) { ?>
                        <div class="text-center mt-4">
                            <a href="edit.php" class="text-sm text-green-600 hover:underline">Edit Profile</a>
                        </div>
                    <?php } ?>
                    <hr class="my-4">
                    <div class="text-center text-sm text-gray-600">
                        <p>Account created <?php echo TimeAgo($userRows['join_date'], date("Y-m-d H:i:s")) ?></p>
                        <p>This user has <?php echo $pcqRows['postCTR']; ?> posts</p>
                        <p>This user commented <?php echo $ccqRows['commentCTR']; ?> times</p>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="w-3/4 bg-white shadow-md rounded-lg p-6 ml-6">
                    <header class="mb-4">
                        <h1 class="text-3xl font-bold text-green-600"><?php echo $userRows['display_name'] ?>'s User Page</h1>
                        <p class="text-sm text-gray-500">User Bio</p>
                    </header>

                    <?php if ($userRows['prof_bio'] != null) { ?>
                        <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                            <p class="text-xl italic"><?php echo $userRows['prof_bio'] ?></p>
                        </div>
                    <?php } else { ?>
                        <div class="mb-6 p-4 bg-red-100 rounded-lg">
                            <p class="text-sm text-gray-600">User has no bio available.</p>
                        </div>
                    <?php } ?>

                    <!-- User Posts -->
                    <?php
                    $count = 1;
                    $sel_query = "SELECT * from posts where submittedby='$user' ORDER BY post_date desc;";
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
                        <div class="mb-6 p-6 bg-white shadow-md rounded-lg">
                            <header class="flex items-center mb-4">
                                <img src="<?php echo $p ?>" class="w-12 h-12 rounded-full mr-4" alt="Avatar">
                                <div>
                                    <p class="font-semibold"><?php echo $n['display_name'] ?></p>
                                    <p class="text-sm text-gray-500"><?php echo $row['submittedby']; ?></p>
                                </div>
                            </header>
                            <p class="text-lg"><?php echo $row['post']; ?></p>

                            <footer class="mt-4 flex justify-between items-center text-sm text-gray-600">
                                <p><?php echo TimeAgo($row['post_date'], date("Y-m-d H:i:s")); ?></p>
                                <?php if ($row['submittedby'] == $_SESSION['username']) { ?>
                                    <div>
                                        <a href="edit_post.php?id=<?php echo $pid; ?>" class="text-blue-500 hover:underline">Edit</a>
                                        <a href="system/delete.php?id=<?php echo $row['post_id'] ?>" class="text-red-600 hover:underline">Delete</a>
                                    </div>
                                <?php } ?>
                            </footer>

                            <!-- Comments Section -->
                            <div class="mt-4">
                                <form action="system/comment.php" method="POST">
                                    <input type="hidden" name="page" value="profile">
                                    <input type="hidden" name="profile" value="<?php echo $a; ?>">
                                    <input type="hidden" name="to" value="<?php echo $pid; ?>">
                                    <input type="hidden" name="submitter" value="<?php echo $_SESSION['username']; ?>">
                                    <div class="flex items-center">
                                        <img src="<?php echo $profile_picture ?>" class="w-8 h-8 rounded-full mr-4" alt="Avatar">
                                        <input type="text" name="commentContent" class="w-full p-2 rounded-lg border border-gray-300" placeholder="Write a comment...">
                                    </div>
                                </form>

                                <!-- Comments Display -->
                                <?php
                                $count = 0;
                                $selComment = "SELECT * FROM comments WHERE post_id=$pid";
                                $commentR = mysqli_query($con, $selComment);
                                while (($count < 3) && $commentN = mysqli_fetch_assoc($commentR)) {
                                    $cpN = $commentN['submittedby'];
                                    $cdp = "";
                                    $cdpq = "SELECT display_name, prof_pic FROM users WHERE username='$cpN'";
                                    $cdpr = mysqli_query($con, $cdpq);
                                    $cdpn = mysqli_fetch_assoc($cdpr);
                                    if ($cdpn['prof_pic'] == null) $cdp = "uploads/avatars/noimg.jpg";
                                    else $cdp = $cdpn['prof_pic'];
                                ?>
                                    <div class="flex items-start mb-4">
                                        <img src="<?php echo $cdp; ?>" class="w-8 h-8 rounded-full mr-4" alt="Avatar">
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold"><?php echo $cdpn['display_name'] ?> - <span class="text-gray-500"><?php echo $commentN['submittedby']; ?></span></div>
                                            <p class="text-sm text-gray-700"><?php echo $commentN['comment']; ?></p>
                                            <div class="text-xs text-gray-500 mt-2"><?php echo TimeAgo($commentN['comment_date'], date("Y-m-d H:i:s")); ?></div>
                                        </div>
                                    </div>
                                <?php $count++; } ?>

                                <?php
                                $cCTR = "SELECT COUNT(comment_id) as cCTR FROM comments WHERE post_id=$pid";
                                $cCTRC = mysqli_query($con, $cCTR);
                                $cCTRQ = mysqli_fetch_assoc($cCTRC);
                                if ($cCTRQ['cCTR'] > 3) { ?>
                                    <a href="#" class="text-blue-600">Show more comments</a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

    </div>

</body>

</html>

