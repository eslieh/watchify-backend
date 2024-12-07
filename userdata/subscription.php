<?php
// Assuming you are passing the user_id as a GET parameter


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");
$userId = $_GET['user_id'];
// Retrieve username from the database based on the user_id
include '../config.php'; // Database connection

