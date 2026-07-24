@extends('themes.basic.workspace.layouts.app')
@section('section', translate('My Items'))
@section('title', translate('New Product'))
@section('back', route('workspace.items.index'))
@section('container', 'dashboard-container-lg')
@section('breadcrumbs', Breadcrumbs::render('workspace.items.create'))
@section('content')
    <form action="{{ route('workspace.items.store') }}" method="POST" class="workspace-item-create-form">
        @csrf
        <input type="hidden" name="category" value="{{ $category->slug }}">

        <div class="row g-3 align-items-start">
            <div class="col-12 col-xl-8">
                <div class="dashboard-card card-v p-0 mb-3">
                    <div class="card-v-header border-bottom py-3 px-3 px-lg-4">
                        <div class="row row-cols-auto align-items-center justify-content-between g-2">
                            <div class="col">
                                <h5 class="mb-0">{{ translate('Product Details') }}</h5>
                            </div>
                            <div class="col">
                                <span class="small text-muted">
                                    <i class="fa-solid fa-folder-open me-1"></i>
                                    {{ $category->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-v-body p-3 p-lg-4">
                        <div class="mb-3">
                            <label for="productName" class="form-label">{{ translate('Product Name') }}</label>
                            <input id="productName" type="text" name="name" class="form-control form-control-md"
                                maxlength="100" value="{{ old('name') }}"
                                placeholder="{{ translate('Give your product a clear, specific name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="item-tags" class="form-label">{{ translate('Tags') }}</label>
                            <input id="item-tags" type="text" name="tags" value="{{ old('tags') }}" required>
                            <div class="form-text">
                                {{ translate('Add up to :maximum_tags search terms. Press Enter after each tag.', ['maximum_tags' => @$settings->item->maximum_tags]) }}
                            </div>
                        </div>

                        <div>
                            <label for="productDescription" class="form-label">{{ translate('Product Description') }}</label>
                            <div class="rich-text-editor">
                                <textarea id="productDescription" name="description" class="form-control ckeditor" rows="10"
                                    placeholder="{{ translate('Explain what the buyer receives and how the product can be used') }}"
                                    required>{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @include('themes.basic.workspace.items.includes.files-box', [
                    'filesBoxTitle' => translate('Product Files'),
                    'filesBoxMargin' => 'mb-3',
                    'filesBoxHeaderPadding' => 'py-3 px-3 px-lg-4',
                    'filesBoxBodyPadding' => 'p-3 p-lg-4',
                    'compactFileFields' => true,
                ])
            </div>

            <div class="col-12 col-xl-4">
                <div class="workspace-item-create-aside">
                    <div class="dashboard-card card-v p-0">
                        <div class="card-v-header border-bottom py-3 px-3 px-lg-4">
                            <h5 class="mb-0">{{ translate('Pricing And Publishing') }}</h5>
                        </div>
                        <div class="card-v-body p-3 p-lg-4">
                            <div class="mb-3">
                                @include('themes.basic.workspace.partials.input-price', [
                                    'label' => translate('Product Price'),
                                    'id' => 'regular-license-price',
                                    'name' => 'regular_license_price',
                                    'min' => @$settings->item->minimum_price,
                                    'max' => @$settings->item->maximum_price,
                                    'required' => true,
                                ])
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12 col-sm-6">
                                    @include('themes.basic.workspace.partials.input-price', [
                                        'label' => translate('Buyer Fee'),
                                        'value' => $category->regular_buyer_fee,
                                        'disabled' => true,
                                        'input_classes' => 'bg-light',
                                    ])
                                </div>
                                <div class="col-12 col-sm-6">
                                    @include('themes.basic.workspace.partials.input-price', [
                                        'label' => translate('Total Price'),
                                        'id' => 'regular-license-purchase-price',
                                        'value' => 0,
                                        'disabled' => true,
                                        'input_classes' => 'bg-light',
                                    ])
                                </div>
                            </div>

                            @if (@$settings->item->free_item_option)
                                <div class="border-top pt-3 mb-3">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ translate('Free Download') }}</h6>
                                            <p class="small text-muted mb-0">
                                                {{ translate('Allow visitors to download this product without payment.') }}
                                            </p>
                                        </div>
                                        <div class="btn-group btn-group-sm flex-shrink-0" role="group"
                                            aria-label="{{ translate('Free download availability') }}">
                                            <input type="radio" class="btn-check free-item-option" name="free_item"
                                                value="0" id="freeItemNo" @checked(old('free_item', '0') == '0')>
                                            <label class="btn btn-outline-secondary" for="freeItemNo">{{ translate('No') }}</label>
                                            <input type="radio" class="btn-check free-item-option" name="free_item"
                                                value="1" id="freeItemYes" @checked(old('free_item') == '1')>
                                            <label class="btn btn-outline-secondary" for="freeItemYes">{{ translate('Yes') }}</label>
                                        </div>
                                    </div>

                                    <div class="purchasing-option {{ old('free_item') == '1' ? '' : 'd-none' }}">
                                        <label class="form-label">{{ translate('Paid Purchase Option') }}</label>
                                        <div class="btn-group btn-group-sm w-100" role="group"
                                            aria-label="{{ translate('Paid purchase availability') }}">
                                            <input type="radio" class="btn-check" name="purchasing_status" value="1"
                                                id="purchaseEnabled" @checked(old('purchasing_status', '1') == '1')>
                                            <label class="btn btn-outline-secondary"
                                                for="purchaseEnabled">{{ translate('Enabled') }}</label>
                                            <input type="radio" class="btn-check" name="purchasing_status" value="0"
                                                id="purchaseDisabled" @checked(old('purchasing_status') == '0')>
                                            <label class="btn btn-outline-secondary"
                                                for="purchaseDisabled">{{ translate('Disabled') }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="border-top pt-3 mb-3">
                                <label for="reviewerMessage" class="form-label">
                                    {{ translate('Submission Note') }}
                                    <span class="text-muted fw-normal">({{ translate('Optional') }})</span>
                                </label>
                                <textarea id="reviewerMessage" name="message" class="form-control" rows="4"
                                    placeholder="{{ translate('Add any details needed to review this product') }}">{{ old('message') }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-md">
                                    <i class="fa-solid fa-check me-1"></i>
                                    {{ translate('Create Product') }}
                                </button>
                                <a href="{{ route('workspace.items.index') }}" class="btn btn-outline-secondary btn-md">
                                    {{ translate('Cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/jquery/jquery.priceformat.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script src="{{ theme_assets_with_version('assets/js/item.js') }}"></script>
    @endpush
    @include('themes.basic.workspace.partials.ckeditor')
@endsection
