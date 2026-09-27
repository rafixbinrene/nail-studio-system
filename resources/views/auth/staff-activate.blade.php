<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Staff Account Activation | Nail Studio & Beauty</title>

<style>
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
    margin-bottom:28px;
    font-size:14px;
    line-height:1.5;
}

.info-box{
    background:rgba(255,255,255,.55);
    border:1px solid rgba(255,255,255,.75);
    border-radius:18px;
    padding:14px;
    margin-bottom:20px;
    color:#6f4e37;
    font-size:14px;
    line-height:1.5;
}

label{
    display:block;
    margin-bottom:8px;
    font-size:14px;
    font-weight:700;
}

input{
    width:100%;
    padding:14px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.7);
    background:rgba(255,255,255,.6);
    outline:none;
    margin-bottom:18px;
}

input:focus{
    border-color:#6f4e37;
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:999px;
    background:linear-gradient(135deg,#8a7158,#5d3f2c);
    color:white;
    font-weight:800;
    cursor:pointer;
}

.alert{
    padding:12px 14px;
    border-radius:16px;
    margin-bottom:18px;
    font-size:14px;
    line-height:1.5;
    background:rgba(150,45,35,.10);
    color:#7a241d;
    border:1px solid rgba(150,45,35,.18);
}

.error-text{
    margin-top:-10px;
    margin-bottom:16px;
    font-size:13px;
    color:#8a2f24;
    font-weight:600;
}
</style>
</head>

<body>

<div class="card">

    <div class="logo">Nail Studio & Beauty</div>

    <h1>Activate Staff Account</h1>

    <p class="subtitle">
        Set your password to activate your staff account.
    </p>

    <div class="info-box">
        <strong>Name:</strong> {{ $user->name }} <br>
        <strong>Email:</strong> {{ $user->email }}
    </div>

    @if ($errors->any())
        <div class="alert">
            Please check your password and try again.
        </div>
    @endif

    <form method="POST" action="{{ route('staff.activate.store', $token) }}">
        @csrf

        <label>New Password</label>
        <input
            type="password"
            name="password"
            placeholder="Create your password"
            required
            autocomplete="new-password"
        >

        @error('password')
            <div class="error-text">{{ $message }}</div>
        @enderror

        <label>Confirm Password</label>
        <input
            type="password"
            name="password_confirmation"
            placeholder="Re-enter your password"
            required
            autocomplete="new-password"
        >

        <button type="submit">
            Activate Account
        </button>
    </form>

</div>

</body>
</html>