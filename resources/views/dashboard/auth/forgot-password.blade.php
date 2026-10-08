@extends('layouts.website')
@section('body_class', 'page')
@section('content')

				<div id="main-content" class="main-content">
					<div id="primary" class="content-area">
						<div id="title" class="page-title">
							<div class="section-container">
								<div class="content-title-heading">
									<h1 class="text-title-heading">
										Forgot Password
									</h1>
								</div>
								<div class="breadcrumbs">
									<a href="/">Home</a><span class="delimiter"></span>Forgot Password
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
													<h2>Forgot Password</h2>
													<p style="margin-bottom: 20px;">Enter your email to receive a password reset link.</p>
													
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
														<div class="form-login">
															<form method="POST" action="{{ route('password.email') }}" class="login">
																@csrf
																<div class="username">
																	<label>Email address <span class="required">*</span></label>
																	<input type="email" class="input-text" name="email" id="email" required autofocus placeholder="email@example.com">
																</div>
																<div class="button-login" style="margin-bottom: 15px;">
																	<input type="submit" class="button" name="submit" value="Email Password Reset Link"> 
																</div>
                                                                <div class="lost-password">
                                                                    <a href="{{ route('login') }}">Or, return to log in</a>
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
