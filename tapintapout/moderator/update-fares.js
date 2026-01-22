// Update Fares JS - Enhanced interactivity
document.addEventListener('DOMContentLoaded', function() {
    const routeSelect = document.getElementById('route_id');
    const oldFareInput = document.getElementById('old_fare');
    const newFareInput = document.getElementById('new_fare');
    const previewDiv = document.getElementById('farePreview');
    const previewOld = document.getElementById('previewOld');
    const previewNew = document.getElementById('previewNew');
    const form = document.querySelector('#fareForm');
    const submitBtn = document.getElementById('submitBtn');

    // Auto-fill old fare & show preview
    routeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const oldFare = parseFloat(selectedOption.getAttribute('data-old-fare'));
        
        if (!isNaN(oldFare)) {
            oldFareInput.value = `৳${oldFare.toFixed(2)}`;
            previewDiv.style.display = 'block';
            previewOld.textContent = `৳${oldFare.toFixed(2)}`;
            newFareInput.focus();
            updateSubmitButton(oldFare);
        } else {
            oldFareInput.value = '';
            previewDiv.style.display = 'none';
            submitBtn.style.background = '';
        }
    });

    // Live preview & button color
    newFareInput.addEventListener('input', function() {
        const newFare = parseFloat(this.value);
        const oldFare = parseFloat(routeSelect.options[routeSelect.selectedIndex]?.getAttribute('data-old-fare') || 0);
        
        if (!isNaN(newFare) && previewDiv.style.display !== 'none') {
            previewNew.textContent = `৳${newFare.toFixed(2)}`;
            updateSubmitButton(oldFare, newFare);
        }
    });

    function updateSubmitButton(oldFare, newFare = null) {
        const btn = submitBtn;
        const change = newFare !== null ? newFare - oldFare : 0;
        
        if (change > 0) {
            btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            btn.innerHTML = '📈 <strong>Increase</strong> Fare';
        } else if (change < 0) {
            btn.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            btn.innerHTML = '📉 <strong>Decrease</strong> Fare';
        } else {
            btn.style.background = '';
            btn.innerHTML = '📤 Submit Request';
        }
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        const newFare = parseFloat(newFareInput.value);
        const oldFare = parseFloat(routeSelect.options[routeSelect.selectedIndex]?.getAttribute('data-old-fare') || 0);
        
        if (isNaN(newFare) || newFare <= 0) {
            e.preventDefault();
            alert('Please enter a valid fare amount (৳0.01 or more)');
            newFareInput.focus();
            return false;
        }
        
        if (Math.abs(newFare - oldFare) < 0.01) {
            e.preventDefault();
            alert('New fare must be different from current fare.');
            newFareInput.focus();
            return false;
        }
        
        const change = newFare - oldFare;
        const message = `Confirm fare change for ${routeSelect.options[routeSelect.selectedIndex]?.textContent || 'selected route'}:\n\n` +
                       `Current: ৳${oldFare.toFixed(2)}\n` +
                       `New: ৳${newFare.toFixed(2)}\n` +
                       `${change > 0 ? 'Increase' : 'Decrease'} by ৳${Math.abs(change).toFixed(2)}`;
        
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
        
        // Loading animation
        submitBtn.innerHTML = '⏳ Processing...';
        submitBtn.disabled = true;
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            form.dispatchEvent(new Event('submit'));
        }
    });
});
