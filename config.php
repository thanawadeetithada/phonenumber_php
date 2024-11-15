<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phone_number";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

function executeQuery($query) {
    global $conn;
    $result = $conn->query($query);
    if (!$result) {
        echo "Error: " . $conn->error;
    }
    return $result;
}
?>
