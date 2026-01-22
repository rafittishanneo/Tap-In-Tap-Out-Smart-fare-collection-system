<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'user') {
    header("Location: ../../php/login.php");
    exit();
}

require_once __DIR__ . '/../../db/db.php';
$username = $_SESSION['username'] ?? 'Passenger';

$error = $success = '';
$card_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_id = trim($_POST['card_id'] ?? '');
    
    if (!preg_match('/^[0-9A-F]{10}$/i', $card_id)) {
        $error = 'Enter valid 10-digit hex card ID';
    } elseif (empty($card_id)) {
        $error = 'Card ID required';
    } else {
        // Check duplicate
        $stmt = $conn->prepare("SELECT id FROM passengers WHERE card_id = ?");
        $stmt->bind_param('s', $card_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = '❌ Card already registered';
        }
        $stmt->close();
        
        if (empty($error)) {
            $uid = (int)($_SESSION['userid'] ?? 0);
            $initial_balance = 50.00;
            $name = $username; // your session username

            $stmt = $conn->prepare("INSERT INTO passengers (user_id, card_id, name, balance) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issd", $uid, $card_id, $name, $initial_balance);

        if ($stmt->execute()) {
            $success = '✅ Card registered & linked to your account! Welcome bonus: ৳50';
            $card_id = '';
        } else {
            $error = '❌ Registration failed. Try again.';
        }
        $stmt->close();

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register NFC Card | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/user-style.css">
</head>
<body>
    <div class="dashboard">
        <header class="header">
            <div class="header-content">
                <div class="welcome">🏷️ Register Your NFC Card</div>
                <div class="user-info">
                    <a href="user-dashboard.php" class="btn btn-primary">← Dashboard</a>
                    <a href="../../php/logout.php" class="logout">Logout</a>
                </div>
            </div>
        </header>

        <main class="content">
            <section class="nfc-card-section">
                <h2 class="section-title">Link RFID Card</h2>
                
                <?php if ($error): ?>
                    <div class="card-status error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="card-status success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" class="card-form">
                    <div class="card-input-group">
                        <input type="text" name="card_id" id="nfcCardInput" 
                               placeholder="Scan JT308 card or enter ID (e.g. 123456789A)" 
                               maxlength="10" value="<?php echo htmlspecialchars($card_id); ?>" required autofocus>
                        <button type="submit" class="btn-scan">✅ Register</button>
                    </div>
                    <p class="help-text">
                        💡 Hold RFID card 5cm from JT308 scanner or type 10 hex digits
                    </p>
                </form>
            </section>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // JT308 auto-fill
        $(document).on('input', 'body', function(e) {
            const input = $(e.target).val().trim();
            if (input.match(/^[0-9A-F]{10}$/i)) {
                $('#nfcCardInput').val(input).trigger('input');
            }
        });
    </script>
</body>
</html>
