<?php
session_start();
if (!isset($_SESSION['isShowData'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Detail</title>
</head>
<body>
    <h1>Edit Profile Page</h1>
    <p>This is the Edit Detail page.</p>
    <a href="index.php">Back to Home</a>
</body>
</html>