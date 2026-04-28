<?php
// ✅ CORS headers add karo (same as booking_create)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../db_connection.php';
header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "apiResponseCode" => 405,
        "apiResponseData" => [
            "responseCode" => 405,
            "responseData" => null,
            "responseMessage" => "Only PUT method allowed",
            "responseFrom" => "update_status"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Only PUT method allowed"
    ]);
    exit;
}


$input = json_decode(file_get_contents("php://input"), true);

$booking_id = $input['booking_id'] ?? null;
$status     = $input['status'] ?? null;


if (!$booking_id || !$status) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "booking_id and status are required",
            "responseFrom" => "update_status"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "booking_id and status are required"
    ]);
    exit;
}


$allowedStatus = ['accepted', 'rejected'];
if (!in_array($status, $allowedStatus)) {
    http_response_code(400);
    echo json_encode([
        "apiResponseCode" => 400,
        "apiResponseData" => [
            "responseCode" => 400,
            "responseData" => null,
            "responseMessage" => "Status must be accepted or rejected",
            "responseFrom" => "update_status"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Status must be accepted or rejected"
    ]);
    exit;
}

try {
    // ✅ Update booking status
    $result = $bookingsCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($booking_id)],
        [
            '$set' => [
                'booking_status' => $status,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ],
            '$unset' => [
                'status' => ""  
            ]
        ]
    );


    if ($result->getMatchedCount() === 0) {
        http_response_code(404);
        echo json_encode([
            "apiResponseCode" => 404,
            "apiResponseData" => [
                "responseCode" => 404,
                "responseData" => null,
                "responseMessage" => "Booking not found",
                "responseFrom" => "update_status"
            ],
            "apiResponseFrom" => "php",
            "apiResponseMessage" => "Booking not found"
        ]);
        exit;
    }

    // ✅ Success response with updated data
    http_response_code(200);
    echo json_encode([
        "apiResponseCode" => 200,
        "apiResponseData" => [
            "responseCode" => 200,
            "responseData" => [
                'booking_id' => $booking_id,
                'new_status' => $status
            ],
            "responseMessage" => "Booking status updated to {$status}",
            "responseFrom" => "update_status"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Booking status updated to {$status}"
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "apiResponseCode" => 500,
        "apiResponseData" => [
            "responseCode" => 500,
            "responseData" => null,
            "responseMessage" => "Failed to update booking status",
            "responseFrom" => "update_status"
        ],
        "apiResponseFrom" => "php",
        "apiResponseMessage" => "Failed to update booking status"
    ]);
}
?>