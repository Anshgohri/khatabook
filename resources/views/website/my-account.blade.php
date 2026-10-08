@extends('layouts.website')
@section('body_class', 'blog')
@section('header_class', 'absolute color-white')
@section('content')

				<div id="main-content" class="main-content">
					<div id="primary" class="content-area">
						<div id="title" class="page-title">
							<div class="section-container">
								<div class="content-title-heading">
									<h1 class="text-title-heading">
										My Account
									</h1>
								</div>
								<div class="breadcrumbs">
									<a href="/">Home</a><span class="delimiter"></span>My Account
								</div>
							</div>
						</div>

						<div id="content" class="site-content" role="main">
							<div class="section-padding">
								<div class="section-container p-l-r">
									<div class="page-my-account">
										<div class="my-account-wrap clearfix">
											<nav class="my-account-navigation">
												<ul class="nav nav-tabs">
													<li class="nav-item">
														<a class="nav-link active" data-toggle="tab" href="/my-account" role="tab">Dashboard</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-toggle="tab" href="/my-account" role="tab">Orders</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-toggle="tab" href="/my-account" role="tab">Addresses</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-toggle="tab" href="/my-account" role="tab">Account details</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" href="/login">Logout</a>
													</li>
												</ul>
											</nav>
											<div class="my-account-content tab-content">
												<div class="tab-pane fade show active" id="dashboard" role="tabpanel">
													<div class="my-account-dashboard">
														<p>
															Hello <strong>Rosie</strong> (not <strong>Rosie</strong>? <a href="/login">Log out</a>)
														</p>
														<p>
															From your account dashboard you can view your <a href="/my-account">recent orders</a>, manage your <a href="/my-account">shipping and billing addresses</a>, and <a href="/my-account">edit your password and account details</a>.
														</p>
													</div>
												</div>
												<div class="tab-pane fade" id="orders" role="tabpanel">
													<div class="my-account-orders">
														<div class="table-responsive">
		                                                    <table class="table">
		                                                        <thead>
		                                                            <tr>
		                                                                <th>Order</th>
		                                                                <th>Date</th>
		                                                                <th>Status</th>
		                                                                <th>Total</th>
		                                                                <th>Actions</th>
		                                                            </tr>
		                                                        </thead>
		                                                        <tbody>
		                                                            <tr>
		                                                                <td>#1357</td>
		                                                                <td>March 45, 2020</td>
		                                                                <td>Processing</td>
		                                                                <td>$125.00 for 2 item</td>
		                                                                <td><a href="/my-account" class="btn-small d-block">View</a></td>
		                                                            </tr>
		                                                            <tr>
		                                                                <td>#2468</td>
		                                                                <td>June 29, 2020</td>
		                                                                <td>Completed</td>
		                                                                <td>$364.00 for 5 item</td>
		                                                                <td><a href="/my-account" class="btn-small d-block">View</a></td>
		                                                            </tr>
		                                                            <tr>
		                                                                <td>#2366</td>
		                                                                <td>August 02, 2020</td>
		                                                                <td>Completed</td>
		                                                                <td>$280.00 for 3 item</td>
		                                                                <td><a href="/my-account" class="btn-small d-block">View</a></td>
		                                                            </tr>
		                                                        </tbody>
		                                                    </table>
		                                                </div>
													</div>
												</div>
												<div class="tab-pane fade" id="addresses" role="tabpanel">
													<div class="my-account-addresses">
														<p>
															The following addresses will be used on the checkout page by default.
														</p>
														<div class="addresses">
															<div class="addresses-col">
																<header class="col-title">
																	<h3>Billing address</h3>
																	<a href="/my-account" class="edit">Edit</a>
																</header>
																<address>
		                                                            3522 Interstate<br>
		                                                            75 Business Spur,<br>
		                                                            Sault Ste.<br>
		                                                            Marie, MI 49783
		                                                        </address>
															</div>
															<div class="addresses-col">
																<header class="col-title">
																	<h3>Shipping address</h3>
																	<a href="/my-account" class="edit">Edit</a>
																</header>
																<address>
		                                                            4299 Express Lane<br>
		                                                            Sarasota,<br>
		                                                            FL 34249 USA <br>
		                                                            Phone: 1.941.227.4444
		                                                        </address>
															</div>
														</div>
													</div>
												</div>
												<div class="tab-pane fade" id="account-details" role="tabpanel">
													<div class="my-account-account-details">
														<form class="edit-account" action="" method="post">
															<p class="form-row">
																<label for="account_first_name">First name <span class="required">*</span></label>
																<input type="text" class="input-text" name="account_first_name">
															</p>
															<p class="form-row">
																<label>Last name <span class="required">*</span></label>
																<input type="text" class="input-text" name="account_last_name">
															</p>
															<div class="clear"></div>
															<p class="form-row">
																<label>Display name <span class="required">*</span></label>
																<input type="text" class="input-text" name="account_display_name"> 
																<span><em>This will be how your name will be displayed in the account section and in reviews</em></span>
															</p>
															<div class="clear"></div>
															<p class="form-row">
																<label>Email address <span class="required">*</span></label>
																<input type="email" class="input-text" name="account_email">
															</p>
															<fieldset>
																<legend>Password change</legend>
																<p class="form-row">
																	<label>Current password (leave blank to leave unchanged)</label>
																	<input type="password" class="input-text" name="password_current" autocomplete="off">
																</p>
																<p class="form-row">
																	<label>New password (leave blank to leave unchanged)</label>
																	<input type="password" class="input-text" name="password_1" autocomplete="off">
																</p>
																<p class="form-row">
																	<label>Confirm new password</label>
																	<input type="password" class="input-text" name="password_2" autocomplete="off">
																</p>
															</fieldset>
															<div class="clear"></div>
															<p class="form-row">
																<button type="submit" class="button" name="save_account_details" value="Save changes">Save changes</button>
															</p>
														</form>
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
