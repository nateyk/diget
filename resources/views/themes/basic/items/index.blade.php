@extends('themes.basic.layouts.single')
@section('header_title', translate('Discover'))
@section('title', translate('Discover'))
@section('breadcrumbs', Breadcrumbs::render('items'))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'items'))
@section('container', 'container-custom')
@section('header_v4', true)
@section('body_class', 'items-index-page')
@section('content')
    @include('themes.basic.partials.catalog-toolbar', [
        'catalogTitle' => request()->query->count() > 0 ? translate('Products matching your search') : translate('All products'),
        'catalogDescription' => translate('Browse digital products from independent creators.'),
        'catalogItems' => $items,
    ])
    <div class="items-index-layout row g-3">
        <div class="col-12 col-xl-3 col-xxl-2">
            @include('themes.basic.partials.search-params')
        </div>
        <div class="col-12 col-xl-9 col-xxl-10">
            @include('themes.basic.partials.search-items', [
                'items' => $items,
            ])
        </div>
    </div>
@endsection
