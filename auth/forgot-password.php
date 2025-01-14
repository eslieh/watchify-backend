<?php
header('Content-Type: application/json');
require_once 'db.php'; // Include your database connection

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? trim($data['email']) : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Invalid email address."]);
    exit;
}

// Check if email exists in the database
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "No account found with that email."]);
    exit;
}

// Generate a unique token
$token = bin2hex(random_bytes(32));
$expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save the token in the database
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expires_at);
$stmt->execute();

// Prepare reset link
$reset_link = "https://fueldash.net/watchify/reset-password.php?token=" . $token;

// Send reset email
$subject = "Password Reset Request";
$message = "Hello,\n\nClick the link below to reset your password:\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.";
$headers = "From: no-reply@fueldash.net";

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(["status" => "success", "message" => "Password reset link sent to your email."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send reset email."]);
}

$conn->close();
?>
