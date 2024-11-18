<?php
session_start();
include 'config.php';

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
        $error = "รหัสผ่าน หรือ อีเมลล์ไม่ถูกต้อง!";
    }
}

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        echo "<script>alert('รหัสผ่านไม่ตรงกัน!');</script>";
    } else {
        $query = "SELECT * FROM users_collection WHERE Email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('อีเมลนี้มีการลงทะเบียนแล้ว!');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users_collection (Name, Email, password, isShowManagement, isShowData) 
                    VALUES (?, ?, ?, 0, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>
                    alert('ลงทะเบียนสำเร็จแล้ว!');
                    window.location.href = 'index.php';
                </script>";
            } else {
                echo "<script>alert('Error: Could not register.');</script>";
            }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    .d-flex.gap-1 {
        gap: 1rem;
    }

    .alert-success {
        margin-bottom: 0px;
        margin-top: 20px;
    }

    .alert-danger {
        margin-bottom: 0px;
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="card login" style="max-width: 400px;">
            <h2 class="title text-center">Login</h2>
            <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif;?>
            <form id="loginForm" action="" method="POST">
                <div class="form-group mt-4">
                    <input type="email" name="email" class="form-control rounded-pill" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control rounded-pill" placeholder="Password"
                        required>
                </div>
                <p class="text-right">
                    <a href="#" data-toggle="modal" data-target="#forgotPasswordModal">Forgot password?</a>
                </p>
                <button type="submit" name="login" class="btn btn-primary btn-block rounded-pill mt-3"
                    style="width: 60%;display: inline-block;">Login</button>
                <p class="text-center mt-3 mb-0">
                    <a href="#" data-toggle="modal" data-target="#registerModal">Don't have an account? Register</a>
                </p>
            </form>
        </div>
    </div>

    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content registor">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto" id="registerModalLabel">Register</h5>
                </div>
                <form method="POST" action="">
                    <div class="modal-body px-4">
                        <div class="form-group">
                            <label class="form-group-label" for="name">Name</label>
                            <input type="text" name="name" id="name" class="form-control rounded-pill"
                                placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-group-label" for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control rounded-pill"
                                placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-group-label" for="registerPassword">Password</label>
                            <input type="password" name="password" id="registerPassword" class="form-control rounded-pill" 
                            placeholder="Enter your password" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="registerPassword"
                            style="top: 3.1rem;padding-right: 5px;"></i>
                        </div>
                        <div class="form-group">
                            <label class="form-group-label" for="confirmPassword">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirmPassword"
                                class="form-control rounded-pill" placeholder="Confirm your password" required>
                            <i class="fa fa-eye-slash toggle-password" data-target="confirmPassword"
                            style="top: 3.1rem;padding-right: 5px;"></i>
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

    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog"
        aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header align-items-center">
                    <h5 class="modal-title mx-auto" id="forgotPasswordModalLabel">Forgot your password?</h5>
                </div>
                <div class="modal-body px-4">
                    <p class="px-2">กรุณาใส่อีเมลที่คุณต้องการรีเซ็ตรหัสผ่าน</p>
                    <form id="forgotPasswordForm" method="POST" action="">
                        <div class="form-group">
                            <input type="email" name="email" class="form-control rounded-pill"
                                placeholder="Enter email address" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill">Reset password</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll(".toggle-password").forEach(function(icon) {
        icon.addEventListener("click", function() {
            const input = document.getElementById(this.getAttribute("data-target"));
            if (input.type === "password") {
                input.type = "text";
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye");
            } else {
                input.type = "password";
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash");
            }
        });
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>