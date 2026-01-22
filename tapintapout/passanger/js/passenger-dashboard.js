let currentCardId = '';
let isJourneyActive = false;
const USER_ID = 2;  // Replace with your test user ID from users table

$(document).ready(function() {
    loadRecentJourneys();
});

// JT308 auto-detection
$(document).on('input', 'body', function(e) {
    const input = $(e.target).val().trim();
    if (input.match(/^[0-9A-F]{10}$/i) && input !== currentCardId) {
        $('#nfcCardInput').val(input);
        verifyCard(input);
    }
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
        const response = await $.post('../moderator/check-card.php', { card_id: cardId });
        
        if (response.success) {
            currentCardId = cardId;
            showCardStatus('success', `✅ ${response.name}<br>Balance: ৳${response.balance}`);
            $('#tapInBtn, #tapOutBtn').prop('disabled', false);
        } else {
            showCardStatus('error', response.message);
        }
    } catch (e) {
        showCardStatus('error', 'Server error');
        console.error(e);
    }
}

async function handleTap(tapType) {
    if (!currentCardId) return alert('Link card first!');

    try {
        const response = await $.post('../moderator/rfid-tap.php', {
            card_id: currentCardId,
            tap_type: tapType
        });

        if (response.success) {
            $('.stat-number:first').text('৳' + response.new_balance.toFixed(2));
            isJourneyActive = (tapType === 'in');
            $('#journey-status').text(isJourneyActive ? 'Active' : 'Completed');
            $('#tapInBtn').prop('disabled', isJourneyActive);
            $('#tapOutBtn').prop('disabled', !isJourneyActive);
            loadRecentJourneys();
            showCardStatus('success', `${tapType.toUpperCase()}: ৳${response.fare}`);
        } else {
            alert(response.message);
        }
    } catch (e) {
        alert('Tap failed');
    }
}

function showCardStatus(type, message) {
    const $status = $('#cardStatus');
    $status.removeClass('success error').addClass(type).html(message);
}

async function loadRecentJourneys() {
    try {
        const response = await $.get('../moderator/get-journeys.php?limit=5');
        let html = response.journeys?.length ? 
            response.journeys.map(j => `
                <div class="journey-item">
                    <div><div class="route-name">${j.vehicle_code}</div>
                    <div class="route-meta">${j.tap_type} • ${new Date(j.timestamp).toLocaleString()}</div></div>
                    <div class="price-info">
                        <div class="price-amount">-৳${(j.route_fare||0).toFixed(2)}</div>
                        <div class="status-badge">${j.tap_type==='out'?'Completed':'Active'}</div>
                    </div>
                </div>
            `).join('') : '<div class="no-data">No journeys</div>';
        $('#journey-list').html(html);
    } catch (e) {
        console.error(e);
    }
}
