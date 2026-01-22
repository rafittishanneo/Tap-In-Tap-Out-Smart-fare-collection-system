$(document).ready(function () {
    const form = $('#vehicleForm');
    const alertBox = $('#alert');

    function showAlert(type, message) {
        alertBox
            .removeClass('alert-error alert-success')
            .addClass(type === 'error' ? 'alert-error' : 'alert-success')
            .text(message)
            .fadeIn();
    }

    function clearErrors() {
        $('.error').text('');
        alertBox.hide();
    }

    form.on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;

        function setError(id, msg) {
            $('#' + id + '_error').text(msg);
            valid = false;
        }

        const vehicle_type = $('#vehicle_type').val().trim();
        const vehicle_code = $('#vehicle_code').val().trim();
        const driver_name = $('#driver_name').val().trim();
        const driver_mobile = $('#driver_mobile').val().trim();
        const driver_license = $('#driver_license').val().trim();
        const rfid_scanner_id = $('#rfid_scanner_id').val().trim();
        const status = $('#status').val();

        if (!vehicle_type) setError('vehicle_type', 'Vehicle type is required');
        if (vehicle_code.length < 3) setError('vehicle_code', 'Vehicle ID must be at least 3 characters');
        if (driver_name.length < 3) setError('driver_name', 'Driver name must be at least 3 characters');

        const mobileRegex = /^01[0-9]{9}$/; // BD style mobile
        if (!mobileRegex.test(driver_mobile)) setError('driver_mobile', 'Enter valid mobile (11 digits starting 01)');

        if (driver_license.length < 5) setError('driver_license', 'License number is too short');
        if (rfid_scanner_id.length < 3) setError('rfid_scanner_id', 'RFID scanner ID is required');

        if (!valid) return;

        $.ajax({
            url: 'vehicle-save.php',
            type: 'POST',
            dataType: 'json',
            data: {
                vehicle_type,
                vehicle_code,
                driver_name,
                driver_mobile,
                driver_license,
                rfid_scanner_id,
                status
            },
            success: function (res) {
                if (res.success) {
                    showAlert('success', res.message);
                    form[0].reset();
                    $('#status').val('active');
                } else {
                    if (res.errors) {
                        Object.keys(res.errors).forEach(function (field) {
                            $('#' + field + '_error').text(res.errors[field]);
                        });
                    }
                    if (res.message) showAlert('error', res.message);
                }
            },
            error: function () {
                showAlert('error', 'Unexpected error. Please try again.');
            }
        });
    });
});
