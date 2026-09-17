<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | NS & Beauty</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(circle at top left,#ead8c4,transparent 35%),radial-gradient(circle at bottom right,#f3ebe2,transparent 35%),linear-gradient(135deg,#f8f6f2,#efe4d8);color:#2f2118}
.card{width:100%;max-width:460px;background:rgba(255,255,255,.45);backdrop-filter:blur(28px);border:1px solid rgba(255,255,255,.7);border-radius:32px;padding:40px;box-shadow:0 20px 60px rgba(47,33,24,.08),inset 0 1px 0 rgba(255,255,255,.7)}
.logo{text-align:center;font-weight:800;margin-bottom:12px}
h1{text-align:center;margin-bottom:10px;background:linear-gradient(135deg,#2f2118,#6f4e37,#a38468);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.subtitle{text-align:center;color:#7d6d60;margin-bottom:30px;font-size:14px;line-height:1.5}
label{display:block;margin-bottom:8px;font-size:14px;font-weight:700}
input{width:100%;padding:14px;border-radius:16px;border:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.6);outline:none;margin-bottom:18px}
button{width:100%;padding:15px;border:none;border-radius:999px;background:linear-gradient(135deg,#8a7158,#5d3f2c);color:white;font-weight:800;cursor:pointer}
.back{text-align:center;display:block;margin-top:20px;color:#7d6d60;text-decoration:none;font-size:14px}
.alert{padding:12px 14px;border-radius:16px;margin-bottom:18px;font-size:14px;line-height:1.5;background:rgba(150,45,35,.10);color:#7a241d;border:1px solid rgba(150,45,35,.18)}
.error-text{margin-top:-10px;margin-bottom:16px;font-size:13px;color:#8a2f24;font-weight:600}
</style>
</head>

<body>
<div class="card">
    <div class="logo">NS &amp; Beauty</div>

    <h1>Forgot Password</h1>

    <p class="subtitle">
        Enter your registered email address. We will send a password recovery OTP.
    </p>

    @if ($errors->any())
        <div class="alert">Please check the form and try again.</div>
    @endif

    <form method="POST" action="{{ route('password.otp.send') }}">
        @csrf

        <label>Email Address</label>
        <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Enter your registered email address"
            required
            autocomplete="email"
        >
        @error('email') <div class="error-text">{{ $message }}</div> @enderror

        <button type="submit">Send Email OTP</button>
    </form>

    <a href="{{ route('login') }}" class="back">← Back to Login</a>
</div>
</body>
</html>