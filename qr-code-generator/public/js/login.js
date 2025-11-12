// login.js - SweetAlert2 version

$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Login form submission
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Get form data
        const email = $('#email').val();
        const password = $('#password').val();

        // Disable button and show loading
        const $btn = $('#signInBtn');
        const $btnText = $btn.find('.btn-text');
        const $spinner = $btn.find('.spinner-border');

        $btn.prop('disabled', true);
        $btnText.addClass('d-none');
        $spinner.removeClass('d-none');

        // AJAX request
        $.ajax({
            url: '/login',
            method: 'POST',
            data: { email, password },
            success: function(response) {
                if (response.success) {
                    // SweetAlert2 success (optional)
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful',
                        showConfirmButton: false,
                        timer: 1000
                    }).then(() => {
                        window.location.href = response.redirect || '/dashboard';
                    });
                } else {
                    showError('An error occurred. Please try again.');
                    resetButton();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;

                    if (errors.email) {
                        $('#email').addClass('is-invalid');
                        $('#email').next('.invalid-feedback').text(errors.email[0]);
                    }

                    if (errors.password) {
                        $('#password').addClass('is-invalid');
                        $('#password').next('.invalid-feedback').text(errors.password[0]);
                    }
                } else if (xhr.status === 401) {
                    showError('Invalid email or password. Please try again.');
                } else {
                    showError('An error occurred. Please try again later.');
                }

                resetButton();
            }
        });
    });

    // Function to show SweetAlert2 error
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    // Function to reset button state
    function resetButton() {
        const $btn = $('#signInBtn');
        const $btnText = $btn.find('.btn-text');
        const $spinner = $btn.find('.spinner-border');

        $btn.prop('disabled', false);
        $btnText.removeClass('d-none');
        $spinner.addClass('d-none');
    }

    // Clear error on input
    $('.form-control').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').text('');
    });

    // Forgot password
    $('.forgot-password').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            icon: 'info',
            title: 'Forgot Password',
            text: 'Password reset functionality coming soon!'
        });
    });
});
