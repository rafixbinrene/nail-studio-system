<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Email OTP Verification | NS & Beauty</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Arial,sans-serif}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:radial-gradient(circle at top left,#ead8c4,transparent 35%),radial-gradient(circle at bottom right,#f3ebe2,transparent 35%),linear-gradient(135deg,#f8f6f2,#efe4d8);color:#2f2118}
.card{width:100%;max-width:450px;background:rgba(255,255,255,.45);backdrop-filter:blur(28px);border:1px solid rgba(255,255,255,.7);border-radius:32px;padding:40px;box-shadow:0 20px 60px rgba(47,33,24,.08),inset 0 1px 0 rgba(255,255,255,.7)}
.logo{text-align:center;font-weight:800;margin-bottom:12px}
h1{text-align:center;margin-bottom:10px;background:linear-gradient(135deg,#2f2118,#6f4e37,#a38468);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.subtitle{text-align:center;color:#7d6d60;margin-bottom:22px;font-size:14px;line-height:1.5}
input{width:100%;text-align:center;font-size:24px;font-weight:900;letter-spacing:10px;padding:16px;border-radius:18px;border:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.6);outline:none;margin-bottom:14px}
input:focus{border-color:#6f4e37}
button{width:100%;padding:15px;border:none;border-radius:999px;background:linear-gradient(135deg,#8a7158,#5d3f2c);color:white;font-weight:800;cursor:pointer}
.resend{text-align:center;margin-top:20px;color:#7d6d60;font-size:14px}
.resend button{width:auto;background:none;color:#6f4e37;padding:0;border-radius:0;font-weight:900}
.alert{padding:12px 14px;border-radius:16px;margin-bottom:18px;font-size:14px;line-height:1.5}
.alert-error{background:rgba(150,45,35,.10);color:#7a241d;border:1px solid rgba(150,45,35,.18)}
.alert-success{background:rgba(50,130,85,.10);color:#285c3f;border:1px solid rgba(50,130,85,.18)}
.back{text-align:center;display:block;margin-top:20px;color:#7d6d60;text-decoration:none;font-size:14px}
</style>
</head>

<body>
<div class="card">
    <div class="logo">NS &amp; Beauty</div>

    <h1>Email OTP Verification</h1>

    <p class="subtitle">
        Enter the 6-digit code sent to your registered email address.
        <br>
        This code expires in 5 minutes.
    </p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            @error('otp') {{ $message }} @else Please check your OTP and try again. @enderror
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <input
            type="text"
            name="otp"
            maxlength="6"
            minlength="6"
            inputmode="numeric"
            pattern="[0-9]{6}"
            placeholder="000000"
            required
            autofocus
        >

        <button type="submit">Verify OTP</button>
    </form>

    <form method="POST" action="{{ route('otp.resend') }}">
        @csrf
        <p class="resend">
            Didn’t receive the code?
            <button type="submit">Resend Email OTP</button>
        </p>
    </form>

    <a href="{{ route('login') }}" class="back">← Back to Login</a>
</div>
</body>
</html>