<?php
session_start();
require_once __DIR__ . '/../../db/db.php';

if (!isset($_SESSION['userid']) || $_SESSION['userrole'] !== 'user') {
    header("Location: ../../php/login.php");
    exit();
}

$user_id = (int)$_SESSION['userid'];

// Latest linked card
$stmt = $conn->prepare("SELECT card_id, balance, name FROM passengers WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$passenger = $stmt->get_result()->fetch_assoc();
$stmt->close();

$card_id = $passenger['card_id'] ?? '';
$balance = (float)($passenger['balance'] ?? 0.00);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tap & Pay</title>
  <style>
    body{font-family:system-ui;background:#0f172a;color:#e2e8f0;padding:20px}
    .card{max-width:820px;margin:0 auto;background:#111827;border:1px solid #1f2937;border-radius:14px;padding:18px}
    select,input,button{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
    button{background:#22c55e;border:none;color:#052e16;font-weight:700;cursor:pointer}
    button:disabled{opacity:.6;cursor:not-allowed}
    .status{padding:12px;border-radius:10px;margin-top:10px}
    .ok{background:#052e16;color:#bbf7d0}
    .bad{background:#3f0a0a;color:#fecaca}
    .muted{opacity:.8;font-size:14px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px}
    .box{background:#0b1220;border:1px solid #1f2937;border-radius:12px;padding:12px}
    .label{font-size:12px;opacity:.75}
    .value{font-size:20px;font-weight:700;margin-top:4px}
    @media (max-width:700px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="card">
    <h2>Tap & Pay</h2>
    <div class="muted">Linked card: <b id="linkedCardText"><?php echo htmlspecialchars($card_id ?: 'No card linked'); ?></b></div>

    <div class="grid">
      <div class="box">
        <div class="label">Current balance</div>
        <div class="value" id="balanceText">৳<?php echo number_format($balance, 2); ?></div>
      </div>
      <div class="box">
        <div class="label">Last fare</div>
        <div class="value" id="fareText">৳0.00</div>
      </div>
    </div>

    <hr style="border-color:#1f2937;margin:14px 0;">

    <label>Vehicle</label>
    <select id="vehicleSelect"><option value="">Loading vehicles...</option></select>

    <label>Pickup</label>
    <select id="pickupSelect"><option value="">Loading routes...</option></select>

    <label>Destination</label>
    <select id="dropoffSelect"><option value="">Select pickup first</option></select>

    <label>RFID Input (JT308 types here)</label>
    <input id="rfidInput" type="text" placeholder="Tap card now..." maxlength="10" autofocus>

    <button id="confirmBtn" type="button">Confirm & Deduct Fare</button>

    <div id="status" class="status muted">Select vehicle + pickup + destination, then tap card.</div>

    <div style="margin-top:14px">
      <a href="user-dashboard.php" style="color:#60a5fa;text-decoration:none">← Back to Dashboard</a>
    </div>
  </div>

<script>
let routes = [];
let vehicles = [];
let linkedCard = "<?php echo htmlspecialchars($card_id); ?>".trim();

function normalizeCard(rawId){
  return rawId.replace(/[^0-9A-Fa-f]/g,'').padStart(10,'0').toUpperCase().slice(-10);
}

function setStatus(msg, ok=true){
  const el = document.getElementById('status');
  el.className = 'status ' + (ok ? 'ok' : 'bad');
  el.textContent = msg;
}

function setFareAndBalance(fare, newBalance){
  if (!Number.isNaN(fare)) document.getElementById('fareText').textContent = '৳' + Number(fare).toFixed(2);
  if (!Number.isNaN(newBalance)) document.getElementById('balanceText').textContent = '৳' + Number(newBalance).toFixed(2);
}

async function loadVehicles(){
  const res = await fetch('/web-tech/tapintapout/passanger/php/api/vehicles.php');
  const raw = await res.text();
  let data;
  try { data = JSON.parse(raw); } catch(e) {
    console.error("Invalid vehicles JSON:", raw);
    setStatus("Vehicles API returned invalid JSON", false);
    return;
  }

  vehicles = data;
  const sel = document.getElementById('vehicleSelect');
  sel.innerHTML = '<option value="">Select vehicle</option>' +
    vehicles.map(v => `<option value="${v.id}">${v.name} (ID:${v.id})</option>`).join('');
}

async function loadRoutes(){
  const res = await fetch('/web-tech/tapintapout/passanger/php/api/routes.php');
  const raw = await res.text();
  let data;
  try { data = JSON.parse(raw); } catch(e) {
    console.error("Invalid routes JSON:", raw);
    setStatus("Routes API returned invalid JSON", false);
    return;
  }
  routes = data;

  const pickupSelect = document.getElementById('pickupSelect');
  const pickups = [...new Set(routes.map(r=>r.pickup_location).filter(x => x && x !== 'null'))].sort();
  pickupSelect.innerHTML =
    '<option value="">Select pickup</option>' +
    pickups.map(p=>`<option value="${p}">${p}</option>`).join('');
}

function loadDropoffs(pickup){
  const dropoffSelect = document.getElementById('dropoffSelect');
  const filtered = routes.filter(r=>r.pickup_location === pickup);
  const dropoffs = [...new Set(filtered.map(r=>r.dropoff_location).filter(x => x && x !== 'null'))].sort();
  dropoffSelect.innerHTML =
    '<option value="">Select destination</option>' +
    dropoffs.map(d=>`<option value="${d}">${d}</option>`).join('');
}

document.getElementById('pickupSelect').addEventListener('change', (e)=>{
  loadDropoffs(e.target.value);
});

document.getElementById('rfidInput').addEventListener('input', (e)=>{
  if(e.target.value.length >= 8){
    e.target.value = normalizeCard(e.target.value);
  }
});

document.getElementById('confirmBtn').addEventListener('click', async ()=>{
  const btn = document.getElementById('confirmBtn');
  btn.disabled = true;

  try {
    if(!linkedCard){
      setStatus('No card linked with this account. Register card first.', false);
      return;
    }

    const vehicleId = document.getElementById('vehicleSelect').value;
    const pickup = document.getElementById('pickupSelect').value;
    const dropoff = document.getElementById('dropoffSelect').value;
    const typed = document.getElementById('rfidInput').value.trim();
    const cardId = normalizeCard(typed);

    if(!vehicleId){
      setStatus('Select vehicle first.', false);
      return;
    }
    if(!pickup || !dropoff){
      setStatus('Select pickup and destination first.', false);
      return;
    }
    if(!typed){
      setStatus('Tap/scan your card first (input is empty).', false);
      return;
    }
    if(cardId !== linkedCard){
      setStatus(`Wrong card. Expected ${linkedCard} but got ${cardId}`, false);
      return;
    }

    const fd = new FormData();
    fd.append('vehicle_id', vehicleId);
    fd.append('card_id', cardId);
    fd.append('pickup', pickup);
    fd.append('dropoff', dropoff);

    const res = await fetch('/web-tech/tapintapout/passanger/php/api/deduct-fare.php', { method:'POST', body: fd });
    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); } catch(e){
      console.error("Invalid JSON from deduct-fare:", raw);
      setStatus('Server returned invalid JSON (check console)', false);
      return;
    }

    if(data.success){
      setFareAndBalance(Number(data.fare ?? 0), Number(data.new_balance ?? NaN));
      setStatus(data.message || 'Success', true);
      document.getElementById('rfidInput').value = '';
      document.getElementById('rfidInput').focus();
    } else {
      setStatus((data.message || 'Failed') + (data.debug ? (' | ' + data.debug) : ''), false);
    }
  } catch (e) {
    console.error(e);
    setStatus('API/Server error', false);
  } finally {
    btn.disabled = false;
  }
});

loadVehicles();
loadRoutes();
document.getElementById('rfidInput').focus();
</script>
</body>
</html>
