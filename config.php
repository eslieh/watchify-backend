<?php
  // $hostname = "localhost:3306";
  // $username = "mealscok_meals";
  // $password = "-cCtu@NY39w5L8";
  // $dbname = "mealscok_meals";
  $hostname = "localhost";
  $username = "root";
  $password = "";
  $dbname = "watchify";

  $conn = mysqli_connect($hostname, $username, $password, $dbname);
  if(!$conn){
    echo "Database connection error: ".mysqli_connect_error();
  }
  date_default_timezone_set("Africa/Nairobi");
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
    
?>