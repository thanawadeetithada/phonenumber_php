<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include 'config.php';

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$selectedCategory = $data['selectedCategory'] ?? 'all';
$tableData = $data['tableData'] ?? [];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Phone Number');
$sheet->setCellValue('B1', 'UserID');
$sheet->setCellValue('C1', 'Category');
$sheet->setCellValue('D1', 'Amount');
$sheet->setCellValue('E1', 'Tag');
$sheet->setCellValue('F1', 'Status');

$rowIndex = 2;
foreach ($tableData as $row) {
    if ($selectedCategory === 'all' || $row[2] === $selectedCategory) {
        $sheet->setCellValue('A' . $rowIndex, $row[0]);
        $sheet->setCellValue('B' . $rowIndex, $row[1]);
        $sheet->setCellValue('C' . $rowIndex, $row[2]);
        $sheet->setCellValue('D' . $rowIndex, $row[3]);
        $sheet->setCellValue('E' . $rowIndex, $row[4]);
        $sheet->setCellValue('F' . $rowIndex, $row[5]);
        $rowIndex++;
    }
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="FilteredTable.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
