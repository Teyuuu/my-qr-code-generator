// departments.js - Place in public/js/departments.js
$(document).ready(function() {
    // Setup CSRF token for AJAX
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Sidebar toggle
    $('#menuBtn').on('click', () => $('#sidebar').addClass('show'));
    $('#closeSidebar').on('click', () => $('#sidebar').removeClass('show'));
    $(document).on('click', function(e) {
        if (window.innerWidth <= 991 && !$(e.target).closest('.sidebar, #menuBtn').length) {
            $('#sidebar').removeClass('show');
        }
    });

    // Open Create Department modal
    $('#createDepartmentBtn').on('click', function() {
        $('#departmentForm')[0].reset();
        $('#departmentId').val('');
        $('#departmentModalTitle').text('Create New Department');
        $('#departmentModal').modal('show');
    });

// Save Department
$('#saveDepartmentBtn').on('click', function() {
    const data = {
        id: $('#departmentId').val(),
        name: $('#departmentName').val(),
        head: $('#departmentHead').val()
    };

    const url = data.id ? `/api/departments/${data.id}` : '/api/departments';
    const method = data.id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: data,
        success: function() {
            $('#departmentModal').modal('hide');
            loadDepartments();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Department saved successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorHtml = '<ul style="text-align:left;">';
                for (let field in errors) {
                    errorHtml += `<li>${errors[field][0]}</li>`;
                }
                errorHtml += '</ul>';
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorHtml
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save department. Try again.'
                });
            }
        }
    });
});


    // Search departments
    let searchTimeout;
    $('#searchDepartmentInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        searchTimeout = setTimeout(() => loadDepartments(query), 300);
    });

    // Load departments on page load


    // Load Departments Function
    function loadDepartments(search='') {
        showLoading();
        $.ajax({
            url: '/api/departments',
            method: 'GET',
            data: { search: search },
            success: function(response){
                hideLoading();
                renderDepartments(response.data || []);
            },
            error: function() {
                hideLoading();
                showError('Failed to load departments.');
            }
        });
    }

    // Render Departments in Table
    function renderDepartments(departments){
        const $tbody = $('#departmentTableBody');
        $tbody.empty();

        if(departments.length === 0){
            $tbody.html(`
                <tr class="empty-state">
                    <td colspan="4"><div class="empty-message">No departments found</div></td>
                </tr>
            `);
            $('#showingDepartmentText').text('Showing 0 of 0 departments');
            return;
        }

        departments.forEach(dept => {
            const row = `
                <tr>
                    <td>${dept.name}</td>
                    <td>${dept.head || '-'}</td>
                    <td>${dept.staff_count || 0}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary edit-dept" data-id="${dept.id}"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-outline-danger delete-dept" data-id="${dept.id}"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });

        $('#showingDepartmentText').text(`Showing ${departments.length} of ${departments.length} departments`);

        attachActionHandlers();
    }

    // Attach Edit/Delete handlers
    function attachActionHandlers() {
        $('.edit-dept').on('click', function() {
            const id = $(this).data('id');
            $.get(`/api/departments/${id}`, function(dept){
                $('#departmentId').val(dept.id);
                $('#departmentName').val(dept.name);
                $('#departmentHead').val(dept.head);
                $('#departmentModalTitle').text('Edit Department');
                $('#departmentModal').modal('show');
            });
        });

        // Replace delete confirmation
        $('.delete-dept').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the department.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/api/departments/${id}`,
                        method: 'DELETE',
                        success: function() {
                            loadDepartments();
                            showSuccess('Department deleted successfully!');
                        },
                        error: function() {
                            showError('Failed to delete department.');
                        }
                    });
                }
            });
        });
    }

    // Utility
    function showLoading(){ $('#loadingOverlay').removeClass('d-none'); }
    function hideLoading(){ $('#loadingOverlay').addClass('d-none'); }

    // Utility functions using SweetAlert2
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
            text: message,
        });
    }

});
