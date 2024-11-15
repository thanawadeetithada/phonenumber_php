<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rowID = $_POST['RowID'] ?? null;
    $userID = $_POST['UserID'] ?? null;
    $amount = $_POST['Amount'] ?? null;
    $tag = $_POST['Tag'] ?? null;
    $action = $_POST['Action'] ?? '';

    if ($action === 'DeleteCategory') {
        $categoryID = $_POST['id'] ?? null;
        
        if (!empty($categoryID) && is_numeric($categoryID)) {
            $stmt = $conn->prepare("DELETE FROM total_category WHERE id = ?");
            $stmt->bind_param('i', $categoryID);

            if ($stmt->execute()) {
                echo "Success";
            } else {
                echo "Error";
            }

            $stmt->close();
        } else {
            echo "Invalid ID";
        }
        $conn->close();
        exit;
    }

    if ($action === 'AddCategory') {
        $categoryName = $_POST['category'] ?? '';
    
        if (!empty($categoryName)) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM total_category WHERE category = ?");
            $stmt->bind_param('s', $categoryName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
    
            if ($row['count'] > 0) {
                echo "Duplicate";
            } else {
                $stmt = $conn->prepare("INSERT INTO total_category (category) VALUES (?)");
                $stmt->bind_param('s', $categoryName);
    
                if ($stmt->execute()) {
                    echo "Success";
                } else {
                    echo "Error: " . $conn->error;
                }
            }
            $stmt->close();
        } else {
            echo "Invalid Category Name";
        }
        $conn->close();
        exit;
    }
    
    if ($action === 'ClearAmount') {
        if ($rowID !== null) {
            $stmt = $conn->prepare("UPDATE phonenumber SET amount = 0 WHERE id = ?");
            $stmt->bind_param('i', $rowID);

            if ($stmt->execute()) {
                echo "Success";
            } else {
                echo "Error: " . $conn->error;
            }

            $stmt->close();
        }
        $conn->close();
        exit;
    }

    if ($action === 'UpdateStatus') {
        $status = $_POST['Status'] ?? null;

        if ($status !== null && $rowID !== null) {
            $stmt = $conn->prepare("UPDATE phonenumber SET status = ? WHERE id = ?");
            $stmt->bind_param('ii', $status, $rowID);

            if ($stmt->execute()) {
                echo "Success";
            } else {
                echo "Error: " . $conn->error;
            }

            $stmt->close();
        }
        $conn->close();
        exit;
    }

    if ($action === 'DeleteRow') {
        if ($rowID !== null) {
            $stmt = $conn->prepare("DELETE FROM phonenumber WHERE id = ?");
            $stmt->bind_param('i', $rowID);

            if ($stmt->execute()) {
                echo "Success";
            } else {
                echo "Error: " . $conn->error;
            }

            $stmt->close();
        }
        $conn->close();
        exit;
    }

    if ($userID !== null && $amount !== null && $tag !== null && $rowID !== null) {
        if (!is_numeric($amount)) {
            $amount = '0';
        }

        error_log("RowID: $rowID, UserID: $userID, Amount: $amount, Tag: $tag");

        $stmt = $conn->prepare("UPDATE phonenumber SET UserID = ?, amount = ?, tag = ? WHERE id = ?");
        $stmt->bind_param('sssi', $userID, $amount, $tag, $rowID);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();
    }
    $conn->close();
    exit;
}

$query = "SELECT * FROM total_tag";
$result = $conn->query($query);

$tags = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tags[] = $row['tag'];
    }
}

