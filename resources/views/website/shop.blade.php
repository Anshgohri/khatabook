@extends('layouts.website')
@section('body_class', 'shop')
@section('header_class', 'absolute color-white')
@section('content')

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="title" class="page-title">
			<div class="section-container">
				<div class="content-title-heading">
					<h1 class="text-title-heading">
						Shop
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="{{ route('home') }}">Home</a><span class="delimiter"></span>Shop
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="section-padding">
				<div class="section-container p-l-r">
					<div class="row">
						<div class="col-xl-12 col-lg-12 col-md-12 col-12">
							<div class="products-topbar clearfix">
								<div class="products-topbar-left">
									<div class="products-count">
										Showing all {{ $products->count() }} results
									</div>
								</div>
								<div class="products-topbar-right">
									<div class="products-sort dropdown">
										<span class="sort-toggle dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Default sorting</span>
										<ul class="sort-list dropdown-menu" x-placement="bottom-start">
											<li class="active"><a href="#">Default sorting</a></li>
											<li><a href="#">Sort by popularity</a></li>
											<li><a href="#">Sort by average rating</a></li>
											<li><a href="#">Sort by latest</a></li>
											<li><a href="#">Sort by price: low to high</a></li>
											<li><a href="#">Sort by price: high to low</a></li>
										</ul>
									</div>
									<ul class="layout-toggle nav nav-tabs">
										<li class="nav-item">
											<a class="layout-grid nav-link active" data-toggle="tab" href="#layout-grid" role="tab"><span class="icon-column"><span class="layer first"><span></span><span></span><span></span></span><span class="layer middle"><span></span><span></span><span></span></span><span class="layer last"><span></span><span></span><span></span></span></span></a>
										</li>
										<li class="nav-item">
											<a class="layout-list nav-link" data-toggle="tab" href="#layout-list" role="tab"><span class="icon-column"><span class="layer first"><span></span><span></span></span><span class="layer middle"><span></span><span></span></span><span class="layer last"><span></span><span></span></span></span></a>
										</li>
									</ul>
								</div>
							</div>

							<div class="tab-content">
								<!-- Grid Layout -->
								<div class="tab-pane fade show active" id="layout-grid" role="tabpanel">
									<div class="products-list grid">
										<div class="row">
											@forelse($products as $product)
											<div class="col-xl-3 col-lg-4 col-md-4 col-sm-6">
												<div class="products-entry clearfix product-wapper">
													<div class="products-thumb">
														<div class="product-thumb-hover">
															<a href="{{ route('catalog.show', $product->id) }}">
																@if($product->image_path)
																<img width="600" height="600" src="{{ Storage::url($product->image_path) }}" class="post-image" alt="{{ $product->name }}">
																@else
																<img width="600" height="600" src="{{ asset('website/media/bamboo-6.jpg') }}" class="post-image" alt="{{ $product->name }}">
																@endif
															</a>
														</div>
													</div>
													<div class="products-content">
														<div class="contents text-center">
															<h3 class="product-title"><a href="{{ route('catalog.show', $product->id) }}">{{ $product->name }}</a></h3>
															<span class="price">Rs.{{ number_format($product->unit_price, 2) }}</span>
														</div>
													</div>
												</div>
											</div>
											@empty
											<div class="col-12 text-center py-5">
												<p class="text-muted">No products available at the moment.</p>
											</div>
											@endforelse
										</div>
									</div>
								</div>

								<!-- List Layout -->
								<div class="tab-pane fade" id="layout-list" role="tabpanel">
									<div class="products-list list">
										@forelse($products as $product)
										<div class="products-entry clearfix product-wapper">
											<div class="row">
												<div class="col-md-4">
													<div class="products-thumb">
														<div class="product-lable">
															<div class="hot">Hot</div>
														</div>
														<div class="product-thumb-hover">
															<a href="{{ route('catalog.show', $product->id) }}">
																@if($product->image_path)
																<img width="600" height="600" src="{{ Storage::url($product->image_path) }}" class="post-image" alt="{{ $product->name }}">
																@else
																<img width="600" height="600" src="{{ asset('website/media/product/1.jpg') }}" class="post-image" alt="{{ $product->name }}">
																<img width="600" height="600" src="{{ asset('website/media/product/1-2.jpg') }}" class="hover-image back" alt="{{ $product->name }}">
																@endif
															</a>
														</div>
														<span class="product-quickview" data-title="Quick View">
															<a href="{{ route('catalog.show', $product->id) }}" class="quickview quickview-button">Quick View <i class="icon-search"></i></a>
														</span>
													</div>
												</div>
												<div class="col-md-8">
													<div class="products-content">
														<h3 class="product-title"><a href="{{ route('catalog.show', $product->id) }}">{{ $product->name }}</a></h3>
														<span class="price">Rs.{{ number_format($product->unit_price, 2) }}</span>
														<div class="rating">
															<div class="star star-5"></div>
														</div>
														<div class="product-button">
															<div class="btn-add-to-cart" data-title="Add to cart">
																<a rel="nofollow" href="{{ route('catalog.show', $product->id) }}" class="product-btn button">Add to cart</a>
															</div>
															<div class="btn-wishlist" data-title="Wishlist">
																<button class="product-btn">Add to wishlist</button>
															</div>
															<div class="btn-compare" data-title="Compare">
																<button class="product-btn">Compare</button>
															</div>
														</div>
														<div class="product-description">{{ $product->description ?? 'No description available for this product.' }}</div>
													</div>
												</div>
											</div>
										</div>
										@empty
										<div class="text-center py-5">
											<p class="text-muted">No products available at the moment.</p>
										</div>
										@endforelse
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div>

@endsection