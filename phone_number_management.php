<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['fetchData'])) {
    $rowsPerPage = isset($_GET['rowsPerPage']) ? (int)$_GET['rowsPerPage'] : 10;
    $currentPage = isset($_GET['currentPage']) ? (int)$_GET['currentPage'] : 1;

    $rowsPerPage = max(1, $rowsPerPage);
    $currentPage = max(1, $currentPage);

    $offset = ($currentPage - 1) * $rowsPerPage;

    $stmt = $conn->prepare("SELECT * FROM phonenumber LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $rowsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    $totalRowsResult = $conn->query("SELECT COUNT(*) as totalRows FROM phonenumber");
    $totalRows = $totalRowsResult->fetch_assoc()['totalRows'];

    $totalPages = ceil($totalRows / $rowsPerPage);

    $queryInactiveCount = "SELECT COUNT(*) as inactiveCount FROM phonenumber WHERE status = 0";
    $resultInactiveCount = $conn->query($queryInactiveCount);

    $queryTotalAmount = "SELECT SUM(amount) as totalAmount FROM phonenumber WHERE status != 0";
    $resultTotalAmount = $conn->query($queryTotalAmount);

    $inactiveCount = 0;
    if ($resultInactiveCount) {
        $row = $resultInactiveCount->fetch_assoc();
        $inactiveCount = $row['inactiveCount'];
    }

    $totalAmount = 0;
    if ($resultTotalAmount) {
        $row = $resultTotalAmount->fetch_assoc();
        $totalAmount = $row['totalAmount'] ?? 0;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'data' => $data,
        'totalRows' => $totalRows,
        'inactiveCount' => $inactiveCount,
        'totalAmount' => $totalAmount,
        'totalPages' => $totalPages,
        'currentPage' => $currentPage,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rowID = $_POST['RowID'] ?? null;
    $userID = $_POST['UserID'] ?? null;
    $amount = $_POST['Amount'] ?? null;
    $tag = $_POST['Tag'] ?? null;
    $action = $_POST['Action'] ?? '';

   if ($action === 'AddPhoneNumber') {
    $phoneNumber = $_POST['phonenumber'] ?? '';
    $category = $_POST['category'] ?? '';

    if (!empty($phoneNumber) && !empty($category)) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM phonenumber WHERE phonenumber = ?");
        $stmt->bind_param('s', $phoneNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            echo "Duplicate";
        } else {
            $userID = "";
            $amount = 0;
            $status = 1;
            $tag = "";

            $stmt = $conn->prepare("INSERT INTO phonenumber (phonenumber, UserID, amount, category, status, tag) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssdsss', $phoneNumber, $userID, $amount, $category, $status, $tag);

            if ($stmt->execute()) {
                echo "Success";
            } else {
                error_log("SQL Error: " . $stmt->error);
                echo "Error: " . $conn->error;
            }
        }
        $stmt->close();
    } else {
        echo "Invalid Input";
    }
    $conn->close();
    exit;
}

    if ($action === 'DeleteTag') {
        $tagID = $_POST['id'] ?? null;
        
        if (!empty($tagID) && is_numeric($tagID)) {
            $stmt = $conn->prepare("DELETE FROM total_tag WHERE id = ?");
            $stmt->bind_param('i', $tagID);

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

    if ($action === 'AddTag') {
        $tagName = $_POST['tag'] ?? '';
        
        if (!empty($tagName)) {
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM total_tag WHERE tag = ?");
            $stmt->bind_param('s', $tagName);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
    
            if ($row['count'] > 0) {
                echo "Duplicate";
            } else {
                $stmt = $conn->prepare("INSERT INTO total_tag (tag) VALUES (?)");
                $stmt->bind_param('s', $tagName);
    
                if ($stmt->execute()) {
                    echo "Success:" . $conn->insert_id;
                } else {
                    echo "Error: " . $conn->error;
                }
            }
            $stmt->close();
        } else {
            echo "Invalid Tag Name";
        }
        $conn->close();
        exit;
    }

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
    <header>
        <?php include 'include/header.php';?>
    </header>
    <div class="centered-container">
        <div class="card">
            <h1>Phone Number Management</h1>
            <div class="header-info">
                <h2 class="show-member"></h2>
                <h2 class="user-close"></h2>
                <h2 class="total-amount">ยอดเงินรวม :</h2>
            </div>

            <div class="button-search-container">
                <div class="button-group">
                    <button class="green-button" data-toggle="modal" data-target="#addcategoryModal">Add New
                        Category</button>
                    <button class="green-button" data-toggle="modal" data-target="#addtagModal">Add New Tag</button>
                    <button class="green-button" data-toggle="modal" data-target="#addphoneModal">Add Phone
                        Number</button>
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
            <div class="pagination-container">
                <div class="left-group">
                    <label for="rowsPerPage">Rows per page:</label>
                    <select id="rowsPerPage" onchange="changeRowsPerPage(this.value)">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                    </select>
                </div>
                <div class="center-group">
                    <span id="paginationInfo"></span>
                </div>
                <div class="right-group">
                    <button id="firstPage" onclick="goToFirstPage()">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    <button id="prevPage" onclick="changePage('prev')">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <span id="currentPageDisplay">1</span>
                    <button id="nextPage" onclick="changePage('next')">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button id="lastPage" onclick="goToLastPage()">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
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
                        <p class="existing-categories px-2 mb-2">Existing Categories</p>
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
                                      echo '<p style = "color: red;">No categories available.</p>';
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

    <div class="modal fade" id="addtagModal" tabindex="-1" role="dialog" aria-labelledby="addtagModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto" id="addtagModalLabel">Add New Tag</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="">
                        <div class="form-group">
                            <input type="text" name="tag_name" class="form-control rounded-pill" placeholder="Tag Name"
                                required>
                        </div>
                        <p class="existing-categories px-2 mb-2">Existing Tags</p>
                        <div class="existing-categories-list px-2 mb-4">
                            <?php
                                  $query = "SELECT * FROM total_tag";
                                  $result = $conn->query($query);

                                  if ($result->num_rows > 0) {
                                      while ($row = $result->fetch_assoc()) {
                                          echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                          echo '<span>' . htmlspecialchars($row['tag']) . '</span>';
                                          echo '<button type="button" class="red-button" onclick="deleteTag(' . $row['id'] . ', this)">Delete</button>';
                                          echo '</div>';
                                      }
                                  } else {
                                      echo '<p style = "color: red;">No tag available.</p>';
                                  }
                                  ?>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="green-button" onclick="addTag()">Add Tag</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addphoneModal" tabindex="-1" role="dialog" aria-labelledby="addphoneModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal">
                <div class="modal-header">
                    <h5 class="modal-title mx-auto" id="addphoneModalLabel">Add Phone Numbers</h5>
                </div>
                <div class="modal-body px-4">
                    <form id="forgotPasswordForm" method="POST" action="">
                        <input type="text" name="phone_number" class="form-control rounded-pill mb-2"
                            placeholder="Phone Number" required>
                        <div class="dropdown-container">
                            <p class="label-category-add-phone" for="category-dropdown-add-phone" style="margin: 8px">
                                Select Category
                            </p>
                            <select id="category-dropdown-add-phone">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?=htmlspecialchars($category)?>"><?=htmlspecialchars($category)?>
                                </option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button class="green-button" onclick="addPhoneNumber()">Add Phone Number</button>
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
            const statusText = statusCell ? statusCell.innerText.trim() : '';
            const disableButton = row.querySelector('button.red-button');
            const activeButton = row.querySelector('button.green-button');

            row.classList.remove('row-disabled');
            disableButton.classList.remove('active', 'inactive');
            activeButton.classList.remove('active', 'inactive');

            if (statusText === '0' || statusText === 'Disable') {
                row.classList.add('row-disabled');
                disableButton.classList.add('disable');
                activeButton.classList.add('inactive');
            } else if (statusText === '1' || statusText === 'Active') {
                disableButton.classList.add('inactive');
                activeButton.classList.add('active');
            }
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        updateActionButtons();
    });

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
                loadTableData(currentPage, rowsPerPage);
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
            if (xhr.status === 200 && xhr.responseText.trim() === "Success") {
                const statusCell = row.querySelector('td:nth-child(6)');
                statusCell.innerText = statusValue === 1 ? "Active" : "Disable";

                updateActionButtons();
                loadTableData(currentPage, rowsPerPage);
            } else {
                alert('Failed to update status!');
            }
        };
        xhr.send(`RowID=${rowID}&Action=UpdateStatus&Status=${statusValue}`);
    }

    function deleteRow(button) {
        if (!confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่?")) {
            return;
        }

        const row = button.closest('tr');
        const rowID = row.getAttribute('data-row-id');

        fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `RowID=${rowID}&Action=DeleteRow`
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'Success') {
                    row.remove();
                    countInactiveRows();
                    alert('ลบข้อมูลสำเร็จ!');
                } else {
                    alert('ลบข้อมูลไม่สำเร็จ!');
                }
            })
            .catch(error => console.error('Error:', error));
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

    function deleteTag(tagId, button) {
        if (!confirm("ต้องการลบ Tag ใช่ไหม?")) return;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "phone_number_management.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status === 200 && xhr.responseText.includes("Success")) {
                const categoryElement = button.closest('.d-flex');
                categoryElement.remove();
                alert("ลบ Tag สำเร็จแล้ว!");
            } else {
                alert("Failed to delete Tag.");
            }
        };

        xhr.send("id=" + tagId + "&Action=DeleteTag");
    }

    function addTag() {
        const tagName = document.querySelector('input[name="tag_name"]').value.trim();

        if (tagName === '') {
            alert("กรุณากรอกชื่อ Tag");
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "phone_number_management.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText.includes("Success")) {
                    alert("เพิ่ม Tag สำเร็จแล้ว!");
                    location.reload();
                } else if (xhr.responseText.includes("Duplicate")) {
                    alert("Tag นี้มีอยู่แล้ว ไม่สามารถเพิ่มซ้ำได้");
                    location.reload();
                } else {
                    alert("Failed to add Tag.");
                }
            }
        };
        xhr.send("Action=AddTag&tag=" + encodeURIComponent(tagName));
    }

    function addPhoneNumber() {
        const phoneNumberInput = document.querySelector('input[name="phone_number"]').value.trim();
        const categoryDropdown = document.querySelector('#category-dropdown-add-phone');
        const category = categoryDropdown ? categoryDropdown.value : '';

        if (!phoneNumberInput || !category) {
            alert("กรุณากรอกข้อมูลให้ครบถ้วน");
            return;
        }
        const phoneNumbers = phoneNumberInput.split(/[\s,]+/).filter(num => num);

        if (phoneNumbers.length === 0) {
            alert("กรุณากรอกเบอร์โทรศัพท์ที่ถูกต้อง");
            return;
        }
        phoneNumbers.forEach(phoneNumber => {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "phone_number_management.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                    if (xhr.responseText.includes("Success")) {
                        alert(`เบอร์โทร ${phoneNumber} เพิ่มสำเร็จ!`);
                        location.reload();
                    } else if (xhr.responseText.includes("Duplicate")) {
                        alert(`เบอร์โทร ${phoneNumber} มีอยู่ในระบบแล้ว`);
                        location.reload();
                    } else {
                        alert(`ไม่สามารถเพิ่มเบอร์โทร ${phoneNumber} ได้`);
                        location.reload();
                    }
                } else {
                    console.error("Request failed:", xhr.status, xhr.statusText);
                }
                loadTableData(currentPage, rowsPerPage);
            };

            const data =
                `Action=AddPhoneNumber&phonenumber=${encodeURIComponent(phoneNumber)}&category=${encodeURIComponent(category)}`;
            console.log(data);
            xhr.send(data);
        });
    }
    let currentPage = 1;
    let totalPages = 1;
    let rowsPerPage = 10;

    function goToFirstPage() {
        currentPage = 1;
        loadTableData(currentPage, rowsPerPage);
    }

    function goToLastPage() {
        currentPage = totalPages;
        loadTableData(currentPage, rowsPerPage);
    }

    function loadTableData(page, rows) {
        fetch(`phone_number_management.php?fetchData=1&currentPage=${page}&rowsPerPage=${rows}`)
            .then(response => response.json())
            .then(data => {
                totalPages = data.totalPages;
                const showMemberElement = document.querySelector('.show-member');
                showMemberElement.innerText = `สมาชิกทั้งหมด : ${data.totalRows} คน`;
                const userCloseElement = document.querySelector('.user-close');
                userCloseElement.innerText = `ปิดการใช้งาน : ${data.inactiveCount} คน`;
                const totalAmountElement = document.querySelector('.total-amount');
                totalAmountElement.innerText = `ยอดเงินรวม : ${data.totalAmount} บาท`;

                renderTable(data.data);
                updatePagination(data.totalRows, data.totalPages, page);
                updateActionButtons();
                document.querySelector('.data-table tbody').addEventListener('click', function(event) {
                    if (event.target.matches('.red-button')) {
                        toggleStatus(event.target, 0);
                    } else if (event.target.matches('.green-button')) {
                        toggleStatus(event.target, 1);
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    }


    function renderTable(data) {
        const tableBody = document.querySelector('.data-table tbody');
        tableBody.innerHTML = '';
        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-row-id', row.id);
            tr.innerHTML = `
            <td>${row.phonenumber}</td>
            <td>${row.UserID || '-'}</td>
            <td>${row.category || '-'}</td>
            <td>${row.amount}</td>
            <td>${row.tag || '-'}</td>
            <td>${row.status == 1 ? 'Active' : 'Disable'}</td>
            <td><i class='fas fa-pencil-alt edit-icon' title='Edit' onclick='enableRowEdit(this)'></i></td>
            <td>
                                                <button class='yellow-button' onclick='clearAmount(this)'>Clear Amount</button>
                                                <button class='red-button' onclick='toggleStatus(this, 0)'>Disable</button>
                                                <button class='green-button' onclick='toggleStatus(this, 1)'>Active</button>
                                                <button class='red-button' onclick='deleteRow(this)'>Delete</button>
                                          </td>
        `
            tableBody.appendChild(tr);
        });
    }

    function updatePagination(totalRows, totalPages, currentPage) {
        const paginationInfo = document.getElementById('paginationInfo');
        paginationInfo.innerText = `${(currentPage - 1) * rowsPerPage + 1}-${Math.min(
        totalRows,
        currentPage * rowsPerPage
    )} of ${totalRows} rows`;

        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        const firstPageButton = document.getElementById('firstPage');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const lastPageButton = document.getElementById('lastPage');

        firstPageButton.disabled = currentPage === 1;
        prevPageButton.disabled = currentPage === 1;
        nextPageButton.disabled = currentPage === totalPages;
        lastPageButton.disabled = currentPage === totalPages;

        document.getElementById('currentPageDisplay').innerText = currentPage;
    }

    function changePage(direction) {
        if (direction === 'prev' && currentPage > 1) {
            currentPage--;
        } else if (direction === 'next' && currentPage < totalPages) {
            currentPage++;
        }
        updatePaginationButtons();
        loadTableData(currentPage, rowsPerPage);
    }

    function changeRowsPerPage(rows) {
        rowsPerPage = parseInt(rows);
        loadTableData(1, rowsPerPage);
    }


    function updatePaginationButtons() {
        const firstPageButton = document.getElementById('firstPage');
        const prevPageButton = document.getElementById('prevPage');
        const nextPageButton = document.getElementById('nextPage');
        const lastPageButton = document.getElementById('lastPage');

        firstPageButton.disabled = currentPage === 1;
        prevPageButton.disabled = currentPage === 1;
        nextPageButton.disabled = currentPage === totalPages;
        lastPageButton.disabled = currentPage === totalPages;

        document.getElementById('currentPageDisplay').innerText = currentPage;
    }

    document.addEventListener('DOMContentLoaded', () => {
        currentPage = 1;
        rowsPerPage = 10;
        loadTableData(currentPage, rowsPerPage);
    });

    function countInactiveRows() {
        let inactiveCount = 0;
        const rows = document.querySelectorAll('.data-table tbody tr');

        rows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(6)');
            if (statusCell && (statusCell.innerText.trim() === 'Disable' || statusCell.innerText.trim() ===
                    '0')) {
                inactiveCount++;
            }
        });

        document.querySelector('.user-close').innerText = `ปิดการใช้งาน : ${inactiveCount} คน`;
    }

    </script>
</body>

</html>