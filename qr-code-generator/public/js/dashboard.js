// dashboard.js - SweetAlert2 version

$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Toggle sidebar on mobile
    $('#menuBtn').on('click', function() {
        $('#sidebar').addClass('show');
    });

    $('#closeSidebar').on('click', function() {
        $('#sidebar').removeClass('show');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if (window.innerWidth <= 991) {
            if (!$(e.target).closest('.sidebar, #menuBtn').length) {
                $('#sidebar').removeClass('show');
            }
        }
    });

    // Search functionality
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        searchTimeout = setTimeout(function() {
            loadQRCodes(query);
        }, 500);
    });

    // Create QR Code button
    $('#createQRBtn').on('click', function() {
        $('#createQRModal').modal('show');
    });

    // Save QR Code
    $('#saveQRBtn').on('click', function() {
        const formData = {
            event_title: $('#eventTitle').val(),
            venue: $('#venue').val(),
            event_date: $('#eventDate').val(),
            event_time: $('#eventTime').val(),
            department: $('#department').val(),
            description: $('#description').val()
        };

        createQRCode(formData);
    });

    // Profile button
    $('#profileBtn').on('click', function(e) {
        e.preventDefault();
        Swal.fire('Profile page coming soon!');
    });

    // Logout button
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/logout',
                    method: 'POST',
                    success: function() {
                        window.location.href = '/login';
                    },
                    error: function() {
                        showError('Logout failed. Please try again.');
                    }
                });
            }
        });
    });

    // Function to load QR codes
    function loadQRCodes(search = '') {
        showLoading();
        $.ajax({
            url: '/api/qr-codes',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                hideLoading();
                renderQRCodes(response.data || []);
            },
            error: function() {
                hideLoading();
                showError('Failed to load QR codes');
            }
        });
    }

    // Function to render QR codes in table
    function renderQRCodes(qrCodes) {
        const $tbody = $('#qrTableBody');
        $tbody.empty();

        if (qrCodes.length === 0) {
            $tbody.html(`
                <tr class="empty-state">
                    <td colspan="6">
                        <div class="empty-message">No QR codes created yet</div>
                    </td>
                </tr>
            `);
            return;
        }

        qrCodes.forEach(function(qr) {
            const row = `
                <tr>
                    <td>
                        <div style="font-weight: 600; margin-bottom: 4px;">${qr.event_title}</div>
                        <div style="font-size: 13px; color: #666;">${qr.description || 'No description'}</div>
                    </td>
                    <td>
                        <div>${formatDate(qr.event_date)}</div>
                        <div style="font-size: 13px; color: #666;">${formatTime(qr.event_time)}</div>
                    </td>
                    <td>${qr.venue}</td>
                    <td>
                        <span class="badge bg-primary">${qr.department}</span>
                    </td>
                    <td>${qr.created_by}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary view-qr" data-id="${qr.id}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary edit-qr" data-id="${qr.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-qr" data-id="${qr.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });

        attachActionHandlers();
    }

    // Function to create QR code
    function createQRCode(data) {
        const $btn = $('#saveQRBtn');
        $btn.prop('disabled', true).text('Creating...');

        $.ajax({
            url: '/api/qr-codes',
            method: 'POST',
            data: data,
            success: function(response) {
                $('#createQRModal').modal('hide');
                $('#createQRForm')[0].reset();
                loadQRCodes();
                showSuccess('QR Code created successfully!');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul style="text-align:left;">';
                    for (let field in errors) errorHtml += `<li>${errors[field][0]}</li>`;
                    errorHtml += '</ul>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorHtml
                    });
                } else {
                    showError('Failed to create QR code. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Create QR Code');
            }
        });
    }

    // Attach action handlers
    function attachActionHandlers() {
        $('.view-qr').on('click', function() {
            const id = $(this).data('id');
            Swal.fire('View QR Code #' + id);
            // Implement view functionality
        });

        $('.edit-qr').on('click', function() {
            const id = $(this).data('id');
            Swal.fire('Edit QR Code #' + id);
            // Implement edit functionality
        });

        $('.delete-qr').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the QR code.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteQRCode(id);
                }
            });
        });
    }

    // Delete QR code
    function deleteQRCode(id) {
        $.ajax({
            url: '/api/qr-codes/' + id,
            method: 'DELETE',
            success: function() {
                loadQRCodes();
                showSuccess('QR Code deleted successfully!');
            },
            error: function() {
                showError('Failed to delete QR code. Please try again.');
            }
        });
    }

    // Utility functions
    function showLoading() {
        $('#loadingOverlay').removeClass('d-none');
    }

    function hideLoading() {
        $('#loadingOverlay').addClass('d-none');
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(hours, minutes);
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
});
