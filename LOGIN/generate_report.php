<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "autoride_db1";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Main data query
$sql = "SELECT 
            v.id, 
            CONCAT(u.firstName, ' ', u.lastName) AS fullname,
            u.id_number,
            u.mobile_number,
            u.email,
            u.course_year_section,
            v.vehicle_type, 
            v.status
        FROM vehicle_registration v
        JOIN users u ON v.user_id = u.Id";

$result = $conn->query($sql);

// Count totals
$total_all = $conn->query("SELECT COUNT(*) AS total FROM vehicle_registration")->fetch_assoc()['total'];
$total_approved = $conn->query("SELECT COUNT(*) AS total FROM vehicle_registration WHERE status = 'Approved'")->fetch_assoc()['total'];
$total_rejected = $conn->query("SELECT COUNT(*) AS total FROM vehicle_registration WHERE status = 'Rejected'")->fetch_assoc()['total'];

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=AutoRide_VehicleReport.csv');

$output = fopen('php://output', 'w');

// Add CSV column headers
fputcsv($output, [
    'ID', 'Full Name', 'ID Number', 'Mobile Number', 'Email', 'Course/Year/Section', 
    'Vehicle Type', 'Status'
]);

// Add main data
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Add an empty row for spacing
fputcsv($output, []);

// Add summary totals
fputcsv($output, ['Summary']);
fputcsv($output, ['Total Registered', $total_all]);
fputcsv($output, ['Total Approved', $total_approved]);
fputcsv($output, ['Total Rejected', $total_rejected]);

fclose($output);
$conn->close();
exit;

?>
