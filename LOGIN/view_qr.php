<?php
session_start();
include("connect.php");

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

require_once('lib/qrlib.php');

$email = $_SESSION['email'];

// Step 1: Get user info
$stmt = $conn->prepare("SELECT id, id_number, firstName, lastName FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo "<p>User information not found.</p>";
    exit();
}

$user_id = $row['id'];
$id_number = $row['id_number'];
$name = $row['firstName'] . ' ' . $row['lastName'];

// Step 2: Get approval status
$stmt = $conn->prepare("SELECT status, approval_number FROM vehicle_registration WHERE user_id = ? ORDER BY approved_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($status, $approval_number);
$stmt->fetch();
$stmt->close();

$status = strtolower($status ?? '');
$showQR = ($status === 'approved' && !empty($approval_number));

if ($showQR) {
    $qrDir = 'temp_qr/';
    if (!file_exists($qrDir)) {
        mkdir($qrDir, 0755, true);
    }

    // Format approval number
    $minDigits = 4;
    $paddedNumber = str_pad($approval_number, $minDigits, '0', STR_PAD_LEFT);
    $approvalCode = '2025' . $paddedNumber; // Format: 2025xxxx

    // QR content as JSON
    $data = [
        'name' => $name,
        'approval_number' => $approvalCode,
        'id_number' => $id_number
    ];
    $qrContent = json_encode($data);

    $qrFile = $qrDir . 'qr_' . md5($qrContent) . '.png';

    // Generate QR code if not yet existing
    if (!file_exists($qrFile)) {
        QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 5);
    }

    // Combine QR code with template and text
    $templateFile = 'QR CODE TEMPLATE.png'; // 800x400
    $combinedFile = $qrDir . 'combined_' . md5($qrContent) . '.png';

    if (file_exists($combinedFile)) {
        unlink($combinedFile);
    }

    $template = imagecreatefrompng($templateFile);
    $qr = imagecreatefrompng($qrFile);

    // ✅ Adjusted QR size
    $qrWidth = 320;
    $qrHeight = 280;

    $resizedQR = imagecreatetruecolor($qrWidth, $qrHeight);
    imagealphablending($resizedQR, false);
    imagesavealpha($resizedQR, true);
    imagecopyresampled($resizedQR, $qr, 0, 0, 0, 0, $qrWidth, $qrHeight, imagesx($qr), imagesy($qr));

    // ✅ Position QR a bit higher
    $posX = 800 - $qrWidth - 40;
    $posY = (400 - $qrHeight) / 2 - 15;

    imagecopy($template, $resizedQR, $posX, $posY, 0, 0, $qrWidth, $qrHeight);

    // ✅ Add approval number text below
    $textColor = imagecolorallocate($template, 0, 0, 0);
    $fontFile = __DIR__ . '/fonts/Montserrat-Bold.ttf';
    $approvalLabel = 'APPROVAL NUMBER: ' . $approvalCode;

    if (!file_exists($fontFile)) {
        imagestring($template, 5, $posX, $posY + $qrHeight + 5, $approvalLabel, $textColor);
    } else {
        $fontSize = 12;
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $approvalLabel);
        $textWidth = $bbox[2] - $bbox[0];
        $textX = $posX + ($qrWidth / 2) - ($textWidth / 2);
        $textY = $posY + $qrHeight + 10;

        imagettftext($template, $fontSize, 0, $textX, $textY, $textColor, $fontFile, $approvalLabel);
    }

    imagepng($template, $combinedFile);
    imagedestroy($template);
    imagedestroy($qr);
    imagedestroy($resizedQR);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My QR Code</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0; padding: 0;
      height: 100vh;
      background: url('wowbg.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .qr-box {
      width: 1000px;
      height: 700px;
      border-radius: 10px;
      overflow: hidden;
      background: #ffffff1c;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }
    img.qr-combined {
      width: 800px;
      height: 400px;
      border-radius: 8px;
      box-shadow: 0 0 10px #00000099;
      margin-bottom: 20px;
    }
    .buttons {
      margin-top: 15px;
    }
    .download-button,
    .back-button {
      background-color: #FFB000;
      color: white;
      padding: 10px 15px;
      border-radius: 6px;
      text-decoration: none;
      transition: background 0.3s;
      margin: 0 5px;
      display: inline-block;
    }
    .download-button:hover,
    .back-button:hover {
      background-color: #d18f00;
    }
    .status-message {
      font-size: 18px;
      color: #fff;
      margin-top: 20px;
      max-width: 700px;
    }

    p {
      font-size: 30px;
      color: #fff;
      margin: 10px 0;
    }
  </style>
</head>
<body>
  <div class="qr-box">
    <?php if ($showQR): ?>
      <img src="<?php echo htmlspecialchars($combinedFile . '?v=' . time()); ?>" alt="QR Code with Template" class="qr-combined" /><br />
      <p class="status-message">Your account is <strong><?php echo htmlspecialchars($status); ?></strong>.</p>
      <p>ID Number: <strong><?php echo htmlspecialchars($id_number); ?></strong></p>
      <div class="buttons">
        <a href="<?php echo htmlspecialchars($combinedFile); ?>" download="MyQRCode.png" class="download-button">DOWNLOAD QR CODE</a>
        <a href="homepage.php" class="back-button">BACK TO HOME</a>
      </div>
    <?php elseif (in_array($status, ['rejected', 'expired'])): ?>
      <img src="QRREJECTED.png" alt="Rejected" style="width: 400px; margin-bottom: 20px;" />
      <p class="status-message">Your account is <strong><?php echo htmlspecialchars($status); ?></strong>. Please contact the admin.</p>
      <a href="homepage.php" class="back-button">BACK TO HOME</a>
    <?php elseif ($status === 'pending'): ?>
      <img src="QRPENDING.png" alt="Pending" style="width: 400px; margin-bottom: 20px;" />
      <p class="status-message">Your account is <strong>pending</strong>. Please wait for admin approval.</p>
      <a href="homepage.php" class="back-button">BACK TO HOME</a>
    <?php else: ?>
      <p class="status-message">Your account status is <strong><?php echo htmlspecialchars($status); ?></strong>. QR code is only available when approved.</p>
      <a href="homepage.php" class="back-button">BACK TO HOME</a>
    <?php endif; ?>
  </div>
</body>
</html>
