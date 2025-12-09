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
        body { background: linear-gradient(135deg, #0A3A6B, #1e5a8c); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { max-width: 520px; width: 100%; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
        .btn-submit { background: #FDB813; border: none; font-weight: bold; color: #000; }
        .btn-submit:hover { background: #e1a00d; }
        .header-logo { width: 90px; height: 90px; object-fit: contain; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="card">
        <div class="card-header text-center bg-primary text-white py-4">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="header-logo mb-3">
            @endif
            <h4 class="mb-1">Visitor Registration</h4>
            <p class="mb-0 fs-5 fw-bold">{{ $url->event_title }}</p>
            @if($url->venue)
                <small>{{ $url->venue }}</small>
            @endif
        </div>
        <div class="card-body p-4">
            <form id="registrationForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="firstname" placeholder="First Name *" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="middlename" placeholder="Middle Name">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="lastname" placeholder="Last Name *" required>
                    </div>
                </div>

                <div class="mt-3">
                    <input type="text" class="form-control" name="lgu_company" placeholder="LGU / Company *" required>
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control" name="position" placeholder="Position *" required>
                </div>
                <div class="mt-3">
                    <input type="text" class="form-control" name="contact" placeholder="Contact Number *" required>
                </div>
                <div class="mt-3">
                    <textarea class="form-control" name="purpose" rows="4" placeholder="Purpose / Remarks *" required></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-submit w-100 py-3 text-uppercase fw-bold">
                        Submit Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('button[type="submit"]');
        btn.prop('disabled', true).html('Submitting...');

        $.ajax({
            url: "{{ route('short.register', $url->short_code) }}",
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thank You!',
                    text: res.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                $('#registrationForm')[0].reset();
            },
            error: function(xhr) {
                let msg = 'Something went wrong.';
                if (xhr.responseJSON?.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', html: msg });
            },
            complete: function() {
                btn.prop('disabled', false).html('Submit Registration');
            }
        });
    });
});
</script>
</body>
</html>
