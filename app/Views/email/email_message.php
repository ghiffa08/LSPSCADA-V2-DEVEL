<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Uji Kompetensi Keahlian - LSP SMKN 2 Kuningan</title>
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

        /* Detail Information */
        .detail-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d47a1;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .detail-box::before {
            content: '';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 70px;
            height: 70px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='70' height='70'%3E%3Cpath fill='none' d='M0 0h24v24H0z'/%3E%3Cpath d='M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z' fill='rgba(13, 71, 161, 0.1)'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-size: contain;
            opacity: 0.5;
            z-index: 0;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            position: relative;
            z-index: 1;
        }

        .detail-table td {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-table td:first-child {
            width: 35%;
            font-weight: 600;
            color: #444;
        }

        .detail-table td:nth-child(2) {
            width: 5%;
            text-align: center;
            color: #888;
        }

        .detail-table tr:last-child td {
            border-bottom: none;
        }

        /* Badge Styling */
        .id-badge {
            display: inline-block;
            background-color: #e3f2fd;
            color: #0d47a1;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(13, 71, 161, 0.15);
        }

        /* Process Steps */
        .process-steps {
            margin: 35px 0;
            position: relative;
            background: linear-gradient(to bottom, rgba(232, 234, 246, 0.4) 0%, rgba(232, 234, 246, 0) 100%);
            padding: 30px 25px;
            border-radius: 10px;
        }

        .process-steps h3 {
            font-size: 20px;
            color: #0d47a1;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 12px;
            display: inline-block;
        }

        .process-steps h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, #0d47a1 0%, #2196f3 100%);
            border-radius: 3px;
        }

        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
        }

        .steps-list:before {
            content: '';
            position: absolute;
            top: 0;
            left: 15px;
            height: 100%;
            width: 2px;
            background: linear-gradient(to bottom, #0d47a1 0%, #90caf9 100%);
            z-index: 0;
        }

        .steps-list li {
            position: relative;
            padding: 0 0 25px 45px;
            margin-left: 0;
        }

        .steps-list li:last-child {
            padding-bottom: 0;
        }

        .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            border-radius: 50%;
            color: white;
            text-align: center;
            font-size: 16px;
            line-height: 32px;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(13, 71, 161, 0.3);
        }

        .step-content {
            color: #444;
            font-size: 15px;
            padding: 6px 0;
        }

        .step-status {
            font-size: 13px;
            margin-top: 5px;
            color: #0d47a1;
            background-color: rgba(144, 202, 249, 0.2);
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
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

            .detail-table td:first-child {
                width: 40%;
            }

            .process-steps {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header dengan SVG Banner Modern -->
        <div class="email-header">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 250" width="650" height="180">
                <!-- Background gradient -->
                <defs>
                    <linearGradient id="headerGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#1a237e" />
                        <stop offset="50%" stop-color="#0d47a1" />
                        <stop offset="100%" stop-color="#1976d2" />
                    </linearGradient>

                    <linearGradient id="certGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ffeb3b" />
                        <stop offset="100%" stop-color="#ffc107" />
                    </linearGradient>

                    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="#000" flood-opacity="0.3" />
                    </filter>
                </defs>

                <!-- Main background -->
                <rect width="800" height="250" fill="url(#headerGradient)" />

                <!-- Decorative elements -->
                <circle cx="150" cy="50" r="100" fill="#ffffff" fill-opacity="0.05" />
                <circle cx="650" cy="200" r="150" fill="#ffffff" fill-opacity="0.05" />

                <!-- Abstract line pattern -->
                <path d="M0,250 C200,180 400,280 800,150" stroke="#ffffff" stroke-width="2" stroke-opacity="0.1" fill="none" />
                <path d="M0,200 C250,250 550,120 800,220" stroke="#ffffff" stroke-width="1.5" stroke-opacity="0.07" fill="none" />
                <path d="M0,100 C150,150 450,50 800,130" stroke="#ffffff" stroke-width="1" stroke-opacity="0.05" fill="none" />

                <!-- Modern school illustration -->
                <g transform="translate(50, 70)">
                    <!-- Certificate design -->
                    <rect x="0" y="0" width="110" height="140" rx="5" fill="#ffffff" filter="url(#shadow)" />
                    <rect x="5" y="5" width="100" height="130" rx="3" fill="#f5f5f5" />
                    <path d="M15,25 L95,25 M15,45 L75,45 M15,65 L95,65 M15,85 L60,85" stroke="#3949ab" stroke-width="2" stroke-opacity="0.7" stroke-linecap="round" />

                    <!-- Medal/Badge -->
                    <circle cx="78" cy="110" r="22" fill="url(#certGradient)" />
                    <circle cx="78" cy="110" r="18" fill="#ffffff" fill-opacity="0.25" />
                    <path d="M78,98 L82,106 L91,107 L84,113 L86,122 L78,118 L70,122 L72,113 L65,107 L74,106 Z" fill="#ffffff" />
                </g>

                <!-- Title and subtitle -->
                <g transform="translate(180, 90)">
                    <text x="0" y="0" font-family="'Segoe UI', sans-serif" font-size="32" font-weight="bold" fill="#ffffff" filter="url(#shadow)">SERTIFIKASI PROFESI</text>
                    <text x="0" y="45" font-family="'Segoe UI', sans-serif" font-size="40" font-weight="bold" fill="#ffffff" filter="url(#shadow)">LSP-P1 SMK NEGERI 2</text>
                    <text x="0" y="90" font-family="'Segoe UI', sans-serif" font-size="40" font-weight="bold" fill="#ffffff" filter="url(#shadow)">KUNINGAN</text>
                    <rect x="0" y="110" width="150" height="5" rx="2.5" fill="#ffffff" />
                </g>

                <!-- Decorative dots -->
                <g fill="#ffffff" fill-opacity="0.2">
                    <circle cx="50" cy="200" r="2" />
                    <circle cx="70" cy="210" r="2" />
                    <circle cx="90" cy="200" r="2" />
                    <circle cx="110" cy="210" r="2" />
                    <circle cx="130" cy="200" r="2" />

                    <circle cx="650" cy="30" r="2" />
                    <circle cx="670" cy="40" r="2" />
                    <circle cx="690" cy="30" r="2" />
                    <circle cx="710" cy="40" r="2" />
                    <circle cx="730" cy="30" r="2" />
                </g>

                <!-- Light beam effects -->
                <path d="M400,0 L460,250" stroke="#ffffff" stroke-width="40" stroke-opacity="0.03" />
                <path d="M600,0 L500,250" stroke="#ffffff" stroke-width="60" stroke-opacity="0.03" />
                <path d="M150,0 L200,250" stroke="#ffffff" stroke-width="30" stroke-opacity="0.03" />
            </svg>
        </div>

        <!-- Content Area -->
        <div class="email-content">
            <div class="email-greeting">
                Hai <?= $name ?>,
            </div>

            <div class="email-body">
                <p>Terima kasih telah mendaftar untuk mengikuti <strong>Uji Kompetensi Keahlian</strong> di LSP-P1 SMK Negeri 2 Kuningan!</p>

                <p>Kami dengan senang hati memberitahukan bahwa data pendaftaran Anda telah kami terima dan saat ini sedang dalam proses validasi oleh tim kami.</p>
            </div>

            <!-- Detail Informasi -->
            <div class="detail-box">
                <table class="detail-table">
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>:</td>
                        <td><?= $name ?></td>
                    </tr>
                    <tr>
                        <td>ID Pendaftaran</td>
                        <td>:</td>
                        <td><span class="id-badge"><?= $id ?></span></td>
                    </tr>
                    <tr>
                        <td>Status Pendaftaran</td>
                        <td>:</td>
                        <td>Dalam Proses Validasi</td>
                    </tr>
                    <tr>
                        <td>Tanggal Pendaftaran</td>
                        <td>:</td>
                        <td><?= date('d F Y') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Informasi Proses -->
            <div class="process-steps">
                <h3>Alur Sertifikasi Profesi</h3>
                <ul class="steps-list">
                    <li>
                        <div class="step-number">1</div>
                        <div class="step-content">Mengisi form pendaftaran sertifikasi profesi LSP-P1 SMK Negeri 2 Kuningan
                            <div class="step-status">✓ Selesai</div>
                        </div>
                    </li>
                    <li>
                        <div class="step-number">2</div>
                        <div class="step-content">Data pendaftaran sertifikasi Anda akan diverifikasi oleh Admin
                            <div class="step-status">⟳ Sedang Diproses</div>
                        </div>
                    </li>
                    <li>
                        <div class="step-number">3</div>
                        <div class="step-content">Mengisi asesmen mandiri</div>
                    </li>
                    <li>
                        <div class="step-number">4</div>
                        <div class="step-content">Jawaban asesmen mandiri Anda akan divalidasi oleh Asesor</div>
                    </li>
                    <li>
                        <div class="step-number">5</div>
                        <div class="step-content">Informasi status pendaftaran dan proses asesmen selanjutnya</div>
                    </li>
                </ul>
            </div>

            <div class="email-body">
                <p>Mohon tunggu beberapa saat sementara kami melakukan validasi data pendaftaran. Jika ada informasi yang perlu diperbaiki, kami akan segera menghubungi Anda melalui email ini.</p>

                <p>Apabila Anda memiliki pertanyaan lebih lanjut, silakan hubungi kami melalui WhatsApp di nomor <strong>081234567890</strong> atau email <strong>lsp@smkn2kuningan.sch.id</strong>.</p>

                <a href="#" class="btn">Cek Status Pendaftaran</a>

                <p>Terima kasih atas kesabaran dan kerjasamanya.</p>

                <p>Salam,</p>
                <p><strong>Tim LSP-P1 SMK Negeri 2 Kuningan</strong></p>
            </div>
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
            <p>Email ini dikirimkan secara otomatis. Mohon tidak membalas email ini.</p>
            <p>Jl. Sukamulya No.77, Kuningan, Jawa Barat 45511</p>
            <p class="signature">&copy; <?= date('Y') ?> LSP-P1 SMK Negeri 2 Kuningan. Hak cipta dilindungi undang-undang.</p>
        </div>
    </div>
</body>

</html>