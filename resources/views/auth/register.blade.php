<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register | Nail Studio & Beauty</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif}
body{min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px;background:radial-gradient(circle at top left,#ead8c4,transparent 35%),radial-gradient(circle at bottom right,#f3ebe2,transparent 35%),linear-gradient(135deg,#f8f6f2,#efe4d8)}
.card{width:100%;max-width:520px;background:rgba(255,255,255,.45);backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);border:1px solid rgba(255,255,255,.7);border-radius:32px;padding:40px;box-shadow:0 20px 60px rgba(47,33,24,.08),inset 0 1px 0 rgba(255,255,255,.7)}
.logo{text-align:center;font-weight:800;margin-bottom:10px}
h1{text-align:center;margin-bottom:10px;background:linear-gradient(135deg,#2f2118,#6f4e37,#a38468);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.subtitle{text-align:center;color:#7d6d60;margin-bottom:30px;font-size:14px}
label{display:block;margin-bottom:8px;font-size:14px;font-weight:600;color:#2f2118}
input{width:100%;padding:14px;border-radius:16px;outline:none;background:rgba(255,255,255,.6);margin-bottom:8px;border:1px solid rgba(255,255,255,.7)}
input:focus{border-color:#6f4e37}
button{width:100%;padding:15px;border:none;cursor:pointer;border-radius:999px;color:white;font-weight:800;background:linear-gradient(135deg,#8a7158,#5d3f2c);margin-top:12px}
.footer{margin-top:25px;text-align:center;font-size:14px}
.footer a{color:#6f4e37;text-decoration:none;font-weight:800}
.back{display:block;text-align:center;margin-top:20px;color:#7d6d60;text-decoration:none}
.error-text{margin-bottom:12px;font-size:13px;color:#8a2f24;font-weight:600}
.alert{padding:12px 14px;border-radius:16px;margin-bottom:18px;font-size:14px;line-height:1.5}
.alert-error{background:rgba(150,45,35,.10);color:#7a241d;border:1px solid rgba(150,45,35,.18)}
.alert-success{background:rgba(50,130,85,.10);color:#285c3f;border:1px solid rgba(50,130,85,.18)}
.note{font-size:13px;color:#7d6d60;line-height:1.5;margin-bottom:14px;background:rgba(255,255,255,.45);border:1px solid rgba(255,255,255,.7);border-radius:16px;padding:12px 14px}
</style>
</head>

<body>
<div class="card">

    <div class="logo">Nail Studio & Beauty</div>

    <h1>Create Account</h1>

    <p class="subtitle">Join our beauty community today</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">Please check the form and try again.</div>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <label>Full Name</label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autocomplete="name">
        @error('name') <div class="error-text">{{ $message }}</div> @enderror

        <label>Phone Number</label>
        <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="Example: 09123456789 or +60123456789" required autocomplete="tel">
        @error('phone_number') <div class="error-text">{{ $message }}</div> @enderror

        <label>Address</label>
        <input type="text" name="address" value="{{ old('address') }}" placeholder="Enter your address" autocomplete="street-address">
        @error('address') <div class="error-text">{{ $message }}</div> @enderror

        <label>Email Address</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autocomplete="email">
        @error('email') <div class="error-text">{{ $message }}</div> @enderror

        <div class="note">
            OTP verification will be sent to this email address after account creation.
        </div>

        <label>Password</label>
        <input type="password" name="password" placeholder="Create a password" required autocomplete="new-password">
        @error('password') <div class="error-text">{{ $message }}</div> @enderror

        <label>Confirm Password</label>
        <input type="password" name="password_confirmation" placeholder="Re-enter your password" required autocomplete="new-password">

        <button type="submit">Create Account & Send Email OTP</button>
    </form>

    <div class="footer">
        Already have an account?
        <a href="{{ route('login') }}">Sign In</a>
    </div>

    <a href="{{ route('home') }}" class="back">← Back to Home</a>
</div>
</body>
</html>