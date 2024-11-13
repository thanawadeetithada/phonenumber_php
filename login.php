<?php
// เริ่มต้นการเชื่อมต่อกับฐานข้อมูล
include 'config.php'; // config.php ควรประกอบด้วยการเชื่อมต่อฐานข้อมูล $conn เช่น $conn = new mysqli('localhost', 'root', '', 'phone_number');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ทำการเข้าสู่ระบบ โดยเปรียบเทียบ email และ password จากฐานข้อมูล
    // ใช้ SQL query เพื่อดึงข้อมูลผู้ใช้ที่ตรงกับอีเมลที่ป้อนมา
    $sql = "SELECT * FROM users_collection WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // ตรวจสอบว่าพบผู้ใช้ที่มีอีเมลนี้หรือไม่
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ตรวจสอบว่ารหัสผ่านที่ใส่ตรงกับรหัสผ่านในฐานข้อมูลหรือไม่ (สามารถใช้ password_hash ในการเข้ารหัส)
        if (password_verify($password, $user['password'])) {
            // หากรหัสผ่านถูกต้อง
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['isShowData'] = $user['isShowData'];

            // ส่งผู้ใช้ไปยังหน้าจัดการหลังจากเข้าสู่ระบบสำเร็จ
            header('Location: phone_number_management_page.php');
            exit();
        } else {
            // หากรหัสผ่านไม่ถูกต้อง
            echo "รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        // หากไม่พบผู้ใช้ที่มีอีเมลนี้
        echo "ไม่พบบัญชีที่มีอีเมลนี้";
    }
}
?>
