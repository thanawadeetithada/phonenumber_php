 <?php
session_start();
include 'config.php'; // เชื่อมต่อฐานข้อมูล

// Handle Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users_collection WHERE Email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['isShowData'] = $user['isShowData'];
        $_SESSION['isShowManagement'] = $user['isShowManagement'];

        header("Location: phone_number_management.php");
        exit;
    } else {
        $error = "Incorrect email or password.";
    }
}

// Handle Register
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $isShowData = 0;
    $isShowManagement = 0;
    $sql = "INSERT INTO users_collection (Name, Email, password, isShowManagement, isShowData) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $name, $email, $password, $isShowManagement, $isShowData);
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!');</script>";
    } else {
        echo "<script>alert('Error: Could not register.');</script>";
    }
}

// ตรวจสอบว่าฟอร์มถูกส่งมาหรือไม่
if (isset($_POST['register'])) {
    // รับข้อมูลจากฟอร์ม
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบรหัสผ่านว่าตรงกันหรือไม่
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        // เข้ารหัสรหัสผ่านด้วย password_hash
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // บันทึกข้อมูลลงในฐานข้อมูล
        $sql = "INSERT INTO users_collection (Name, Email, password, isShowManagement, isShowData) 
                VALUES (?, ?, ?, 0, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!');</script>";
        } else {
            echo "<script>alert('Error: Could not register');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Number Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="card login">
            <h2 class="title text-center">Login</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif;?>
            <form id="loginForm" action="" method="POST">
                <div class="form-group mt-3">
                    <input type="email" name="email" class="form-control rounded-pill" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control rounded-pill" placeholder="Password" required>
                </div>
                <p class="text-right">
                    <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot password?</a>
                </p>
                <button type="submit" name="login" class="btn btn-primary btn-block rounded-pill">Login</button>
                <p class="text-center mt-3">
                    <a href="#" data-toggle="modal" data-target="#registerModal">Don't have an account? Register</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content registor">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto" id="registerModalLabel">Register</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body px-4">
                        <div class="form-group">
                            <label for="registerName" class="sr-only">Name</label>
                            <input type="text" name="name" id="registerName" class="form-control rounded-pill" placeholder="Name" required>
                        </div>
                        <div class="form-group">
                            <label for="registerEmail" class="sr-only">Email</label>
                            <input type="email" name="email" id="registerEmail" class="form-control rounded-pill" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <label for="registerPassword" class="sr-only">Password</label>
                            <input type="password" name="password" id="registerPassword" class="form-control rounded-pill"  placeholder="Password" required>
                        </div>
                        <div class="form-group confirmPassword">
                            <label for="confirmPassword"  class="sr-only">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword" class="form-control rounded-pill"  placeholder="Confirm password" required>
                        </div>
                    </div>
                    <div class="modal-footer registor">
                        <button type="submit" name="register" class="btn btn-primary rounded-pill">Register</button>
                        <p class="text-center">
                            <a href="#" data-dismiss="modal">Have an Account? Login Here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal">
            <div class="modal-header align-items-center">
                <h5 class="modal-title mx-auto" id="forgotPasswordModalLabel">Forgot your password?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body px-4">
                <p class="px-2">กรุณาใส่อีเมลที่คุณต้องการรีเซ็ตรหัสผ่าน</p>
                <form id="forgotPasswordForm" method="POST" action="">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control rounded-pill" placeholder="Enter email address" required>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-link" data-dismiss="modal">กลับไปหน้า Login</button>
                        <button type="submit" class="btn btn-primary rounded-pill">Reset password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


    <!-- Bootstrap JS (สำหรับ modal) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
