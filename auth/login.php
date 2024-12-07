<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json"); // Ensure the response is in JSON format

// Simulating the database query for user authentication
// Use your actual database logic here
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_data = json_decode(file_get_contents("php://input"), true); // Read JSON input

    $email = trim($input_data['email']);
    $password = md5($input_data['password']);

    // Prepare SQL statement
    $sql = mysqli_query($conn, "SELECT user_id, password, profile FROM users WHERE email = '$email'");
    if (mysqli_num_rows($sql) > 0) {
        $userdata = mysqli_fetch_assoc($sql);
        $user_id = $userdata['user_id'];
        $encPas = $userdata['password'];
        $profile = $userdata['profile'];
        if ($encPas === $password) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful!',
                'user_id' => $user_id,
                'profile' => $profile,  // Include profile image
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Incorrect password.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => `$email deos not exist`
        ]);
    }
}
?>