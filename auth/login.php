<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json"); // Ensure the response is in JSON format

// Simulating the database query for user authentication
// Use your actual database logic here
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_data = json_decode(file_get_contents("php://input"), true); // Read JSON input

    $email = trim($input_data['email']);
    $password = md5($input_data['password']);

    // Prepare SQL statement
    $sql = mysqli_query($conn, "SELECT user_id, password, profile FROM users WHERE email = '$email'");
    if (mysqli_num_rows($sql) > 0) {
        $userdata = mysqli_fetch_assoc($sql);
        $user_id = $userdata['user_id'];
        $encPas = $userdata['password'];
        $profile = $userdata['profile'];
        if ($encPas === $password) {
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
                WHERE s.user_id = '$user_id'
            ");
            if (mysqli_num_rows($query) < 1) {
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
                        VALUES ('$user_id', '$planId', '$billingDate', '$nextBillingDate', '$price')
                    ";

                    if (mysqli_query($conn, $insertSubscriptionQuery)) {
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Login successful!',
                            'user_id' => $user_id,
                            'profile' => $profile,  // Include profile image
                        ]);
                    }
                }
            } else {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Login successful!',
                    'user_id' => $user_id,
                    'profile' => $profile,  // Include profile image
                ]);
            }

        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Incorrect password.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => "email doesn't exist, try again or signing up"
        ]);
    }
}
?>