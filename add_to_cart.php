<?php
require 'db_config.php';

// Kiểm tra tham số đầu vào
if (!isset($_POST['user_id'], $_POST['food_id'], $_POST['quantity'])) {
    echo json_encode(["success" => false, "message" => "Thiếu tham số!"]);
    exit;
}

// Nhận giá trị từ POST và kiểm tra hợp lệ
$user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
$food_id = filter_var($_POST['food_id'], FILTER_VALIDATE_INT);
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

if (!$user_id || !$food_id || !$quantity || $quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

// Kiểm tra xem món ăn đã có trong giỏ hàng chưa
$check_sql = "SELECT quantity FROM cart WHERE user_id = ? AND food_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $user_id, $food_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    // Nếu đã có, cập nhật số lượng
    $new_quantity = $row['quantity'] + $quantity;
    $update_sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND food_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("iii", $new_quantity, $user_id, $food_id);
    $response['success'] = $update_stmt->execute();
} else {
    // Nếu chưa có, thêm mới
    $insert_sql = "INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iii", $user_id, $food_id, $quantity);
    $response['success'] = $insert_stmt->execute();
}

// Kiểm tra lỗi SQL
if (!$response['success']) {
    $response['message'] = "Lỗi SQL: " . $conn->error;
}

echo json_encode($response);
?>
