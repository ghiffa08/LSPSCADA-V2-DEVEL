<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation - Validate Your Account</title>
    <style>
        /* Reset CSS */
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f7f7f7;
        }

        /* Container Styling */
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        /* Header Styling */
        .email-header {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 50%, #1976d2 100%);
            padding: 0;
            position: relative;
            overflow: hidden;
        }

        .email-header svg {
            display: block;
            margin: 0 auto;
            max-width: 100%;
        }

        /* Content Styling */
        .email-content {
            padding: 35px 40px;
            position: relative;
        }

        .email-greeting {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 18px;
            color: #0d47a1;
            letter-spacing: 0.3px;
        }

        .email-body {
            font-size: 16px;
            margin-bottom: 25px;
            color: #444;
            line-height: 1.7;
        }

        /* Button styling */
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            margin: 15px 0;
            text-align: center;
            box-shadow: 0 4px 8px rgba(13, 71, 161, 0.2);
            transition: all 0.3s ease;
        }

        .btn:hover {
            box-shadow: 0 6px 12px rgba(13, 71, 161, 0.3);
            transform: translateY(-2px);
        }

        /* Detail Information */
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d47a1;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .info-box::before {
            content: '';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 70px;
            height: 70px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='70' height='70'%3E%3Cpath fill='none' d='M0 0h24v24H0z'/%3E%3Cpath d='M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z' fill='rgba(13, 71, 161, 0.1)'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.5;
            z-index: 0;
        }

        /* Footer Styling */
        .email-footer {
            background-color: #f8f9fa;
            padding: 30px 0;
            text-align: center;
            font-size: 14px;
            color: #777;
            border-top: 1px solid #eee;
            position: relative;
        }

        .footer-wave {
            position: absolute;
            top: -2px;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #0d47a1, #1976d2, #0d47a1);
        }

        .email-footer p {
            margin: 5px 0;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #0d47a1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: #1976d2;
        }

        .security-note {
            background-color: #e8f5e9;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #2e7d32;
            border-left: 3px solid #43a047;
        }

        .signature {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-style: italic;
            color: #888;
        }

        /* Icon styling */
        .icon {
            vertical-align: middle;
            margin-right: 5px;
        }

        /* Responsive Design */
        @media screen and (max-width: 600px) {
            .email-content {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with SVG Banner -->
        <div class="email-header">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 200" width="650" height="150">
                <!-- Background gradient -->
                <defs>
                    <linearGradient id="headerGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1a237e" />
                        <stop offset="50%" stop-color="#0d47a1" />
                        <stop offset="100%" stop-color="#1976d2" />
                    </linearGradient>

                    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="#000" flood-opacity="0.3" />
                    </filter>
                </defs>

                <!-- Main background -->
                <rect width="800" height="200" fill="url(#headerGradient)" />

                <!-- Decorative elements -->
                <circle cx="150" cy="50" r="100" fill="#ffffff" fill-opacity="0.05" />
                <circle cx="650" cy="150" r="100" fill="#ffffff" fill-opacity="0.05" />

                <!-- Abstract line pattern -->
                <path d="M0,200 C200,150 400,220 800,120" stroke="#ffffff" stroke-width="2" stroke-opacity="0.1" fill="none" />
                <path d="M0,160 C250,200 550,100 800,170" stroke="#ffffff" stroke-width="1.5" stroke-opacity="0.07" fill="none" />
                <path d="M0,80 C150,120 450,40 800,100" stroke="#ffffff" stroke-width="1" stroke-opacity="0.05" fill="none" />

                <!-- Email verification illustration -->
                <g transform="translate(50, 60)">
                    <!-- Envelope design -->
                    <rect x="0" y="0" width="100" height="70" rx="5" fill="#ffffff" filter="url(#shadow)" />
                    <path d="M0,0 L50,40 L100,0" stroke="#3949ab" stroke-width="2" stroke-opacity="0.7" fill="none" />
                    <path d="M0,70 L35,35" stroke="#3949ab" stroke-width="2" stroke-opacity="0.7" fill="none" />
                    <path d="M100,70 L65,35" stroke="#3949ab" stroke-width="2" stroke-opacity="0.7" fill="none" />


                </g>

                <!-- Title and subtitle -->
                <g transform="translate(200, 80)">
                    <text x="0" y="0" font-family="'Segoe UI', sans-serif" font-size="32" font-weight="bold" fill="#ffffff" filter="url(#shadow)">ACCOUNT ACTIVATION</text>
                    <text x="0" y="45" font-family="'Segoe UI', sans-serif" font-size="20" font-weight="normal" fill="#ffffff" filter="url(#shadow)">PLEASE VERIFY YOUR EMAIL ADDRESS</text>
                    <rect x="0" y="65" width="150" height="4" rx="2" fill="#ffffff" />
                </g>

                <!-- Decorative dots -->
                <g fill="#ffffff" fill-opacity="0.2">
                    <circle cx="50" cy="170" r="2" />
                    <circle cx="70" cy="180" r="2" />
                    <circle cx="90" cy="170" r="2" />
                    <circle cx="110" cy="180" r="2" />
                    <circle cx="130" cy="170" r="2" />

                    <circle cx="650" cy="30" r="2" />
                    <circle cx="670" cy="40" r="2" />
                    <circle cx="690" cy="30" r="2" />
                    <circle cx="710" cy="40" r="2" />
                    <circle cx="730" cy="30" r="2" />
                </g>

                <!-- Light beam effects -->
                <path d="M400,0 L460,200" stroke="#ffffff" stroke-width="40" stroke-opacity="0.03" />
                <path d="M600,0 L500,200" stroke="#ffffff" stroke-width="60" stroke-opacity="0.03" />
                <path d="M150,0 L200,200" stroke="#ffffff" stroke-width="30" stroke-opacity="0.03" />
            </svg>
        </div>

        <!-- Content Area -->
        <div class="email-content">
            <?= $content ?>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-wave"></div>
            <div class="social-links">
                <a href="#">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    Website
                </a> |
                <a href="#">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                    Instagram
                </a> |
                <a href="#">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                    </svg>
                    Facebook
                </a>
            </div>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>If you need assistance, please contact our support team.</p>
            <p class="signature">&copy; <?= date('Y') ?> LSP-P1 SMK Negeri 2 Kuningan. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>