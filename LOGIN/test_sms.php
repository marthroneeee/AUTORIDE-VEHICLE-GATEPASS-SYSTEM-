<?php
function sendSMS($mobile, $message) {
    $endpoint = 'https://api.itexmo.com/api/broadcast';
    $email = 'marthroneandroquijano@gmail.com'; // <-- Replace with your actual iTexMo client email
    $password = 'marthroneandroquijano1216';        // Your iTexMo password

    $payload = json_encode([
        'Email' => $email,
        'Password' => $password,
        'ApiCode' => 'PR-API-CODE',               // Optional: your API code
        'Recipients' => [$mobile],
        'Message' => $message,
    ]);

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode("$email:$password")
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        curl_close($ch);
        return "Curl error: $error_msg";
    }
    curl_close($ch);
    return $response;
}

// Replace with a valid mobile number and message
$testMobile = '09157253994';  
$testMessage = 'Hello! This is a test SMS from your iTexMo integration.';

$result = sendSMS($testMobile, $testMessage);

echo "API Response: " . $result;
