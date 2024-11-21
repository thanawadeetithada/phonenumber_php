<?php
require 'config.php';

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = "SELECT * FROM users_collection WHERE reset_token = ? AND reset_expiry > NOW() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f2f2f2;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    .card {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        padding: 2.5rem;
        border-radius: 1rem;
    }
    .title {
        font-weight: bold;
        margin-bottom: 30px;
        font-size: 24px;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 5px;
        display: block;
    }

    .form-control {
        border-radius: 50px !important;
        padding: 10px 15px;
        font-size: 14px;
    }

    button {
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 50px;
        padding: 5px;
        font-size: 16px;
        cursor: pointer;
        width: 60%;
        margin-top: 10px;
    }
    button:hover {
        background-color: #0056b3;
    }
    .btn-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card" style="max-width: 400px; width: 100%;">
            <h2 class="title text-center">Reset Password</h2>
            <form action="process_reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control"
                        placeholder="Confirm new password" required>
                </div>
                <div class="btn-container">
                <button type="submit">Reset Password</button>
                </div>
            </form>
        </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>

</html>
<?php
    } else {
        echo "<script>alert('ลิงค์นี้หมดอายุแล้ว'); window.location.href = 'index.php';</script>";
    }
}
?>