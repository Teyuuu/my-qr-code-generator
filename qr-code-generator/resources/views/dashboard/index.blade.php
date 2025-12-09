<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Generator - Admin Panel</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
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
            <a href="#" class="nav-item active" data-page="qr-codes">
                <i class="bi bi-qr-code"></i>
                <span>QR Code Records</span>
            </a>
            <a href="{{ route('staff.management') }}" class="nav-item" data-page="users">
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
            <h1 class="page-title">All QR Code Records</h1>
            <button class="btn btn-create" id="createQRBtn">
                <i class="bi bi-plus-lg"></i>
                <span>Create New QR Code</span>
            </button>
        </div>

        <div class="content-wrapper">
            <p class="page-subtitle">View and manage all QR codes from all departments</p>

            <div class="search-section">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by title, venue, department, or creator...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="qrTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Department</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="qrTableBody">
                        <tr class="empty-state">
                            <td colspan="6">
                                <div class="empty-message">No QR codes created yet</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="loading-overlay d-none" id="loadingOverlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create QR Code Modal -->
    <div class="modal fade" id="createQRModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createQRForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="eventTitle" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="eventTitle" name="event_title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" id="venue" name="venue" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventDate" class="form-label">Event Date</label>
                                <input type="date" class="form-control" id="eventDate" name="event_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="eventTime" class="form-label">Event Time</label>
                                <input type="time" class="form-control" id="eventTime" name="event_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="IT">IT</option>
                                    <option value="HR">HR</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Operations">Operations</option>
                                </select>
                            </div>

                            <!-- New Section: Registration Link Options -->
                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold text-primary">Registration Form Link</label>
                                <div class="border rounded p-3 bg-light">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="link_type" id="useExternalLink" value="external" checked>
                                        <label class="form-check-label" for="useExternalLink">
                                            Use External Link (Google Forms, Microsoft Forms, etc.)
                                        </label>
                                    </div>
                                    <div id="externalLinkGroup">
                                        <input type="url" class="form-control" id="externalLink" name="external_link" placeholder="https://forms.gle/abc123 or https://forms.office.com/...">
                                    </div>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="radio" name="link_type" id="useInternalLink" value="internal">
                                        <label class="form-check-label" for="useInternalLink">
                                            Generate System Link (Built-in Registration Form)
                                        </label>
                                    </div>
                                    <div id="internalLinkPreview" class="mt-2 p-3 bg-white border rounded d-none">
                                        <small class="text-muted d-block mb-2">Your unique registration link will be:</small>
                                        <code class="text-primary" id="generatedLinkPreview">https://yourdomain.com/s/xxxxxx</code>
                                        <small class="text-success d-block mt-2">This link will be generated automatically after saving.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQRBtn">Create QR Code</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Profile Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="profileForm">
                        @csrf
                        <div class="mb-3">
                            <label for="profileName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="profileName" name="name" value="Admin User" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="profileEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="profileEmail" name="email" value="admin@company.com" readonly>
                        </div>
                        <!-- Add more profile fields if needed -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Datatables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- SweetAlert2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
