<?php
require_once __DIR__ . '/../../db_connection.php';
header('Content-Type: application/json');

// ✅ Allow only GET method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'code' => 405,
        'message' => 'Only GET method allowed'
    ]);
    exit;
}

// ✅ Get user_id from query params
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'user_id is required'
    ]);
    exit;
}

try {
    // ✅ Fetch data from MongoDB
    $cursor = $bookingsCollection->find(['user_id' => (string)$user_id]);
    $bookings = iterator_to_array($cursor);

    // ✅ If no bookings found
    if (empty($bookings)) {
        http_response_code(200); // 200 is better than 204 (204 = no response body)
        echo json_encode([
            'success' => true,
            'code' => 200,
            'message' => 'No bookings found for this user',
            'data' => []
        ]);
        exit;
    }

    // ✅ Format the bookings before sending
    foreach ($bookings as &$b) {
        $b['_id'] = (string)$b['_id'];
        if (isset($b['created_at'])) {
            $b['created_at'] = $b['created_at']->toDateTime()->format('Y-m-d H:i:s');
        }
    }

    // ✅ Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'code' => 200,
        'message' => 'Bookings fetched successfully',
        'data' => $bookings
    ]);

} catch (Exception $e) {
    // ✅ Error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'code' => 500,
        'message' => 'Failed to fetch bookings',
        'error' => $e->getMessage()
    ]);
}
?>
