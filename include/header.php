<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isShowData'])) {
    header("Location: login.php");
    exit();
}
$isShowData = $_SESSION['isShowData'];
$isShowManagement = $_SESSION['isShowManagement'] ?? false;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <header class="tab-bar">
        <div class="tab-bar-container">
            <h1 class="tab-title">Phone Number Management</h1>
            <nav class="tab-nav">
                <ul class="tab-menu">
                    <?php if ($isShowData): ?>
                    <li class="dropdown">
                        <a href="#" title="User Management">
                            <i class="fas fa-user"></i>
                        </a>
                        <div class="dropdown-menu">
                        <?php if ($isShowData): ?>
                            <a href="edit_profile.php">Edit Profile</a>
                        <?php endif; ?>
                        <?php if ($isShowManagement): ?>
                            <a href="user_management.php">Users Management</a>
                        <?php endif; ?>
                        </div>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="logout.php" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropdown = document.querySelector('.dropdown');

        dropdown.addEventListener('click', (event) => {
            dropdown.classList.toggle('active');
        });

        document.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
    </script>
</body>
</html>