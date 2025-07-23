<?php
// check_copyvio.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$email = "YOUR_EMAIL"; // Your Copyleaks email
$api_key = "YOUR_API_KEY"; // Your API key

$text = $_POST['text'] ?? '';
if (!$text) {
    echo json_encode(['error' => 'No text submitted']);
    exit;
}

// Step 1: Authenticate
$token_url = "https://id.copyleaks.com/v3/account/login/api";
$token_data = json_encode(['email' => $email, 'key' => $api_key]);

$token_opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => $token_data,
    ]
];
$context  = stream_context_create($token_opts);
$response = file_get_contents($token_url, false, $context);
$auth = json_decode($response, true);
$access_token = $auth['access_token'] ?? '';

if (!$access_token) {
    echo json_encode(['error' => 'Failed to authenticate']);
    exit;
}

// Step 2: Submit scan
$scan_id = uniqid("justapedia_", true);
$submit_url = "https://api.copyleaks.com/v3/scans/submit/$scan_id";

$submit_data = json_encode([
    "base64" => base64_encode($text),
    "filename" => "Justapedia Copyvio Check"
]);

$submit_opts = [
    'http' => [
        'method'  => 'PUT',
        'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $access_token\r\n",
        'content' => $submit_data,
    ]
];
$submit_context = stream_context_create($submit_opts);
$submit_response = file_get_contents($submit_url, false, $submit_context);

echo json_encode(['success' => 'Text submitted successfully. Check results in Copyleaks dashboard.']);
