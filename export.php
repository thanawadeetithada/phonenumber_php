<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$selectedCategory = $data['selectedCategory'] ?? 'all';
$searchInput = $data['search'] ?? '';

$query = "SELECT phonenumber, UserID, category, amount, tag, status FROM phonenumber WHERE 1=1";

if ($selectedCategory !== 'all') {
    $query .= " AND category = ?";
}

if (!empty($searchInput)) {
    $query .= " AND (phonenumber LIKE ? OR UserID LIKE ? OR category LIKE ? OR tag LIKE ?)";
}

$stmt = $conn->prepare($query);

$bindParams = [];
if ($selectedCategory !== 'all') {
    $bindParams[] = $selectedCategory;
}
if (!empty($searchInput)) {
    $searchWildcard = '%' . $searchInput . '%';
    $bindParams = array_merge($bindParams, [$searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard]);
}
if ($bindParams) {
    $stmt->bind_param(str_repeat('s', count($bindParams)), ...$bindParams);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = ['Phone Number', 'UserID', 'Category', 'Amount', 'Tag', 'Status'];
$sheet->fromArray($headers, NULL, 'A1');

$rowIndex = 2;
foreach ($data as $row) {
    $sheet->fromArray(array_values($row), NULL, 'A' . $rowIndex);
    $rowIndex++;
}


$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="PhoneNumbers.xlsx"');
$writer->save('php://output');
exit;
?>
