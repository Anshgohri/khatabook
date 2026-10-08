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
													<p style="margin-bottom: 20px;">Please enter your new password below.</p>
													
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
															<form method="POST" action="{{ route('password.update') }}" class="login">
																@csrf
                                                                <input type="hidden" name="token" value="{{ request()->route('token') }}">
                                                                
																<div class="username">
																	<label>Email address <span class="required">*</span></label>
																	<input type="email" class="input-text" name="email" id="email" required autocomplete="email" value="{{ request('email') }}">
																</div>
																<div class="password">
																	<label>Password <span class="required">*</span></label>
																	<input class="input-text" type="password" name="password" required autocomplete="new-password">
																</div>
																<div class="password-confirm">
																	<label>Confirm Password <span class="required">*</span></label>
																	<input type="password" class="input-text" name="password_confirmation" required autocomplete="new-password">
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
			
@endsection
