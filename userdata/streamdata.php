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
    if (!isset($input['user_id'], $input['movie_id'], $input['movie_thumb'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    $user_id = $input['user_id'];
    $movie_id = $input['movie_id'];
    $watch_time = $input['length'] ?? 0; // Default to 0 if no length is provided
    $movie_thumb = $input['movie_thumb'];

    // Check if the request is for a series
    $season_number = isset($input['season_number']) ? $input['season_number'] : null;
    $episode_number = isset($input['episode_number']) ? $input['episode_number'] : null;

    try {
        // Prepare the SQL query with UPSERT logic
        if ($season_number !== null && $episode_number !== null) {
            // If it's a series with season and episode, insert into a different table or use additional fields
            $query = "
                INSERT INTO user_watch_data (user_id, movie_id, watch_time, movie_thumb, s, e)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                watch_time = ?, 
                movie_thumb = ?, 
                s = ?,
                e = ?,
                updated_at = CURRENT_TIMESTAMP
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "siisssiiii", // Types: i = int, s = string
                $user_id,
                $movie_id,
                $watch_time,
                $movie_thumb,
                $season_number,
                $episode_number,
                $watch_time,
                $movie_thumb,
                $season_number,
                $episode_number
            );

        } else {
            // If it's a movie, insert into the movie-specific table
            $query = "
                INSERT INTO user_watch_data (user_id, movie_id, watch_time, movie_thumb)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                watch_time = ?, 
                movie_thumb = ?, 
                updated_at = CURRENT_TIMESTAMP
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "siisss", // Types: i = int, s = string
                $user_id,
                $movie_id,
                $watch_time,
                $movie_thumb,
                $watch_time,
                $movie_thumb
            );
        }

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to save or update data"]);
        }

        $stmt->close();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}elseif ($_SERVER['REQUEST_METHOD'] === "GET") {
    // Check if the user_id is provided in the query string
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        try {
            // Prepare the SQL query to fetch all movies for the given user_id
            $query = "SELECT * FROM user_watch_data WHERE user_id = ? ORDER BY updated_at DESC LIMIT 15";

            // Prepare the statement
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $user_id); // "s" indicates that user_id is a string

            // Execute the query
            $stmt->execute();

            // Get the result of the query
            $result = $stmt->get_result();

            // Fetch all the rows as an associative array
            $data = $result->fetch_all(MYSQLI_ASSOC);

            // Check if data is available
            if ($data) {
                echo json_encode(["success" => true, "data" => $data]);
            } else {
                echo json_encode(["success" => false, "message" => "No watch data found for this user."]);
            }

            // Close the statement
            $stmt->close();
        } catch (Exception $e) {
            // Handle any errors that occur during the query execution
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        // If user_id is not provided, send an error response
        http_response_code(400);
        echo json_encode(["error" => "user_id parameter is required"]);
    }
}
 else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>