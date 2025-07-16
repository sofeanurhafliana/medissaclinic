<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Booking - Dental Clinic</title>
    @vite('resources/css/app.css')
</head>
<body>

<div class="user-payment">
    <div class="payment-box">
        <h2>Confirm & Pay RM10 Deposit</h2>

        <p><strong>Service:</strong> {{ $data['service'] }}</p>
        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($data['booking_date'])->format('d/m/Y') }}</p>
        <p><strong>Time:</strong> {{ $data['booking_time'] }}</p>
        <p><strong>Doctor:</strong> {{ $doctor->user->name }}</p>
        <p><strong>Branch:</strong> {{ $branch->name }}</p>
        <p><strong>Total Deposit:</strong> RM{{ number_format($deposit, 2) }}</strong></p>

        <form method="POST" action="{{ route('user.booking.confirm') }}" id="paymentForm">
            @csrf
            <button type="submit" class="btn-success">Pay Now</button>
        </form>

        <div class="button-container">
            <button id="backBtn" class="btn-back">Back</button>
        </div>



    </div>
</div>

<script>
    let isSubmitting = false;

    document.getElementById('paymentForm').addEventListener('submit', function () {
        isSubmitting = true;
    });

    document.getElementById('backBtn').addEventListener('click', function (e) {
        if (!confirm('Are you sure you want to leave without confirming payment?')) {
            e.preventDefault();
        } else {
            isSubmitting = true;
            window.location.href = '{{ route('dashboard.user') }}';
        }
    });

    window.addEventListener('beforeunload', function (e) {
        if (!isSubmitting) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>

</body>
</html>
