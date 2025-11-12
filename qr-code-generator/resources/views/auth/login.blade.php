<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Generator System - Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="qr-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="8" height="8" rx="1" fill="white"/>
                    <rect x="13" y="3" width="8" height="8" rx="1" fill="white"/>
                    <rect x="3" y="13" width="8" height="8" rx="1" fill="white"/>
                    <rect x="5" y="5" width="4" height="4" fill="#0A3A6B"/>
                    <rect x="15" y="5" width="4" height="4" fill="#0A3A6B"/>
                    <rect x="5" y="15" width="4" height="4" fill="#0A3A6B"/>
                    <rect x="13" y="13" width="3" height="3" fill="white"/>
                    <rect x="17" y="13" width="2" height="2" fill="white"/>
                    <rect x="13" y="17" width="2" height="2" fill="white"/>
                    <rect x="16" y="17" width="2" height="2" fill="white"/>
                    <rect x="19" y="17" width="2" height="2" fill="white"/>
                    <rect x="17" y="20" width="2" height="1" fill="white"/>
                </svg>
            </div>

            <h1 class="system-title">QR Code Generator System</h1>
            <p class="subtitle">Sign in to access the system</p>

            <form id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your.email@company.com" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="text-end mb-3">
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-signin w-100" id="signInBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>

            <div class="demo-credentials">
                <p class="demo-title">Demo Credentials:</p>
                <p class="demo-item"><strong>Admin:</strong> admin@company.com / admin123</p>
                <p class="demo-item"><strong>Staff (IT):</strong> staff1@company.com / staff123</p>
                <p class="demo-item"><strong>Staff (HR):</strong> staff2@company.com / staff123</p>
                <p class="demo-item"><strong>Staff (Finance):</strong> staff3@company.com / staff123</p>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
