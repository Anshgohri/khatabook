@extends('layouts.website')
@section('body_class', 'page')
@section('content')

				<div id="main-content" class="main-content">
					<div id="primary" class="content-area">
						<div id="title" class="page-title">
							<div class="section-container">
								<div class="content-title-heading">
									<h1 class="text-title-heading">
										Reset Password
									</h1>
								</div>
								<div class="breadcrumbs">
									<a href="/">Home</a><span class="delimiter"></span>Reset Password
								</div>
							</div>
						</div>

						<div id="content" class="site-content" role="main">
							<div class="section-padding">
								<div class="section-container p-l-r">
									<div class="page-login-register">
										<div class="row justify-content-center">
											<div class="col-lg-6 col-md-8 col-sm-12 m-auto" style="float: none; margin: 0 auto;">
												<div class="box-form-login">
													<h2>Reset Password</h2>
													
                                                    <!-- Session Status -->
                                                    @if (session('status'))
                                                        <div style="color: #166534; background-color: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; font-weight: 500;">
                                                            {{ session('status') }}
                                                        </div>
                                                    @endif

													@if ($errors->any())
														<div style="color: #991b1b; background-color: #fef2f2; border: 1px solid #fecaca; padding: 12px 16px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; font-weight: 500;">
															{{ $errors->first() }}
														</div>
													@endif
													<div class="box-content">
														<p style="margin-bottom: 20px; color: #555; font-size: 14px;">Please enter your new password below.</p>
														<div class="form-login">
															<form method="POST" action="{{ route('password.update') }}" class="login">
																@csrf
                                                                <input type="hidden" name="token" value="{{ request()->route('token') }}">
                                                                
																<div class="username">
																	<label>Email address <span class="required">*</span></label>
																	<input type="email" class="input-text" name="email" id="email" required autocomplete="email" value="{{ request('email') }}">
																</div>
																<div class="password">
																	<label>Password <span class="required">*</span></label>
																	<div style="position: relative;">
																		<input class="input-text" type="password" name="password" id="reset_password" required autocomplete="new-password" style="padding-right: 40px; width: 100%;">
																		<button type="button" onclick="togglePasswordVisibility('reset_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #666; padding: 4px; display: flex; align-items: center; justify-content: center; z-index: 10;" aria-label="Toggle password visibility">
																			<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
																			<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
																		</button>
																	</div>
																</div>
																<div class="password-confirm">
																	<label>Confirm Password <span class="required">*</span></label>
																	<div style="position: relative;">
																		<input type="password" class="input-text" name="password_confirmation" id="reset_password_confirmation" required autocomplete="new-password" style="padding-right: 40px; width: 100%;">
																		<button type="button" onclick="togglePasswordVisibility('reset_password_confirmation', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #666; padding: 4px; display: flex; align-items: center; justify-content: center; z-index: 10;" aria-label="Toggle password visibility">
																			<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
																			<svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
																		</button>
																	</div>
																</div>
																<div class="button-login" style="margin-bottom: 15px; margin-top: 15px;">
																	<input type="submit" class="button" name="submit" value="Reset Password"> 
																</div>
															</form>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div><!-- #content -->
					</div><!-- #primary -->
				</div><!-- #main-content -->

<script>
function togglePasswordVisibility(inputId, btn) {
	const input = document.getElementById(inputId);
	if (!input) return;
	const eyeIcon = btn.querySelector('.eye-icon');
	const eyeOffIcon = btn.querySelector('.eye-off-icon');
	if (input.type === 'password') {
		input.type = 'text';
		if (eyeIcon) eyeIcon.style.display = 'none';
		if (eyeOffIcon) eyeOffIcon.style.display = 'inline-block';
	} else {
		input.type = 'password';
		if (eyeIcon) eyeIcon.style.display = 'inline-block';
		if (eyeOffIcon) eyeOffIcon.style.display = 'none';
	}
}
</script>
			
@endsection
