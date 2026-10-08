@extends('layouts.website')
@section('body_class', 'home home-10 title-10')
@section('header_class', 'color-white')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<section class="section m-b-70">
				<!-- Block Sliders -->
				<div class="block block-sliders layout-10 color-white nav-center">
					<div class="slick-sliders" data-autoplay="false" data-dots="true" data-nav="false" data-columns4="1" data-columns3="1" data-columns2="1" data-columns1="1" data-columns1440="1" data-columns="1">

						<div class="item slick-slide">
							<div class="item-content">
								<div class="content-image">
									<img width="1920" height="1080" src="{{ asset('website/media/slider/banner-2.jpg') }}" alt="Image Slider">
								</div>
								<div class="item-info horizontal-center vertical-middle">
									<div class="content text-center">
										<h2 class="title-slider">{{ config('store.name') }}</h2>
										<a class="button-slider button-white" href="/shop">SHOP NOW</a>
									</div>
								</div>
							</div>
						</div>
						<div class="item slick-slide">
							<div class="item-content">
								<div class="content-image">
									<img width="1920" height="1080" src="{{ asset('website/media/slider/banner-3.jpg') }}" alt="Image Slider">
								</div>
								<div class="item-info horizontal-center vertical-middle">
									<div class="content text-center">
										<h2 class="title-slider">{{ config('store.name') }}</h2>
										<a class="button-slider button-white" href="/shop">SHOP NOW</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section class="section section-padding">
				<div class="section-container">
					<!-- Block Products -->
					<div class="block block-products slider">
						<div class="block-widget-wrap">
							<div class="block-title">
								<h2>Trending Products</h2>
							</div>
							<div class="block-content">
								<div class="content-product-list slick-wrap">
									<div class="slick-sliders products-list grid" data-slidestoscroll="true" data-dots="false" data-nav="1" data-columns4="1" data-columns3="2" data-columns2="3" data-columns1="3" data-columns1440="4" data-columns="4">
										@foreach($products as $product)
										<div class="item-product slick-slide">
											<div class="items">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="{{ route('catalog.show', $product->id) }}">
																@if($product->image_path)
																	<img width="600" height="600" src="{{ asset('storage/' . $product->image_path) }}" class="post-image" alt="{{ $product->name }}">
																@else
																	<img width="600" height="600" src="{{ asset('website/media/product/1.jpg') }}" class="post-image" alt="{{ $product->name }}">
																	<img width="600" height="600" src="{{ asset('website/media/product/1-2.jpg') }}" class="hover-image back" alt="{{ $product->name }}">
																@endif
															</a>
														</div>
														<div class="product-button">
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="#" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents">
															<h3 class="product-title"><a href="{{ route('catalog.show', $product->id) }}">{{ $product->name }}</a></h3>
															<span class="price">Rs.{{ number_format($product->unit_price, 2) }}</span>
															<div class="btn-add-to-cart">
																<div data-title="Add to cart">
																	<a href="#" class="button">Add to cart</a>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										@endforeach
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section class="section m-b-70">
				<!-- Block Lookbook -->
				<div class="block block-lookbook layout-3 no-space">
					<div class="background-overlay"></div>
					<div class="row">
						<div class="col-lg-6">
							<div class="lookbook-intro-wrap">
								<div class="lookbook-intro">
									<h2 class="title">Exclusive to Online Prints<br> &amp; Rugs Range</h2>
									<div class="description">Shop from the comfort of home and order with the click of a button. Our brand-new exclusive to online print and rug collection showcases so much more than we have in-store.</div>
									<a href="/shop" class="button button-black">SHOP NOW</a>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="lookbook-wrap default">
								<div class="lookbook-container">
									<div class="lookbook-content">
										<div class="item">
											<img width="961" height="668" src="{{ asset('website/media/banner.jpg') }}" alt="Look Book 1">
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section class="section section-padding m-b-70">
				<div class="section-container">
					<!-- Block Product Categories -->
					<div class="block block-product-cats layout-2 items-equal">
						<div class="block-widget-wrap">
							<div class="block-title">
								<h2>Trending</h2>
							</div>
							<div class="block-content">
								<div class="row">
									<div class="col-md-3 sm-m-b-50">
										<div class="cat-item">
											<div class="cat-image">
												<a href="/shop">
													<img width="331" height="421" src="{{ asset('website/media/product/org/img-4.jpg') }}" alt="Product Category">
												</a>
											</div>
											<div class="cat-title">
												<a href="/shop">
													<h3>Boucle</h3>
												</a>
											</div>
										</div>
									</div>
									<div class="col-md-3 sm-m-b-50">
										<div class="cat-item">
											<div class="cat-image">
												<a href="/shop">
													<img width="331" height="421" src="{{ asset('website/media/product/org/img-5.jpg') }}" alt="Product Category">
												</a>
											</div>
											<div class="cat-title">
												<a href="/shop">
													<h3>Rattan</h3>
												</a>
											</div>
										</div>
									</div>
									<div class="col-md-3 sm-m-b-50">
										<div class="cat-item">
											<div class="cat-image">
												<a href="/shop">
													<img width="331" height="421" src="{{ asset('website/media/product/org/img-6.jpg') }}" alt="Product Category">
												</a>
											</div>
											<div class="cat-title">
												<a href="/shop">
													<h3>Cue The Curves</h3>
												</a>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<div class="cat-item">
											<div class="cat-image">
												<a href="/shop">
													<img width="331" height="421" src="{{ asset('website/media/product/org/img-1.jpg') }}" alt="Product Category">
												</a>
											</div>
											<div class="cat-title">
												<a href="/shop">
													<h3>Small space solutions</h3>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<section class="section section-padding background-7 p-t-70 p-b-80 m-b-0">
				<div class="section-container">
					<div class="block block-newsletter layout-2 one-col m-b-15">
						<div class="block-widget-wrap">
							<div class="newsletter-title-wrap">
								<h2 class="newsletter-title">Let's be friends</h2>
								<div class="newsletter-text">Sign up for the latest trends, products, and inspiration.</div>
							</div>
							<form action="" method="post" class="newsletter-form">
								<input type="email" name="your-email" value="" size="40" placeholder="Email address">
								<span class="btn-submit">
									<input type="submit" value="SUBSCRIBE">
								</span>
							</form>
						</div>
					</div>
				</div>
			</section>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection