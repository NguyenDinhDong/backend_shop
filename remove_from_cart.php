<?php
require 'db_config.php';

$user_id = $_POST['user_id'];
$food_id = $_POST['food_id'];

$sql = "DELETE FROM cart WHERE user_id = ? AND food_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $food_id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = "Không thể xóa sản phẩm";
}

echo json_encode($response);
?>
