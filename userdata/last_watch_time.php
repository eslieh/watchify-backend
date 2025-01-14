<?php

// Headers for CORS and JSON content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Include database connection
include '../config.php'; // Replace with your actual database configuration file
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // Validate input parameters
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    $movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : null;

    if (!$user_id || !$movie_id) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing or invalid user_id or movie_id"
        ]);
        exit;
    }

    try {
        // Prepare and execute SQL query
        $query = "SELECT watch_time FROM user_watch_data WHERE user_id = ? AND movie_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if data exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                "status" => "success",
                "last_watch_time" => intval($row["watch_time"])
            ]);
        } else {
            echo json_encode([
                "status" => "success",
                "last_watch_time" => 0 // Default to 0 if no record exists
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Database query failed",
            "error" => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
?>
