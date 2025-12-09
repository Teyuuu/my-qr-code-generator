// departments.js - FINAL PROFESSIONAL VERSION
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Sidebar toggle
    $('#menuBtn, #closeSidebar').click(() => $('#sidebar').toggleClass('show'));
    $(document).click(e => {
        if (window.innerWidth <= 991 && !$(e.target).closest('.sidebar, #menuBtn').length) {
            $('#sidebar').removeClass('show');
        }
    });

    // DataTable
    const table = $('#departmentTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '/api/departments/datatables',
        columns: [
            {
                data: 'name',
                render: data => `<strong>${data}</strong>`
            },
            {
                data: 'head',
                render: data => data ? `<span class="text-primary">${data}</span>` : '<span class="text-muted">—</span>'
            },
            {
                data: 'staff_count',
                render: data => `<span class="badge bg-success fs-6">${data}</span>`
            },
            {
                data: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: data => data
            }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        language: {
            processing: '<div class="spinner-border text-primary"></div>',
            emptyTable: '<div class="text-center py-5"><i class="bi bi-building fs-1 text-muted"></i><p class="mt-3 text-muted">No departments yet</p></div>',
            zeroRecords: '<div class="text-center py-4 text-muted">No matching departments found</div>',
            info: "Showing _START_ to _END_ of _TOTAL_ departments",
            lengthMenu: "Show _MENU_ entries"
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });

    // Search
    $('#searchDepartmentInput').on('keyup', function () {
        table.search(this.value).draw();
    });

    // Create New
    $('#createDepartmentBtn').click(function () {
        $('#departmentForm')[0].reset();
        $('#departmentId').val('');
        $('#departmentModalTitle').text('Create New Department');
        $('#departmentModal').modal('show');
    });

    // Save
    $('#saveDepartmentBtn').click(function () {
        const id = $('#departmentId').val();
        const data = {
            name: $('#departmentName').val().trim(),
            head: $('#departmentHead').val().trim() || null
        };

        if (!data.name) {
            Swal.fire('Error', 'Department name is required', 'error');
            return;
        }

        const url = id ? `/api/departments/${id}` : '/api/departments';
        const method = id ? 'PUT' : 'POST';

        $(this).prop('disabled', true).text('Saving...');

        $.ajax({
            url, method, data,
            success: function () {
                $('#departmentModal').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire({
                    icon: 'success',
                    title: id ? 'Updated!' : 'Created!',
                    text: 'Department saved successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.errors
                    ? Object.values(xhr.responseJSON.errors).flat().join('<br>')
                    : xhr.responseJSON?.message || 'Something went wrong';
                Swal.fire('Error', msg, 'error');
            },
            complete: () => $(this).prop('disabled', false).text('Save Department')
        });
    });

    // Edit
    $('#departmentTable tbody').on('click', '.edit-dept', function () {
        const id = $(this).data('id');
        $.get(`/api/departments/${id}`).done(res => {
            const d = res.data;
            $('#departmentId').val(d.id);
            $('#departmentName').val(d.name);
            $('#departmentHead').val(d.head || '');
            $('#departmentModalTitle').text('Edit Department');
            $('#departmentModal').modal('show');
        });
    });

    // Delete
    $('#departmentTable tbody').on('click', '.delete-dept', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Department?',
            text: "This cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/departments/${id}`,
                    method: 'DELETE',
                    success: () => {
                        table.ajax.reload(null, false);
                        Swal.fire('Deleted!', 'Department removed', 'success');
                    },
                    error: (xhr) => Swal.fire('Error', xhr.responseJSON?.message || 'Cannot delete', 'error')
                });
            }
        });
    });
});
