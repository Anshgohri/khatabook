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
                        <div class="row align-items-center">
                            <div class="col-3 col-sm-3 header-left">
                                <div class="navbar-header">
                                    <button type="button" id="show-megamenu" class="navbar-toggle" aria-label="Toggle navigation"></button>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 header-center text-center">
                                <div class="site-logo">
                                    <x-app-logo href="/" textColor="#FFFFFF" height="44px" />
                                </div>
                            </div>
                            <div class="col-3 col-sm-3 header-right text-right">
                                <div class="header-page-link">
                                    <div class="login-header">
                                        @auth
                                        <a href="{{ route('dashboard') }}" class="mobile-user-link" title="Dashboard">
                                            <i class="wpb-icon-user"></i>
                                        </a>
                                        @else
                                        <a href="javascript:void(0);" class="active-login mobile-user-link" title="Login / Register">
                                            <i class="wpb-icon-user"></i>
                                        </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="header-mobile-fixed">
                    <!-- Home -->
                    <div class="home-page {{ request()->is('/') ? 'active' : '' }}">
                        <a href="/">
                            <i class="fa fa-home"></i>
                            <span>Home</span>
                        </a>
                    </div>

                    <!-- Shop -->
                    <div class="shop-page {{ request()->is('shop*') || request()->is('catalog*') ? 'active' : '' }}">
                        <a href="/shop">
                            <i class="wpb-icon-shop"></i>
                            <span>Shop</span>
                        </a>
                    </div>

                    <!-- Contact -->
                    <div class="contact-page {{ request()->is('contact*') ? 'active' : '' }}">
                        <a href="/contact">
                            <i class="fa fa-phone"></i>
                            <span>Contact</span>
                        </a>
                    </div>

                    <!-- Login / Account -->
                    <div class="my-account">
                        <div class="login-header">
                            @auth
                            <a href="{{ route('dashboard') }}">
                                <i class="wpb-icon-user"></i>
                                <span>Dashboard</span>
                            </a>
                            @else
                            <a href="javascript:void(0);" class="active-login">
                                <i class="wpb-icon-user"></i>
                                <span>Login</span>
                            </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-desktop">
                <div class="header-wrapper">
                    <div class="section-padding">
                        <div class="section-container p-l-r">
                            <div class="row align-items-center">
                                <div class="col-xl-3 col-lg-2 col-md-12 col-sm-12 col-12 header-left">
                                    <div class="site-logo">
                                        <x-app-logo href="/" textColor="#FFFFFF" height="52px" />
                                    </div>
                                </div>

                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12 text-center header-center">
                                    <div class="site-navigation">
                                        <nav id="main-navigation">
                                            <ul id="menu-main-menu" class="menu">
                                                <li class="level-0 menu-item {{ request()->is('/') ? 'current-menu-item' : '' }}">
                                                    <a href="/"><span class="menu-item-text">Home</span></a>
                                                </li>
                                                <li class="level-0 menu-item {{ request()->is('about*') ? 'current-menu-item' : '' }}">
                                                    <a href="/about"><span class="menu-item-text">About</span></a>
                                                </li>
                                                <li class="level-0 menu-item {{ request()->is('shop*') || request()->is('catalog*') ? 'current-menu-item' : '' }}">
                                                    <a href="/shop"><span class="menu-item-text">Shop</span></a>
                                                </li>
                                                <li class="level-0 menu-item {{ request()->is('contact*') ? 'current-menu-item' : '' }}">
                                                    <a href="/contact"><span class="menu-item-text">Contact</span></a>
                                                </li>
                                                @auth
                                                <li class="level-0 menu-item">
                                                    <a href="{{ route('dashboard') }}"><span class="menu-item-text">Dashboard</span></a>
                                                </li>
                                                @else
                                                <li class="level-0 menu-item">
                                                    <a href="javascript:void(0);" class="active-login"><span class="menu-item-text">Login / Register</span></a>
                                                </li>
                                                @endauth
                                            </ul>
                                        </nav>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 col-12 header-right">
                                    <div class="header-page-link">
                                        <!-- Login / Dashboard -->
                                        <div class="login-header">
                                            @auth
                                            <a href="{{ route('dashboard') }}" class="desktop-login-link">
                                                <span class="menu-item-text">Go to Dashboard</span>
                                            </a>
                                            @else
                                            <a class="active-login desktop-login-link" href="javascript:void(0);">
                                                <i class="wpb-icon-user mr-1"></i>
                                                <span class="menu-item-text">Login / Register</span>
                                            </a>
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

        @guest
        <!-- Global Responsive Login & Register Modal -->
        <div class="form-login-register" id="global-auth-modal" role="dialog" aria-modal="true" aria-labelledby="modal-auth-heading">
            <div class="box-form-login">
                <button type="button" class="close-modal-btn active-login" aria-label="Close dialog">
                    <span aria-hidden="true">&times;</span>
                </button>
                <div class="box-content">
                    <div class="form-login active">
                        <form id="login_ajax" method="POST" action="{{ route('login.store') }}" class="login">
                            @csrf
                            <h2 id="modal-auth-heading">Sign In</h2>
                            <p class="modal-subtitle">Welcome back! Please enter your details.</p>
                            <div class="ajax-status-message status" style="display: none;">
                                <span class="ajax-status-icon"></span>
                                <span class="ajax-status-text"></span>
                            </div>
                            <div class="content">
                                <div class="username form-group">
                                    <label for="modal_email">Email Address <span class="required">*</span></label>
                                    <input type="email" required="required" class="input-text" name="email" id="modal_email" placeholder="name@example.com" autocomplete="username">
                                </div>
                                <div class="password form-group">
                                    <label for="modal_password">Password <span class="required">*</span></label>
                                    <div class="password-wrapper">
                                        <input class="input-text" required="required" type="password" name="password" id="modal_password" placeholder="••••••••" autocomplete="current-password">
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('modal_password', this)" aria-label="Toggle password visibility">
                                            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="rememberme-lost">
                                    <div class="rememberme">
                                        <input name="rememberme" type="checkbox" id="modal_rememberme" value="forever">
                                        <label for="modal_rememberme" class="inline">Remember me</label>
                                    </div>
                                    <div class="lost_password">
                                        <a href="{{ route('password.request') }}">Forgot password?</a>
                                    </div>
                                </div>
                                <div class="button-login">
                                    <input type="submit" class="button btn-block" name="login" value="Sign In">
                                </div>
                                <div class="switch-form text-center">
                                    <span>Don't have an account?</span>
                                    <a href="javascript:void(0);" class="button-next-reregister">Create an account</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="form-register">
                        <form id="register_ajax" method="POST" action="{{ route('register.store') }}" class="register">
                            @csrf
                            <h2>Create Account</h2>
                            <p class="modal-subtitle">Register to manage your orders with ease.</p>
                            <div class="ajax-status-message register-status" style="display: none;">
                                <span class="ajax-status-icon"></span>
                                <span class="ajax-status-text"></span>
                            </div>
                            <div class="content">
                                <div class="name form-group">
                                    <label for="modal_reg_name">Full Name <span class="required">*</span></label>
                                    <input type="text" class="input-text" placeholder="Your Full Name" name="name" id="modal_reg_name" required autocomplete="name">
                                </div>
                                <div class="email form-group">
                                    <label for="modal_reg_email">Email Address <span class="required">*</span></label>
                                    <input type="email" class="input-text" placeholder="name@example.com" name="email" id="modal_reg_email" required autocomplete="email">
                                </div>
                                <div class="password form-group">
                                    <label for="modal_reg_password">Password <span class="required">*</span></label>
                                    <div class="password-wrapper">
                                        <input type="password" class="input-text" placeholder="At least 8 characters" name="password" id="modal_reg_password" required autocomplete="new-password">
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('modal_reg_password', this)" aria-label="Toggle password visibility">
                                            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="password-confirm form-group">
                                    <label for="modal_reg_password_confirmation">Confirm Password <span class="required">*</span></label>
                                    <div class="password-wrapper">
                                        <input type="password" class="input-text" placeholder="Re-enter password" name="password_confirmation" id="modal_reg_password_confirmation" required autocomplete="new-password">
                                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('modal_reg_password_confirmation', this)" aria-label="Toggle password visibility">
                                            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="button-register">
                                    <input type="submit" class="button btn-block" name="register" value="Register Now">
                                </div>
                                <div class="switch-form text-center">
                                    <span>Already have an account?</span>
                                    <a href="javascript:void(0);" class="button-next-login">Sign in</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endguest

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
        /* ========================================================
           GLOBAL RESPONSIVE RESET & CONTAINER ADJUSTMENTS
           ======================================================== */
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
        }

        body.modal-open {
            overflow: hidden !important;
        }

        img, video, iframe {
            max-width: 100%;
            height: auto;
        }

        /* Prevent header logo distortion on mobile */
        .site-header .site-logo img,
        .site-header .site-logo svg {
            max-height: 44px;
            width: auto;
            max-width: 100%;
            display: inline-block;
        }

        /* Top Mobile Header bar */
        .site-header .header-mobile .section-container > .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-user-link {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff !important;
            font-size: 18px;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .site-header.header-v1:not(.color-white) .mobile-user-link {
            background: rgba(15, 23, 42, 0.08);
            color: #0f172a !important;
        }

        .mobile-user-link:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.3);
        }

        /* Desktop Login Link */
        .desktop-login-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            color: inherit;
            text-decoration: none !important;
            transition: opacity 0.2s;
        }

        .desktop-login-link:hover {
            opacity: 0.85;
        }

        /* ========================================================
           FIXED BOTTOM NAVIGATION (Mobile App Experience)
           ======================================================== */
        @media (max-width: 991px) {
            body {
                padding-bottom: 64px !important;
            }

            .site-header .header-mobile .header-mobile-fixed {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                height: 60px !important;
                background: #ffffff !important;
                border-top: 1px solid #e2e8f0 !important;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.07) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-around !important;
                z-index: 99999 !important;
                padding: 0 4px !important;
                margin: 0 !important;
            }

            .header-mobile-fixed > div {
                flex: 1 !important;
                text-align: center !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .header-mobile-fixed a {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                color: #64748b !important;
                text-decoration: none !important;
                font-size: 11px !important;
                font-weight: 500 !important;
                padding: 6px 2px !important;
                line-height: 1.2 !important;
                transition: color 0.15s ease !important;
            }

            .header-mobile-fixed a i {
                font-size: 18px !important;
                line-height: 1 !important;
                margin-bottom: 3px !important;
                display: block !important;
            }

            .header-mobile-fixed > div.active a,
            .header-mobile-fixed a:hover {
                color: #0f172a !important;
                font-weight: 600 !important;
            }

            .back-top {
                bottom: 74px !important;
            }
        }

        /* ========================================================
           GLOBAL RESPONSIVE LOGIN / REGISTER MODAL
           ======================================================== */
        .form-login-register {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: rgba(15, 23, 42, 0.75) !important;
            backdrop-filter: blur(6px) !important;
            -webkit-backdrop-filter: blur(6px) !important;
            z-index: 9999999 !important;
            display: none !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 16px !important;
            box-sizing: border-box !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
        }

        .form-login-register.active {
            display: flex !important;
        }

        .form-login-register .box-form-login {
            position: relative !important;
            top: auto !important;
            left: auto !important;
            right: auto !important;
            bottom: auto !important;
            transform: none !important;
            -webkit-transform: none !important;
            margin: auto !important;
            width: 100% !important;
            max-width: 440px !important;
            max-height: calc(100vh - 32px) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            background: #ffffff !important;
            border-radius: 16px !important;
            padding: 30px 24px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important;
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            box-sizing: border-box !important;
        }

        /* Modal Close Button */
        .form-login-register .box-form-login .close-modal-btn {
            position: absolute !important;
            top: 14px !important;
            right: 14px !important;
            width: 34px !important;
            height: 34px !important;
            border-radius: 50% !important;
            background: #f1f5f9 !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 24px !important;
            line-height: 1 !important;
            color: #64748b !important;
            cursor: pointer !important;
            z-index: 20 !important;
            transition: all 0.2s ease !important;
            padding: 0 !important;
        }

        .form-login-register .box-form-login .close-modal-btn:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
            transform: scale(1.08);
        }

        /* Modal Typography */
        .form-login-register .box-form-login h2 {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 0 4px 0 !important;
            text-transform: none !important;
            letter-spacing: -0.3px;
        }

        .form-login-register .box-form-login .modal-subtitle {
            font-size: 13.5px !important;
            color: #64748b !important;
            margin-bottom: 20px !important;
            line-height: 1.4 !important;
        }

        /* Form Controls */
        .form-login-register .box-form-login .form-group {
            margin-bottom: 15px !important;
        }

        .form-login-register .box-form-login label {
            display: block !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            margin-bottom: 5px !important;
            text-align: left !important;
        }

        .form-login-register .box-form-login input.input-text {
            width: 100% !important;
            height: 44px !important;
            padding: 0 14px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            box-sizing: border-box !important;
            transition: border-color 0.2s, background 0.2s;
        }

        .form-login-register .box-form-login input.input-text:focus {
            border-color: #0f172a !important;
            background: #ffffff !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08) !important;
        }

        /* Remember Me & Lost Password */
        .form-login-register .box-form-login .rememberme-lost {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin: 14px 0 18px !important;
            font-size: 13px !important;
        }

        .form-login-register .box-form-login .rememberme {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin: 0 !important;
        }

        .form-login-register .box-form-login .rememberme input {
            width: 16px !important;
            height: 16px !important;
            margin: 0 !important;
            accent-color: #0f172a !important;
            cursor: pointer !important;
        }

        .form-login-register .box-form-login .rememberme label {
            margin: 0 !important;
            cursor: pointer !important;
            font-weight: 500 !important;
            color: #475569 !important;
        }

        .form-login-register .box-form-login .lost_password a {
            color: #2563eb !important;
            text-decoration: none !important;
            font-weight: 500 !important;
        }

        .form-login-register .box-form-login .lost_password a:hover {
            text-decoration: underline !important;
        }

        /* Submit Buttons */
        .form-login-register .box-form-login input.button[type="submit"] {
            width: 100% !important;
            height: 46px !important;
            line-height: 46px !important;
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-size: 14.5px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            border: none !important;
            cursor: pointer !important;
            transition: background 0.2s, transform 0.1s !important;
        }

        .form-login-register .box-form-login input.button[type="submit"]:hover {
            background: #1e293b !important;
        }

        /* Switch Login/Register footer */
        .form-login-register .box-form-login .switch-form {
            margin-top: 16px !important;
            font-size: 13px !important;
            color: #64748b !important;
        }

        .form-login-register .box-form-login .switch-form a {
            color: #0f172a !important;
            font-weight: 600 !important;
            text-decoration: underline !important;
            margin-left: 5px !important;
            cursor: pointer !important;
        }

        /* Active/Inactive tabs in modal */
        .form-login-register .box-content > div:not(.active) {
            display: none !important;
        }

        .form-login-register .box-content > div.active {
            display: block !important;
            transform: none !important;
            -webkit-transform: none !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .form-login-register .box-form-login .box-content {
            max-height: none !important;
            overflow: visible !important;
        }

        /* ========================================================
           RESPONSIVE POLISH ACROSS ALL PAGES
           ======================================================== */
        @media (max-width: 767px) {
            .section-padding {
                padding-top: 30px !important;
                padding-bottom: 30px !important;
            }

            .section-container.p-l-r {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }

            .page-title {
                padding: 30px 0 !important;
            }

            .page-title .text-title-heading {
                font-size: 26px !important;
                line-height: 1.2 !important;
            }

            /* Shop listing topbar */
            .products-topbar {
                display: flex !important;
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
            }

            .products-topbar .products-topbar-right {
                width: 100% !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
            }

            /* Product cards grid (2 columns on mobile) */
            .products-list.grid .row > [class*="col-"] {
                flex: 0 0 50% !important;
                max-width: 50% !important;
                padding-left: 7px !important;
                padding-right: 7px !important;
            }

            .products-entry {
                margin-bottom: 18px !important;
            }

            .products-content .product-title {
                font-size: 13.5px !important;
                line-height: 1.35 !important;
            }

            .products-content .price {
                font-size: 14px !important;
            }

            /* Cart Page responsive */
            .shop-cart .bottom-cart {
                display: flex !important;
                flex-direction: column !important;
                gap: 12px !important;
                align-items: stretch !important;
            }

            .shop-cart .bottom-cart .coupon {
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .shop-cart .bottom-cart .coupon input {
                width: 100% !important;
            }

            .shop-cart .bottom-cart .coupon button,
            .shop-cart .bottom-cart button[name="update_cart"] {
                width: 100% !important;
            }

            .shop-cart .cart-items .product-thumbnail img {
                max-width: 65px !important;
                height: auto !important;
            }

            /* Product detail image */
            .shop-details .product-images .img-item img {
                max-height: 320px !important;
            }

            .shop-details .product-info {
                margin-top: 20px !important;
            }

            .shop-details .product-info h1.title {
                font-size: 22px !important;
            }

            /* Dedicated Login / Register page */
            .page-login-register .box-form-login {
                padding: 24px 18px !important;
                margin-bottom: 25px !important;
            }

            .page-login-register .box-form-login .box-content {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
        }

        @media (max-width: 359px) {
            .products-list.grid .row > [class*="col-"] {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
        }

        /* Status Messages */
        .ajax-status-message {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 13.5px;
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
            margin-right: 10px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .ajax-status-icon svg {
            width: 18px;
            height: 18px;
        }

        @keyframes ajax-spin {
            to { transform: rotate(360deg); }
        }

        .ajax-spin-icon {
            animation: ajax-spin 1s linear infinite;
        }

        .swal2-container {
            z-index: 99999999 !important;
        }

        /* Password Visibility Toggle */
        .password-wrapper {
            position: relative !important;
            display: block !important;
            width: 100% !important;
        }

        .password-wrapper input.input-text,
        .password-wrapper input[type="password"],
        .password-wrapper input[type="text"] {
            padding-right: 44px !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .btn-toggle-password {
            position: absolute !important;
            right: 12px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            color: #64748b !important;
            padding: 4px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 10 !important;
            line-height: 1 !important;
            transition: color 0.15s ease, transform 0.15s ease !important;
        }

        .btn-toggle-password:hover {
            color: #0f172a !important;
        }

        .btn-toggle-password:focus {
            outline: none !important;
            color: #0f172a !important;
        }
    </style>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global Password Visibility Toggle
        window.togglePasswordVisibility = function(inputId, btn) {
            let input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
            if (!input && btn) {
                const wrapper = btn.closest('.password-wrapper') || btn.parentElement;
                input = wrapper ? wrapper.querySelector('input') : null;
            }
            if (!input) return;

            const eyeIcon = btn ? btn.querySelector('.eye-icon') : null;
            const eyeOffIcon = btn ? btn.querySelector('.eye-off-icon') : null;

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeIcon) eyeIcon.style.display = 'none';
                if (eyeOffIcon) eyeOffIcon.style.display = 'inline-block';
            } else {
                input.type = 'password';
                if (eyeIcon) eyeIcon.style.display = 'inline-block';
                if (eyeOffIcon) eyeOffIcon.style.display = 'none';
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Modal event handling (delegated for dynamically created mobile navigation links)
            $(document).on('click', '.active-login', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close mobile drawer if open
                if ($('.site-mobile-navigation').length) {
                    $('.site-mobile-navigation').removeClass('active');
                }
                $('#remove-megamenu').trigger('click');

                const $modal = $('.form-login-register');
                if ($(this).hasClass('close-modal-btn') || $(this).closest('.box-form-login').length > 0 && !$(this).hasClass('desktop-login-link')) {
                    $modal.removeClass('active');
                    $('body').removeClass('modal-open');
                } else {
                    $modal.addClass('active');
                    $('body').addClass('modal-open');
                    // Reset to login form tab by default
                    $('.form-login').addClass('active');
                    $('.form-register').removeClass('active');
                }
            });

            // Close modal when clicking outside modal box
            $(document).on('click', '.form-login-register', function(e) {
                if (!$(e.target).closest('.box-form-login').length) {
                    $(this).removeClass('active');
                    $('body').removeClass('modal-open');
                }
            });

            // Close on Escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    $('.form-login-register').removeClass('active');
                    $('body').removeClass('modal-open');
                }
            });

            // Switch between Login and Register tabs
            $(document).on('click', '.button-next-reregister', function(e) {
                e.preventDefault();
                $('.form-login').removeClass('active');
                $('.form-register').addClass('active');
            });

            $(document).on('click', '.button-next-login', function(e) {
                e.preventDefault();
                $('.form-register').removeClass('active');
                $('.form-login').addClass('active');
            });

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