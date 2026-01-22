<?php
session_start();

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'user') {
    header("Location: ../../php/login.php");
    exit();
}

require_once __DIR__ . '/../../db/db.php';
$user_id = (int)$_SESSION['userid'];

// 1. Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$username = $user_data['email'] ?? 'Passenger';
$useremail = $user_data['email'] ?? '';

// 2. Get user's card balance (latest linked card)  ✅ simpler & correct
$stmt = $conn->prepare("SELECT balance FROM passengers WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$balance_row = $stmt->get_result()->fetch_assoc();
$walletBalance = $balance_row['balance'] ?? 0.00;
$stmt->close();

// 3. Count trips (optional; keep your old if needed)
$stmt = $conn->prepare("SELECT COUNT(*) as total_trips FROM journeys WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$trips_row = $stmt->get_result()->fetch_assoc();
$totalTrips = $trips_row['total_trips'] ?? 0;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passenger Dashboard | Tap in Tap Out</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web-tech/tapintapout/css/user-style.css">
    <style>
  /* Force modal text to be visible */
  #topupModal * {
    color: #111827 !important; /* dark text */
  }

  /* Keep button text readable */
  #confirmTopup {
    color: #053b19 !important;
  }
  #closeTopup {
    color: #111827 !important;
  }

  /* Input text + placeholder */
  #topupAmount {
    color: #111827 !important;
    background: #ffffff !important;
  }
  #topupAmount::placeholder {
    color: #6b7280 !important;
  }
</style>

</head>

<body>
<div class="dashboard">
    <header class="header">
        <div class="header-content">
            <div class="welcome">
                Welcome back, <strong><?php echo htmlspecialchars($username); ?>!</strong>
            </div>
            <div class="user-info">
                <div class="user-email"><?php echo htmlspecialchars($useremail); ?></div>
                <a href="../../php/logout.php" class="logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="content">
        <div class="stats-grid">
            <div class="stat-card wallet">
                <div class="stat-icon">💳</div>
                <div class="stat-number" id="wallet-display">৳<?php echo number_format((float)$walletBalance, 2); ?></div>
                <div class="stat-label">Wallet Balance</div>
            </div>
            <div class="stat-card trips">
                <div class="stat-icon">🚌</div>
                <div class="stat-number"><?php echo (int)$totalTrips; ?></div>
                <div class="stat-label">Total Trips</div>
            </div>
            <div class="stat-card status">
                <div class="stat-icon">📍</div>
                <div class="stat-number" id="journey-status">Not Active</div>
                <div class="stat-label">Journey Status</div>
            </div>
        </div>

        <section class="nfc-card-section">
            <h2 class="section-title">🏷️ NFC/RFID Card</h2>
            <div class="card-input-group">
                <input type="text" id="nfcCardInput" placeholder="Scan card or enter ID (10 hex digits)" maxlength="10">
                <button class="btn-scan" type="button" onclick="scanCard()">🔍 Scan/Link Card</button>
            </div>
            <div id="cardStatus" class="card-status"></div>
        </section>

        <div class="quick-actions">
            <button class="btn btn-primary" id="tapInBtn" type="button" onclick="window.location.href='tap-page.php'">🚀 Tap In</button>
            <button class="btn btn-primary" id="tapOutBtn" type="button" onclick="handleTap('out')" disabled>🛑 Tap Out</button>
            <a href="card-registration.php" class="btn btn-outline">🏷️ Register Card</a>
            <button class="btn btn-outline" type="button" id="topupBtn">💰 Top Up Wallet</button>
        </div>

        <section class="recent-journeys">
            <h2 class="section-title">Recent Journeys</h2>
            <div id="journey-list">
                <div class="no-data">Scan card to start</div>
            </div>
        </section>
    </main>
</div>

