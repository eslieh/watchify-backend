<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Allow CORS and set content type
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Include database connection
include '../config.php';

// Check if the user_id is provided as a GET parameter
$data = [];
$query = mysqli_query($conn, "SELECT id, name, price, duration, `description` FROM plans");
if (mysqli_num_rows($query) > 0) {
    // Fetch results into an associative array
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }

    // Return data as JSON
    echo json_encode($data);
}else{
    echo json_encode(["error" => "data not found"]);
}

// Close database connection
mysqli_close($conn);
?>
