<?php
// db_connect.php: your database connection file
include 'db_connect.php';

// iTexMo SMS function
function itexmo($number, $message, $apicode){
    $url = 'https://www.itexmo.com/php_api/api.php';
    $params = array(
        '1' => $number,
        '2' => $message,
        '3' => $apicode
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Get registration id from URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Update the status to approved and record approval time
    $sql_update = "UPDATE registrations 
                   SET status = 'approved', approved_at = NOW() 
                   WHERE id = $id";
    $update_result = mysqli_query($conn, $sql_update);

    if ($update_result) {
        // Fetch user info for SMS
        $sql_fetch = "SELECT name, mobile_number, vehicle_type FROM registrations WHERE id = $id";
        $fetch_result = mysqli_query($conn, $sql_fetch);

        if ($fetch_result && mysqli_num_rows($fetch_result) > 0) {
            $row = mysqli_fetch_assoc($fetch_result);

            $name = $row['name'];
            $mobile = $row['mobile_number'];    // e.g. "09123456789"
            $vehicle_type = $row['vehicle_type'];

            // Compose the SMS message
            $message = "Hello $name, your $vehicle_type registration has been approved. Thank you!";

            // Your iTexMo API code here
            $api_code = "YOUR_ITEXMO_API_CODE_HERE";

            // Send SMS
            $sms_result = itexmo($mobile, $message, $api_code);

            if ($sms_result == 0) {
                echo "Registration approved and SMS sent successfully.";
            } else {
                echo "Registration approved but SMS sending failed. Error code: $sms_result";
            }
        } else {
            echo "Could not find user info.";
        }
    } else {
        echo "Failed to update registration status.";
    }
} else {
    echo "No registration ID provided.";
}
?>
