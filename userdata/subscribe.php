<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

include('../config.php');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required parameters
    if (!isset($input['user_id']) || !isset($input['plan_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user_id or plan_id"]);
        exit;
    }

    $user_id = $input['user_id'];
    $plan_name = $input['plan_id']; // Plan name is passed as plan_id

    // Get the plan_id from the plans table based on the plan name
    $query = "SELECT id, price, duration FROM plans WHERE name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $plan_name); // Bind the plan name
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Plan not found"]);
        exit;
    }

    $plan = $result->fetch_assoc();
    $plan_id = $plan['id'];
    $pricing = $plan['price']; // Fixed column name from pricing to price
    $duration = $plan['duration'];

    // Set billing and next billing date (example: 7 days later for next billing)
    $billing_date = date("Y-m-d");
    $next_billing_date = date("Y-m-d", strtotime("+$duration days"));

    // Check if any existing subscription exists for the current user
    $checkSubscriptionQuery = "SELECT * FROM subscription WHERE user_id = ?";
    $checkStmt = $conn->prepare($checkSubscriptionQuery);
    $checkStmt->bind_param("s", $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Subscription exists for the user, so update the subscription details
        $updateQuery = "UPDATE subscription 
                        SET plan_id = ?, billing_date = ?, next_billing_date = ?, pricing = ? 
                        WHERE user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssss", $plan_id, $billing_date, $next_billing_date, $pricing, $user_id);

        if ($updateStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Subscription updated successfully"]);
        } else {
            echo json_encode(["error" => mysqli_error($conn)]); // Output the MySQL error if execution fails
        }

        // Close the update statement
        $updateStmt->close();
    } else {
        // No existing subscription found, so insert a new one
        $insertQuery = "INSERT INTO subscription (user_id, plan_id, billing_date, next_billing_date, pricing) 
                        VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssss", $user_id, $plan_id, $billing_date, $next_billing_date, $pricing);

        if ($insertStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Subscription created successfully"]);
        } else {
            echo json_encode(["error" => mysqli_error($conn)]); // Output the MySQL error if execution fails
        }

        // Close the insert statement
        $insertStmt->close();
    }

    // Close the check subscription statement
    $checkStmt->close();
    // Close the database connection
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
