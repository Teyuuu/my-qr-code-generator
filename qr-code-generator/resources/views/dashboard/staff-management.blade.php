<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Management - QR Generator</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    <link href="{{ asset('css/staff-management.css') }}" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="btn btn-close-sidebar" id="closeSidebar">
                <i class="bi bi-x"></i>
            </button>
            <div class="logo-section">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="8" height="8" rx="1" fill="#FDB813"/>
                    <rect x="13" y="3" width="8" height="8" rx="1" fill="#FDB813"/>
                    <rect x="3" y="13" width="8" height="8" rx="1" fill="#FDB813"/>
                    <rect x="5" y="5" width="4" height="4" fill="#0A3A6B"/>
                    <rect x="15" y="5" width="4" height="4" fill="#0A3A6B"/>
                    <rect x="5" y="15" width="4" height="4" fill="#0A3A6B"/>
                </svg>
                <div class="logo-text">
                    <div class="logo-title">QR Generator</div>
                    <div class="logo-subtitle">Admin Panel</div>
                </div>
            </div>
        </div>

        <div class="user-info">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-details">
                <div class="user-name">Admin User</div>
                <div class="user-email">admin@company.com</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item">
                <i class="bi bi-qr-code"></i>
                <span>QR Code Records</span>
            </a>
            <a href="{{ route('staff.management') }}" class="nav-item active">
                <i class="bi bi-people"></i>
                <span>Staff Management</span>
            </a>
            <a href="{{ route('departments') }}" class="nav-item">
                <i class="bi bi-building"></i>
                <span>Departments</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="#" class="nav-item" id="profileBtn">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
            <a href="#" class="nav-item" id="logoutBtn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="top-bar">
            <button class="btn btn-menu" id="menuBtn">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">Staff Management</h1>
            <button class="btn btn-create" id="createStaffBtn">
                <i class="bi bi-person-plus"></i>
                <span>Create New User</span>
            </button>
        </div>

        <div class="content-wrapper">
            <p class="page-subtitle">Create and manage user accounts</p>

            <div class="search-section">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, role, or department...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table staff-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="staffTableBody">
                        <tr class="empty-state">
                            <td colspan="5">
                                <div class="empty-message">Loading users...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p class="showing-text" id="showingText">Showing 0 of 0 users</p>
            </div>

            <div class="loading-overlay d-none" id="loadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Staff Modal -->
    <div class="modal fade" id="staffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffModalTitle">Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="staffForm">
                        @csrf
                        <input type="hidden" id="staffId" name="id">

                        <div class="mb-3">
                            <label for="staffName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="staffName" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="staffEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="staffEmail" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="staffPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="staffPassword" name="password">
                            <small class="text-muted" id="passwordHint">Leave blank to keep current password</small>
                        </div>

                        <div class="mb-3">
                            <label for="staffRole" class="form-label">Role</label>
                            <select class="form-select" id="staffRole" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>

                        <div class="mb-3" id="departmentField">
                            <label for="staffDepartment" class="form-label">Department</label>
                            <select class="form-select" id="staffDepartment" name="department">
                                <option value="">Select Department</option>
                                <option value="IT Department">IT Department</option>
                                <option value="HR Department">HR Department</option>
                                <option value="Finance Department">Finance Department</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveStaffBtn">Save User</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
    <script src="{{ asset('js/staff-management.js') }}"></script>
</body>
</html>
