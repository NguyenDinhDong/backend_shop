<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Gọi file db_config.php
require_once "db_config.php"; // Đảm bảo rằng đường dẫn đúng

// Kiểm tra kết nối database
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Lỗi kết nối Database"]);
    exit();
}

// Truy vấn dữ liệu từ bảng foods
$query = "SELECT * FROM foods";
$result = mysqli_query($conn, $query);

// Kiểm tra lỗi SQL
if (!$result) {
    echo json_encode(["status" => "error", "message" => "Lỗi SQL: " . mysqli_error($conn)]);
    exit();
}

// Chuyển dữ liệu thành JSON
$foods = [];
while ($row = mysqli_fetch_assoc($result)) {
    $foods[] = $row;
}

echo json_encode(["status" => "success", "foods" => $foods]);

// Đóng kết nối
mysqli_close($conn);
?>