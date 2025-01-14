<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../config.php'; // Include your database connection

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the user_id and movie_id from the query string
    if (!isset($_GET['user_id'], $_GET['movie_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid parameters"]);
        exit;
    }

    $user_id = $_GET['user_id'];
    $movie_id = $_GET['movie_id'];

    try {
        // Prepare the SQL query to fetch the last watch time
        $query = mysqli_query($conn, "SELECT watch_time FROM user_watch_data WHERE user_id = '$user_id' AND movie_id = '$movie_id'");
        $data = mysqli_fetch_assoc($query);
        if (mysqli_num_rows($query) > 0) {
            echo json_encode(["status" => "success", "last_watch_time" => $data['watch_time']]);
        } else {
            echo json_encode(["status" => "success", "last_watch_time" => 0]); // No previous watch time
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
