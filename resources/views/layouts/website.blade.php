<!doctype html>
<html lang="en">

<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $storeDetails['storeName'] ?? 'Shashi Bala Baans Store' }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('website/media/favicon.png') }}">

    <!-- Dependency Styles -->
    <link rel="stylesheet" href="{{ asset('website/libs/bootstrap/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/feather-font/css/iconfont.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/icomoon-font/css/icomoon.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/font-awesome/css/font-awesome.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/wpbingofont/css/wpbingofont.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/elegant-icons/css/elegant.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/slick/css/slick.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/slick/css/slick-theme.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('website/libs/mmenu/css/mmenu.min.css') }}" type="text/css">

    <!-- Site Stylesheet -->
    <link rel="stylesheet" href="{{ asset('website/assets/css/app.css') }}" type="text/css">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="@yield('body_class', 'home')">
    <div id="page" class="hfeed page-wrapper">
        <header id="site-header" class="site-header header-v1 @yield('header_class')">
            <div class="header-mobile">
                <div class="section-padding">
                    <div class="section-container">
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-3 col-3 header-left">
                                <div class="navbar-header">
                                    <button type="button" id="show-megamenu" class="navbar-toggle"></button>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-6 col-6 header-center">
                                <div class="site-logo">
                                    <x-app-logo href="/" textColor="#FFFFFF" height="52px" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="header-mobile-fixed">
                    <!-- Shop -->
                    <div class="shop-page">
                        <a href="/shop"><i class="wpb-icon-shop"></i></a>
                    </div>

                    <!-- Login -->
                    <div class="my-account">
                        <div class="login-header">
                            <a href="/my-account"><i class="wpb-icon-user"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-desktop">
                <div class="header-wrapper">
                    <div class="section-padding">
                        <div class="section-container p-l-r">
                            <div class="row">
                                <div class="col-xl-3 col-lg-2 col-md-12 col-sm-12 col-12 header-left">
                                    <div class="site-logo">
                                        <x-app-logo href="/" textColor="#FFFFFF" height="52px" />
                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 text-center header-center">
                                    <div class="site-navigation">
                                        <nav id="main-navigation">
                                            <ul id="menu-main-menu" class="menu">
                                                <li class="level-0 menu-item current-menu-item">
                                                    <a href="/"><span class="menu-item-text">Home</span></a>
                                                </li>
                                                <li class="level-0 menu-item">
                                                    <a href="/about"><span class="menu-item-text">About</span></a>
                                                </li>
                                                <li class="level-0 menu-item">
                                                    <a href="/shop"><span class="menu-item-text">Shop</span></a>
                                                </li>
                                                <li class="level-0 menu-item">
                                                    <a href="/contact"><span class="menu-item-text">Contact</span></a>
                                                </li>
                                                <li class="level-0 menu-item">
                                                    <a href="/my-account"><span class="menu-item-text">My Account</span></a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-12 header-right">
                                    <div class="header-page-link">
                                        <!-- Login / Dashboard -->
                                        <div class="login-header">
                                            @auth
                                            <a href="{{ route('dashboard') }}">
                                                <span class="menu-item-text">Go to Dashboard</span>
                                            </a>
                                            @else
                                            <a class="active-login" href="/">Login</a>
                                            <div class="form-login-register">
                                                <div class="box-form-login">
                                                    <div class="active-login"></div>
                                                    <div class="box-content">
                                                        <div class="form-login active">
                                                            <form id="login_ajax" method="POST" action="{{ route('login.store') }}" class="login">
                                                                @csrf
                                                                <h2>Sign in</h2>
                                                                <div class="ajax-status-message status" style="display: none;">
                                                                    <span class="ajax-status-icon"></span>
                                                                    <span class="ajax-status-text"></span>
                                                                </div>
                                                                <div class="content">
                                                                    <div class="username">
                                                                        <input type="email" required="required" class="input-text" name="email" id="email" placeholder="Your Email">
                                                                    </div>
                                                                    <div class="password">
                                                                        <input class="input-text" required="required" type="password" name="password" id="password" placeholder="Password">
                                                                    </div>
                                                                    <div class="rememberme-lost">
                                                                        <div class="rememberme">
                                                                            <input name="rememberme" type="checkbox" id="rememberme" value="forever">
                                                                            <label for="rememberme" class="inline">Remember me</label>
                                                                        </div>
                                                                        <div class="lost_password">
                                                                            <a href="{{ route('password.request') }}">Lost your password?</a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="button-login">
                                                                        <input type="submit" class="button" name="login" value="Login">
                                                                    </div>
                                                                    <div class="button-next-reregister">Create An Account</div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <div class="form-register">
                                                            <form id="register_ajax" method="POST" action="{{ route('register.store') }}" class="register">
                                                                @csrf
                                                                <h2>REGISTER</h2>
                                                                <div class="ajax-status-message register-status" style="display: none;">
                                                                    <span class="ajax-status-icon"></span>
                                                                    <span class="ajax-status-text"></span>
                                                                </div>
                                                                <div class="content">
                                                                    <div class="name">
                                                                        <input type="text" class="input-text" placeholder="Name" name="name" id="reg_name" required>
                                                                    </div>
                                                                    <div class="email">
                                                                        <input type="email" class="input-text" placeholder="Email" name="email" id="reg_email" required>
                                                                    </div>
                                                                    <div class="password">
                                                                        <input type="password" class="input-text" placeholder="Password" name="password" id="reg_password" required>
                                                                    </div>
                                                                    <div class="password-confirm">
                                                                        <input type="password" class="input-text" placeholder="Confirm Password" name="password_confirmation" id="reg_password_confirmation" required>
                                                                    </div>
                                                                    <div class="button-register">
                                                                        <input type="submit" class="button" name="register" value="Register">
                                                                    </div>
                                                                    <div class="button-next-login">Already has an account</div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div id="site-main" class="site-main">
            @yield('content')
        </div>
        <footer id="site-footer" class="site-footer">
            <div class="footer">
                <div class="section-padding">
                    <div class="section-container">
                        <div class="block-widget-wrap">
                            <div class="row">
                                <div class="col-lg-3 col-md-6">
                                    <div class="block block-image">
                                        <x-app-logo href="/" textColor="#FFFFFF" height="48px" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="block block-menu">
                                        <h2 class="block-title">Contact Us</h2>
                                        <div class="block-content">
                                            <ul>
                                                <li>
                                                    <a href="/contact">{{ $storeDetails['storeAddress'] }}</a>
                                                </li>
                                                @foreach($storeDetails['storePhonesArray'] ?? array_filter(array_map('trim', preg_split('/[,|]/', (string) ($storeDetails['storePhone'] ?? '')))) as $phone)
                                                <li>
                                                    <a href="/contact">{{ $phone }}</a>
                                                </li>
                                                @endforeach
                                                <li>
                                                    <a href="/contact">{{ $storeDetails['storeEmail'] }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="block block-menu">
                                        <h2 class="block-title">Services</h2>
                                        <div class="block-content">
                                            <ul>
                                                <li>
                                                    <a href="/about">Sale</a>
                                                </li>
                                                <li>
                                                    <a href="/about">Quick Ship</a>
                                                </li>
                                                <li>
                                                    <a href="/about">New Designs</a>
                                                </li>
                                                <li>
                                                    <a href="/about">Accidental Fabric Protection</a>
                                                </li>
                                                <li>
                                                    <a href="/about">Furniture Care</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <div class="block block-newsletter">
                                        <h2 class="block-title">Newsletter</h2>
                                        <div class="block-content">
                                            <div class="newsletter-text">Enter your email below to be the first to know about new collections and product launches.</div>
                                            <form action="" method="post" class="newsletter-form">
                                                <input type="email" name="your-email" value="" size="40" placeholder="Email address">
                                                <span class="btn-submit">
                                                    <input type="submit" value="Subscribe">
                                                </span>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="section-padding">
                    <div class="section-container">
                        <div class="block-widget-wrap">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="footer-left">
                                        <p class="copyright">Copyright © {{ date('Y') }} {{ $storeDetails['storeName'] ?? 'Shashi Bala Baans Store' }}. Prop. {{ $storeDetails['storeProprietor'] ?? 'Ashok Kumar' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="footer-right">
                                        <div class="block block-social">
                                            <ul class="social-link">
                                                <li><a href="/"><i class="fa fa-twitter"></i></a></li>
                                                <li><a href="/"><i class="fa fa-instagram"></i></a></li>
                                                <li><a href="/"><i class="fa fa-facebook"></i></a></li>
                                                <li><a href="/"><i class="fa fa-youtube"></i></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Back Top button -->
    <div class="back-top button-show">
        <i class="arrow_carrot-up"></i>
    </div>
    <!-- Page Loader -->
    <div class="page-preloader">
        <div class="loader">
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Dependency Scripts -->
    <script src="{{ asset('website/libs/popper/js/popper.min.js') }}"></script>
    <script src="{{ asset('website/libs/jquery/js/jquery.min.js') }}"></script>
    <script src="{{ asset('website/libs/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('website/libs/slick/js/slick.min.js') }}"></script>
    <script src="{{ asset('website/libs/countdown/js/jquery.countdown.min.js') }}"></script>
    <script src="{{ asset('website/libs/mmenu/js/jquery.mmenu.all.min.js') }}"></script>
    <script src="{{ asset('website/libs/elevatezoom/js/jquery.elevatezoom.js') }}"></script>
    <script>
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.elevateZoom === 'undefined') {
            jQuery.fn.elevateZoom = function() {
                return this;
            };
        }
    </script>

    <!-- Site Scripts -->
    <script src="{{ asset('website/assets/js/app.js') }}"></script>

    <style>
        .ajax-status-message {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
            text-align: left;
        }

        .ajax-status-message.error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .ajax-status-message.success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .ajax-status-message.processing {
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .ajax-status-icon {
            margin-right: 12px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .ajax-status-icon svg {
            width: 20px;
            height: 20px;
        }

        @keyframes ajax-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .ajax-spin-icon {
            animation: ajax-spin 1s linear infinite;
        }

        /* Remove scrollbar from login modal */
        .form-login-register .box-form-login .box-content {
            max-height: none !important;
            overflow: visible !important;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .form-login-register .box-form-login .box-content::-webkit-scrollbar {
            display: none;
        }

        /* Ensure SweetAlert shows above everything */
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errorIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>`;
            const successIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>`;
            const processingIcon = `<svg class="ajax-spin-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

            function showStatus(statusEl, type, message) {
                statusEl.style.display = 'flex';
                statusEl.className = 'ajax-status-message ' + type + (statusEl.classList.contains('register-status') ? ' register-status' : ' status');
                const iconEl = statusEl.querySelector('.ajax-status-icon');
                const textEl = statusEl.querySelector('.ajax-status-text');
                if (type === 'error') iconEl.innerHTML = errorIcon;
                else if (type === 'success') iconEl.innerHTML = successIcon;
                else iconEl.innerHTML = processingIcon;
                textEl.innerText = message;
            }

            function handleAjaxForm(formId, statusSelector) {
                const form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const status = form.querySelector(statusSelector);
                    showStatus(status, 'processing', 'Processing...');

                    fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (response.ok) {
                                showStatus(status, 'success', 'Success! Redirecting...');
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'You have logged in successfully!',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500,
                                        allowOutsideClick: false
                                    }).then(() => {
                                        window.location.href = "{{ url(config('fortify.home', '/')) }}";
                                    });
                                } else {
                                    alert('Success! Redirecting...');
                                    window.location.href = "{{ url(config('fortify.home', '/')) }}";
                                }
                            } else {
                                return response.json().then(data => {
                                    let errorMsg = 'An error occurred.';
                                    if (data.errors) {
                                        errorMsg = Object.values(data.errors)[0][0];
                                    } else {
                                        errorMsg = data.message || 'An error occurred.';
                                    }
                                    showStatus(status, 'error', errorMsg);
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: errorMsg,
                                            icon: 'error',
                                            confirmButtonText: 'OK',
                                            confirmButtonColor: '#d33'
                                        });
                                    } else {
                                        alert('Error: ' + errorMsg);
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            showStatus(status, 'error', 'A network error occurred. Please try again.');
                            Swal.fire({
                                title: 'Network Error',
                                text: 'A network error occurred. Please try again.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        });
                });
            }

            handleAjaxForm('login_ajax', '.status');
            handleAjaxForm('register_ajax', '.register-status');
        });
    </script>
</body>

</html>