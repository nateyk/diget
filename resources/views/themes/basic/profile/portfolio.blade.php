@extends('themes.basic.profile.layout')
@section('section', $user->username)
@section('title', translate('Portfolio'))
@section('content_size', 9)
@section('content')
    <div class="mb-4">
        <div class="row row-cols-auto justify-content-between align-items-center g-3">
            <div class="col">
                <h4 class="mb-0">{{ translate('Portfolio') }}</h4>
            </div>
            <div class="col">
                <div class="row g-3 row-cols-auto align-items-center">
                    <div class="col">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="form-search form-search-reverse">
                                <button class="icon">
                                    <i class="fa fa-search"></i>
                                </button>
                                <input type="text" name="search" placeholder="{{ translate('Search...') }}"
                                    class="form-control" value="{{ request('search') }}">
                            </div>
                        </form>
                    </div>
                    <div class="col">
                        @include('themes.basic.partials.grid-buttons')
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($items->count() > 0)
        <div class="items">
            <div class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-3">
                @foreach ($items as $item)
                    <div class="col w-100">
                        @include('themes.basic.partials.item', [
                            'item' => $item,
                            'item_classes' => 'border item-inline',
                        ])
                    </div>
                @endforeach
            </div>
        </div>
        {{ $items->links() }}
    @else
        @include('themes.basic.partials.public-empty-state', [
            'icon' => 'fa-regular fa-file-lines',
            'title' => translate('No portfolio entries yet'),
            'description' => translate('This creator has not published products to their portfolio yet.'),
            'actionUrl' => $user->getProfileLink(),
            'actionLabel' => translate('View creator storefront'),
        ])
    @endif
@endsection
