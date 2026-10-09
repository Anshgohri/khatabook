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
						Contact Us
					</h1>
				</div>
				<div class="breadcrumbs">
					<a href="/">Home</a><span class="delimiter"></span>Contact Us
				</div>
			</div>
		</div>

		<div id="content" class="site-content" role="main">
			<div class="page-contact">
				<section class="section section-padding m-b-70">
					<div class="section-container">
						<!-- Block Contact Info -->
						<div class="block block-contact-info">
							<div class="block-widget-wrap">
								<div class="info-icon">
									<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="svg-icon2 plant" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve">
										<g>
											<path xmlns="http://www.w3.org/2000/svg" d="m320.174 28.058a8.291 8.291 0 0 0 -7.563-4.906h-113.222a8.293 8.293 0 0 0 -7.564 4.907l-66.425 148.875a8.283 8.283 0 0 0 7.564 11.655h77.336v67.765a20.094 20.094 0 1 0 12 0v-67.765h27.7v288.259h-48.441a6 6 0 0 0 0 12h108.882a6 6 0 0 0 0-12h-48.441v-288.259h117.04a8.284 8.284 0 0 0 7.564-11.657zm-103.874 255.567a8.094 8.094 0 1 1 8.094-8.093 8.1 8.1 0 0 1 -8.094 8.093zm-77.61-107.036 63.11-141.437h108.4l63.11 141.437z" fill="" data-original="" style=""></path>
										</g>
									</svg>
								</div>
								<div class="info-title">
									<h2>Need Help?</h2>
								</div>
								<div class="info-items">
									<div class="row">
										<div class="col-md-4 sm-m-b-30">
											<div class="info-item">
												<div class="item-tilte">
													<h2>Address</h2>
												</div>
												<div class="item-content">
													{{ $storeDetails['storeAddress'] }}
												</div>
											</div>
										</div>
										<div class="col-md-4 sm-m-b-30">
											<div class="info-item">
												<div class="item-tilte">
													<h2>Phone & Email</h2>
												</div>
												<div class="item-content">
													@foreach($storeDetails['storePhonesArray'] ?? array_filter(array_map('trim', preg_split('/[,|]/', (string) ($storeDetails['storePhone'] ?? '')))) as $phone)
													<p>{{ $phone }}</p>
													@endforeach
													<p style="margin-top: 5px; color: #666;">✉️ {{ $storeDetails['storeEmail'] }}</p>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="info-item">
												<div class="item-tilte">
													<h2>Customer Service</h2>
												</div>
												<div class="item-content">
													<p>Monday to Friday</p>
													<p>8:00am – 4:00pm</p>
													<p>Saturday and Sunday closed</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				<section class="section section-padding contact-background m-b-0">
					<div class="section-container small">
						<!-- Block Contact Form -->
						<div class="block block-contact-form">
							<div class="block-widget-wrap">
								<div class="block-title">
									<h2>Send Us Your Questions!</h2>
									<div class="sub-title">We’ll get back to you within two days.</div>
								</div>
								<div class="block-content">
									@if(session('success'))
										<div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border: 1px solid #c3e6cb; margin-bottom: 25px; font-weight: 500;">
											{{ session('success') }}
										</div>
									@endif

									@if($errors->any())
										<div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; border: 1px solid #f5c6cb; margin-bottom: 25px;">
											<ul style="margin: 0; padding-left: 20px;">
												@foreach($errors->all() as $error)
													<li>{{ $error }}</li>
												@endforeach
											</ul>
										</div>
									@endif

									<form action="{{ route('contact.store') }}" method="post" class="contact-form">
										@csrf
										<div class="contact-us-form">
											<div class="row">
												<div class="col-sm-12 col-md-6" style="margin-bottom: 15px;">
													<label class="required">Name</label><br>
													<span class="form-control-wrap">
														<input type="text" name="name" value="{{ old('name') }}" size="40" class="form-control" aria-required="true" required>
													</span>
													@error('name')
														<span style="color: #dc3545; font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>
													@enderror
												</div>
												<div class="col-sm-12 col-md-6" style="margin-bottom: 15px;">
													<label class="required">Phone Number</label><br>
													<span class="form-control-wrap">
														<input type="tel" name="phone" value="{{ old('phone') }}" size="40" class="form-control" aria-required="true" required placeholder="e.g. +91 9876543210">
													</span>
													@error('phone')
														<span style="color: #dc3545; font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>
													@enderror
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12 col-md-6" style="margin-bottom: 15px;">
													<label>Email Address</label><br>
													<span class="form-control-wrap">
														<input type="email" name="email" value="{{ old('email') }}" size="40" class="form-control" placeholder="e.g. name@example.com">
													</span>
													@error('email')
														<span style="color: #dc3545; font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>
													@enderror
												</div>
												<div class="col-sm-12 col-md-6" style="margin-bottom: 15px;">
													<label>Inquiry Type</label><br>
													<span class="form-control-wrap">
														<select name="inquiry_type" class="form-control" style="width: 100%; height: 45px; border: 1px solid #e5e5e5; padding: 0 15px; border-radius: 4px; background-color: #fff;">
															<option value="General Inquiry" {{ old('inquiry_type', 'General Inquiry') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
															<option value="Bamboo Purchase" {{ old('inquiry_type') == 'Bamboo Purchase' ? 'selected' : '' }}>Bamboo Purchase</option>
															<option value="Scaffolding Rental" {{ old('inquiry_type') == 'Scaffolding Rental' ? 'selected' : '' }}>Scaffolding Rental</option>
															<option value="Bulk Order Request" {{ old('inquiry_type') == 'Bulk Order Request' ? 'selected' : '' }}>Bulk Order Request</option>
															<option value="Custom Requirement" {{ old('inquiry_type') == 'Custom Requirement' ? 'selected' : '' }}>Custom Requirement</option>
														</select>
													</span>
													@error('inquiry_type')
														<span style="color: #dc3545; font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>
													@enderror
												</div>
											</div>
											<div class="row">
												<div class="col-sm-12" style="margin-bottom: 15px;">
													<label class="required">Message</label><br>
													<span class="form-control-wrap">
														<textarea name="message" cols="40" rows="8" class="form-control" aria-required="true" required placeholder="How can we help you?">{{ old('message') }}</textarea>
													</span>
													@error('message')
														<span style="color: #dc3545; font-size: 0.85rem; margin-top: 4px; display: block;">{{ $message }}</span>
													@enderror
												</div>
											</div>
											<div class="form-button">
												<input type="submit" value="Submit Inquiry" class="button">
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
</div><!-- #main-content -->

@endsection