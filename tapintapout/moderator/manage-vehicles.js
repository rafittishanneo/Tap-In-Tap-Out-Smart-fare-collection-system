function handleCardTap(cardId) {
    if (!currentVehicleId) {
        $('#status').html(`❌ আগে একটি বাস (Vehicle) সিলেক্ট করুন!`);
        return;
    }

    // আইডি ১০ ডিজিট ফিক্স করা
    let normalizedId = cardId.toString().padStart(10, '0');
    $('#status').html(`⏳ কার্ড চেক হচ্ছে: ${normalizedId}...`);
    
    // সিঙ্গেল এপিআই এন্ডপয়েন্ট
    $.ajax({
        url: '../api/process-tap.php', // এই ফাইলটি আমরা নিচে তৈরি করবো
        type: 'POST',
        data: JSON.stringify({ card_id: normalizedId, vehicle_id: currentVehicleId }),
        contentType: 'application/json',
        success: function(data) {
            if (data.success) {
                if (data.action === 'tap_in') {
                    $('#status').html(`✅ Tap IN সফল! বর্তমান ব্যালেন্স: ৳${data.balance}`).css('color', '#22c55e');
                } else {
                    $('#status').html(`✅ Tap OUT সফল! ভাড়া: ৳${data.fare} | বাকি: ৳${data.balance}`).css('color', '#3b82f6');
                }
                // লিস্টে ডাটা আপডেট করা
                updateJourneyList(data.journey_details);
            } else {
                $('#status').html(`❌ ${data.message}`).css('color', '#ef4444');
            }
        },
        error: function() {
            $('#status').html(`❌ সার্ভার কানেকশন এরর!`);
        }
    });
}