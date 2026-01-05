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

// ✅ Only PUT method allowed
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'code' => 405,
        'message' => 'Only PUT method allowed'
    ]);
    exit;
}

// ✅ Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

$booking_id = $input['booking_id'] ?? null;
$status     = $input['status'] ?? null;

// ✅ Validation
if (!$booking_id || !$status) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'booking_id and status are required'
    ]);
    exit;
}

// ✅ Allow only accepted or rejected
$allowedStatus = ['accepted', 'rejected'];
if (!in_array($status, $allowedStatus)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'Status must be accepted or rejected'
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
            ]
        ]
    );

    // ✅ If no document updated
    if ($result->getMatchedCount() === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'code' => 404,
            'message' => 'Booking not found'
        ]);
        exit;
    }

    // ✅ Success response with updated data
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'code' => 200,
        'message' => "Booking status updated to {$status}",
        'data' => [
            'booking_id' => $booking_id,
            'new_status' => $status
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'code' => 500,
        'message' => 'Failed to update booking status',
        'error' => $e->getMessage()
    ]);
}
?>