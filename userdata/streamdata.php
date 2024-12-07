<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Allow the preflight request to proceed
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['user_id'], $input['movie_id'], $input['movie_thumb'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    include '../config.php'; // Include your database connection

    $user_id = $input['user_id'];
    $movie_id = $input['movie_id'];
    $length = $input['length'] ?? 0;
    $movie_thumb = $input['movie_thumb'];

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare("INSERT INTO watch_data (user_id, movie_id, length, watch_date, movie_thumb) VALUES (?, ?, ?, NOW(), ?)");
    if ($stmt->execute([$user_id, $movie_id, $length, $movie_thumb])) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save data"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
