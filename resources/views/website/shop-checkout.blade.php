@extends('layouts.website')
@section('body_class', 'shop')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="title" class="page-title">
			<div class="section-container">
				<div class="content-title-heading">
					<h1 class="text-title-heading">
						Checkout
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="/">Home</a><span class="delimiter"></span><a href="/shop">Shop</a><span class="delimiter"></span>Shopping Cart
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="section-padding">
				<div class="section-container p-l-r">
					<div class="shop-checkout">
						<form name="checkout" method="post" class="checkout" action="" autocomplete="off">
							<div class="row">
								<div class="col-xl-8 col-lg-7 col-md-12 col-12">
									<div class="customer-details">
										<div class="billing-fields">
											<h3>Billing details</h3>
											<div class="billing-fields-wrapper">
												<p class="form-row form-row-first validate-required">
													<label>First name <span class="required" title="required">*</span></label>
													<span class="input-wrapper"><input type="text" class="input-text" name="billing_first_name" value=""></span>
												</p>
												<p class="form-row form-row-last validate-required">
													<label>Last name <span class="required" title="required">*</span></label>
													<span class="input-wrapper"><input type="text" class="input-text" name="billing_last_name" value=""></span>
												</p>
												<p class="form-row form-row-wide">
													<label>Company name <span class="optional">(optional)</span></label>
													<span class="input-wrapper"><input type="text" class="input-text" name="billing_company" value=""></span>
												</p>
												<p class="form-row form-row-wide validate-required">
													<label>Country / Region <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<select name="billing_country" class="country-select custom-select">
															<option value="">Select a country / region…</option>
															<option value="AF">Afghanistan</option>
															<option value="AX">Åland Islands</option>
															<option value="AL">Albania</option>
															<option value="DZ">Algeria</option>
															<option value="AS">American Samoa</option>
															<option value="AD">Andorra</option>
														</select>
													</span>
												</p>
												<p class="form-row address-field validate-required form-row-wide">
													<label>Street address <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<input type="text" class="input-text" name="billing_address_1" placeholder="House number and street name" value="">
													</span>
												</p>
												<p class="form-row address-field form-row-wide">
													<label>Apartment, suite, unit, etc.&nbsp;<span class="optional">(optional)</span></label>
													<span class="input-wrapper">
														<input type="text" class="input-text" name="billing_address_2" placeholder="Apartment, suite, unit, etc. (optional)" value="">
													</span>
												</p>
												<p class="form-row address-field validate-required form-row-wide">
													<label for="billing_city" class="">Town / City <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<input type="text" class="input-text" name="billing_city" value="">
													</span>
												</p>
												<p class="form-row address-field validate-required validate-state form-row-wide">
													<label>State / County <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<select name="billing_state" class="state-select custom-select">
															<option value="">Select a state / county…</option>
															<option value="VN">Vinnytsia Oblast</option>
															<option value="VL">Volyn Oblast</option>
															<option value="DP">Dnipropetrovsk Oblast</option>
															<option value="DT">Donetsk Oblast</option>
															<option value="ZT">Zhytomyr Oblast</option>
														</select>
													</span>
												</p>
												<p class="form-row address-field validate-required validate-postcode form-row-wide">
													<label>Postcode / ZIP <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<input type="text" class="input-text" name="billing_postcode" value="">
													</span>
												</p>
												<p class="form-row form-row-wide validate-required validate-phone">
													<label>Phone <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<input type="tel" class="input-text" name="billing_phone" value="">
													</span>
												</p>
												<p class="form-row form-row-wide validate-required validate-email">
													<label>Email address <span class="required" title="required">*</span></label>
													<span class="input-wrapper">
														<input type="email" class="input-text" name="billing_email" value="" autocomplete="off">
													</span>
												</p>
											</div>
										</div>
										<div class="account-fields">
											<p class="form-row form-row-wide">
												<label class="checkbox">
													<input class="input-checkbox" type="checkbox" name="createaccount" value="1">
													<span>Create an account?</span>
												</label>
											</p>
											<div class="create-account">
												<p class="form-row validate-required">
													<label>Create account password <span class="required" title="required">*</span></label>
													<span class="input-wrapper password-input">
														<input type="password" class="input-text" name="account_password" value="" autocomplete="off">
														<span class="show-password-input"></span>
													</span>
												</p>
												<div class="clear"></div>
											</div>
										</div>
									</div>
									<div class="shipping-fields">
										<p class="form-row form-row-wide ship-to-different-address">
											<label class="checkbox">
												<input class="input-checkbox" type="checkbox" name="ship_to_different_address" value="1">
												<span>Ship to a different address?</span>
											</label>
										</p>
										<div class="shipping-address">
											<p class="form-row form-row-first validate-required">
												<label>First name <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_first_name" value="">
												</span>
											</p>
											<p class="form-row form-row-last validate-required">
												<label>Last name <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_last_name" value="">
												</span>
											</p>
											<p class="form-row form-row-wide">
												<label>Company name <span class="optional">(optional)</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_company" value="">
												</span>
											</p>
											<p class="form-row form-row-wide address-field validate-required">
												<label for="shipping_country" class="">Country / Region <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<select name="billing_state" class="state-select custom-select">
														<option value="">Select a country / region…</option>
														<option value="VN">Vinnytsia Oblast</option>
														<option value="VL">Volyn Oblast</option>
														<option value="DP">Dnipropetrovsk Oblast</option>
														<option value="DT">Donetsk Oblast</option>
														<option value="ZT">Zhytomyr Oblast</option>
													</select>
												</span>
											</p>
											<p class="form-row address-field validate-required form-row-wide">
												<label>Street address <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_address_1" placeholder="House number and street name" value="">
												</span>
											</p>
											<p class="form-row address-field form-row-wide">
												<label>Apartment, suite, unit, etc. <span class="optional">(optional)</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_address_2" placeholder="Apartment, suite, unit, etc. (optional)" value="">
												</span>
											</p>
											<p class="form-row address-field validate-required form-row-wide">
												<label>Town / City <span class="required" title="required">*</span></label>
												<span class="input-wrapper"><input type="text" class="input-text" name="shipping_city" value=""></span>
											</p>
											<p class="form-row address-field validate-required validate-state form-row-wide">
												<label for="shipping_state" class="">State / County <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<select name="billing_state" class="state-select custom-select">
														<option value="">Select a state / county…</option>
														<option value="VN">Vinnytsia Oblast</option>
														<option value="VL">Volyn Oblast</option>
														<option value="DP">Dnipropetrovsk Oblast</option>
														<option value="DT">Donetsk Oblast</option>
														<option value="ZT">Zhytomyr Oblast</option>
													</select>
												</span>
											</p>
											<p class="form-row address-field validate-required validate-postcode form-row-wide">
												<label>Postcode / ZIP <span class="required" title="required">*</span></label>
												<span class="input-wrapper">
													<input type="text" class="input-text" name="shipping_postcode" value="">
												</span>
											</p>
										</div>
									</div>
									<div class="additional-fields">
										<p class="form-row notes">
											<label>Order notes <span class="optional">(optional)</span></label>
											<span class="input-wrapper">
												<textarea name="order_comments" class="input-text" placeholder="Notes about your order, e.g. special notes for delivery." rows="2" cols="5"></textarea>
											</span>
										</p>
									</div>
								</div>
								<div class="col-xl-4 col-lg-5 col-md-12 col-12">
									<div class="checkout-review-order">
										<div class="checkout-review-order-table">
											<div class="review-order-title">Product</div>
											<div class="cart-items">
												<div class="cart-item">
													<div class="info-product">
														<div class="product-thumbnail">
															<img width="600" height="600" src="{{ asset('website/media/product/3.jpg') }}" alt="">
														</div>
														<div class="product-name">
															Chair Oak Matt Lacquered
															<strong class="product-quantity">QTY : 2</strong>
														</div>
													</div>
													<div class="product-total">
														<span>$300.00</span>
													</div>
												</div>
												<div class="cart-item">
													<div class="info-product">
														<div class="product-thumbnail">
															<img width="600" height="600" src="{{ asset('website/media/product/1.jpg') }}" alt="">
														</div>
														<div class="product-name">
															Zunkel Schwarz
															<strong class="product-quantity">QTY : 1</strong>
														</div>
													</div>
													<div class="product-total">
														<span>$180.00</span>
													</div>
												</div>
											</div>
											<div class="cart-subtotal">
												<h2>Subtotal</h2>
												<div class="subtotal-price">
													<span>$480.00</span>
												</div>
											</div>
											<div class="shipping-totals shipping">
												<h2>Shipping</h2>
												<div data-title="Shipping">
													<ul class="shipping-methods custom-radio">
														<li>
															<input type="radio" name="shipping_method" data-index="0" value="free_shipping" class="shipping_method" checked="checked"><label>Free shipping</label>
														</li>
														<li>
															<input type="radio" name="shipping_method" data-index="0" value="flat_rate" class="shipping_method"><label>Flat rate</label>
														</li>
													</ul>
												</div>
											</div>
											<div class="order-total">
												<h2>Total</h2>
												<div class="total-price">
													<strong>
														<span>$480.00</span>
													</strong>
												</div>
											</div>
										</div>
										<div id="payment" class="checkout-payment">
											<ul class="payment-methods methods custom-radio">
												<li class="payment-method">
													<input type="radio" class="input-radio" name="payment_method" value="bacs" checked="checked">
													<label for="payment_method_bacs">Direct bank transfer</label>
													<div class="payment-box">
														<p>Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.</p>
													</div>
												</li>
												<li class="payment-method">
													<input type="radio" class="input-radio" name="payment_method" value="cheque">
													<label>Check payments</label>
													<div class="payment-box">
														<p>Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode.</p>
													</div>
												</li>
												<li class="payment-method">
													<input type="radio" class="input-radio" name="payment_method" value="cod">
													<label>Cash on delivery</label>
													<div class="payment-box">
														<p>Pay with cash upon delivery.</p>
													</div>
												</li>
												<li class="payment-method">
													<input type="radio" class="input-radio" name="payment_method" value="paypal">
													<label>PayPal</label>
													<div class="payment-box">
														<p>Pay via PayPal; you can pay with your credit card if you don’t have a PayPal account.</p>
													</div>
												</li>
											</ul>
											<div class="form-row place-order">
												<div class="terms-and-conditions-wrapper">
													<div class="privacy-policy-text"></div>
												</div>
												<button type="submit" class="button alt" name="checkout_place_order" value="Place order">Place order</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection