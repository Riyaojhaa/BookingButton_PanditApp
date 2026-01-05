<?php
function sendResponse($success, $code, $message, $data = null, $error = null) {
    http_response_code($code);

    $response = [
        'success' => $success,
        'response_code' => $code,
        'response_message' => $message
    ];

    if (!empty($data)) {
        $response['data'] = $data;
    }

    if (!empty($error)) {
        $response['error'] = $error;
    }

    echo json_encode($response);
    exit;
}
?>
