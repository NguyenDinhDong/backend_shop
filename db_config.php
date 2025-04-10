<?php
// db_config.php

// Định nghĩa các biến kết nối
$db_host = "localhost"; // Địa chỉ máy chủ MySQL
$db_user = "root"; // Tên người dùng MySQL
$db_pass = ""; // Mật khẩu MySQL
$db_name = "thoima"; // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
} 

?>
