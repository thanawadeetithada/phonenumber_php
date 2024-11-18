<?php
session_start();
include 'config.php';

if (!isset($_SESSION['isShowManagement']) || $_SESSION['isShowManagement'] != 1) {
    header("Location: index.php");
    exit();
}

$count_query = "SELECT COUNT(*) AS total_users FROM users_collection";
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_users = $count_row['total_users']; 

$query = "SELECT * FROM users_collection";
$result = $conn->query($query);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $isShowManagement = isset($_POST['isShowManagement']) ? 1 : 0;
        $isShowData = isset($_POST['isShowData']) ? 1 : 0;
    
        $update_query = "UPDATE users_collection SET Name = ?, Email = ?, isShowManagement = ?, isShowData = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssiii", $name, $email, $isShowManagement, $isShowData, $id);
    
        if ($stmt->execute()) {
            http_response_code(200);
            exit();
        } else {
            http_response_code(500);
            exit();
        }
    }
    

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $delete_query = "DELETE FROM users_collection WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: user_management.php");
        exit();
    } else {
        $error = "Failed to delete user.";
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

    .table.table-striped {
        margin: 0;
    }

    .table thead th {
        border: 0px !important;
        background-color: #F2F2F2;
        padding: 20px;
    }

    .table td {
        background-color: white;
        padding: 20px;
    }

    .modal-content {
        width: 100%;
        border-radius: 1rem;
    }

    .modal-body {
        padding: 20px;
    }

    input[type=checkbox] {
        transform: scale(1.5);
    }

    .form-check-label {
        margin-left: 5px;
    }

    .modal-footer-user {
        display: flex;
        justify-content: center;
        align-items: center;
        padding-bottom: 20px;
        gap: 1rem;
        padding-bottom: 20px;
    }

    .modal-footer-user button {
        width: 25%;
    }

    .icon-disabled {
        color: gray;
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
    </style>
</head>

<body>
    <a href="phone_number_management.php" class="back-link">
        <i class="fa-solid fa-arrow-left-long"></i>
        <span>User Management</span>
    </a>

    <div class="edit-profile">
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Show Data</th>
                    <th>User Management</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                    <td style="text-align: center;">
                        <input type="checkbox" disabled <?php echo $row['isShowData'] ? 'checked' : ''; ?>>
                    </td>
                    <td style="text-align: center;">
                        <input type="checkbox" disabled <?php echo $row['isShowManagement'] ? 'checked' : ''; ?>>
                    </td>
                    <td style="gap: 1rem; text-align: center;">
                        <i class='fas fa-pencil-alt edit-icon' data-toggle='modal' style='margin-right: 10px;'
                            data-target='#editUserModal<?php echo $row['id']; ?>'></i>

                        <?php if ($total_users <= 1): ?>
                        <i class="fa-regular fa-trash-can icon-disabled" title="ไม่สามารถลบผู้ใช้นี้ได้"></i>
                        <?php else: ?>
                        <i class="fa-regular fa-trash-can"
                            onclick="if(confirm('ต้องการลบข้อมูลผู้ใช้นี้ใช่ไหม?')) window.location.href='?delete_user=<?php echo $row['id']; ?>';"
                            style="cursor: pointer; color: red;"></i>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="editUserModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit User</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="editUserForm<?php echo $row['id']; ?>" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" class="form-control rounded-pill"
                                            value="<?php echo htmlspecialchars($row['Name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" class="form-control rounded-pill"
                                            value="<?php echo htmlspecialchars($row['Email']); ?>" required>
                                    </div>
                                    <div class="form-check px-5 mb-2">
                                        <input type="checkbox" name="isShowData" class="form-check-input"
                                            <?php echo $row['isShowData'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isShowData">Show Data</label>
                                    </div>
                                    <div class="form-check px-5">
                                        <input type="checkbox" name="isShowManagement" class="form-check-input"
                                            <?php echo $row['isShowManagement'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isShowManagement">User Management</label>
                                    </div>
                                </div>
                                <div class="modal-footer-user">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill"
                                        data-dismiss="modal">ยกเลิก</button>
                                    <button type="submit"
                                        class="btn btn-primary rounded-pill saveUserButton">บันทึกข้อมูล</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script>
    $(document).on('submit', '[id^="editUserForm"]', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        $.ajax({
            url: 'user_management.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert('บันทึกข้อมูลสำเร็จ!');
                location.reload();
            },
            error: function() {
                alert('เกิดข้อผิดพลาด! กรุณาลองใหม่อีกครั้ง');
            }
        });
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>