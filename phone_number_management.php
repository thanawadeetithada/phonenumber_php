<?php
include 'config.php'; // เพิ่มการเชื่อมต่อฐานข้อมูล

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'include/header.php';?>
    <div class="centered-container">
        <div class="card">
            <h1>Phone Number Management</h1>
            <div class="header-info">
                <h2 class="show-member">สมาชิกทั้งหมด :</h2>
                <h2 class="user-close">ปิดการใช้งาน :</h2>
                <h2 class="total-amount">ยอดเงินรวม :</h2>
            </div>

            <div class="button-search-container">
                <div class="button-group">
                    <button class="green-button">Add New Category</button>
                    <button class="green-button">Add New Tag</button>
                    <button class="green-button">Add Phone Number</button>
                </div>
                <div class="search-group">
                    <button class="green-button">Download</button>
                    <div class="search-box">
                        <input type="text" placeholder="Search..." />
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="dropdown-container">
                <h3 for="category-dropdown">Phone Numbers</h3>
                <select id="category-dropdown" onchange="filterData()">
                    <option value="all">All data</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Phone Number</th>
                <th>UserID</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Tag</th>
                <th>Status</th>
                <th>Edit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT phonenumber, UserID, category, amount, tag, status FROM phonenumber";
        $result = $conn->query($query);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['phonenumber']) . "</td>";
                echo "<td>" . htmlspecialchars($row['UserID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                echo "<td>" . htmlspecialchars($row['tag']) . "</td>";
                echo "<td>" . ($row['status'] == 0 ? "Disable" : "Active") . "</td>";
                echo "<td><button><i class='fas fa-pencil-alt'></i></button></td>";
                echo "<td><button>Delete</button></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No data available</td></tr>";
        }
        ?>
    </tbody>
    </table>
</div>

        </div>
    </div>

    <script>
    function filterData() {
        const selectedCategory = document.getElementById('category-dropdown').value;
        // Implement AJAX or redirection logic to fetch and display data based on the selected category
        console.log("Selected category:", selectedCategory);
    }
    </script>
</body>

</html>