<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/simplebar/simplebar.min.css') }}">
    @endpush
    @section('noindex', true)
    @include('themes.basic.includes.head')
</head>

<body>
    <div class="dashboard workspace-dashboard">
        @include('themes.basic.workspace.includes.sidebar')
        <div class="dashboard-body">
            @include('themes.basic.workspace.includes.navbar')
            <div class="dashboard-container @yield('container') pt-4 pb-5">
                @if (@settings('kyc')->status && @settings('kyc')->required && !authUser()->isKycVerified())
                    @if (authUser()->isKycPending())
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">{{ translate('KYC Verification Pending') }}</h4>
                            <span>{{ translate('Your KYC verification is currently pending. We are processing your information, and you will be notified once the verification is complete.') }}</span>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">{{ translate('KYC Verification Required') }}</h4>
                            <p>{{ translate('Your KYC verification is required to continue using our platform. Please complete the verification process as soon as possible.') }}
                            </p>
                            <a href="{{ route('workspace.settings.kyc') }}"
                                class="btn btn-danger">{{ translate('Complete KYC') }}<i
                                    class="fa-solid fa-arrow-right ms-2"></i></a>
                        </div>
                    @endif
                @endif
                @if (licenseType(2) && @$settings->premium->status && authUser()->isSubscribed())
                    @if (authUser()->subscription->isAboutToExpire())
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">{{ translate('Your subscription is about to expire') }}</h4>
                            <span>{{ translate('Your current subscription is nearing its expiration date. To continue enjoying uninterrupted access to premium features, please renew your subscription before it expires.') }}</span>
                        </div>
                    @elseif(authUser()->subscription->isExpired())
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">{{ translate('Your subscription has been expired') }}</h4>
                            <span>{{ translate('Your subscription has expired, and you no longer have access to premium features. Please renew your subscription to regain access to all features.') }}</span>
                        </div>
                    @endif
                @endif
                @if (!request()->routeIs('workspace.become-an-author'))
                    @include('themes.basic.workspace.partials.page-header')
                    @yield('content')
                @else
                    <div class="mt-4">
                        @yield('content')
                    </div>
                @endif
            </div>
            <footer class="dashboard-footer">
                <div class="row justify-content-between">
                    <div class="col-auto">
                        <p class="mb-0">&copy; <span data-year></span>
                            {{ @$settings->general->site_name }} - {{ translate('All rights reserved') }}.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/simplebar/simplebar.min.js') }}"></script>
    @endpush
    @include('themes.basic.includes.config')
    @include('themes.basic.includes.scripts')
</body>

</html>
