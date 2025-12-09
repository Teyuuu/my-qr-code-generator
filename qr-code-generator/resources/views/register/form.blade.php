<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Visitor Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0A3A6B, #1e5a8c);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .card {
            max-width: 580px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #0A3A6B, #1e5a8c);
            color: white;
            padding: 2rem 1.5rem;
            border-radius: 16px 16px 0 0;
        }
        .header-logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        .card-header h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .event-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #FDB813;
            margin-bottom: 0.25rem;
        }
        .card-body {
            padding: 2rem 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #0A3A6B;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }
        .required {
            color: #dc3545;
        }
        .form-control {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 0.65rem 0.9rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #FDB813;
            box-shadow: 0 0 0 0.15rem rgba(253, 184, 19, 0.15);
        }
        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0A3A6B;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #FDB813;
        }
        .btn-submit {
            background: #FDB813;
            border: none;
            font-weight: 700;
            color: #000;
            padding: 0.9rem;
            font-size: 1rem;
            border-radius: 8px;
            text-transform: uppercase;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #e1a00d;
        }
        .btn-submit:disabled {
            opacity: 0.7;
        }
        .char-count {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: right;
            margin-top: 0.25rem;
        }
        @media (max-width: 768px) {
            .card-body { padding: 1.5rem 1rem; }
            .card-header { padding: 1.5rem 1rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header text-center">
            @if(file_exists(public_path('images/bacoor-logo.png')))
                <img src="{{ asset('images/bacoor-logo.png') }}" alt="Logo" class="header-logo">
            @endif
            <h4>Visitor Registration</h4>
            <p class="event-title mb-1">{{ $url->event_title }}</p>
            @if($url->venue)
                <small class="text-white-50">📍 {{ $url->venue }}</small>
            @endif
        </div>
        <div class="card-body">
            <form id="registrationForm">
                @csrf

                <div class="mb-4">
                    <div class="section-title">Personal Information</div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middlename">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name <span class="required">*</span></label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="section-title">Professional Details</div>
                    <div class="mb-3">
                        <label class="form-label">LGU / Company <span class="required">*</span></label>
                        <input type="text" class="form-control" name="lgu_company" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position <span class="required">*</span></label>
                        <input type="text" class="form-control" name="position" required>
                    </div>
                    <div>
                        <label class="form-label">Contact Number <span class="required">*</span></label>
                        <input type="tel" class="form-control" name="contact" placeholder="09XX XXX XXXX" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="section-title">Purpose of Visit</div>
                    <textarea class="form-control" name="purpose" rows="4" maxlength="500" id="purposeField" required></textarea>
                    <div class="char-count"><span id="charCount">0</span> / 500</div>
                </div>

                <button type="submit" class="btn btn-submit w-100" id="submitBtn">
                    Submit Registration
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $('#purposeField').on('input', function() {
        $('#charCount').text($(this).val().length);
    });

    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#submitBtn');
        btn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: "{{ route('short.register', $url->short_code) }}",
            method: 'POST',
            data: $(this).serialize(),
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: res.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                $('#registrationForm')[0].reset();
                $('#charCount').text('0');
            },
            error: function(xhr) {
                let msg = 'Something went wrong.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({icon: 'error', title: 'Error', text: msg});
            },
            complete: function() {
                btn.prop('disabled', false).text('Submit Registration');
            }
        });
    });
});
</script>
</body>
</html>
