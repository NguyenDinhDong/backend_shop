<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Lấy dữ liệu user theo email
    $query = "SELECT id, username, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $username, $hashed_password);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        echo json_encode(["status" => "success", "message" => "Đăng nhập thành công", "user_id" => $id, "username" => $username]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email hoặc mật khẩu không chính xác"]);
    }
}
?>
