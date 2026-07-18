<span class="d-inline-block">
    @if (authUser())
        <button wire:click="addToFavorite"
            class="btn {{ authUser()->hasItemInFavorite($item->id) ? 'btn-custom' : 'btn-outline-custom' }} btn-md px-3"
            aria-label="{{ authUser()->hasItemInFavorite($item->id) ? translate('Remove :name from favorites', ['name' => $item->name]) : translate('Add :name to favorites', ['name' => $item->name]) }}"
            aria-pressed="{{ authUser()->hasItemInFavorite($item->id) ? 'true' : 'false' }}">
            <i class=" {{ authUser()->hasItemInFavorite($item->id) ? 'fa-solid' : 'fa-regular' }} fa-heart"></i>
        </button>
    @else
    <a href="{{ route('login') }}" class="btn btn-outline-custom btn-md px-3"
        aria-label="{{ translate('Sign in to add :name to favorites', ['name' => $item->name]) }}">
            <i class="fa-regular fa-heart"></i>
        </a>
    @endif
</span>
