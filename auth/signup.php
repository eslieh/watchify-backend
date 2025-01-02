<?php
ini_set('display_errors', 1);  // Enable error reporting
error_reporting(E_ALL);  // Report all errors

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Predefined array of profile image URLs
$profile_images = [
    "https://i.pinimg.com/736x/91/86/1b/91861b749841221d52122f0c2933d8a6.jpg",
    "https://i.pinimg.com/736x/b2/a0/29/b2a029a6c2757e9d3a09265e3d07d49d.jpg",
    "https://i.pinimg.com/736x/e4/6e/c0/e46ec04238a0613900385043239267a6.jpg",
    "https://i.pinimg.com/736x/22/df/fa/22dffa1cdf5e8ba349395a5cd4e534eb.jpg",
    "https://i.pinimg.com/736x/af/89/3a/af893aa0962716cc68395446cf323f44.jpg"
];

// Randomly pick an image URL from the predefined array
$profile_image = $profile_images[array_rand($profile_images)];

include '../config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_data = json_decode(file_get_contents("php://input"), true); // Get input data

    if ($input_data) {
        $uname = trim($input_data['username']);
        $email = trim($input_data['email']);
        $password = md5($input_data['password']); // Hash password
        if (empty($password)) {
            $response = [
                "status" => "error",
                "message" => "password $password."
            ];
        } else {
            $response = [];

            // Check if email already exists
            $checkEmailSQL = "SELECT id FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkEmailSQL);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $response = [
                    "status" => "error",
                    "message" => "Email is already registered."
                ];
            } else {
                // Generate a unique user_id
                $user_id = uniqid("user_");

                // Insert new user along with the randomly selected profile image
                $sql = "INSERT INTO users (user_id, username, email, `password`, profile) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $user_id, $uname, $email, $password, $profile_image);

                // Check if the statement executed successfully
                if ($stmt->execute()) {
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
                            $response = [
                                "status" => "success",
                                "message" => "Registration successful! You can now log in.",
                                "user_id" => $user_id,
                                "profile" => $profile_image
                            ];
                        }
                    }
                    
                } else {
                    $response = [
                        "status" => "error",
                        "message" => "An error occurred during registration. Please try again."
                    ];
                    error_log("Database insertion failed: " . $stmt->error);  // Log any database error
                }

                $stmt->close();
            }

            $checkStmt->close();
            $conn->close();
        }


    } else {
        $response = [
            "status" => "error",
            "message" => "Invalid request method or missing data."
        ];
    }

    echo json_encode($response);  // Send response as JSON
}
?>