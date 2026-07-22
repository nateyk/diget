@extends('themes.basic.layouts.single')
@section('header_title', $category->name)
@section('title', $category->title ?? $category->name)
@if ($category->description)
    @section('description', $category->description)
@endif
@section('header_v4', true)
@section('body_class', 'items-index-page')
@section('breadcrumbs', Breadcrumbs::render('categories.category', $category))
@section('breadcrumbs_schema', Breadcrumbs::view('breadcrumbs::json-ld', 'categories.category', $category))
@section('container', 'container-custom')
@section('content')
    <x-ad alias="category_page_top" @class('mb-5') />
    @include('themes.basic.partials.catalog-toolbar', [
        'catalogTitle' => $category->name,
        'catalogDescription' => $category->description,
        'catalogItems' => $items,
        'clearFiltersUrl' => route('categories.category', $category->slug),
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
