// dashboard.js - DataTables + SweetAlert2 version
$(document).ready(function () {
    // ---------- Setup ----------
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ---------- Sidebar toggle ----------
    $('#menuBtn').click(() => $('#sidebar').addClass('show'));
    $('#closeSidebar').click(() => $('#sidebar').removeClass('show'));
    $(document).click(function (e) {
        if (window.innerWidth <= 991 && !$(e.target).closest('.sidebar, #menuBtn').length) {
            $('#sidebar').removeClass('show');
        }
    });

    // ---------- ULTRA RESPONSIVE DATATABLE ----------
const qrTable = $('#qrTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: "/api/qr-codes/datatables",
        type: 'GET'
    },
    responsive: true,  // THIS MAKES IT MOBILE PERFECT
    autoWidth: false,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[1, 'desc']], // Sort by date
    language: {
        processing: "Loading QR Codes...",
        emptyTable: "No QR codes created yet",
        zeroRecords: "No matching QR codes found",
        info: "Showing _START_ to _END_ of _TOTAL_ QR codes",
        infoEmpty: "",
        lengthMenu: "Show _MENU_ entries",
        paginate: {
            first: "First",
            last: "Last",
            next: '<i class="bi bi-chevron-right"></i>',
            previous: '<i class="bi bi-chevron-left"></i>'
        }
    },
    columns: [
        {
            data: 'event_title',
            title: 'Event',
            render: (data) => data // Already rich HTML
        },
        {
            data: 'event_date',
            title: 'Date & Time',
            render: (data) => data || '—'
        },
        {
            data: 'venue',
            title: 'Type',
            render: (data) => data
        },
        {
            data: 'department',
            title: 'Department',
            render: (data) => data
        },
        {
            data: 'created_by',
            title: 'Created By'
        },
        {
            data: 'action',
            title: 'Actions',
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: (data) => data
        }
    ],
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
    drawCallback: function () {
        // Re-init tooltips if you use any
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
});

// Make it even more mobile-friendly
$(window).on('resize', function () {
    qrTable.responsive.recalc();
});

    // ---------- Search ----------
    $('#searchInput').on('keyup', function () {
        qrTable.search(this.value).draw();
    });

    // ---------- Create QR ----------
    $('#createQRBtn').click(() => $('#createQRModal').modal('show'));

    $('#saveQRBtn').click(function () {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Creating...');

        const formData = {
            event_title: $('#eventTitle').val(),
            venue: $('#venue').val(),
            event_date: $('#eventDate').val(),
            event_time: $('#eventTime').val(),
            department: $('#department').val(),
            description: $('#description').val(),
            link_type: $('input[name="link_type"]:checked').val(),
            external_link: $('#externalLink').val().trim()
        };

        $.post('/api/qr-codes', formData)
            .done(res => {
                if (res.success) {
                    $('#createQRModal').modal('hide');
                    $('#createQRForm')[0].reset();
                    reloadTable();
                    Swal.fire({ icon: 'success', title: 'QR Code created!', timer: 1500, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong' });
                }
            })
            .fail(xhr => {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul style="text-align:left;">';
                    for (let f in errors) errorHtml += `<li>${errors[f][0]}</li>`;
                    errorHtml += '</ul>';
                    Swal.fire({ icon: 'error', title: 'Validation Error', html: errorHtml });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to create QR code.' });
                }
            })
            .always(() => $btn.prop('disabled', false).text('Create QR Code'));
    });
    // Toggle between external and internal link options
    $('input[name="link_type"]').on('change', function () {
        if ($(this).val() === 'internal') {
            $('#externalLinkGroup').addClass('d-none');
            $('#externalLink').prop('required', false);
            $('#internalLinkPreview').removeClass('d-none');
            // Simulate a short code preview
            const randomCode = Math.random().toString(36).substring(2, 8).toUpperCase();
            $('#generatedLinkPreview').text(`${window.location.origin}/s/${randomCode}`);
        } else {
            $('#externalLinkGroup').removeClass('d-none');
            $('#externalLink').prop('required', true);
            $('#internalLinkPreview').addClass('d-none');
        }
    });

    // ---------- Dynamic action buttons ----------
    $('#qrTable tbody').on('click', '.view-qr', function () {
        const id = $(this).data('id');
        Swal.fire('View QR Code #' + id);
    });

    $('#qrTable tbody').on('click', '.edit-qr', function () {
        const id = $(this).data('id');
        Swal.fire('Edit QR Code #' + id);
    });

    $('#qrTable tbody').on('click', '.delete-qr', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the QR code.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => { if (result.isConfirmed) deleteQRCode(id); });
    });

    function deleteQRCode(id) {
        $.ajax({
            url: '/api/qr-codes/' + id,
            method: 'DELETE',
            success: () => {
                reloadTable();
                Swal.fire({ icon: 'success', title: 'Deleted!', text: 'QR Code deleted successfully.', timer: 1500, showConfirmButton: false });
            },
            error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete QR code.' })
        });
    }

    // ---------- New Action Buttons ----------
    $('#qrTable tbody').on('click', '.copy-link', function () {
        const link = $(this).data('link');
        navigator.clipboard.writeText(link).then(() => {
            Swal.fire({ icon: 'success', title: 'Copied!', text: 'Link copied to clipboard', timer: 1500, showConfirmButton: false });
        });
    });

    $('#qrTable tbody').on('click', '.download-qr', function () {
        const code = $(this).data('code');
        const url = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(window.location.origin + '/s/' + code)}`;
        const a = document.createElement('a');
        a.href = url;
        a.download = `QR_${code}.png`;
        a.click();
    });

    $('#qrTable tbody').on('click', '.delete-qr', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete QR Code?',
            text: "This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                deleteQRCode(id);
            }
        });
    });

    // QR CODE PREVIEW MODAL
$('#qrTable tbody').on('click', '.preview-qr', function () {
    const id = $(this).data('id');
    const title = $(this).data('title');
    const venue = $(this).data('venue');
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(window.location.origin + '/s/' + $(this).closest('tr').find('td').first().text().trim())}`;

    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="qrPreviewModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="bi bi-qr-code-scan me-2"></i>QR Code Preview
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center py-5">
                        <div class="mb-4">
                            <h4 class="fw-bold text-primary">${title || 'Event'}</h4>
                            ${venue ? `<p class="text-muted mb-0">${venue}</p>` : ''}
                        </div>
                        <img src="${qrUrl}" alt="QR Code" class="img-fluid rounded shadow" style="max-width: 300px;">
                        <div class="mt-4">
                            <p class="text-success fw-bold">
                                <i class="bi bi-phone-vibrate"></i> Scan with your phone to test!
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove old modal if exists
    $('#qrPreviewModal').remove();
    $('body').append(modalHtml);

    // Show modal
    const modal = new bootstrap.Modal('#qrPreviewModal');
    modal.show();

    // Auto-remove after close
    $('#qrPreviewModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
});

    // ---------- Logout ----------
    $('#logoutBtn').click(e => {
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/logout', () => window.location.href = '/login')
                    .fail(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Logout failed. Please try again.' }));
            }
        });
    });

    // ---------- Utility ----------
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        const [hours, minutes] = timeString.split(':');
        const date = new Date();
        date.setHours(hours, minutes);
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
});

$('#profileBtn').on('click', function(e){
    e.preventDefault();
    const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
    profileModal.show();
});

