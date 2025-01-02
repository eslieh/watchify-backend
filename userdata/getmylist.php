<?php
// Headers for CORS and JSON content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Database connection settings
include '../config.php';
$user_id = $_GET['user_id'];
// Query to fetch movies from the "my_list" table
$sql = "SELECT movie_id, title, thumb_url FROM my_list WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $movies = [];

    // Fetch each row as an associative array
    while ($row = $result->fetch_assoc()) {
        $movies[] = [
            "id" => $row["movie_id"],
            "title" => $row["title"],
            "poster_path" => $row["thumb_url"] // Map 'thumb_url' to 'poster_path' for consistency
        ];
    }

    // Return movies as a JSON response
    echo json_encode($movies);
} else {
    // If no movies found, return an empty array
    echo json_encode([]);
}

// Close the database connection
$conn->close();
?>
