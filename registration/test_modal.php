<?php
// Dummy condition to test modal display
$show_modal = true; // I-set true to test modal show, false to hide

$message = "You have already submitted a registration request. Please wait for admin approval.";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Modal Test</title>
  <style>
    .modal {
      display: none;
      position: fixed;
      z-index: 100;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }
    .modal.show {
      display: flex;
    }
    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 8px;
      max-width: 400px;
      text-align: center;
      position: relative;
    }
    .close {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<?php if ($show_modal): ?>
<div id="infoModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Notice</h2>
    <p><?= htmlspecialchars($message) ?></p>
  </div>
</div>
<?php endif; ?>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('infoModal');
    if (modal) {
      modal.classList.add('show');  // show modal on page load

      // Close modal on clicking close button
      modal.querySelector('.close').addEventListener('click', () => {
        modal.classList.remove('show');
      });

      // Optional: close on clicking outside modal content
      modal.addEventListener('click', e => {
        if (e.target === modal) {
          modal.classList.remove('show');
        }
      });
    }
  });
</script>

</body>
</html>