$queryCategories = "SELECT category FROM total_category";
$resultCategories = $conn->query($queryCategories);
$categories = [];
if ($resultCategories) {
    while ($row = $resultCategories->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Number Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
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
                    <button class="green-button" data-toggle="modal" data-target="#addcategoryModal">Add New
                        Category</button>
                    <button class="green-button">Add New Tag</button>
                    <button class="green-button">Add Phone Number</button>
                </div>
                <div class="search-group">
                    <button class="green-button">Download</button>
                    <div class="search-box">
                        <input type="text" placeholder="Search..." id="search-input" onkeyup="searchTable()" />
                        <i class="fas fa-search search-icon"></i>
                    </div>
                </div>
            </div>

            <div class="dropdown-container">
                <h3 for="category-dropdown">Phone Numbers</h3>
                <select id="category-dropdown" onchange="filterData()">
                    <option value="all">All data</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?=htmlspecialchars($category)?>"><?=htmlspecialchars($category)?></option>
                    <?php endforeach;?>
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
$query = "SELECT id, phonenumber, UserID, category, amount, tag, status FROM phonenumber";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $amount = $row['amount'];
        if (strpos($amount, '.') !== false && rtrim(substr($amount, strpos($amount, '.') + 1), '0') === '') {
            $amount = (int) $amount;
        }
        echo "<tr data-row-id='" . htmlspecialchars($row['id']) . "'>";
        echo "<td>" . htmlspecialchars($row['phonenumber']) . "</td>";
        echo "<td>" . htmlspecialchars($row['UserID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . htmlspecialchars($amount) . "</td>";
        echo "<td>" . htmlspecialchars($row['tag']) . "</td>";
        echo "<td>" . ($row['status'] == 0 ? "Disable" : "Active") . "</td>";
        echo "<td><i class='fas fa-pencil-alt edit-icon' title='Edit' onclick='enableRowEdit(this)'></i></td>";
        echo "<td>
                       <button class='yellow-button' onclick='clearAmount(this)'>Clear Amount</button>
                        <button class='red-button' onclick='toggleStatus(this, 0)'>Disable</button>
                        <button class='green-button' onclick='toggleStatus(this, 1)'>Active</button>
                        <button class='red-button' onclick='deleteRow(this)'>Delete</button>
                    </td>";
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

    <div class="modal fade" id="addcategoryModal" tabindex="-1" role="dialog" aria-labelledby="addcategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto" id="addcategoryModalLabel">Add New Category</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="">
                        <div class="form-group">
                            <input type="text" name="category_name" class="form-control rounded-pill"
                                placeholder="Category Name" required>
                        </div>
                        <p class="existing-categories px-2">Existing Categories</p>
                        <div class="existing-categories-list px-2 mb-4">
                            <?php
$query = "SELECT * FROM total_category";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="d-flex justify-content-between align-items-center mb-2">';
        echo '<span>' . htmlspecialchars($row['category']) . '</span>';
        echo '<button type="button" class="red-button" onclick="deleteCategory(' . $row['id'] . ', this)">Delete</button>';
        echo '</div>';
    }
} else {
    echo '<p>No categories available.</p>';
}
?>

                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="green-button" onclick="addCategory()">Add Category</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function filterData() {
        const selectedCategory = document.getElementById('category-dropdown').value;
        const rows = document.querySelectorAll('.data-table tbody tr');

        rows.forEach(row => {
            const categoryCell = row.querySelector('td:nth-child(3)');
            const category = categoryCell ? categoryCell.innerText.trim() : '';

            if (selectedCategory === 'all' || category === selectedCategory) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function searchTable() {
        const input = document.getElementById('search-input');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.data-table tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;

            cells.forEach((cell, index) => {
                if (index === cells.length - 1) return;

                if (cell.innerText.toLowerCase().includes(filter)) {
                    match = true;
                }
            });

            row.style.display = match ? '' : 'none';
        });
    }
    document.addEventListener("DOMContentLoaded", () => {
        updateActionButtons();
    });

    function updateActionButtons() {
        const rows = document.querySelectorAll('.data-table tbody tr');
        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(6)');
            const statusText = statusCell.innerText.trim();
            const disableButton = row.querySelector('button.red-button');
            const activeButton = row.querySelector('button.green-button');

            disableButton.classList.remove('active', 'inactive');
            activeButton.classList.remove('active', 'inactive');

            if (statusText === '0' || statusText === 'Disable') {
                disableButton.classList.add('disable');
                activeButton.classList.add('inactive');
            } else if (statusText === '1' || statusText === 'Active') {
                disableButton.classList.add('inactive');
                activeButton.classList.add('active');
            }
        });
    }


    const tags = <?php echo json_encode($tags); ?>;

    function enableRowEdit(editIcon) {
        const row = editIcon.closest('tr');
        const cells = row.querySelectorAll('td');

        const allRows = document.querySelectorAll('.data-table tr');
        allRows.forEach(otherRow => {
            if (otherRow !== row) {
                otherRow.classList.add('locked-row');
            } else {
                const actionButtons = otherRow.querySelectorAll('button');
                actionButtons.forEach(button => button.classList.add('locked'));
            }
        });

        const userIDInput = document.createElement('input');
        userIDInput.type = 'text';
        userIDInput.value = cells[1].innerText;
        cells[1].innerHTML = '';
        cells[1].appendChild(userIDInput);

        const amountInput = document.createElement('input');
        amountInput.type = 'number';
        amountInput.value = cells[3].innerText;
        amountInput.min = '0';
        cells[3].innerHTML = '';
        cells[3].appendChild(amountInput);

        amountInput.addEventListener('input', function(event) {
            let value = event.target.value;

            const sanitizedValue = value.match(/^-?[0-9]*\.?[0-9]*$/);

            if (!sanitizedValue) {
                event.target.value = event.target.value.slice(0, -1);
            }
        });

        const tagDropdown = document.createElement('select');
        tags.forEach(tag => {
            const option = document.createElement('option');
            option.value = tag;
            option.textContent = tag;
            if (cells[4].innerText === tag) {
                option.selected = true;
            }
            tagDropdown.appendChild(option);
        });

        cells[4].innerHTML = '';
        cells[4].appendChild(tagDropdown);

        editIcon.outerHTML = `<i class="fas fa-save save-icon" title="Save" onclick="saveRowEdit(this)"></i>`;
    }


    function saveRowEdit(saveIcon) {
        const row = saveIcon.closest('tr');
        const cells = row.querySelectorAll('td');

        const userID = cells[1].querySelector('input').value;
        let amount = cells[3].querySelector('input').value;
        const tag = cells[4].querySelector('select').value;
        const rowID = row.getAttribute('data-row-id');

        amount = parseFloat(amount);

        if (isNaN(amount)) {
            amount = 0;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('แก้ไขข้อมูลสำเร็จ!');

                cells[1].innerText = userID;
                cells[3].innerText = formatAmount(amount);
                cells[4].innerText = tag;
                const allRows = document.querySelectorAll('.data-table tr');
                allRows.forEach(otherRow => {
                    const otherActionButtons = otherRow.querySelectorAll('button');
                    otherActionButtons.forEach(button => button.classList.remove('locked'));
                    otherRow.classList.remove('locked-row');
                });
                saveIcon.outerHTML =
                    `<i class="fas fa-pencil-alt edit-icon" title="Edit" onclick="enableRowEdit(this)"></i>`;
            } else {
                alert('แก้ไขข้อมูลไม่สำเร็จ!');
            }
        };

        xhr.send(`RowID=${rowID}&UserID=${userID}&Amount=${amount}&Tag=${tag}`);
    }

    function formatAmount(amount) {
        if (amount % 1 === 0) {
            return parseInt(amount);
        }
        return parseFloat(amount);
    }

    function clearAmount(button) {
        const row = button.closest('tr');
        const cells = row.querySelectorAll('td');
        const rowID = row.getAttribute('data-row-id');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                cells[3].innerText = 0;
            } else {
                alert('Failed to clear amount!');
            }
        };

        xhr.send(`RowID=${rowID}&Action=ClearAmount`);
    }

    function toggleStatus(button, statusValue) {
        const row = button.closest('tr');
        const rowID = row.getAttribute('data-row-id');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const statusCell = row.querySelector('td:nth-child(6)');
                statusCell.innerText = statusValue === 1 ? "Active" : "Disable";

                updateActionButtons();
            } else {
                alert('Failed to update status!');
            }
        };
        xhr.send(`RowID=${rowID}&Action=UpdateStatus&Status=${statusValue}`);
    }

    function deleteRow(button) {
        if (!confirm("ต้องการลบข้อมูลใช่ไหม?")) {
            return;
        }

        const row = button.closest('tr');
        const rowID = row.getAttribute('data-row-id');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert('ทำการลบสำเร็จแล้ว!');
                row.remove();
            } else {
                alert('ทำการลบไม่สำเร็จแล้ว!');
            }
        };

        xhr.send(`RowID=${rowID}&Action=DeleteRow`);
    }

    function deleteCategory(categoryId, button) {
        if (!confirm("ต้องการลบ Category ใช่ไหม?")) return;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "phone_number_management.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200 && xhr.responseText.includes("Success")) {
                const categoryElement = button.closest('.d-flex');
                categoryElement.remove();
                alert("ลบ Category สำเร็จแล้ว!");
            } else {
                alert("Failed to delete category.");
            }
        };

        xhr.send("id=" + categoryId + "&Action=DeleteCategory");
    }

    function addCategory() {
        const categoryName = document.querySelector('input[name="category_name"]').value.trim();

        if (categoryName === '') {
            alert("กรุณากรอกชื่อ Category");
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "phone_number_management.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText.includes("Success")) {
                    alert("เพิ่ม Category สำเร็จแล้ว!");
                    location.reload();
                } else if (xhr.responseText.includes("Duplicate")) {
                    alert("Category นี้มีอยู่แล้ว ไม่สามารถเพิ่มซ้ำได้");
                    location.reload();
                } else {
                    alert("Failed to add category.");
                }
            }
        };

        xhr.send("Action=AddCategory&category=" + encodeURIComponent(categoryName));
    }
    </script>
</body>

</html>