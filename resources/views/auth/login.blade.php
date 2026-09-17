<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Nail Studio & Beauty</title>

<style>
/*
|--------------------------------------------------------------------------
| NS BEAUTY COMMENT:
| Login Page Styling
|--------------------------------------------------------------------------
| Purpose:
| - Uses the Muji brown / pearl white theme.
| - Keeps the login UI clean, simple, and responsive.
|--------------------------------------------------------------------------
*/

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    background:
        radial-gradient(circle at top left,#ead8c4,transparent 35%),
        radial-gradient(circle at bottom right,#f3ebe2,transparent 35%),
        linear-gradient(135deg,#f8f6f2,#efe4d8);
}

.card{
    width:100%;
    max-width:500px;
    background:rgba(255,255,255,.45);
    backdrop-filter:blur(28px);
    -webkit-backdrop-filter:blur(28px);
    border:1px solid rgba(255,255,255,.7);
    border-radius:32px;
    padding:40px;
    box-shadow:
        0 20px 60px rgba(47,33,24,.08),
        inset 0 1px 0 rgba(255,255,255,.7);
}

.logo{
    text-align:center;
    font-weight:800;
    margin-bottom:10px;
}

h1{
    text-align:center;
    margin-bottom:10px;
    background:linear-gradient(135deg,#2f2118,#6f4e37,#a38468);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.subtitle{
    text-align:center;
    color:#7d6d60;
    margin-bottom:26px;
    font-size:14px;
}

label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
}

input{
    width:100%;
    padding:14px;
    border-radius:16px;
    outline:none;
    background:rgba(255,255,255,.6);
    margin-bottom:18px;
    border:1px solid rgba(255,255,255,.7);
}

input:focus{
    border-color:#6f4e37;
}

button{
    width:100%;
    padding:15px;
    border:none;
    cursor:pointer;
    border-radius:999px;
    color:white;
    font-weight:800;
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
}

.footer{
    margin-top:25px;
    text-align:center;
    font-size:14px;
}

.footer a,
.forgot{
    color:#6f4e37;
    text-decoration:none;
    font-weight:800;
}

.back{
    display:block;
    text-align:center;
    margin-top:20px;
    color:#7d6d60;
    text-decoration:none;
}

.login-options{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    font-size:14px;
}

.remember{
    display:flex;
    align-items:center;
    gap:8px;
    color:#7d6d60;
    font-size:14px;
}

.remember input{
    width:auto;
    margin:0;
    accent-color:#6f4e37;
}

.alert{
    padding:12px 14px;
    border-radius:16px;
    margin-bottom:18px;
    font-size:14px;
    line-height:1.5;
}

.alert-error{
    background:rgba(150,45,35,.10);
    color:#7a241d;
    border:1px solid rgba(150,45,35,.18);
}

.alert-success{
    background:rgba(50,130,85,.10);
    color:#285c3f;
    border:1px solid rgba(50,130,85,.18);
}

.error-text{
    margin-top:-10px;
    margin-bottom:16px;
    font-size:13px;
    color:#8a2f24;
    font-weight:600;
}

.security-note{
    margin-top:20px;
    text-align:center;
    color:#7d6d60;
    font-size:13px;
    line-height:1.5;
}

.security-box{
    font-size:13px;
    color:#7d6d60;
    line-height:1.5;
    margin-bottom:18px;
    background:rgba(255,255,255,.45);
    border:1px solid rgba(255,255,255,.7);
    border-radius:16px;
    padding:12px 14px;
}

@media(max-width:520px){
    .card{
        padding:32px 28px;
    }

    .login-options{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }
}
</style>
</head>

<body>

<div class="card">

    <div class="logo">Nail Studio & Beauty</div>

    <h1>Welcome Back</h1>

    <p class="subtitle">
        Login using your email, password, and Email OTP
    </p>

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            Please check the form and try again.
        </div>
    @endif

    <!--
    |--------------------------------------------------------------------------
    | NS BEAUTY COMMENT:
    | Login Form
    |--------------------------------------------------------------------------
    | Purpose:
    | - User must enter email and password.
    | - If credentials are correct, Email OTP will be sent.
    | - User must verify OTP before entering the dashboard.
    |--------------------------------------------------------------------------
    -->
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <label>Email Address</label>
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Enter your email address"
            required
            autocomplete="email"
            autofocus
        >

        @error('email')
            <div class="error-text">{{ $message }}</div>
        @enderror

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Enter your password"
            required
            autocomplete="current-password"
        >

        @error('password')
            <div class="error-text">{{ $message }}</div>
        @enderror

        <div class="security-box">
            After entering the correct email and password, an Email OTP will be sent to your registered email address.
        </div>

        <div class="login-options">
            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <a href="{{ route('password.request') }}" class="forgot">
                Forgot Password?
            </a>
        </div>

        <button type="submit">
            Send Email OTP
        </button>
    </form>

    <div class="footer">
        Don't have an account?
        <a href="{{ route('register') }}">Create Account</a>
    </div>

    <a href="{{ route('home') }}" class="back">
        ← Back to Home
    </a>

    <div class="security-note">
        Email OTP verification is required before accessing the system.
    </div>
</div>

</body>
</html>