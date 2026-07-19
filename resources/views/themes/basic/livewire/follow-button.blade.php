<span class="d-inline-block">
    @if (authUser())
        @if ($user->id != authUser()->id)
            <button wire:click="followAction"
                class="btn {{ authUser()->isFollowingUser($user->id) ? 'btn-custom' : 'btn-outline-custom' }} {{ $iconButton ? 'btn-padding' : '' }}"
                aria-label="{{ authUser()->isFollowingUser($user->id) ? translate('Following') : translate('Follow') }}">
                @if (authUser()->isFollowingUser($user->id))
                    <i class="fa-solid fa-user-check"></i>
                    @if (!$iconButton)
                        <span class="ms-1">{{ translate('Following') }}</span>
                    @endif
                @else
                    <i class="fa-solid fa-user-plus"></i>
                    @if (!$iconButton)
                        <span class="ms-1">{{ translate('Follow') }}</span>
                    @endif
                @endif
            </button>
        @else
            <button class="btn btn-outline-custom {{ $iconButton ? 'btn-padding' : '' }} disabled"
                aria-label="{{ translate('Follow') }}">
                <i class="fa-solid fa-user-plus"></i>
                @if (!$iconButton)
                    <span class="ms-1">{{ translate('Follow') }}</span>
                @endif
            </button>
        @endif
    @else
        <a href="{{ route('login') }}" class="btn btn-outline-custom {{ $iconButton ? 'btn-padding' : '' }}"
            aria-label="{{ translate('Follow') }}">
            <i class="fa-solid fa-user-plus"></i>
            @if (!$iconButton)
                <span class="ms-1">{{ translate('Follow') }}</span>
            @endif
        </a>
    @endif
</span>
