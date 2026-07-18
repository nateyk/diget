@php
    $idSuffix = $idSuffix ?? '';
    $dateFilters = [
        ['value' => '', 'label' => translate('Any time')],
        ['value' => 'this_month', 'label' => translate('This month')],
        ['value' => 'last_month', 'label' => translate('Last month')],
        ['value' => 'this_year', 'label' => translate('This year')],
        ['value' => 'last_year', 'label' => translate('Last year')],
    ];
@endphp

<div class="discover-filters-header">
    {{ translate('Filters') }}
</div>

@if (isset($category) && $category->categoryOptions->count() > 0)
    @foreach ($category->categoryOptions as $categoryOptionIndex => $categoryOption)
        <details class="discover-filter-section">
            <summary>
                <span>{{ $categoryOption->name }}</span>
                <i class="fa fa-chevron-right"></i>
            </summary>
            <div class="discover-filter-body">
                @foreach ($categoryOption->options as $optionIndex => $value)
                    @php($inputId = 'categoryOption' . $idSuffix . $categoryOptionIndex . $optionIndex)
                    <label class="discover-filter-option" for="{{ $inputId }}">
                        <span>{{ $value }}</span>
                        <input class="form-check-input search-param"
                            type="{{ $categoryOption->isMultiple() ? 'checkbox' : 'radio' }}"
                            name="{{ strtolower(Str::slug($categoryOption->name, '_')) }}{{ $categoryOption->isMultiple() ? '[]' : '' }}"
                            value="{{ strtolower(Str::slug($value)) }}" id="{{ $inputId }}"
                            {{ $categoryOption->isMultiple() ? 'data-multiple=true' : '' }}>
                    </label>
                @endforeach
            </div>
        </details>
    @endforeach
@endif

<details class="discover-filter-section">
    <summary>
        <span>{{ translate('Price') }}</span>
        <i class="fa fa-chevron-right"></i>
    </summary>
    <div class="discover-filter-body">
        <div class="discover-filter-price">
            <input id="{{ $priceMinId }}" type="number" name="min_price" class="form-control form-control-md"
                placeholder="{{ translate('min') }}" value="{{ request()->input('min_price') }}" />
            <input id="{{ $priceMaxId }}" type="number" name="max_price" class="form-control form-control-md"
                placeholder="{{ translate('max') }}" value="{{ request()->input('max_price') }}" />
            <button id="{{ $priceButtonId }}" class="btn btn-primary btn-md btn-padding search-by-price"
                aria-label="{{ translate('Apply price') }}">
                <i class="fa fa-arrow-right fa-rtl"></i>
            </button>
        </div>
    </div>
</details>

@if (@$settings->item->reviews_status)
    <details class="discover-filter-section">
        <summary>
            <span>{{ translate('Rating') }}</span>
            <i class="fa fa-chevron-right"></i>
        </summary>
        <div class="discover-filter-body">
            <label class="discover-filter-option" for="ratingAll{{ $idSuffix }}">
                <span>{{ translate('Show All') }}</span>
                <input class="form-check-input search-param" type="radio" name="stars" value=""
                    id="ratingAll{{ $idSuffix }}">
            </label>
            @foreach ([5, 4, 3, 2, 1] as $stars)
                <label class="discover-filter-option discover-filter-rating" for="rating{{ $stars }}{{ $idSuffix }}">
                    <span>
                        @include('themes.basic.partials.rating-stars', [
                            'stars' => $stars,
                            'ratings_classes' => 'discover-rating-stars',
                        ])
                        <span>{{ $stars }} {{ translate($stars > 1 ? 'stars' : 'star') }}</span>
                    </span>
                    <input class="form-check-input search-param" type="radio" name="stars" value="{{ $stars }}"
                        id="rating{{ $stars }}{{ $idSuffix }}">
                </label>
            @endforeach
        </div>
    </details>
@endif

<details class="discover-filter-section">
    <summary>
        <span>{{ translate('Date Added') }}</span>
        <i class="fa fa-chevron-right"></i>
    </summary>
    <div class="discover-filter-body">
        @foreach ($dateFilters as $key => $filter)
            @php($inputId = 'dateFilter' . $idSuffix . $key)
            <label class="discover-filter-option" for="{{ $inputId }}">
                <span>{{ $filter['label'] }}</span>
                <input class="form-check-input search-param" type="radio" name="date" value="{{ $filter['value'] }}"
                    id="{{ $inputId }}">
            </label>
        @endforeach
    </div>
</details>
