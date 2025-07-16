@vite('resources/css/auth.css')

<div class="container">
    <div class="form-box register">

        <form method="POST" action="{{ route('register.process') }}">
            @csrf

            <h1>Register</h1>

            <div class="input-box">
                <input type="text" name="name" id="name" placeholder="Name" value="{{ old('name') }}" required>
                <i class='bx bx-user'></i> 
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-box">
                <input type="email" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required>
                <i class='bx bx-at'></i>  
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="Password" required>
                <i class='bx bx-lock'></i> 
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="input-box">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Password confirmation" required>
         
            </div> 

            <div class="form-group" id="branch-section" style="display: none;">
                <label for="branch_id">Branch</label>
                <select name="branch_id" class="form-control">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const emailInput = document.querySelector('input[name="email"]');
                    const branchSection = document.getElementById('branch-section');

                    emailInput.addEventListener('input', function () {
                        if (emailInput.value.endsWith('@admin.medissa.com')) {
                            branchSection.style.display = 'block';
                        } else {
                            branchSection.style.display = 'none';
                        }
                    });
                });
            </script>


            <div>
                <button type="submit" class="btn">Register</button>
            </div>
        </form>
    </div>
    <div class="toggle-box">
        <div class="toggle-panel toggle-left">
            <h1>Medissa Clinic</h1>
            <p> Already have an account?</p>
            <button type="button" onclick="window.location.href='{{ route('login') }}'" class="btn">
                Log In
            </button>
        </div>
    </div>
</div>
