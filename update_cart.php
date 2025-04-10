<?php
require 'db_config.php';

$user_id = $_POST['user_id'];
$food_id = $_POST['food_id'];
$quantity = $_POST['quantity'];

$sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND food_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $quantity, $user_id, $food_id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['message'] = "Không thể cập nhật số lượng";
}

echo json_encode($response);
?>
