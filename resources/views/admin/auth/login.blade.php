<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Sign In — TradePro Control Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #070a13;
            --bg-card: #0f1629;
            --border: #1e2d47;
            --accent: #4f8ef7;
            --accent-glow: rgba(79,142,247,0.25);
            --red: #ff4c6a;
            --text-primary: #e8edf5;
            --text-secondary: #7a8aaa;
            --text-muted: #4a5c80;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        /* Background decorative gradients */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 500px;
            background: radial-gradient(ellipse at center, rgba(79,142,247,0.12) 0%, rgba(123,94,167,0.06) 45%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5), 0 0 1px rgba(79,142,247,0.3);
            position: relative;
            z-index: 1;
        }
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-badge {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #4f8ef7 0%, #7b5ea7 100%);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(79,142,247,0.35);
        }
        .login-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .login-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%;
            padding: 13px 16px;
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-control::placeholder {
            color: var(--text-muted);
        }
        .form-extra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 13px;
        }
        .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }
        .checkbox-wrap input {
            accent-color: var(--accent);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f8ef7 0%, #3b74db 100%);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(79,142,247,0.3);
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(79,142,247,0.45);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        .alert-box {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }
        .alert-error {
            background: rgba(255,76,106,0.12);
            border: 1px solid rgba(255,76,106,0.35);
            color: var(--red);
        }
        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }
        .login-footer a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.15s;
        }
        .login-footer a:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div style="display:flex;justify-content:center;margin-bottom:16px;">
            <img src="/profile/images/logo-dark.svg" alt="Quotex" height="30" style="height:30px;width:auto;display:block;">
        </div>
        <h1 class="login-title">Quotex Admin</h1>
        <p class="login-subtitle">Sign in to access administrator management console</p>
    </div>

    @if($errors->any())
        <div class="alert-box alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Admin Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                placeholder="admin@admin.com" 
                value="{{ old('email') }}" 
                required 
                autofocus
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control" 
                placeholder="••••••••••••" 
                required
            >
        </div>

        <div class="form-extra">
            <label class="checkbox-wrap">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <span style="color:var(--text-muted)">Secure Area</span>
        </div>

        <button type="submit" class="btn-submit">Sign In to Dashboard →</button>
    </form>

    <div class="login-footer">
        <a href="{{ route('home') }}">← Return to Trading Platform</a>
    </div>
</div>

</body>
</html>
