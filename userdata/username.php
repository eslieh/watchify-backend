<?php
// Assuming you are passing the user_id as a GET parameter


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
$userId = $_GET['user_id'];
// Retrieve username from the database based on the user_id
include '../config.php'; // Database connection

$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();

if ($username) {
    echo json_encode(['status' => 'success', 'username' => $username]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
