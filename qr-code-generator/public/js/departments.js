// departments.js - Server-side DataTables version
$(document).ready(function() {
    // ---------- Setup CSRF ----------
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ---------- Sidebar toggle ----------
    $('#menuBtn').click(() => $('#sidebar').addClass('show'));
    $('#closeSidebar').click(() => $('#sidebar').removeClass('show'));
    $(document).click(function(e) {
        if (window.innerWidth <= 991 && !$(e.target).closest('.sidebar, #menuBtn').length) {
            $('#sidebar').removeClass('show');
        }
    });

    // ---------- Logout ----------
    $('#logoutBtn').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Logout',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0A3A6B',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/logout', () => window.location.href = '/login')
                    .fail(() => showError('Logout failed. Please try again.'));
            }
        });
    });

    // ---------- Initialize DataTable ----------
    const departmentTable = $('#departmentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/departments/datatables',
            type: 'GET'
        },
        columns: [
            { data: 'name' },
            { data: 'head' },
            { data: 'staff_count' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 10
    });

    // ---------- Search ----------
    $('#searchDepartmentInput').on('keyup', function() {
        departmentTable.search(this.value).draw();
    });

    // ---------- Open Create Department modal ----------
    $('#createDepartmentBtn').click(function() {
        $('#departmentForm')[0].reset();
        $('#departmentId').val('');
        $('#departmentModalTitle').text('Create Department');
        $('#departmentModal').modal('show');
    });

    // ---------- Save Department (Create/Update) ----------
    $('#saveDepartmentBtn').click(function() {
        const id = $('#departmentId').val();
        const data = {
            name: $('#departmentName').val(),
            head: $('#departmentHead').val()
        };

        if (!data.name || !data.name.trim()) {
            showError('Please enter a department name');
            return;
        }

        const url = id ? `/api/departments/${id}` : '/api/departments';
        const method = id ? 'PUT' : 'POST';
        const $btn = $(this);
        $btn.prop('disabled', true).text(id ? 'Updating...' : 'Creating...');

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(res) {
                $('#departmentModal').modal('hide');
                departmentTable.ajax.reload(null, false); // reload table
                Swal.fire({
                    icon: 'success',
                    title: id ? 'Department updated!' : 'Department created!',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let html = '<ul style="text-align:left;">';
                    for (let field in errors) html += `<li>${errors[field][0]}</li>`;
                    html += '</ul>';
                    Swal.fire({ icon: 'error', title: 'Validation Error', html: html });
                } else {
                    showError('Failed to save department.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Department');
            }
        });
    });

    // ---------- Edit Department ----------
    $('#departmentTable tbody').on('click', '.edit-dept', function() {
        const id = $(this).data('id');
        $.get(`/api/departments/${id}`, function(res) {
            const dept = res.data;
            $('#departmentId').val(dept.id);
            $('#departmentName').val(dept.name);
            $('#departmentHead').val(dept.head || '');
            $('#departmentModalTitle').text('Edit Department');
            $('#departmentModal').modal('show');
        }).fail(() => showError('Failed to load department details.'));
    });

    // ---------- Delete Department ----------
    $('#departmentTable tbody').on('click', '.delete-dept', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will permanently delete the department!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/departments/${id}`,
                    method: 'DELETE',
                    success: function(res) {
                        departmentTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message || 'Deleted!', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                    },
                    error: function(xhr) {
                        showError(xhr.responseJSON?.message || 'Failed to delete department.');
                    }
                });
            }
        });
    });

    // ---------- Utility functions ----------
    function showError(msg) {
        Swal.fire({ icon: 'error', title: 'Oops...', text: msg, confirmButtonColor: '#0A3A6B' });
    }
});
