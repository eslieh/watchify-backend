<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../config.php'; // Include your database connection

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the input from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the required fields are set
    if (!isset($input['user_id'], $input['movie_id'], $input['watch_time'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    $user_id = $input['user_id'];
    $movie_id = $input['movie_id'];
    $watch_time = $input['watch_time'];

    try {
        // Prepare the SQL query to update the watch time
        $stmt = $conn->prepare("
            UPDATE user_watch_data
            SET watch_time = ?, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ? AND movie_id = ?
        ");

        // Execute the query
        if ($stmt->execute([$watch_time, $user_id, $movie_id])) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update watch time"]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
