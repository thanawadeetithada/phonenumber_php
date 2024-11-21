<?php
session_start();
include 'config.php';


if (!isset($_SESSION['isShowData'])) {
    $_SESSION['isShowData'] = false;
}

$isShowData = $_SESSION['isShowData'];

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users_collection WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $update_query = "UPDATE users_collection SET Name = ?, Email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $name, $email, $user_id);

        if ($update_stmt->execute()) {
            $success = "บันทึกข้อมูลสำเร็จแล้ว!";
            $_SESSION['isShowData'] = $name;

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "บันทึกข้อมูลไม่สำเร็จ! กรุณาลองอีกครั้ง";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #f9f1f9;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .back-link {
        position: absolute;
        color: black;
        top: 10px;
        left: 10px;
        font-size: 1.5rem;
        text-decoration: none;
        display: flex;
        padding: 10px;
        margin: 10px;
    }

    .back-link:hover {
        color: black;
        text-decoration: none;
    }

    .back-link i {
        font-size: 25px;
        margin-top: 5px;
    }

    .back-link span {
        padding-left: 15px;
        font-weight: bold;
    }

    .edit-profile {
        background-color: #fff;
        padding: 20px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        width: 400px;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 15px;
        text-align: left;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-size: 16px;
        color: #333;
        font-weight: bold;
    }

    .form-group input {
        width: -webkit-fill-available;
        padding: 10px;
        font-size: 18px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .btn {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
    }

    .btn button {
        width: 40%;
    }

    .d-flex.gap-1 {
        gap: 1rem;
    }

    .alert-success {
        display: flex;
        justify-content: center;
    }

    .alert-danger {
        display: flex;
        justify-content: center;
    }
    </style>
</head>

<body>
<?php if ($isShowData): ?>
    <a href="phone_number_management.php" class="back-link">
        <i class="fa-solid fa-arrow-left-long"></i>
        <span>Edit Profile</span>
    </a>

    <div class="edit-profile">
        <h1>Edit Profile</h1>
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="edit_profile.php">
            <div class="form-group">
                <label class="px-1" for="name">Name</label>
                <input type="text" id="name" name="name" class="form-control rounded-pill"
                    value="<?php echo htmlspecialchars($user['Name']); ?>" required>
            </div>
            <div class="form-group">
                <label class="px-1" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control rounded-pill"
                    value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <p class="text-right">
                     <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot password?</a>
                 </p>
            <div class="btn">
                <button type="submit" class="btn btn-primary rounded-pill">บันทึก</button>
                <button type="button" class="btn btn-outline-secondary rounded-pill"
                    onclick="window.location.href='phone_number_management.php'">ยกเลิก</button>
            </div>
        </form>
    </div>
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto">Forgot Password</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="process_forgot_password.php">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control rounded-pill"
                                placeholder="Enter your email address" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill">Send Reset Link</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
    <div style="text-align: center; padding: 50px; font-size: 20px;">
        <p>No Data</p>
    </div>
    <?php endif; ?>
</body>

</html>