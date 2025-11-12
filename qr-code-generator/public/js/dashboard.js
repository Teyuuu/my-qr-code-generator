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

    // ---------- DataTable ----------
    const qrTable = $('#qrTable').DataTable({
        processing: true,
        serverSide: false, // client-side works fine for moderate rows
        ajax: {
            url: '/api/qr-codes',
            type: 'GET',
            dataSrc: function (json) { return json.data || []; }
        },
        columns: [
            {
                data: 'event_title',
                render: (data, type, row) => `
                    <div style="font-weight:600;">${data}</div>
                    <div style="font-size:13px;color:#666;">${row.description || 'No description'}</div>`
            },
            {
                data: 'event_date',
                render: (data, type, row) => `
                    ${formatDate(data)}<br>
                    <span style="font-size:13px;color:#666;">${formatTime(row.event_time)}</span>`
            },
            { data: 'venue' },
            {
                data: 'department',
                render: (data) => `<span class="badge bg-primary">${data}</span>`
            },
            { data: 'created_by' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: (id) => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary view-qr" data-id="${id}"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-outline-secondary edit-qr" data-id="${id}"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger delete-qr" data-id="${id}"><i class="bi bi-trash"></i></button>
                    </div>`
            }
        ],
        order: [[1, 'desc']],
        pageLength: 10
    });

    const reloadTable = () => qrTable.ajax.reload(null, false);

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
            description: $('#description').val()
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

