/**
 * Handle Tap In and Tap Out functionality
 */
function handleTap(type) {
    const statusDisplay = document.getElementById('active-status');
    const statusCard = statusDisplay.closest('.stat-card'); // Gets the whole card
    
    if (type === 'in') {
        // Logic for Tapping In
        alert("Scanning QR... You have Tapped In successfully!");
        
        statusDisplay.innerText = "1 Active";
        statusCard.style.border = "2px solid #10b981"; // Highlight green
        statusCard.style.transform = "scale(1.05)";    // Subtle pop effect
        
        setTimeout(() => { statusCard.style.transform = "scale(1)"; }, 300);
        
    } else {
        // Logic for Tapping Out
        const confirmOut = confirm("Are you sure you want to Tap Out and end your journey?");
        
        if (confirmOut) {
            alert("Journey Completed. $0 deducted from your wallet.");
            
            statusDisplay.innerText = "0 Active";
            statusCard.style.border = "none";
            statusCard.style.opacity = "0.7"; // Dim the card when inactive
            
            /* Next Step: In a real app, you would use:
               fetch('../php/update_journey.php', { method: 'POST' })
            */
        }
    }
}

// Check if the script is loaded correctly in the console
console.log("Tap system initialized for: " + document.title);