<?php
// scanner_page.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Code Scanner - AATC Visitor Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <!-- QR Scanner Library -->
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', sans-serif;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .scanner-container {
      max-width: 600px;
      margin: auto;
      padding: 3rem 1rem;
      background-color: white;
      border-radius: 1.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      margin-top: 5rem;
      text-align: center;
    }

    .scanner-title {
      color: #4361ee;
      font-weight: 800;
      font-size: 1.75rem;
      margin-bottom: 1.5rem;
    }

    #reader {
      width: 100%;
      max-width: 400px;
      margin: 0 auto 1.5rem;
    }

    #result {
      font-size: 1rem;
      color: #212529;
      background-color: #e9f7ef;
      padding: 1rem;
      border-radius: 0.5rem;
      margin-top: 1rem;
      min-height: 60px;
      word-wrap: break-word;
    }

    .footer {
      text-align: center;
      margin-top: auto;
      padding: 1rem;
      font-size: 0.875rem;
      color: #6c757d;
    }

    .logo {
      width: 60px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
 <!-- #region -->
  <div class="scanner-container">
    <img src="assets/logo-green-yellow.png" alt="AATC Logo" class="logo">
    <div class="scanner-title">Scan Visitor QR Code</div>
    <div id="reader"></div>
    <div id="result">Waiting for scan...</div>
    <button id="checkinBtn" class="btn btn-success mt-3" style="display: none;" onclick="checkInVisitor()">Check-In Visitor</button>
  </div>

  <div class="mt-3 text-center">
  <button id="notifyBtn" class="btn btn-primary" style="display: none;">Notify Host</button>
</div>


  
  <div class="footer">
    &copy; <?php echo date("Y"); ?> AATC Visitor Management System
  </div>

  <script>
    function onScanSuccess(decodedText, decodedResult) {
      document.getElementById("result").innerHTML = "Scanned: " + decodedText;
      document.getElementById("checkinBtn").style.display = "none";

      fetch("verify_qr.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "qr_data=" + encodeURIComponent(decodedText),
      })
      .then(response => response.text())
      .then(data => {
        const parts = data.split("|");
        const message = parts[0];
        const status = parts[1];

        document.getElementById("result").innerHTML = message;

        if (status === "FOUND") {
          document.getElementById("checkinBtn").style.display = "inline-block";
        }
      });
    }

    function checkInVisitor() {
      alert("Visitor Checked In! (You can implement check-in logic here)");
    }

    const html5QrcodeScanner = new Html5QrcodeScanner("reader", {
      fps: 10,
      qrbox: 250
    });

    html5QrcodeScanner.render(onScanSuccess);
  </script>

</body>
</html>

