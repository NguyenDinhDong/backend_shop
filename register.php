<?php
header("Content-Type: application/json");
require_once "db_config.php"; // File cấu hình kết nối database

$response = array();

// Kiểm tra nếu request là POST
if ($_SERVER["REQUEST_METHOD"] ?? '' === "POST") {
    // Lấy dữ liệu từ Flutter
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $address = trim($_POST["address"] ?? '');

    // Kiểm tra xem có thiếu dữ liệu không
    if (empty($username) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $response["error"] = true;
        $response["message"] = "Vui lòng nhập đầy đủ thông tin.";
        echo json_encode($response);
        exit();
    }

    // Kiểm tra email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $response["error"] = true;
        $response["message"] = "Email đã được sử dụng!";
        echo json_encode($response);
        exit();
    }

    // Mã hóa mật khẩu
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Thêm người dùng mới vào database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $passwordHash, $phone, $address);

    if ($stmt->execute()) {
        $response["error"] = false;
        $response["message"] = "Đăng ký thành công!";
    } else {
        $response["error"] = true;
        $response["message"] = "Lỗi trong quá trình đăng ký.";
    }

    $stmt->close();
} else {
    $response["error"] = true;
    $response["message"] = "Phương thức không hợp lệ!";
}

echo json_encode($response);
?>
