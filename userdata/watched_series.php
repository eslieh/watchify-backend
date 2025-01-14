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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if(isset($_GET['user_id']) && isset($_GET['series_id'])){
        $user_id = $_GET['user_id'];
        $series_id = $_GET['series_id'];
        $query = mysqli_query($conn, "SELECT s, e FROM user_watch_data WHERE user_id = '$user_id' AND movie_id = '$series_id'");
        if(mysqli_num_rows($query) > 0){
            $data = mysqli_fetch_assoc($query);
            echo json_encode(["status" => "success", "s" => $data['s'], "e" => $data['e']]);
        }else{
            echo json_encode(["status" => "failed", "message" => "watched data not found"]); 
        }
    }else{
        echo json_encode(["error" => "Invaid values"]);
    }
}else{
    echo json_encode(["error" => "Bad request"]); 
}