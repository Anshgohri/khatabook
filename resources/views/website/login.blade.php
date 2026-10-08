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
																	<label for="password">Password <span class="required">*</span></label>
																	<input class="input-text" type="password" name="password">
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
																	<label>Password <span class="required">*</span></label>
																	<input type="password" class="input-text" name="password" required>
																</div>
																<div class="password-confirm">
																	<label>Confirm Password <span class="required">*</span></label>
																	<input type="password" class="input-text" name="password_confirmation" required>
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
									</div>
								</div>
							</div>
						</div><!-- #content -->
					</div><!-- #primary -->
				</div><!-- #main-content -->
			
@endsection
