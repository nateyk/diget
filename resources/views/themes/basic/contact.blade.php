@extends('themes.basic.layouts.single')
@section('header_title', translate('Contact US'))
@section('title', translate('Contact US'))
@section('breadcrumbs', Breadcrumbs::render('contact'))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'contact'))
@section('header_v3', true)
@section('content')
    <div class="public-contact-page">
        <div class="row g-3 align-items-stretch">
            <div class="col-12 col-lg-5">
                <div class="card-v border public-contact-intro h-100 p-4">
                    <span class="public-page-kicker">{{ translate('Support') }}</span>
                    <h2>{{ translate('Tell us what you need help with') }}</h2>
                    <p>
                        {{ translate('Send a clear message and our team will get back to you as soon as possible.') }}
                    </p>
                    <div class="public-contact-points">
                        <div>
                            <i class="fa-regular fa-clock"></i>
                            <span>{{ translate('Fast response for account and storefront questions') }}</span>
                        </div>
                        <div>
                            <i class="fa-regular fa-circle-check"></i>
                            <span>{{ translate('Help with purchases, products, and creator tools') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <form action="{{ route('contact') }}" method="POST" class="card-v border public-form-card p-4 h-100">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">{{ translate('Name') }}</label>
                            <input type="text" name="name" class="form-control form-control-md"
                                value="{{ auth()->user() ? auth()->user()->getName() : '' }}" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">{{ translate('Email') }}</label>
                            <input type="email" name="email" class="form-control form-control-md"
                                value="{{ auth()->user() ? auth()->user()->email : '' }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Subject') }}</label>
                            <input type="text" name="subject" class="form-control form-control-md"
                                value="{{ old('subject') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ translate('Message') }}</label>
                            <textarea class="form-control form-control-md" name="message" rows="6" required>{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <x-captcha />
                    <button class="btn btn-primary btn-md px-4">{{ translate('Send message') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
