@extends('layouts.website')
@section('body_class', 'page')
@section('header_class', 'absolute color-white')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="title" class="page-title">
			<div class="section-container">
				<div class="content-title-heading">
					<h1 class="text-title-heading">
						Login / Register
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="/">Home</a><span class="delimiter"></span>Login / Register
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="section-padding">
				<div class="section-container p-l-r">
					<div class="page-login-register">
						@auth
						<div class="text-center py-5">
							<h2 class="mb-3">You are already logged in</h2>
							<p class="text-muted mb-4">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
							<a href="{{ route('dashboard') }}" class="button">
								Go to Dashboard &rarr;
							</a>
						</div>
						@else
						@if (\App\Models\User::doesntExist())
						<div class="alert alert-info text-center mb-4 p-3" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
							<p class="mb-2 text-primary font-weight-bold">System setup required</p>
							<a href="{{ route('setup') }}" class="button" style="display: inline-block;">{{ __('Set up admin account') }}</a>
						</div>
						@endif
						<div class="row">
							<div class="col-lg-6 col-md-6 col-sm-12 sm-m-b-50">
								<div class="box-form-login">
									<h2>Login</h2>
									@if ($errors->any())
									<div style="color: red; margin-bottom: 15px;">
										{{ $errors->first() }}
									</div>
									@endif
									<div class="box-content">
										<div class="form-login">
											<form method="POST" action="{{ route('login.store') }}" class="login">
												@csrf
												<div class="username">
													<label>Username or email address <span class="required">*</span></label>
													<input type="email" class="input-text" name="email" id="email">
												</div>
												<div class="password">
													<label for="page_login_password">Password <span class="required">*</span></label>
													<div class="password-wrapper">
														<input class="input-text" type="password" name="password" id="page_login_password" required autocomplete="current-password">
														<button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('page_login_password', this)" aria-label="Toggle password visibility">
															<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
															<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
														</button>
													</div>
												</div>
												<div class="rememberme-lost">
													<div class="remember-me">
														<input name="rememberme" type="checkbox" value="forever">
														<label class="inline">Remember me</label>
													</div>
													<div class="lost-password">
														<a href="{{ route('password.request') }}">Lost your password?</a>
													</div>
												</div>
												<div class="button-login">
													<input type="submit" class="button" name="login" value="Login">
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-6 col-md-6 col-sm-12">
								<div class="box-form-login">
									<h2 class="register">Register</h2>
									@if ($errors->any())
									<div style="color: red; margin-bottom: 15px;">
										{{ $errors->first() }}
									</div>
									@endif
									<div class="box-content">
										<div class="form-register">
											<form method="POST" action="{{ route('register.store') }}" class="register">
												@csrf
												<div class="name">
													<label>Name <span class="required">*</span></label>
													<input type="text" class="input-text" name="name" required>
												</div>
												<div class="email">
													<label>Email address <span class="required">*</span></label>
													<input type="email" class="input-text" name="email" required>
												</div>
												<div class="password">
													<label for="page_reg_password">Password <span class="required">*</span></label>
													<div class="password-wrapper">
														<input type="password" class="input-text" name="password" id="page_reg_password" required autocomplete="new-password">
														<button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('page_reg_password', this)" aria-label="Toggle password visibility">
															<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
															<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
														</button>
													</div>
												</div>
												<div class="password-confirm">
													<label for="page_reg_password_confirmation">Confirm Password <span class="required">*</span></label>
													<div class="password-wrapper">
														<input type="password" class="input-text" name="password_confirmation" id="page_reg_password_confirmation" required autocomplete="new-password">
														<button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('page_reg_password_confirmation', this)" aria-label="Toggle password visibility">
															<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
															<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
														</button>
													</div>
												</div>
												<div class="button-register">
													<input type="submit" class="button" name="register" value="Register">
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
						@endauth
					</div>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection