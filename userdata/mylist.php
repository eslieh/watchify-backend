<?php

// Headers for CORS and JSON content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Include database connection
include '../config.php'; // Replace with your actual database configuration file

// Handle different request methods
$method = $_SERVER["REQUEST_METHOD"];

// Get the action from the query string
$action = $_GET['action'] ?? null;

// Validate that the user_id exists
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode(["error" => "User ID is required"]);
    exit();
}

$user_id = $_GET['user_id']; // Use user_id as a string

try {
    if ($method === "GET" && $action === "check") {
        // Check if the movie exists in the user's "My List"
        if (!isset($_GET['movie_id']) || !is_numeric($_GET['movie_id'])) {
            echo json_encode(["error" => "Valid Movie ID is required"]);
            exit();
        }

        $movie_id = intval($_GET['movie_id']);

        // Prepared statement to check the movie
        $stmt = $conn->prepare("SELECT id FROM my_list WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("si", $user_id, $movie_id); // "si" - string and integer
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["found" => true]);
        } else {
            echo json_encode(["found" => false]);
        }

        $stmt->close();

    }elseif ($method === "POST" && ($action === "add" || $action === "remove")) {
        // Get the raw POST data
        $inputData = json_decode(file_get_contents('php://input'), true);
    
        if (!isset($inputData['movie_id']) || !is_numeric($inputData['movie_id'])) {
            echo json_encode(["error" => "Valid Movie ID is required"]);
            exit();
        }
    
        $movie_id = intval($inputData['movie_id']);
        $title = $inputData['title'] ?? null; // Optional title
        $thumb_url = $inputData['thumb_url'] ?? null; // Optional thumb_url
    
        if ($action === "add") {
            // Check for required fields
            if (!$title || !$thumb_url) {
                echo json_encode(["error" => "Title and thumbnail URL are required"]);
                exit();
            }
    
            // Prepared statement to add the movie
            $stmt = $conn->prepare("INSERT INTO my_list (user_id, movie_id, title, thumb_url) VALUES (?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE title = VALUES(title), thumb_url = VALUES(thumb_url)");
            $stmt->bind_param("siss", $user_id, $movie_id, $title, $thumb_url);
    
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Movie added to My List"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add: " . $stmt->error]);
            }
    
            $stmt->close();
    
        } elseif ($action === "remove") {
            // Prepared statement to remove the movie
            $stmt = $conn->prepare("DELETE FROM my_list WHERE user_id = ? AND movie_id = ?");
            $stmt->bind_param("si", $user_id, $movie_id);
    
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Movie removed from My List"]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to remove: " . $stmt->error]);
            }
    
            $stmt->close();
        }
    } else {
        echo json_encode(["error" => "Invalid request method or action"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}

?>
