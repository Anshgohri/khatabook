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
						{{ $product->name }}
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="{{ route('home') }}">Home</a><span class="delimiter"></span><a href="{{ route('shop') }}">Shop</a><span class="delimiter"></span>{{ $product->name }}
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="shop-details">
				<div class="product-top-info">
					<div class="section-padding">
						<div class="section-container p-l-r">
							<div class="row">
								<div class="product-images col-lg-7 col-md-12 col-12">
									<div class="row">
										<div class="col-12">
											<div class="scroll-image main-image text-center">
												<div class="img-item" style="border-radius: 12px; overflow: hidden; background: #f8fafc; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
													<img width="900" height="900" src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; max-height: 550px; object-fit: contain; border-radius: 12px; display: block; margin: 0 auto;">
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="product-info col-lg-5 col-md-12 col-12 ">
									<h1 class="title">{{ $product->name }}</h1>
									<span class="price">
										<ins><span>Rs.{{ number_format($product->unit_price, 2) }}</span></ins>
									</span>
									<div class="rating">
										<div class="star star-5"></div>
									</div>
									<div class="description">
										<p>{{ !empty(trim($product->description ?? '')) ? $product->description : 'High quality ' . $product->name . ' designed for durability and strength.' }}</p>
									</div>
									<div class="product-meta">
										@if($product->category)
										<span class="posted-in">Category: <a href="{{ route('shop') }}" rel="tag">{{ $product->category->name }}</a></span>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="product-tabs">
					<div class="section-padding">
						<div class="section-container p-l-r">
							<div class="product-tabs-wrap">
								<ul class="nav nav-tabs" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a>
									</li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane fade show active" id="description" role="tabpanel">
										<p>{{ !empty(trim($product->description ?? '')) ? $product->description : 'High quality ' . $product->name . ' designed for durability and strength. Ideal for construction, scaffolding, and commercial site needs.' }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				@if(isset($relatedProducts) && $relatedProducts->count() > 0)
				<div class="product-related">
					<div class="section-padding">
						<div class="section-container p-l-r">
							<div class="block block-products slider">
								<div class="block-title">
									<h2>Related Products</h2>
								</div>
								<div class="block-content">
									<div class="content-product-list slick-wrap">
										<div class="slick-sliders products-list grid" data-slidestoscroll="true" data-dots="false" data-nav="1" data-columns4="1" data-columns3="2" data-columns2="3" data-columns1="3" data-columns1440="4" data-columns="4">
											@foreach($relatedProducts as $related)
											<div class="item-product slick-slide">
												<div class="items">
													<div class="products-entry clearfix product-wapper">
														<div class="products-thumb">
															<div class="product-thumb-hover">
																<a href="{{ route('catalog.show', $related->id) }}">
																	<img width="600" height="600" src="{{ $related->image_url }}" class="post-image" alt="{{ $related->name }}">
																</a>
															</div>
														</div>
														<div class="products-content">
															<div class="contents text-center">
																<h3 class="product-title"><a href="{{ route('catalog.show', $related->id) }}">{{ $related->name }}</a></h3>
																<div class="rating">
																	<div class="star star-5"></div>
																</div>
																<span class="price">Rs.{{ number_format($related->unit_price, 2) }}</span>
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
				</div>
				@endif
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div>
@endsection