<!-- ✅ TOPUP MODAL (must be BEFORE scripts) -->
<div id="topupModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.6); padding:20px; z-index:9999;">
    <div style="max-width:420px; margin:80px auto; background:#fff; border-radius:12px; padding:18px;">
        <h3 style="margin-bottom:10px;">Top Up Wallet</h3>

        <label>Amount (BDT)</label>
        <input type="number" id="topupAmount" min="10" step="1" placeholder="e.g., 200"
               style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; margin:8px 0;">

        <div id="topupMsg" style="margin:8px 0; font-size:14px;"></div>

        <div style="display:flex; gap:10px; margin-top:10px;">
            <button type="button" id="confirmTopup"
                    style="flex:1; padding:12px; border:none; border-radius:10px; background:#22c55e; color:#053b19; font-weight:700; cursor:pointer;">
                Confirm
            </button>
            <button type="button" id="closeTopup"
                    style="flex:1; padding:12px; border:1px solid #ddd; border-radius:10px; background:#f8fafc; cursor:pointer;">
                Cancel
            </button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    let currentCardId = '';
    let isJourneyActive = false;

    // ---------------------------
    // ✅ TOPUP MODAL (WORKING)
    // ---------------------------
    $(document).ready(function () {

        function openTopup(){
            $('#topupMsg').text('').css('color','');
            $('#topupAmount').val('');
            $('#topupModal').fadeIn(120);
        }
        function closeTopup(){
            $('#topupModal').fadeOut(120);
        }

        // open
        $(document).on('click', '#topupBtn', function () {
            openTopup();
        });

        // cancel
        $(document).on('click', '#closeTopup', function () {
            closeTopup();
        });

        // click outside modal closes it
        $(document).on('click', '#topupModal', function(e){
            if (e.target.id === 'topupModal') closeTopup();
        });

        // confirm
        $(document).on('click', '#confirmTopup', async function () {
            const amt = parseFloat($('#topupAmount').val() || '0');

            if (!amt || amt < 10) {
                $('#topupMsg').css('color','red').text('Minimum top up is ৳10');
                return;
            }

            try {
                const fd = new FormData();
                fd.append('amount', amt);

                const res = await fetch('/web-tech/tapintapout/passanger/php/api/topup.php', {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (data.success) {
                    $('#topupMsg').css('color','green').text(data.message);
                    $('#wallet-display').text('৳' + Number(data.new_balance).toFixed(2));
                    setTimeout(closeTopup, 700);
                } else {
                    $('#topupMsg').css('color','red').text(data.message);
                }
            } catch (err) {
                console.error(err);
                $('#topupMsg').css('color','red').text('API/Server error');
            }
        });

        // JT308 auto-detect (optional)
        $(document).on('input', 'body', function(e) {
            const input = $(e.target).val().trim();
            if (input.match(/^[0-9A-F]{10}$/i) && input !== currentCardId) {
                $('#nfcCardInput').val(input);
                verifyCard(input);
            }
        });

    });

    async function scanCard() {
        const cardId = $('#nfcCardInput').val().trim().toUpperCase();
        if (!cardId.match(/^[0-9A-F]{10}$/i)) {
            showCardStatus('error', 'Enter 10-digit hex ID');
            return;
        }
        await verifyCard(cardId);
    }

    async function verifyCard(cardId) {
        try {
            const response = await $.post('../../moderator/check-card.php', { card_id: cardId });
            if (response.success) {
                currentCardId = cardId;
                showCardStatus('success', `✅ Balance: ৳${response.balance}`);
                $('#tapOutBtn').prop('disabled', false);
            } else {
                showCardStatus('error', response.message);
            }
        } catch (e) {
            showCardStatus('error', 'Server error');
        }
    }

    async function handleTap(tapType) {
        if (!currentCardId) return alert('Link card!');
        try {
            const response = await $.post('../../moderator/rfid-tap.php', {
                card_id: currentCardId,
                tap_type: tapType
            });
            if (response.success) {
                $('#wallet-display').text('৳' + Number(response.new_balance).toFixed(2));
                showCardStatus('success', `${tapType.toUpperCase()}: ৳${response.fare}`);
            } else {
                alert(response.message);
            }
        } catch (e) {
            alert('Tap failed');
        }
    }

    function showCardStatus(type, message) {
        $('#cardStatus').removeClass('success error').addClass(type).html(message);
    }
</script>

</body>
</html>
