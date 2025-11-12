// staff-management.js - SweetAlert2 version

$(document).ready(function() {
    let editMode = false;
    let currentStaffId = null;

    // Search functionality
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        searchTimeout = setTimeout(function() {
            loadStaff(query);
        }, 500);
    });

    // Create Staff button
    $('#createStaffBtn').on('click', function() {
        editMode = false;
        currentStaffId = null;
        resetForm();
        $('#staffModalTitle').text('Create New User');
        $('#passwordHint').removeClass('show');
        $('#staffPassword').attr('required', true);
        $('#staffModal').modal('show');
    });

    // Role change - show/hide department field
    $('#staffRole').on('change', function() {
        const role = $(this).val();
        if (role === 'admin') {
            $('#departmentField').addClass('d-none');
            $('#staffDepartment').removeAttr('required');
        } else {
            $('#departmentField').removeClass('d-none');
            $('#staffDepartment').attr('required', true);
        }
    });

    // Save Staff button
    $('#saveStaffBtn').on('click', function() {
        if (editMode) {
            updateStaff();
        } else {
            createStaff();
        }
    });

    // Function to load staff
    function loadStaff(search = '') {
        showLoading();
        $.ajax({
            url: '/api/staff',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                hideLoading();
                renderStaff(response.data || []);
                updateShowingText(response.data.length);
            },
            error: function() {
                hideLoading();
                showError('Failed to load staff members');
            }
        });
    }

    // Function to render staff in table
    function renderStaff(staff) {
        const $tbody = $('#staffTableBody');
        $tbody.empty();

        if (staff.length === 0) {
            $tbody.html(`
                <tr class="empty-state">
                    <td colspan="5">
                        <div class="empty-message">No staff members found</div>
                    </td>
                </tr>
            `);
            return;
        }

        staff.forEach(function(user) {
            const initials = getInitials(user.name);
            const isCurrentUser = user.email === 'admin@company.com';
            const roleClass = user.role === 'admin' ? 'admin' : 'staff';
            const roleIcon = user.role === 'admin' ? 'bi-shield-check' : 'bi-person';
            const department = user.department || '—';

            const row = `
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-cell">${initials}</div>
                            <div class="user-info-cell">
                                <div class="user-name-cell">${user.name}</div>
                                ${isCurrentUser ? '<span class="user-label">You</span>' : ''}
                            </div>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td>
                        <span class="role-badge ${roleClass}">
                            <i class="bi ${roleIcon}"></i>
                            ${capitalizeFirst(user.role)}
                        </span>
                    </td>
                    <td>
                        <span class="department-badge ${!user.department ? 'empty' : ''}">${department}</span>
                    </td>
                    <td>
                        <button class="action-btn edit-staff" data-id="${user.id}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="action-btn delete delete-staff" data-id="${user.id}" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $tbody.append(row);
        });

        attachActionHandlers();
    }

    // Function to create staff
    function createStaff() {
        const formData = {
            name: $('#staffName').val(),
            email: $('#staffEmail').val(),
            password: $('#staffPassword').val(),
            role: $('#staffRole').val(),
            department: $('#staffRole').val() === 'admin' ? null : $('#staffDepartment').val()
        };

        // Validation
        if (!formData.name || !formData.email || !formData.password || !formData.role) {
            showError('Please fill in all required fields');
            return;
        }
        if (formData.role === 'staff' && !formData.department) {
            showError('Please select a department for staff members');
            return;
        }

        const $btn = $('#saveStaffBtn');
        $btn.prop('disabled', true).text('Creating...');

        $.ajax({
            url: '/api/staff',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#staffModal').modal('hide');
                loadStaff();
                showSuccess('User created successfully!');
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
                    showError('Failed to create user. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save User');
            }
        });
    }

    // Function to update staff
    function updateStaff() {
        const formData = {
            name: $('#staffName').val(),
            email: $('#staffEmail').val(),
            role: $('#staffRole').val(),
            department: $('#staffRole').val() === 'admin' ? null : $('#staffDepartment').val()
        };
        const password = $('#staffPassword').val();
        if (password) formData.password = password;

        const $btn = $('#saveStaffBtn');
        $btn.prop('disabled', true).text('Updating...');

        $.ajax({
            url: '/api/staff/' + currentStaffId,
            method: 'PUT',
            data: formData,
            success: function(response) {
                $('#staffModal').modal('hide');
                loadStaff();
                showSuccess('User updated successfully!');
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
                    showError('Failed to update user. Please try again.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save User');
            }
        });
    }

    // Attach action handlers
    function attachActionHandlers() {
        $('.edit-staff').on('click', function() {
            const id = $(this).data('id');
            editStaff(id);
        });

        $('.delete-staff').on('click', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the user.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteStaff(id);
                }
            });
        });
    }

    // Edit staff
    function editStaff(id) {
        $.ajax({
            url: '/api/staff/' + id,
            method: 'GET',
            success: function(response) {
                const user = response.data;

                editMode = true;
                currentStaffId = id;

                $('#staffModalTitle').text('Edit User');
                $('#staffId').val(user.id);
                $('#staffName').val(user.name);
                $('#staffEmail').val(user.email);
                $('#staffPassword').val('').removeAttr('required');
                $('#staffRole').val(user.role).trigger('change');
                $('#staffDepartment').val(user.department || '');

                $('#passwordHint').addClass('show');

                $('#staffModal').modal('show');
            },
            error: function() {
                showError('Failed to load user details');
            }
        });
    }

    // Delete staff
    function deleteStaff(id) {
        $.ajax({
            url: '/api/staff/' + id,
            method: 'DELETE',
            success: function() {
                loadStaff();
                showSuccess('User deleted successfully!');
            },
            error: function() {
                showError('Failed to delete user. Please try again.');
            }
        });
    }

    // Reset form
    function resetForm() {
        $('#staffForm')[0].reset();
        $('#staffId').val('');
        $('#departmentField').removeClass('d-none');
        $('#staffDepartment').attr('required', true);
    }

    // Update showing text
    function updateShowingText(count) {
        $('#showingText').text(`Showing ${count} of ${count} users`);
    }

    // Utility functions
    function getInitials(name) {
        const parts = name.split(' ');
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return name.substring(0, 2).toUpperCase();
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

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
});
