<?php
header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| Allow only GET method
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'code' => 405,
        'message' => 'Only GET method allowed'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get query parameters
|--------------------------------------------------------------------------
*/
$poojaType  = $_GET['pooja_type'] ?? null;
$travelPref = $_GET['travel_preference'] ?? null;

/*
|--------------------------------------------------------------------------
| Validate required params
|--------------------------------------------------------------------------
*/
if (!$poojaType || !$travelPref) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'pooja_type and travel_preference are required'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Base prices (same as Android)
|--------------------------------------------------------------------------
*/
$basePrices = [
    "Griha Pravesh"       => 2500,
    "Satyanarayan Katha" => 3000,
    "Rudrabhishek"       => 4000,
    "Wedding Pooja"      => 15000,
    "Navagraha Shanti"   => 3500
];

/*
|--------------------------------------------------------------------------
| Travel multipliers
|--------------------------------------------------------------------------
*/
$travelMultipliers = [
    "Within 10 km" => 1.0,
    "Within State" => 1.5,
    "All India"    => 2.5
];

/*
|--------------------------------------------------------------------------
| Validate pooja type
|--------------------------------------------------------------------------
*/
if (!array_key_exists($poojaType, $basePrices)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'Invalid pooja type'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Validate travel preference
|--------------------------------------------------------------------------
*/
if (!array_key_exists($travelPref, $travelMultipliers)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'code' => 400,
        'message' => 'Invalid travel preference'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Price calculation logic
|--------------------------------------------------------------------------
*/
$basePrice  = $basePrices[$poojaType];
$multiplier = $travelMultipliers[$travelPref];

$juniorPrice = (int) ($basePrice * $multiplier);
$midPrice    = (int) ($basePrice * $multiplier * 1.3);
$seniorPrice = (int) ($basePrice * $multiplier * 1.6);

/*
|--------------------------------------------------------------------------
| Success response
|--------------------------------------------------------------------------
*/
http_response_code(200);
echo json_encode([
    'success' => true,
    'code' => 200,
    'message' => 'Pandit price calculated successfully',
    'data' => [
        'pooja_type' => $poojaType,
        'travel_preference' => $travelPref,
        'travel_multiplier' => $multiplier,
        'base_price' => $basePrice,
        'prices' => [
            'junior' => $juniorPrice,
            'mid'    => $midPrice,
            'senior' => $seniorPrice
        ]
    ]
]);
exit;
