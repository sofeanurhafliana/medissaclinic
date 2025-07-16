@vite('resources/css/auth.css')
<div class="container">
    <div class="form-box register"> 

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <h1>Login</h1>

        @if ($errors->has('login_error'))
            <div class="error-message">
                {{ $errors->first('login_error') }}
            </div>  
        @endif
            
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" id="email" value="{{ old('email') }}" required>
                <i class='bx bx-at'></i>
                
            </div>  

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" id="password" required>
                <i class='bx bx-lock'></i>
            </div> 

            <label>
                <input type="checkbox" name="remember" {{ old('remember') == 'on' ? 'checked' : '' }}>
                Remember me
            </label>

            <div>
                <button type="submit" class="btn">Log In</button>
            </div>
        </form>
    </div>
    <div class="toggle-box">
        <div class="toggle-panel toggle-left">
            <h1>Medissa Clinic</h1>
            <p> Don't have an account?</p>
            <button type="button" onclick="window.location.href='{{ route('register') }}'" class="btn">
                Register
            </button>
            <br>
            <button type="button" onclick="window.location.href='{{ url('/') }}'" class="btn btn-wide">
                Back to Homepage
            </button>
        </div>
    </div>
</div>
