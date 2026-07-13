@extends('themes.basic.workspace.layouts.app')
@section('section', translate('My Items'))
@section('title', translate('New Item'))
@section('back', route('workspace.items.index'))
@section('container', 'dashboard-container-sm')
@section('breadcrumbs', Breadcrumbs::render('workspace.items.create'))
@section('content')
    <form action="{{ route('workspace.items.store') }}" method="POST">
        @csrf
        <div class="dashboard-card card-v p-0 mb-4">
            <div class="card-v-header border-bottom py-3 px-4">
                <h5 class="mb-0">{{ translate('Name And Description') }}</h5>
            </div>
            <div class="card-v-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <label class="form-label">{{ translate('Name') }}</label>
                        <input type="text" name="name" class="form-control form-control-md" maxlength="100"
                            value="{{ old('name') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Description') }}</label>
                        <div class="rich-text-editor">
                            <textarea name="description" class="form-control ckeditor" rows="10"
                                placeholder="{{ translate('Describe your item in detail') }}">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-card card-v p-0 mb-4">
            <div class="card-v-header border-bottom py-3 px-4">
                <h5 class="mb-0">{{ translate('Category And Attributes') }}</h5>
            </div>
            <div class="card-v-body p-4">
                <div class="row g-4 mb-3">
                    <div class="col-lg-12">
                        <label class="form-label">{{ translate('Category') }}</label>
                        <input type="hidden" name="category" value="{{ $category->slug }}">
                        <select class="form-select form-select-md" disabled>
                            @foreach ($categories as $mainCategory)
                                <option value="{{ $mainCategory->slug }}" @selected($category->id == $mainCategory->id)>
                                    {{ $mainCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ translate('Tags') }}</label>
                        <input id="item-tags" type="text" name="tags" value="{{ old('tags') }}" required>
                        <div class="form-text">
                            {{ translate('Type your tag and click enter, maximum :maximum_tags tags.', ['maximum_tags' => @$settings->item->maximum_tags]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('themes.basic.workspace.items.includes.files-box')
        <div class="dashboard-card card-v p-0 mb-4">
            <div class="card-v-header border-bottom py-3 px-4">
                <h5 class="mb-0">{{ translate('Item Price') }}</h5>
            </div>
            <div class="card-v-body p-4">
                <div class="row g-4 mb-3">
                    <div class="col-md-12 col-lg-4 col-xxl-5">
                        @include('themes.basic.workspace.partials.input-price', [
                            'label' => translate('Item Price'),
                            'id' => 'regular-license-price',
                            'name' => 'regular_license_price',
                            'min' => @$settings->item->minimum_price,
                            'max' => @$settings->item->maximum_price,
                            'required' => true,
                        ])
                    </div>
                    <div class="col-md-12 col-lg-4 col-xxl-3">
                        @include('themes.basic.workspace.partials.input-price', [
                            'label' => translate('Buyer fee'),
                            'value' => $category->regular_buyer_fee,
                            'disabled' => true,
                        ])
                    </div>
                    <div class="col-md-12 col-lg-4 col-xxl-4">
                        @include('themes.basic.workspace.partials.input-price', [
                            'label' => translate('Purchase price'),
                            'id' => 'regular-license-purchase-price',
                            'value' => 0,
                            'disabled' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
        @if (@$settings->item->free_item_option)
            <div class="dashboard-card card-v p-0 mb-3">
                <div class="card-v-header border-bottom py-3 px-4">
                    <h5 class="mb-0">{{ translate('Free Item') }}</h5>
                </div>
                <div class="card-v-body p-4">
                    <p>
                        {{ translate('You can allow downloading your item for free, please note that everyone can download the item directly from the item page without purchasing, please make sure your item has no purchase code verification.') }}
                    </p>
                    <div>
                        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                            <input type="radio" class="btn-check free-item-option" name="free_item" value="0"
                                id="op1" @checked(old('free_item') ? old('free_item') == '0' : true)>
                            <label class="btn btn-outline-secondary" for="op1">{{ translate('No') }}</label>
                            <input type="radio" class="btn-check free-item-option" name="free_item" value="1"
                                id="op2" @checked(old('free_item') == '1')>
                            <label class="btn btn-outline-secondary" for="op2">{{ translate('Yes') }}</label>
                        </div>
                    </div>
                    <div class="mt-3 d-none purchasing-option">
                        <p>
                            {{ translate('You can also allow the purchase option along with the free download in case anyone wants to purchase a license.') }}
                        </p>
                        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                            <input type="radio" class="btn-check" name="purchasing_status" value="1"
                                id="opg1" @checked(old('purchasing_status') ? old('purchasing_status') == '0' : true)>
                            <label class="btn btn-outline-secondary"
                                for="opg1">{{ translate('Enable Purchasing') }}</label>
                            <input type="radio" class="btn-check" name="purchasing_status" value="0"
                                id="opg2" @checked(old('purchasing_status') == '1')>
                            <label class="btn btn-outline-secondary"
                                for="opg2">{{ translate('Disable Purchasing') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="dashboard-card card-v p-0 mb-3">
            <div class="card-v-header border-bottom py-3 px-4">
                <h5 class="mb-0">{{ translate('Message to the Reviewer') }}</h5>
            </div>
            <div class="card-v-body p-4">
                <textarea name="message" class="form-control" rows="6" placeholder="{{ translate('Your message') }}">{{ old('message') }}</textarea>
            </div>
        </div>
        <div class="dashboard-card card-v p-0">
            <div class="card-v-body p-4">
                <button class="btn btn-primary btn-md">{{ translate('Submit') }}</button>
            </div>
        </div>
    </form>
    @push('top_scripts')
        <script>
            "use strict";
            const itemConfig = {!! json_encode([
                'buyer_fee' => [
                    'regular' => $category->regular_buyer_fee,
                    'extended' => $category->extended_buyer_fee,
                ],
                'max_tags' => intval(@$settings->item->maximum_tags),
                'load_files_route' => route('workspace.items.files.load', hash_encode($category->id)),
            ]) !!};
        </script>
    @endpush
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script src="{{ theme_assets_with_version('assets/js/item.js') }}"></script>
    @endpush
    @include('themes.basic.workspace.partials.ckeditor')
@endsection
