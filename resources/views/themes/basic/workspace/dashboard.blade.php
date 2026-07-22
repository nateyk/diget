@extends('themes.basic.workspace.layouts.app')
@section('title', translate('Dashboard'))
@section('breadcrumbs', Breadcrumbs::render('workspace.dashboard'))
@section('content')
    <div class="workspace-dashboard">
        <div class="workspace-stats mb-3">
            <div class="row g-3">
                <div class="col-12 col-lg-6 col-xl-{{ @$settings->referral->status ? 3 : 4 }}">
                    @include('themes.basic.workspace.partials.stat-card', [
                        'variant' => 'dashboard-counter-info',
                        'icon' => 'fa-solid fa-cart-arrow-down',
                        'label' => translate('Total Sales'),
                        'value' => number_format($counters['total_sales']),
                    ])
                </div>
                <div class="col-12 col-lg-6 col-xl-{{ @$settings->referral->status ? 3 : 4 }}">
                    @include('themes.basic.workspace.partials.stat-card', [
                        'icon' => 'fa-solid fa-dollar',
                        'label' => translate('Sales Earnings'),
                        'value' => getAmount($counters['sales_earnings']),
                    ])
                </div>
                @if (@$settings->referral->status)
                    <div class="col-12 col-lg-6 col-xl-3">
                        @include('themes.basic.workspace.partials.stat-card', [
                            'variant' => 'dashboard-counter-danger',
                            'icon' => 'fa-solid fa-money-bill-trend-up',
                            'label' => translate('Referral Earnings'),
                            'value' => getAmount($counters['referrals_earnings']),
                        ])
                    </div>
                @endif
                <div
                    class="col-12 {{ @$settings->referral->status ? 'col-lg-6' : '' }} col-xl-{{ @$settings->referral->status ? 3 : 4 }}">
                    @include('themes.basic.workspace.partials.stat-card', [
                        'variant' => 'dashboard-counter-secondary',
                        'icon' => 'fa-regular fa-eye',
                        'label' => translate('Total Views'),
                        'value' => number_format($counters['total_views']),
                    ])
                </div>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-xxl-7">
                <div class="dashboard-chart-card dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Sales Statistics') }}</h2>
                    <div class="dashboard-chart">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xxl-5">
                <div class="dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Top selling items') }}</h2>
                    @each('themes.basic.workspace.partials.dashboard-top-selling-item', $topSellingItems, 'topSellingItem', 'themes.basic.workspace.partials.card-empty')
                </div>
            </div>
            <div class="col-12 col-xxl-5">
                <div class="dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Top purchasing countries') }}</h2>
                    @each('themes.basic.workspace.partials.dashboard-top-purchasing-country', $topPurchasingCountries, 'topPurchasingCountry', 'themes.basic.workspace.partials.card-empty')
                </div>
            </div>
            <div class="col-12 col-xxl-7">
                <div class="dashboard-chart-card dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Purchasing Countries') }}</h2>
                    <div class="dashboard-chart">
                        <div class="chart w-100" id="countries-chart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xxl-7">
                <div class="dashboard-chart-card dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Views Statistics') }}</h2>
                    <div class="dashboard-chart">
                        <canvas id="views-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xxl-5">
                <div class="dashboard-chart-card dashboard-card card-v workspace-panel h-100">
                    <h2 class="workspace-panel-title">{{ translate('Top Referrals') }}</h2>
                    @each('themes.basic.workspace.partials.dashboard-referral', $referrals, 'referral', 'themes.basic.workspace.partials.card-empty')
                </div>
            </div>
        </div>
    </div>
    @push('top_scripts')
        @php
            $chartsConfig = [
                'sales' => $charts['sales'],
                'views' => $charts['views'],
                'geo' => [
                    'data' => [],
                ],
            ];
            $chartsConfig['geo']['data'][] = ['Country', translate('Sales')];
            if (!$geoCountries->isEmpty()) {
                foreach ($geoCountries as $geoCountry) {
                    $chartsConfig['geo']['data'][] = [$geoCountry->country, (int) $geoCountry->total_sales];
                }
            }
        @endphp
        <script>
            "use strict";
            const chartsConfig = @json($chartsConfig);
        </script>
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/chartjs/chart.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/geochart/geochart.min.js') }}"></script>
        <script src="{{ theme_assets_with_version('assets/js/charts.js') }}"></script>
    @endpush
@endsection
