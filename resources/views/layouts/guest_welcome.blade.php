<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Azania Bank Escrow')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 15px 0;
        }
        .btn-primary {
            background-color: #3399BB;
            border-color: #3399BB;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #2980a5;
            border-color: #2980a5;
        }
        .btn-outline-light:hover {
            color: #3399BB;
        }
        footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e5e5e5;
            padding: 20px 0;
            margin-top: 50px;
        }
        .footer-links {
            list-style: none;
            padding-left: 0;
        }
        .footer-links li {
            display: inline-block;
            margin-right: 15px;
        }
        .footer-links a {
            color: #6c757d;
            text-decoration: none;
        }
        .footer-links a:hover {
            color: #3399BB;
            text-decoration: underline;
        }
    </style>
    @yield('styles')
</head>
<body>
    <header>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="/" class="d-flex align-items-center text-decoration-none">
                    <img src="{{ asset('images/azania-logo.png') }}" alt="Azania Bank" class="img-fluid" style="max-height: 50px;">
                </a>
                <div>
                    <!-- Removed Sign In and Create Account buttons from here -->
                </div>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} Azania Bank Escrow Service. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <ul class="footer-links mb-0">
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>