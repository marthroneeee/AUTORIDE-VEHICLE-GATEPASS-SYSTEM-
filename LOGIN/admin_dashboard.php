<?php
session_start();

$host = 'localhost';
$db = 'autoride_db1';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-update expired QR status to 'Expired' if qr_expiration is past and status is still 'Approved'
$updateExpired = $conn->prepare("UPDATE vehicle_registration SET status = 'Expired', qr_status = 'Inactive' WHERE status = 'Approved' AND qr_expiration < NOW()");
$updateExpired->execute();
$updateExpired->close();

// Update users request_status to 'Expired' if their vehicle registration status is 'Expired'
$updateUserStatus = $conn->prepare("
    UPDATE users u
    JOIN vehicle_registration v ON u.id = v.user_id
    SET u.request_status = 'Expired'
    WHERE v.status = 'Expired'
"); 
$updateUserStatus->execute();
$updateUserStatus->close();

// Function to send SMS via your gateway
function sendSms($mobileNumber, $message) {
    $username = 'sms';  
    $password = 'autoride';  
    $url = 'http://172.20.10.6:1216/message';  

    $data = [
        "message" => $message,
        "phoneNumbers" => [
            $mobileNumber  
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
    $error = curl_errno($ch) ? curl_error($ch) : null;
    curl_close($ch);

    if ($error) {
        return "Error sending SMS: " . $error;
    } else {
        return $response;
    }
}

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];

    // Fetch user mobile number for SMS
    $stmt = $conn->prepare("
        SELECT u.mobile_number 
        FROM vehicle_registration v
        JOIN users u ON v.user_id = u.id
        WHERE v.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($mobile_number);
    $stmt->fetch();
    $stmt->close();

    if ($action === 'approve') {
        // Get next approval_number
        $result = $conn->query("SELECT COALESCE(MAX(approval_number), 0) AS max_approval FROM vehicle_registration");
        $row = $result->fetch_assoc();
        $next_approval_number = $row['max_approval'] + 1;

        // Update vehicle_registration with approval and approval_number
        $stmt = $conn->prepare("UPDATE vehicle_registration SET status = 'Approved', qr_status = 'Active', approved_at = NOW(), approval_number = ? WHERE id = ?");
        $stmt->bind_param("ii", $next_approval_number, $id);
        $stmt->execute();
        $stmt->close();

        // Set qr_expiration 7 days from now
        $expirationDate = date('Y-m-d H:i:s', strtotime('+7 day'));
        $stmt = $conn->prepare("UPDATE vehicle_registration SET qr_expiration = ? WHERE id = ?");
        $stmt->bind_param("si", $expirationDate, $id);
        $stmt->execute();
        $stmt->close();

        // Get id_number and email from vehicle_registration
        $stmt = $conn->prepare("
            SELECT users.id_number, users.email 
            FROM vehicle_registration 
            JOIN users ON vehicle_registration.user_id = users.id 
            WHERE vehicle_registration.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id_number, $email);
        $stmt->fetch();
        $stmt->close();

        // Update users request_status to 'Approved' matching id_number or email
        $stmt = $conn->prepare("UPDATE users SET request_status = 'Approved' WHERE id_number = ? OR email = ?");
        $stmt->bind_param("ss", $id_number, $email);
        $stmt->execute();
        $stmt->close();

        // Send SMS
        $message = "[AutoRide] Your vehicle registration has been approved. You may now view and download your QR code gatepass. Thank you!";
        $smsResponse = sendSms($mobile_number, $message);

        $_SESSION['flash_message'] = "Vehicle registration approved and SMS notification sent.";

    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE vehicle_registration SET status = 'Rejected', qr_status = 'Inactive', approved_at = NULL, qr_expiration = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Get id_number and email from vehicle_registration
        $stmt = $conn->prepare("
            SELECT users.id_number, users.email 
            FROM vehicle_registration 
            JOIN users ON vehicle_registration.user_id = users.id 
            WHERE vehicle_registration.id = ?
        "); 
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id_number, $email);
        $stmt->fetch();
        $stmt->close();

        // Update users request_status to 'Rejected' matching id_number or email
        $stmt = $conn->prepare("UPDATE users SET request_status = 'Rejected' WHERE id_number = ? OR email = ?");
        $stmt->bind_param("ss", $id_number, $email);
        $stmt->execute();
        $stmt->close();

        // Send SMS
        $message = "[AutoRide] Your vehicle registration has been rejected by the admin. If you have questions, please contact the administration. Thank you!";
        $smsResponse = sendSms($mobile_number, $message);

        $_SESSION['flash_message'] = "Vehicle registration rejected and SMS notification sent.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}


$search = $_GET['search'] ?? '';
$search = $conn->real_escape_string($search);

// Fetch vehicle registrations including qr_expiration
$sql = "
    SELECT 
        v.id, 
        u.firstName, 
        u.lastName, 
        u.id_number, 
        u.mobile_number, 
        u.email, 
        u.course_year_section, 
        v.vehicle_type,
        v.ownership_type,   -- ADD THIS LINE
        v.license_file, 
        v.or_file, 
        v.cr_file, 
        v.valid_id_file, 
        v.proof_of_purchase_file, 
        v.registration_date, 
        v.status, 
        v.qr_status, 
        v.approved_at, 
        v.qr_expiration
    FROM vehicle_registration v
    JOIN users u ON v.user_id = u.id
";

if (!empty($search)) {
    $sql .= " WHERE 
        CONCAT(u.firstName, ' ', u.lastName) LIKE '%$search%' OR
        u.course_year_section LIKE '%$search%' OR
        u.mobile_number LIKE '%$search%' OR
        v.vehicle_type LIKE '%$search%' OR
        v.registration_date LIKE '%$search%' OR
        u.id_number LIKE '%$search%' OR
        u.email LIKE '%$search%'
    ";
}

$sql .= " ORDER BY v.registration_date DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard</title>
<style>
  body {
    font-family: Arial, sans-serif;
    color: #fff;
    padding: 2rem;
    height: 100vh;
    background: url('wowbg.jpg') no-repeat center center fixed;
    background-size: cover;
    justify-content: center;
    align-items: center;
    position: relative;
  }
  a.logout {
    display: inline-block;
    margin-top: 1rem;
    background: #FFB000;
    padding: 10px 15px;
    color: #222;
    font-weight: bold;
    text-decoration: none;
    border-radius: 6px;
  }
  a.logout:hover {
    background: #d18f00;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    color: #fff;
  }
  th, td {
    border: 1px solid #444;
    padding: 8px;
    text-align: center;
    font-size: 12px;
  }
  th {
    background-color: #333;
  }
  tr:nth-child(even) {
    background-color: #2a2a2a;
  }
  button {
    padding: 6px 10px;
    margin: 2px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
  }
  button.approve {
    background-color: #FFB000;
    color: white;
  }
  button.reject {
    background-color: #012e3e;
    color: white;
  }
  a.file-link {
    color: #FFB000;
    text-decoration: none;
  }
  a.file-link:hover {
    color: #d18f00;
  }
  .homepage-logo {
    display: block;
    margin: 0 auto 2rem auto;
    width: 400px;
    height: auto;
  }
  h1 {
    text-align: center;
    color: rgb(255, 255, 255);
    font-size: 2rem;
    margin-bottom: -10px;
    letter-spacing: 15px;
    margin-top: -10px;
  }
  p {
    text-align: center;
    color: #fff;
    font-size: 1.2rem;
    margin-bottom: 50px;
  }
  .logout {
    position: absolute;
    top: 30px;
    right: 40px;
    background-color: #FFB000;
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }
  .logout:hover {
    background-color: #d18f00;
  }
  .logo {
    position: absolute;
    top: 30px;
    left: 40px;
    width: 65px;
    height: auto;
    z-index: 10;
  }

  .flash-message {
    background-color: #28a745;
    color: white;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-weight: bold;
    text-align: center;
  }

  .file-link {
  color: #007bff;
  text-decoration: underline;
  cursor: pointer;
  }
  .file-link:hover {  
    text-decoration: none;
  }

</style>
</head>
<body>

<img src="AUTORIDE HOME.png" alt="Logo" class="homepage-logo" />

<h1>ADMIN DASHBOARD</h1>
<p>This is the admin dashboard. Manage vehicle registrations below.</p>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="flash-message">
        <?= htmlspecialchars($_SESSION['flash_message']) ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<form method="GET" style="position: absolute; top: 50px; left: 40px;">
  <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding: 10px 15px; border-radius: 5px; border: none; font-size: 14px;">
  <button type="submit" style="padding: 10px 15px; background-color: #FFB000; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Search</button>
</form>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>ID Number</th>
      <th>Mobile</th>
      <th>Email</th>
      <th>Course / Year / Section</th>
      <th>Vehicle Type</th>
      <th>Ownership Type</th>
      <th>License File</th>
      <th>OR File</th>
      <th>CR File</th>
      <th>Valid ID File</th>
      <th>Authorization</th>
      <th>Registration Date</th>
      <th>Status</th>
      <th>QR Status</th>
      <th>Approved At</th>
      <th>QR Expiration</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
          <td><?= htmlspecialchars($row['id_number']) ?></td>
          <td><?= htmlspecialchars($row['mobile_number']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['course_year_section']) ?></td>
          <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
          <td><?= htmlspecialchars($row['ownership_type']) ?></td>

          <td>
          <?= $row['license_file'] 
              ? '<a class="file-link" href="javascript:void(0);" onclick="openModal(\'../registration/' . htmlspecialchars($row['license_file']) . '\')">View</a>' 
              : 'N/A' ?>
          </td>

          <td>
            <?= $row['or_file'] 
                ? '<a class="file-link" href="javascript:void(0);" onclick="openModal(\'../registration/' . htmlspecialchars($row['or_file']) . '\')">View</a>' 
                : 'N/A' ?>
          </td>

          <td>
            <?= $row['cr_file'] 
                ? '<a class="file-link" href="javascript:void(0);" onclick="openModal(\'../registration/' . htmlspecialchars($row['cr_file']) . '\')">View</a>' 
                : 'N/A' ?>
          </td>

          <td>
            <?= $row['valid_id_file'] 
                ? '<a class="file-link" href="javascript:void(0);" onclick="openModal(\'../registration/' . htmlspecialchars($row['valid_id_file']) . '\')">View</a>' 
                : 'N/A' ?>
          </td>

          <td>
            <?= $row['proof_of_purchase_file'] 
                ? '<a class="file-link" href="javascript:void(0);" onclick="openModal(\'../registration/' . htmlspecialchars($row['proof_of_purchase_file']) . '\')">View</a>' 
                : 'N/A' ?>
          </td>


          <td><?= htmlspecialchars($row['registration_date']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td><?= htmlspecialchars($row['qr_status']) ?></td>
          <td><?= htmlspecialchars($row['approved_at']) ?: 'N/A' ?></td>
          <td><?= htmlspecialchars($row['qr_expiration']) ?: 'N/A' ?></td>

          <td>
            <?php if ($row['status'] === 'Pending'): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button class="approve" type="submit" name="action" value="approve">Approve</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button class="reject" type="submit" name="action" value="reject">Reject</button>
              </form>
            <?php else: ?>
              <em>No actions</em>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="17">No vehicle registrations found.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<div style="margin-top: 20px; text-align: right;">
  <a href="generate_report.php" style="padding: 10px 15px; background-color: #FFB000; color: black; text-decoration: none; border-radius: 5px; font-weight: bold;">Generate Report</a>
</div>
<a href="logout.php" class="logout">Logout</a>


<script>
function openModal(imageUrl) {
  const modal = document.getElementById('fileModal');
  const img = document.getElementById('modalImage');

  img.src = imageUrl;

  // Once image loads, we can check its natural size
  img.onload = () => {
    // Get natural dimensions of image
    const imgWidth = img.naturalWidth;
    const imgHeight = img.naturalHeight;

    // Calculate scale to fit in viewport max size
    const maxWidth = window.innerWidth * 0.9;  
    const maxHeight = window.innerHeight * 0.9; 

    let width = imgWidth;
    let height = imgHeight;

    // scale down if bigger than max sizes
    if (width > maxWidth) {
      const scaleFactor = maxWidth / width;
      width = maxWidth;
      height = height * scaleFactor;
    }

    if (height > maxHeight) {
      const scaleFactor = maxHeight / height;
      height = maxHeight;
      width = width * scaleFactor;
    }

    // Set modal size accordingly
    modal.style.width = width + 'px';
    modal.style.height = height + 'px';

    // Show modal
    modal.style.display = 'block';
  }

}

function closeModal() {
  const modal = document.getElementById('fileModal');
  modal.style.display = 'none';
  document.getElementById('modalImage').src = '';
}

// **I-add ni nga part para mo-close ang modal kung iclick outside image**
const modal = document.getElementById('fileModal');
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    closeModal();
  }
});


</script>

</body>

<div id="fileModal" style="
    display:none; 
    position:fixed; 
    top:50%; left:50%; 
    transform: translate(-50%, -50%);
    background:#fff; 
    padding:10px; 
    border-radius:8px; 
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    max-width: 90vw;       /* max 90% sa screen width */
    max-height: 90vh;      /* max 90% sa screen height */
    overflow:auto;
    z-index: 1000;
">
  <span onclick="closeModal()" style="
      position:absolute; 
      top:5px; 
      right:10px; 
      cursor:pointer; 
      font-size:25px; 
      color:#333;
      user-select:none;
  ">&times;</span>

  <img id="modalImage" src="" style="display:block; max-width:100%; max-height:100%;"/>
</div>



</html>
