<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'B2B Fleet Rental CRM') }} - Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body.login-page {
                font-family: 'Inter', sans-serif;
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #0f172a;
                position: relative;
                overflow: hidden;
            }

            /* Animated aurora blobs */
            .aurora-blob-1 {
                position: absolute;
                top: -20%;
                right: -10%;
                width: 600px;
                height: 600px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, rgba(139, 92, 246, 0.15) 50%, transparent 70%);
                filter: blur(80px);
                animation: float1 8s ease-in-out infinite;
            }

            .aurora-blob-2 {
                position: absolute;
                bottom: -25%;
                left: -10%;
                width: 500px;
                height: 500px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(20, 184, 166, 0.12) 50%, transparent 70%);
                filter: blur(80px);
                animation: float2 10s ease-in-out infinite;
            }

            .aurora-blob-3 {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 400px;
                height: 400px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, transparent 60%);
                filter: blur(100px);
                animation: float3 12s ease-in-out infinite;
            }

            @keyframes float1 {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(-30px, 20px) scale(1.05); }
                66% { transform: translate(20px, -15px) scale(0.95); }
            }

            @keyframes float2 {
                0%, 100% { transform: translate(0, 0) scale(1); }
                33% { transform: translate(25px, -20px) scale(1.08); }
                66% { transform: translate(-15px, 25px) scale(0.92); }
            }

            @keyframes float3 {
                0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.6; }
                50% { transform: translate(-50%, -50%) scale(1.15); opacity: 1; }
            }

            /* Subtle grid pattern */
            .grid-pattern {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
                background-size: 50px 50px;
            }

            /* Login container */
            .login-container {
                position: relative;
                z-index: 10;
                width: 100%;
                max-width: 440px;
                padding: 0 16px;
            }

            /* Logo area */
            .logo-wrap {
                text-align: center;
                margin-bottom: 36px;
            }

            .logo-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 64px;
                height: 64px;
                border-radius: 18px;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                transition: transform 0.3s, box-shadow 0.3s;
            }

            .logo-icon:hover {
                transform: scale(1.08);
                box-shadow: 0 12px 40px rgba(99, 102, 241, 0.3);
            }

            .logo-icon svg {
                width: 36px;
                height: 36px;
                color: #fff;
            }

            .logo-text {
                margin-top: 16px;
                font-size: 26px;
                font-weight: 800;
                color: #fff;
                letter-spacing: 1px;
            }

            .logo-text span {
                background: linear-gradient(135deg, #818cf8, #a78bfa);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* Card */
            .login-card {
                background: #fff;
                border-radius: 24px;
                padding: 40px 36px;
                box-shadow:
                    0 25px 60px rgba(0, 0, 0, 0.4),
                    0 0 0 1px rgba(255, 255, 255, 0.05);
                position: relative;
                overflow: hidden;
            }

            .login-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #6366f1, #a855f7, #6366f1);
                background-size: 200% 100%;
                animation: shimmer 3s linear infinite;
            }

            @keyframes shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            /* Footer */
            .login-footer {
                text-align: center;
                margin-top: 32px;
                font-size: 13px;
                color: rgba(148, 163, 184, 0.7);
                line-height: 1.6;
            }

            /* Form Styles */
            .form-label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #374151;
                margin-bottom: 6px;
            }

            .input-wrap {
                position: relative;
            }

            .input-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                width: 18px;
                height: 18px;
                color: #9ca3af;
                pointer-events: none;
                transition: color 0.2s;
            }

            .form-input {
                width: 100%;
                padding: 12px 14px 12px 44px;
                border: 2px solid #e5e7eb;
                border-radius: 14px;
                font-size: 14px;
                font-family: 'Inter', sans-serif;
                background: #f9fafb;
                color: #111827;
                transition: all 0.2s ease;
                outline: none;
                box-sizing: border-box;
            }

            .form-input::placeholder {
                color: #9ca3af;
            }

            .form-input:focus {
                border-color: #6366f1;
                background: #fff;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }

            .form-input:focus ~ .input-icon,
            .input-wrap:focus-within .input-icon {
                color: #6366f1;
            }

            .form-link {
                font-size: 13px;
                font-weight: 600;
                color: #6366f1;
                text-decoration: none;
                transition: color 0.2s;
            }

            .form-link:hover {
                color: #4f46e5;
            }

            .checkbox-wrap {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .checkbox-wrap input[type="checkbox"] {
                width: 18px;
                height: 18px;
                border-radius: 5px;
                border: 2px solid #d1d5db;
                accent-color: #6366f1;
                cursor: pointer;
            }

            .checkbox-wrap label {
                font-size: 14px;
                color: #374151;
                cursor: pointer;
                user-select: none;
            }

            .btn-submit {
                width: 100%;
                padding: 14px;
                border: none;
                border-radius: 14px;
                font-size: 15px;
                font-weight: 700;
                font-family: 'Inter', sans-serif;
                color: #fff;
                background: linear-gradient(135deg, #6366f1, #7c3aed);
                cursor: pointer;
                transition: all 0.25s ease;
                box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
                letter-spacing: 0.3px;
            }

            .btn-submit:hover {
                background: linear-gradient(135deg, #4f46e5, #6d28d9);
                box-shadow: 0 6px 25px rgba(99, 102, 241, 0.5);
                transform: translateY(-1px);
            }

            .btn-submit:active {
                transform: translateY(0);
            }

            .divider {
                position: relative;
                text-align: center;
                margin-top: 28px;
            }

            .divider::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 1px;
                background: #e5e7eb;
            }

            .divider span {
                position: relative;
                padding: 0 12px;
                background: #fff;
                font-size: 12px;
                color: #9ca3af;
                font-weight: 500;
                letter-spacing: 1px;
                text-transform: uppercase;
            }

            .form-group + .form-group {
                margin-top: 20px;
            }

            .form-header-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 6px;
            }

            /* Responsive */
            @media (max-width: 480px) {
                .login-card {
                    padding: 32px 24px;
                    border-radius: 20px;
                }
                .logo-text {
                    font-size: 22px;
                }
            }
        </style>
    </head>
    <body class="login-page">
        <!-- Aurora Background -->
        <div class="aurora-blob-1"></div>
        <div class="aurora-blob-2"></div>
        <div class="aurora-blob-3"></div>
        <div class="grid-pattern"></div>

        <div class="login-container">
            <!-- Logo -->
            <div class="logo-wrap">
                <a href="/" style="text-decoration: none;">
                    <div class="logo-icon" style="display: inline-flex;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 17h2a2 2 0 002-2V9a2 2 0 00-2-2H8a2 2 0 00-2 2v6a2 2 0 002 2zm6 0h2a2 2 0 002-2V9a2 2 0 00-2-2h-2a2 2 0 00-2 2v6a2 2 0 002 2zM3 21h18M3 3h18"></path>
                        </svg>
                    </div>
                    <div class="logo-text">Fleet<span>CRM</span></div>
                </a>
            </div>

            <!-- Form Card -->
            <div class="login-card">
                {{ $slot }}
            </div>

            <div class="login-footer">
                &copy; {{ date('Y') }} B2B Fleet Rental System.<br>All rights reserved.
            </div>
        </div>
    </body>
</html>
