<?php
// Add these lines at the very top, right after <?php
header('Access-Control-Allow-Origin: *'); // or specify your domain
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
require_once __DIR__ . '/../../db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false,'code' => 405 , 'message' => 'Only POST allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$required = ['user_id', 'pandit_id', 'pooja_type', 'date', 'time', 'address'];

foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false,'code' => 400 , 'message' => "Missing field: $field"]);
        exit;
    }
}

// Allowed payment statuses
$valid_payment_status = ['unpaid', 'paid', 'pending'];

// If user sends payment_status use it, otherwise default 'unpaid'
$payment_status = isset($data['payment_status']) && in_array($data['payment_status'], $valid_payment_status)
    ? $data['payment_status']
    : 'unpaid';

$booking = [
    'user_id' => $data['user_id'],
    'pandit_id' => $data['pandit_id'],
    'pooja_type' => $data['pooja_type'],
    'date' => $data['date'],
    'time' => $data['time'],
    'address' => $data['address'],
    'additional_notes' => $data['additional_notes'] ?? '',
    'booking_status' => 'pending',
    'payment_status' => $payment_status, // fixed values only
    'otp_for_confirmation' => strval(rand(100000, 999999)), // OTP converted to string
    'created_at' => new MongoDB\BSON\UTCDateTime()
];

$existingBooking = $bookingsCollection->findOne([
    'user_id'   => $data['user_id'],
    'pandit_id' => $data['pandit_id'],
    'date'      => $data['date'],
    'time'      => $data['time']
]);

if ($existingBooking) {
    http_response_code(409); // Conflict
    echo json_encode([
        'success' => false,
        'code' => 409,
        'message' => 'Booking already exists for this pandit at the selected date and time'
    ]);
    exit;
}

try {
    $insert = $bookingsCollection->insertOne($booking);

    $booking['booking_id'] = (string)$insert->getInsertedId();
    $booking['created_at'] = date('Y-m-d H:i:s');

    echo json_encode([
        'success' => true,
        'code' => 200,
        'message' => 'Booking created successfully',
        'data' => $booking
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'code' => 500 ,
        'message' => 'Insert failed',
        'error' => $e->getMessage()
    ]);
}
?>
