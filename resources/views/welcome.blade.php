<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Medissa Clinic</title>
    @vite('resources/css/app.css')
</head>
<body>
    <button onclick="scrollToTop()" id="backToTopBtn" title="Go to top">⬆️</button>

    <!-- Navigation -->
    <nav class=home>
    <div class="logo">
        <img src="{{ asset('images/medissadental.jpg') }}" alt="Medissa Logo" style="height: 90px; vertical-align: middle;">
        <strong style="font-size: 1.6rem; margin-left: 10px;">Medissa Dental Clinic</strong>
    </div>
        <div class="links">
            <a href="#" class="active">Home</a>
            <a href="#branches">Our Branches</a>
            <a href="#services">Services ▾</a>
            <a href="#book-section" class="book-button">Book Today</a>
            <a href="#footer" class="book-button">Contact Us</a>

        </div>
    </nav>

    <!-- Home Section -->
        <section style="display: flex; align-items: center; justify-content: space-between; padding: 40px;">
            
            <!-- Left: Text Content -->
            <div style="flex: 1; padding-right: 30px;">
                <h1>Welcome to Medissa Dental Clinic</h1>
                <p>
                    We are dedicated to providing high-quality dental care to patients of all ages.
                    At Medissa Clinic, we offer a wide range of services delivered by experienced dentists
                    using state-of-the-art equipment in a friendly and welcoming environment.
                </p>
            </div>

            <!-- Right: Image -->
            <div style="flex: 1; text-align: right;">
                <img src="{{ asset('images/dental.jpg') }}" alt="Clinic Hero"
                    style="max-width: 100%; height: auto; border-radius: 20px;">
            </div>

        </section>

        <section id="branches" style="padding: 60px 20px;"> 
            <h2>Our Branches</h2>
            <div class="branches">
                @foreach ($branches as $branch)
                    <div class="branch-card">
                        <h3>{{ $branch->name }} Branch</h3>
                        <p>{{ $branch->address }}</p>
                        @if ($branch->google_maps_link)
                            <a href="{{ $branch->google_maps_link }}" target="_blank" class="map-button">View on Google Maps</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

<section id="services" style="padding: 60px 20px;">
    <h2 style="text-align: center; margin-bottom: 30px;">Our Services</h2>
    <div class="services-grid">

        @php
            $services = [
                ['name' => 'Braces', 'image' => 'services/braces.jpeg'],
                ['name' => 'Whitening', 'image' => 'services/whitening.jpeg'],
                ['name' => 'Scaling & Polishing', 'image' => 'services/scaling.jpg'],
                ['name' => 'Extraction', 'image' => 'services/extraction.jpeg'],
                ['name' => 'Crown & Bridge', 'image' => 'services/crown_bridge.png'],
                ['name' => 'Veneers', 'image' => 'services/veneers.png'],
                ['name' => 'Implant', 'image' => 'services/implant.jpg'],
                ['name' => 'Root Canal Treatment', 'image' => 'services/root_canal.jpg'],
                ['name' => 'Filling', 'image' => 'services/filling.jpg'],
                ['name' => 'Denture', 'image' => 'services/denture.jpeg'],
                ['name' => 'Minor Oral Surgery', 'image' => 'services/surgery.jpg'],
                ['name' => 'Radiology', 'image' => 'services/radiology.jpeg'],
                ['name' => 'Kids Treatment', 'image' => 'services/kids.jpg'],
                ['name' => 'Dental Checkup', 'image' => 'services/checkup.jpg'],
            ];
        @endphp

        @foreach ($services as $service)
            <div class="service-card">
                <img src="{{ asset('images/' . $service['image']) }}" alt="{{ $service['name'] }}">
                <h4>{{ $service['name'] }}</h4>
            </div>
        @endforeach

    </div>
</section>

<section id="book-section">
    <h2>🗓️ Quick Booking Preview</h2>

    <form action="{{ route('login') }}" method="GET" class="booking-form">
        <div class="booking-fields">
            <select required>
                <option value="" disabled selected>Select Branch</option>
                <option>Kota Damansara</option>
                <option>Shah Alam</option>
                <option>Bangi</option>
                <option>Senawang</option>
                <option>Banting</option>
                <option>Klang</option>
                <option>Subang Jaya</option>
                <option>Selayang</option>
                <option>Puchong</option>
                <option>Langkawi</option>
                <option>Kuala Selangor</option>
                <option>Rimbayu</option>
                <option>Puncak Alam</option>
                <option>Kota Warisan</option>
            </select>

            <input type="date" required>
            
            <select required>
                <option value="" disabled selected>Select Service</option>
                <option>Braces</option>
                <option>Whitening</option>
                <option>Scaling & Polishing</option>
                <option>Extraction</option>
                <option>Crown & Bridge</option>
                <option>Veneers</option>
                <option>Implant</option>
                <option>Root Canal Treatment</option>
                <option>Filling</option>
                <option>Denture</option>
                <option>Minor Oral Surgery</option>
                <option>Radiology</option>
                <option>Kids Treatment</option>
                <option>Dental Checkup</option>
            </select>

            <input type="time" required>

            <button type="submit" class="book-button">
                Book Now
            </button>
        </div>
    </form>

    <p class="booking-note">*You will be asked to log in to complete your booking.</p>
</section>

<script>
    // Show the button when the user scrolls down 100px
    window.onscroll = function () {
        const btn = document.getElementById("backToTopBtn");
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            btn.style.display = "block";
        } else {
            btn.style.display = "none";
        }
    };

    // Smooth scroll to top
    function scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

<footer id="footer" style="padding: 40px; background-color: #f0f0f0; text-align: center;">
    <h3>Contact Us</h3>
    <p>🕑 Operating hours: 9AM - 5PM (Monday - Saturday)</p>
    <p>📞 +60 13-7071644</p>
    <p>📞 +60 10-4303380</p>
    <p>📧 customer@medissaclinic.com</p>
</footer>


</body>
</html>
