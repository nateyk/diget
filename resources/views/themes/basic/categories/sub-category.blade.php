@extends('themes.basic.layouts.single')
@section('header_title', $subCategory->name)
@section('title', $subCategory->title ?? $subCategory->name)
@if ($subCategory->description)
    @section('description', $subCategory->description)
@endif
@section('breadcrumbs', Breadcrumbs::render('categories.sub-category', $category, $subCategory))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'categories.sub-category', $category,
    $subCategory))
@section('header_v4', true)
@section('body_class', 'items-index-page')
@section('container', 'container-custom')
@section('content')
    <x-ad alias="category_page_top" @class('mb-5') />
    @include('themes.basic.partials.catalog-toolbar', [
        'catalogTitle' => $subCategory->name,
        'catalogDescription' => $subCategory->description,
        'catalogItems' => $items,
        'clearFiltersUrl' => route('categories.sub-category', [$category->slug, $subCategory->slug]),
    ])
    <div class="items-index-layout row g-3">
        <div class="col-12 col-xl-3">
            @include('themes.basic.partials.search-params')
        </div>
        <div class="col-12 col-xl-9">
            @include('themes.basic.partials.search-items', [
                'items' => $items,
            ])
        </div>
    </div>
    <x-ad alias="category_page_bottom" @class('mt-5') />
@endsection
