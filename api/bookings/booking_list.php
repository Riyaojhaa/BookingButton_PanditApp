<?php
// ✅ CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../db_connection.php';
header('Content-Type: application/json');

// ✅ Allow only GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "apiResponseCode" => 405,
        "apiResponseData" => [
            "responseCode" => 405,
            "responseData" => null,
            "responseMessage" => "Only GET method allowed",
            "responseFrom" => "booking_list"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Only GET method allowed"
    ]);
    exit;
}

// ✅ Get user_id or pandit_id from query params
$user_id   = $_GET['user_id'] ?? null;
$pandit_id = $_GET['pandit_id'] ?? null;

// ❌ If neither is provided
if (!$user_id && !$pandit_id) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "user_id or pandit_id is required",
            "responseFrom" => "booking_list"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "user_id or pandit_id is required"
    ]);
    exit;
}

// ❌ If both are provided
if ($user_id && $pandit_id) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "Send only one: user_id OR pandit_id",
            "responseFrom" => "booking_list"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Send only one: user_id OR pandit_id"
    ]);
    exit;
}

try {
    // ✅ Build MongoDB filter dynamically
    $filter = [];

    if ($user_id) {
        $filter['user_id'] = (string)$user_id;
    } else {
        $filter['pandit_id'] = (string)$pandit_id;
    }

    // ✅ Fetch bookings (latest first)
    $cursor = $bookingsCollection->find(
        $filter,
        ['sort' => ['created_at' => -1]]
    );

    $bookings = iterator_to_array($cursor);

    // ✅ If no bookings found
    if (empty($bookings)) {
        http_response_code(200);
        echo json_encode([
            "apiResponseCode" => 200,
            "apiResponseData" => [
                "responseCode" => 200,
                "responseData" => [],
                "responseMessage" => "No bookings found",
                "responseFrom" => "booking_list"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "No bookings found"
        ]);
        exit;
    }

    // ✅ Format response
    foreach ($bookings as &$b) {
        $b['booking_id'] = (string)$b['_id'];
        unset($b['_id']);

        if (isset($b['created_at'])) {
            $b['created_at'] = $b['created_at']->toDateTime()->format('Y-m-d H:i:s');
        }

        if (isset($b['updated_at'])) {
            $b['updated_at'] = $b['updated_at']->toDateTime()->format('Y-m-d H:i:s');
        }
    }

    // ✅ Success response
    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => array_values($bookings),
            "responseMessage" => "Bookings fetched successfully",
            "responseFrom" => "booking_list"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Bookings fetched successfully"
    ]);

} catch (Exception $e) {
    // ❌ Error response
    http_response_code(500);
    echo json_encode([
        "apiResponseCode" => 500,
        "apiResponseData" => [
            "responseCode" => 500,
            "responseData" => null,
            "responseMessage" => "Failed to fetch bookings",
            "responseFrom" => "booking_list"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Failed to fetch bookings"
    ]);
}
?>
