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
						Login
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="/">Home</a><span class="delimiter"></span>Login
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
						<div class="row justify-content-center">
							<div class="col-lg-6 col-md-8 col-sm-12">
								<div class="box-form-login" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px 30px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);">
									<h2 style="font-size: 26px; font-weight: 700; margin-bottom: 20px; color: #1e293b;">Login</h2>

									<div id="page_login_alert" class="ajax-status-message error" style="{{ $errors->any() ? 'display: flex;' : 'display: none;' }}; margin-bottom: 20px;">
										<span class="ajax-status-icon">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
										</span>
										<span class="ajax-status-text">{{ $errors->first() }}</span>
									</div>

									<div class="box-content">
										<div class="form-login">
											<form id="page_login_form" method="POST" action="{{ route('login.store') }}" class="login">
												@csrf
												<div class="username form-group mb-3">
													<label for="page_login_email" style="font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block; color: #334155;">Username or email address <span class="required" style="color: #ef4444;">*</span></label>
													<input type="email" class="input-text" name="email" id="page_login_email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com" style="width: 100%; height: 48px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 14px;">
												</div>
												<div class="password form-group mb-3">
													<label for="page_login_password" style="font-weight: 600; font-size: 14px; margin-bottom: 6px; display: block; color: #334155;">Password <span class="required" style="color: #ef4444;">*</span></label>
													<div class="password-wrapper" style="position: relative;">
														<input class="input-text" type="password" name="password" id="page_login_password" required autocomplete="current-password" placeholder="••••••••" style="width: 100%; height: 48px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 45px 0 14px;">
														<button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('page_login_password', this)" aria-label="Toggle password visibility">
															<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
															<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
														</button>
													</div>
												</div>
												<div class="rememberme-lost d-flex justify-content-between align-items-center mb-4">
													<div class="remember-me">
														<label class="inline mb-0" style="font-size: 14px; color: #475569; cursor: pointer; display: flex; align-items: center; gap: 6px;">
															<input name="rememberme" type="checkbox" id="page_rememberme" value="forever">
															<span>Remember me</span>
														</label>
													</div>
													<div class="lost-password">
														<a href="{{ route('password.request') }}" style="font-size: 13.5px; color: #0284c7; text-decoration: none;">Lost your password?</a>
													</div>
												</div>
												<div class="button-login mb-3">
													<button type="submit" class="button btn-block" id="page_login_submit_btn" name="login" style="width: 100%; height: 50px; font-weight: 600; font-size: 15px; border-radius: 8px; letter-spacing: 0.5px; background: #000000; color: #ffffff; border: none; cursor: pointer; transition: background-color 0.2s ease;">
														<span class="btn-text">LOGIN</span>
													</button>
												</div>
												<div class="switch-form text-center mt-4 pt-3" style="border-top: 1px solid #f1f5f9; font-size: 14px;">
													<span style="color: #64748b;">Don't have an account?</span>
													<a href="{{ route('register') }}" style="font-weight: 600; color: #0284c7; text-decoration: underline; margin-left: 6px;">Register here</a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
	const loginForm = document.getElementById('page_login_form');
	if (!loginForm) return;

	const alertBox = document.getElementById('page_login_alert');
	const alertText = alertBox ? alertBox.querySelector('.ajax-status-text') : null;
	const alertIcon = alertBox ? alertBox.querySelector('.ajax-status-icon') : null;
	const submitBtn = document.getElementById('page_login_submit_btn');
	const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
	const passwordInput = document.getElementById('page_login_password');

	const errorSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>`;
	const successSvg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>`;
	const spinSvg = `<svg class="ajax-spin-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width: 18px; height: 18px; animation: ajax-spin 0.8s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

	function showAlert(type, message) {
		if (!alertBox) return;
		alertBox.className = 'ajax-status-message ' + type;
		alertBox.style.display = 'flex';
		if (alertIcon) {
			alertIcon.innerHTML = type === 'error' ? errorSvg : (type === 'success' ? successSvg : spinSvg);
		}
		if (alertText) {
			alertText.textContent = message;
		}
	}

	loginForm.addEventListener('submit', function(e) {
		e.preventDefault();

		showAlert('processing', 'Checking credentials...');
		if (submitBtn) submitBtn.disabled = true;
		if (btnText) btnText.textContent = 'LOGGING IN...';

		fetch(loginForm.action, {
			method: 'POST',
			body: new FormData(loginForm),
			credentials: 'same-origin',
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest'
			}
		})
		.then(async response => {
			if (response.ok) {
				showAlert('success', 'Logged in successfully! Redirecting...');
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Success!',
						text: 'You have logged in successfully!',
						icon: 'success',
						showConfirmButton: false,
						timer: 1500,
						allowOutsideClick: false
					}).then(() => {
						window.location.href = "{{ route('dashboard') }}";
					});
				} else {
					setTimeout(() => {
						window.location.href = "{{ route('dashboard') }}";
					}, 600);
				}
			} else {
				let errorMsg = 'These credentials do not match our records.';
				try {
					const data = await response.json();
					if (data.errors && Object.values(data.errors).length > 0) {
						errorMsg = Object.values(data.errors)[0][0];
					} else if (data.message) {
						errorMsg = data.message;
					}
				} catch (err) {}

				showAlert('error', errorMsg);
				if (typeof Swal !== 'undefined') {
					Swal.fire({
						title: 'Login Failed',
						text: errorMsg,
						icon: 'error',
						confirmButtonText: 'Try Again',
						confirmButtonColor: '#000000'
					});
				}
				if (submitBtn) submitBtn.disabled = false;
				if (btnText) btnText.textContent = 'LOGIN';
				if (passwordInput) {
					passwordInput.value = '';
					passwordInput.focus();
				}
			}
		})
		.catch(() => {
			showAlert('error', 'A network error occurred. Please try again.');
			if (typeof Swal !== 'undefined') {
				Swal.fire({
					title: 'Network Error',
					text: 'A network error occurred. Please try again.',
					icon: 'error',
					confirmButtonText: 'OK',
					confirmButtonColor: '#000000'
				});
			}
			if (submitBtn) submitBtn.disabled = false;
			if (btnText) btnText.textContent = 'LOGIN';
		});
	});
});
</script>

@endsection