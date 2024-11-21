<?php
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Bangkok');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $query = "SELECT * FROM users_collection WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $updateQuery = "UPDATE users_collection SET reset_token = ?, reset_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sss", $token, $expiry, $email);
        if (!$stmt->execute()) {
            echo "Error updating token: " . $stmt->error;
            exit;
        }

        $resetLink = "http://localhost/phonenumber_php/reset_password.php?token=$token";
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mitinventor015@gmail.com';
            $mail->Password = 'ukebjwmfzmwuipjw';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('mitinventor015@gmail.com', 'noreply@mail.com');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body = "Click this link to reset your password: <a href='$resetLink'>$resetLink</a>";

            $mail->send();
            echo "<script>alert('ลิงค์รีเซ็ตส่งไปที่อีเมล์ของคุณแล้ว'); window.location.href = 'index.php';</script>";
        } catch (Exception $e) {
            echo "Failed to send email. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('อีเมลนี้ไม่ได้ลงทะเบียน!'); window.history.back();</script>";
    }
}
?>
