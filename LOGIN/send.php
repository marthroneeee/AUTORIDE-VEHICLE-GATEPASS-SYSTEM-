<?php
$username = 'sms';  // ilisi sa imong username sa SMS Gateway
$password = 'autoride';  // ilisi sa imong password

$url = 'http://192.168.100.5:1216/message';  // device IP ug port

$data = [
    "message" => "[AutoRide] Your vehicle registration has been approved. You may now view and download your QR code gatepass. Thank you!",
    "phoneNumbers" => [
        "+639692506594"  // +63 prefix sa imong number, importane ang format
    ]
];

$jsonData = json_encode($data);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo "Response from SMS Gateway: " . $response;
}

curl_close($ch);
?>
