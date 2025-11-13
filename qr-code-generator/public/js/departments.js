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

    // Profile button
    $('#profileBtn').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            icon: 'info',
            title: 'Profile',
            text: 'Profile page coming soon!',
            confirmButtonColor: '#0A3A6B'
        });
    });

    // Logout button
    $('#logoutBtn').on('click', function(e) {
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

    // Load departments on page load
    loadDepartments();

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

        // Validation
        if (!data.name || !data.name.trim()) {
            showError('Please enter a department name');
            return;
        }

        const url = data.id ? `/api/departments/${data.id}` : '/api/departments';
        const method = data.id ? 'PUT' : 'POST';
        const $btn = $('#saveDepartmentBtn');
        const btnText = data.id ? 'Updating...' : 'Creating...';

        $btn.prop('disabled', true).text(btnText);

        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function() {
                $('#departmentModal').modal('hide');
                loadDepartments();
                showSuccess(data.id ? 'Department updated successfully!' : 'Department created successfully!');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul style="text-align:left; margin:0; padding-left:20px;">';
                    for (let field in errors) {
                        errorHtml += `<li>${errors[field][0]}</li>`;
                    }
                    errorHtml += '</ul>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorHtml,
                        confirmButtonColor: '#0A3A6B'
                    });
                } else {
                    showError('Failed to save department. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Department');
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

    // Load Departments Function
    function loadDepartments(search = '') {
        showLoading();
        $.ajax({
            url: '/api/departments',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                hideLoading();
                if (response.success && response.data) {
                    renderDepartments(response.data);
                } else {
                    renderDepartments([]);
                }
            },
            error: function(xhr) {
                hideLoading();
                if (xhr.status === 404) {
                    showError('Departments API endpoint not found. Check your routes.');
                } else if (xhr.status === 500) {
                    showError('Server error. Check Laravel logs.');
                } else {
                    showError('Failed to load departments.');
                }
            }
        });
    }

    // Render Departments in Table
    function renderDepartments(departments) {
        const $tbody = $('#departmentTableBody');
        $tbody.empty();

        if (departments.length === 0) {
            $tbody.html(`
                <tr class="empty-state">
                    <td colspan="4">
                        <div class="empty-message">No departments found</div>
                    </td>
                </tr>
            `);
            $('#showingDepartmentText').text('Showing 0 of 0 departments');
            return;
        }

        departments.forEach(dept => {
            const row = `
                <tr>
                    <td>
                        <div style="font-weight: 600; color: #1a1a1a;">${dept.name}</div>
                    </td>
                    <td>${dept.head || '—'}</td>
                    <td>
                        <span class="badge bg-primary">${dept.staff_count || 0}</span>
                    </td>
                    <td>
                        <button class="action-btn edit-dept" data-id="${dept.id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="action-btn delete delete-dept" data-id="${dept.id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
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
        // Edit Department
        $('.edit-dept').on('click', function() {
            const id = $(this).data('id');

            $.ajax({
                url: `/api/departments/${id}`,
                method: 'GET',
                success: function(response) {
                    const dept = response.data || response;
                    $('#departmentId').val(dept.id);
                    $('#departmentName').val(dept.name);
                    $('#departmentHead').val(dept.head || '');
                    $('#departmentModalTitle').text('Edit Department');
                    $('#departmentModal').modal('show');
                },
                error: function() {
                    showError('Failed to load department details.');
                }
            });
        });

        // Delete Department
        $('.delete-dept').on('click', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the department and unassign all staff members!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/api/departments/${id}`,
                        method: 'DELETE',
                        success: function() {
                            loadDepartments();
                            showSuccess('Department deleted successfully!');
                        },
                        error: function(xhr) {
                            if (xhr.status === 403) {
                                showError('Cannot delete department with assigned staff members.');
                            } else {
                                showError('Failed to delete department.');
                            }
                        }
                    });
                }
            });
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
            title: 'Success!',
            text: message,
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: message,
            confirmButtonColor: '#0A3A6B'
        });
    }
});
