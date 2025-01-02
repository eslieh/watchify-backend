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
if (isset($_GET['user_id'])) {
    $userId = $_GET['user_id'];

    // SQL query to fetch subscription details joined with plan details
    $query = mysqli_query($conn, "
        SELECT 
            s.id AS subscription_id, 
            s.user_id, 
            p.name AS plan_name, 
            s.billing_date, 
            s.next_billing_date, 
            s.pricing, 
            p.duration 
        FROM subscription s
        INNER JOIN plans p ON s.plan_id = p.id
        WHERE s.user_id = '$userId'
    ");

    // Check if query execution was successful
    if ($query) {
        $data = [];

        if (mysqli_num_rows($query) > 0) {
            // Fetch results into an associative array
            while ($row = mysqli_fetch_assoc($query)) {
                $data[] = $row;
            }

            // Return data as JSON
            echo json_encode($data);
        } else {
            // If no subscription found, create one with the free plan
            $freePlanQuery = mysqli_query($conn, "SELECT id, price FROM plans WHERE name = 'Mini' LIMIT 1");

            if ($freePlanQuery && mysqli_num_rows($freePlanQuery) > 0) {
                $freePlan = mysqli_fetch_assoc($freePlanQuery);
                $planId = $freePlan['id'];
                $price = 'free'; // Set price to free for the new subscription
                $billingDate = date('Y-m-d'); // Set today's date as the billing date
                $nextBillingDate = date('Y-m-d', strtotime('+1 week')); // Set the next billing date a week later
                
                // Insert the subscription
                $insertSubscriptionQuery = "
                    INSERT INTO subscription (user_id, plan_id, billing_date, next_billing_date, pricing)
                    VALUES ('$userId', '$planId', '$billingDate', '$nextBillingDate', '$price')
                ";
                
                if (mysqli_query($conn, $insertSubscriptionQuery)) {
                    // Return the created subscription as JSON
                    echo json_encode([
                        "subscription_id" => mysqli_insert_id($conn),
                        "user_id" => $userId,
                        "plan_name" => 'Mini',
                        "billing_date" => $billingDate,
                        "next_billing_date" => $nextBillingDate,
                        "pricing" => $price,
                        "duration" => 7 // Mini plan is 7 days
                    ]);
                } else {
                    echo json_encode(["error" => "Failed to create a new subscription: " . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(["error" => "Failed to fetch Mini plan details"]);
            }
        }
    } else {
        // Handle query error
        echo json_encode(["error" => "Query failed: " . mysqli_error($conn)]);
    }
} else {
    // Return an error if user_id is not provided
    echo json_encode(["error" => "Missing user_id parameter"]);
}

// Close database connection
mysqli_close($conn);
?>
