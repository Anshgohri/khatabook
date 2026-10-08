@extends('layouts.website')
@section('body_class', 'shop')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="title" class="page-title">
			<div class="section-container">
				<div class="content-title-heading">
					<h1 class="text-title-heading">
						Shopping Cart
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
					<div class="shop-cart">
						<div class="row">
							<div class="col-xl-8 col-lg-12 col-md-12 col-12">
								<form class="cart-form" action="" method="post">
									<div class="table-responsive">
										<table class="cart-items table" cellspacing="0">
											<thead>
												<tr>
													<th class="product-thumbnail">Product</th>
													<th class="product-price">Price</th>
													<th class="product-quantity">Quantity</th>
													<th class="product-subtotal">Subtotal</th>
													<th class="product-remove">&nbsp;</th>
												</tr>
											</thead>
											<tbody>
												<tr class="cart-item">
													<td class="product-thumbnail">
														<a href="/product-detail">
															<img width="600" height="600" src="{{ asset('website/media/product/3.jpg') }}" class="product-image" alt="">
														</a>
														<div class="product-name">
															<a href="/product-detail">Chair Oak Matt Lacquered</a>
														</div>
													</td>
													<td class="product-price">
														<span class="price">$150.00</span>
													</td>
													<td class="product-quantity">
														<div class="quantity">
															<button type="button" class="minus">-</button>
															<input type="number" class="qty" step="1" min="0" max="" name="quantity" value="2" title="Qty" size="4" placeholder="" inputmode="numeric" autocomplete="off">
															<button type="button" class="plus">+</button>
														</div>
													</td>
													<td class="product-subtotal">
														<span>$300.00</span>
													</td>
													<td class="product-remove">
														<a href="/cart" class="remove">×</a>
													</td>
												</tr>
												<tr class="cart-item">
													<td class="product-thumbnail">
														<a href="/product-detail">
															<img width="600" height="600" src="{{ asset('website/media/product/1.jpg') }}" class="product-image" alt="">
														</a>
														<div class="product-name">
															<a href="/product-detail">Zunkel Schwarz</a>
														</div>
													</td>
													<td class="product-price">
														<span>$180.00</span>
													</td>
													<td class="product-quantity">
														<div class="quantity">
															<button type="button" class="minus">-</button>
															<input type="number" class="qty" step="1" min="0" max="" name="quantity" value="1" title="Qty" size="4" placeholder="" inputmode="numeric" autocomplete="off">
															<button type="button" class="plus">+</button>
														</div>
													</td>
													<td class="product-subtotal">
														<span class="price">$180.00</span>
													</td>
													<td class="product-remove">
														<a href="/cart" class="remove">×</a>
													</td>
												</tr>
												<tr>
													<td colspan="6" class="actions">
														<div class="bottom-cart">
															<div class="coupon">
																<input type="text" name="coupon_code" class="input-text" id="coupon-code" value="" placeholder="Coupon code">
																<button type="submit" name="apply_coupon" class="button" value="Apply coupon">Apply coupon</button>
															</div>
															<h2><a href="/shop">Continue Shopping</a></h2>
															<button type="submit" name="update_cart" class="button" value="Update cart">Update cart</button>
														</div>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</form>
							</div>
							<div class="col-xl-4 col-lg-12 col-md-12 col-12">
								<div class="cart-totals">
									<h2>Cart totals</h2>
									<div>
										<div class="cart-subtotal">
											<div class="title">Subtotal</div>
											<div><span>$480.00</span></div>
										</div>
										<div class="shipping-totals">
											<div class="title">Shipping</div>
											<div>
												<ul class="shipping-methods custom-radio">
													<li>
														<input type="radio" name="shipping_method" data-index="0" value="free_shipping" class="shipping_method" checked="checked"><label>Free shipping</label>
													</li>
													<li>
														<input type="radio" name="shipping_method" data-index="0" value="flat_rate" class="shipping_method"><label>Flat rate</label>
													</li>
												</ul>
												<p class="shipping-desc">
													Shipping options will be updated during checkout.
												</p>
											</div>
										</div>
										<div class="order-total">
											<div class="title">Total</div>
											<div><span>$480.00</span></div>
										</div>
									</div>
									<div class="proceed-to-checkout">
										<a href="/checkout" class="checkout-button button">
											Proceed to checkout
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="shop-cart-empty">
						<div class="notices-wrapper">
							<p class="cart-empty">Your cart is currently empty.</p>
						</div>
						<div class="return-to-shop">
							<a class="button" href="/shop">
								Return to shop
							</a>
						</div>
					</div>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection