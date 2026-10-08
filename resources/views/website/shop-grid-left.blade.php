@extends('layouts.website')
@section('body_class', 'shop')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="title" class="page-title">
			<div class="section-container">
				<div class="content-title-heading">
					<h1 class="text-title-heading">
						Bed &amp; Bath
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="/">Home</a><span class="delimiter"></span><a href="/shop">Shop</a><span class="delimiter"></span>Bed &amp; Bath
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="section-padding">
				<div class="section-container p-l-r">
					<div class="row">
						<div class="col-xl-3 col-lg-3 col-md-12 col-12 sidebar left-sidebar md-b-50">
							<!-- Block Product Categories -->
							<div class="block block-product-cats">
								<div class="block-title">
									<h2>Categories</h2>
								</div>
								<div class="block-content">
									<div class="product-cats-list">
										<ul>
											<li class="current">
												<a href="/shop">Bed &amp; Bath <span class="count">9</span></a>
											</li>
											<li>
												<a href="/shop">Furniture <span class="count">4</span></a>
											</li>
											<li>
												<a href="/shop">Home Décor <span class="count">3</span></a>
											</li>
											<li>
												<a href="/shop">Lighting <span class="count">6</span></a>
											</li>
											<li>
												<a href="/shop">Office <span class="count">2</span></a>
											</li>
											<li>
												<a href="/shop">Outdoor <span class="count">4</span></a>
											</li>
										</ul>
									</div>
								</div>
							</div>

							<!-- Block Product Filter -->
							<div class="block block-product-filter">
								<div class="block-title">
									<h2>Price</h2>
								</div>
								<div class="block-content">
									<div id="slider-range" class="price-filter-wrap">
										<div class="filter-item price-filter">
											<div class="layout-slider">
												<input id="price-filter" name="price" value="0;100">
											</div>
											<div class="layout-slider-settings"></div>
										</div>
									</div>
								</div>
							</div>

							<!-- Block Product Filter -->
							<div class="block block-product-filter clearfix">
								<div class="block-title">
									<h2>Size</h2>
								</div>
								<div class="block-content">
									<ul class="filter-items text">
										<li><span>L</span></li>
										<li><span>M</span></li>
										<li><span>S</span></li>
									</ul>
								</div>
							</div>

							<!-- Block Product Filter -->
							<div class="block block-product-filter clearfix">
								<div class="block-title">
									<h2>Brands</h2>
								</div>
								<div class="block-content">
									<ul class="filter-items image">
										<li><span><img src="{{ asset('website/media/brand/1.jpg') }}" alt="Brand"></span></li>
										<li><span><img src="{{ asset('website/media/brand/2.jpg') }}" alt="Brand"></span></li>
										<li><span><img src="{{ asset('website/media/brand/3.jpg') }}" alt="Brand"></span></li>
										<li><span><img src="{{ asset('website/media/brand/4.jpg') }}" alt="Brand"></span></li>
										<li><span><img src="{{ asset('website/media/brand/5.jpg') }}" alt="Brand"></span></li>
									</ul>
								</div>
							</div>

							<!-- Block Products -->
							<div class="block block-products">
								<div class="block-title">
									<h2>Feature Product</h2>
								</div>
								<div class="block-content">
									<ul class="products-list">
										<li class="product-item">
											<a href="/product-detail" class="product-image">
												<img src="{{ asset('website/media/product/6.jpg') }}">
											</a>
											<div class="product-content">
												<h2 class="product-title">
													<a href="/product-detail">
														Dining Table
													</a>
												</h2>
												<div class="rating small">
													<div class="star star-5"></div>
												</div>
												<span class="price">
													<del aria-hidden="true"><span>$150.00</span></del>
													<ins><span>$100.00</span></ins>
												</span>
											</div>
										</li>
										<li class="product-item">
											<a href="/product-detail" class="product-image">
												<img src="{{ asset('website/media/product/8.jpg') }}">
											</a>
											<div class="product-content">
												<h2 class="product-title">
													<a href="/product-detail">
														Spinning Pendant Lamp
													</a>
												</h2>
												<div class="rating small">
													<div class="star star-0"></div>
												</div>
												<span class="price">$120.00</span>
											</div>
										</li>
										<li class="product-item">
											<a href="/product-detail" class="product-image">
												<img src="{{ asset('website/media/product/9.jpg') }}">
											</a>
											<div class="product-content">
												<h2 class="product-title">
													<a href="/product-detail">
														Bora Armchair
													</a>
												</h2>
												<div class="rating small">
													<div class="star star-5"></div>
												</div>
												<span class="price">
													<del aria-hidden="true"><span>$200.00</span></del>
													<ins><span>$180.00</span></ins>
												</span>
											</div>
										</li>
									</ul>
								</div>
							</div>
						</div>

						<div class="col-xl-9 col-lg-9 col-md-12 col-12">
							<div class="products-topbar clearfix">
								<div class="products-topbar-left">
									<div class="products-count">
										Showing all 21 results
									</div>
								</div>
								<div class="products-topbar-right">
									<div class="products-sort dropdown">
										<span class="sort-toggle dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Default sorting</span>
										<ul class="sort-list dropdown-menu" x-placement="bottom-start">
											<li class="active"><a href="/shop">Default sorting</a></li>
											<li><a href="/shop">Sort by popularity</a></li>
											<li><a href="/shop">Sort by average rating</a></li>
											<li><a href="/shop">Sort by latest</a></li>
											<li><a href="/shop">Sort by price: low to high</a></li>
											<li><a href="/shop">Sort by price: high to low</a></li>
										</ul>
									</div>
									<ul class="layout-toggle nav nav-tabs">
										<li class="nav-item">
											<a class="layout-grid nav-link active" data-toggle="tab" href="/shop" role="tab"><span class="icon-column"><span class="layer first"><span></span><span></span><span></span></span><span class="layer middle"><span></span><span></span><span></span></span><span class="layer last"><span></span><span></span><span></span></span></span></a>
										</li>
										<li class="nav-item">
											<a class="layout-list nav-link" data-toggle="tab" href="/shop" role="tab"><span class="icon-column"><span class="layer first"><span></span><span></span></span><span class="layer middle"><span></span><span></span></span><span class="layer last"><span></span><span></span></span></span></a>
										</li>
									</ul>
								</div>
							</div>

							<div class="tab-content">
								<div class="tab-pane fade show active" id="layout-grid" role="tabpanel">
									<div class="products-list grid">
										<div class="row">
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/6.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/6-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Dining Table</a></h3>
															<span class="price">$150.00</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="onsale">-33%</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/4.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/4-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Pillar Dining Table Round</a></h3>
															<span class="price">
																<del aria-hidden="true"><span>$150.00</span></del>
																<ins><span>$100.00</span></ins>
															</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/7.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/7-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Mags Sofa 2.5 Seater</a></h3>
															<span class="price">$150.00</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="onsale">-33%</div>
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/8.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/8-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Spinning pendant lamp</a></h3>
															<span class="price">
																<del aria-hidden="true"><span>$150.00</span></del>
																<ins><span>$100.00</span></ins>
															</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="onsale">-23%</div>
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/9.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/9-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Bora Armchair</a></h3>
															<span class="price">
																<del aria-hidden="true"><span>$100.00</span></del>
																<ins><span>$90.00</span></ins>
															</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="onsale">-37%</div>
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/10.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/10-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Panton Dining Table</a></h3>
															<span class="price">
																<del aria-hidden="true"><span>$79.00</span></del>
																<ins><span>$50.00</span></ins>
															</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/11.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/11-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Kittchen Table</a></h3>
															<span class="price">$120.00</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="onsale">-10%</div>
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/12.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/12-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Mundo Sofa With Cushion</a></h3>
															<span class="price">
																<del aria-hidden="true"><span>$200.00</span></del>
																<ins><span>$180.00</span></ins>
															</span>
														</div>
													</div>
												</div>
											</div>
											<div class="col-xl-4 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/5.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/5-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
															<span class="product-quickview" data-title="Quick View">
																<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
															</span>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="/product-detail">Amp Pendant Light Large</a></h3>
															<span class="price">$140.00</span>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="tab-pane fade" id="layout-list" role="tabpanel">
									<div class="products-list list">
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/6.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/6-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Dining Table</a></h3>
														<span class="price">$150.00</span>
														<div class="rating">
															<div class="star star-5"></div>
															<div class="review-count">
																(1<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/4.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/4-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Pillar Dining Table Round</a></h3>
														<span class="price">
															<del aria-hidden="true"><span>$150.00</span></del>
															<ins><span>$100.00</span></ins>
														</span>
														<div class="rating">
															<div class="star star-0"></div>
															<div class="review-count">
																(0<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/7.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/7-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Mags Sofa 2.5 Seater</a></h3>
														<span class="price">$150.00</span>
														<div class="rating">
															<div class="star star-4"></div>
															<div class="review-count">
																(1<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/8.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/8-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Spinning pendant lamp</a></h3>
														<span class="price">
															<del aria-hidden="true"><span>$120.00</span></del>
															<ins><span>$100.00</span></ins>
														</span>
														<div class="rating">
															<div class="star star-0"></div>
															<div class="review-count">
																(0<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/9.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/9-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Bora Armchair</a></h3>
														<span class="price">
															<del aria-hidden="true"><span>$100.00</span></del>
															<ins><span>$90.00</span></ins>
														</span>
														<div class="rating">
															<div class="star star-5"></div>
															<div class="review-count">
																(3<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/10.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/10-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Panton Dining Table</a></h3>
														<span class="price">
															<del aria-hidden="true"><span>$79.00</span></del>
															<ins><span>$50.00</span></ins>
														</span>
														<div class="rating">
															<div class="star star-5"></div>
															<div class="review-count">
																(2<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/11.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/11-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Kittchen Table</a></h3>
														<span class="price">$120.00</span>
														<div class="rating">
															<div class="star star-4"></div>
															<div class="review-count">
																(1<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/12.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/12-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Mundo Sofa With Cushion</a></h3>
														<span class="price">
															<del aria-hidden="true"><span>$200.00</span></del>
															<ins><span>$180.00</span></ins>
														</span>
														<div class="rating">
															<div class="star star-5"></div>
															<div class="review-count">
																(4<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="/product-detail">
																<img width="600" height="600" src="{{ asset('website/media/product/5.jpg') }}" class="post-image" alt="">
																<img width="600" height="600" src="{{ asset('website/media/product/5-2.jpg') }}" class="hover-image back" alt="">
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="/shop" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="/product-detail">Amp Pendant Light Large</a></h3>
														<span class="price">$140.00</span>
														<div class="rating">
															<div class="star star-5"></div>
															<div class="review-count">
																(1<span> review</span>)
															</div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="/shop" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis…</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<nav class="pagination">
								<ul class="page-numbers">
									<li><a class="prev page-numbers" href="/shop">Previous</a></li>
									<li><span aria-current="page" class="page-numbers current">1</span></li>
									<li><a class="page-numbers" href="/shop">2</a></li>
									<li><a class="page-numbers" href="/shop">3</a></li>
									<li><a class="next page-numbers" href="/shop">Next</a></li>
								</ul>
							</nav>
						</div>
					</div>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection