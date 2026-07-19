@extends('themes.basic.workspace.layouts.app')
@section('title', translate('Settings'))
@section('breadcrumbs', Breadcrumbs::render('workspace.settings.profile'))
@section('content')
    @php
        $profileSocialPlatforms = config('profile_socials.platforms', []);
        $profileSocialLimit = config('profile_socials.max_links', 7);
        $selectedProfileSocialLinks = collect((array) $user->profile_social_links)
            ->filter(fn($value) => filled($value))
            ->only(array_keys($profileSocialPlatforms))
            ->take($profileSocialLimit);
    @endphp
    @include('themes.basic.workspace.settings.includes.tabs')
    <form action="{{ route('workspace.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="dashboard-card card-v mb-3">
            <div class="form-section">
                <h5 class="mb-0">{{ translate('Profile Details') }}</h5>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="p-4 border bg-light rounded-2">
                        <h5>{{ translate('Avatar') }}</h5>
                        <div class="my-3">
                            <img id="image-preview-1" class="border p-2 rounded-4 bg-light" src="{{ $user->getAvatar() }}"
                                alt="{{ $user->getName() }}" width="100px" height="100px">
                        </div>
                        <input type="file" name="avatar" class="form-control form-control-md image-input" data-id="1"
                            accept="image/png, image/jpg, image/jpeg">
                        <div class="form-text mt-2">
                            {{ translate('Allowed types (JPG,PNG) Size 120x120px') }}
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="p-4 border bg-light rounded-2">
                        <h5>{{ translate('Profile Cover') }}</h5>
                        <div class="my-3">
                            <img id="image-preview-2" class="border p-2 rounded-4 bg-light"
                                src="{{ $user->getProfileCover() }}" alt="{{ $user->getName() }}" width="200px"
                                height="100px">
                        </div>
                        <input type="file" name="profile_cover" class="form-control form-control-md image-input"
                            data-id="2" accept="image/png, image/jpg, image/jpeg">
                        <div class="form-text mt-2">
                            {{ translate('Allowed types (JPG,PNG) Size 1200x500px') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Profile Title') }}</label>
                <input type="text" name="profile_heading" class="form-control form-control-md"
                    value="{{ $user->profile_heading }}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Creator Card Description') }}</label>
                <textarea name="profile_card_description" class="form-control form-control-md" rows="3"
                    placeholder="{{ translate('Short creator intro for your storefront card') }}">{{ old('profile_card_description', $user->profile_card_description) }}</textarea>
                <div class="form-text">
                    {{ translate('Shown on the creator card. Keep it simple, up to 100 words.') }}
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ translate('Profile Description') }}</label>
                <textarea name="profile_description" class="ckeditor">{{ $user->profile_description }}</textarea>
            </div>
        </div>
        <div class="dashboard-card card-v mb-3">
            <div class="form-section">
                <h5 class="mb-0">{{ translate('Profile Contact Email') }}</h5>
            </div>
            <div>
                <label class="form-label">{{ translate('Email') }}</label>
                <input type="email" name="profile_contact_email" class="form-control form-control-md"
                    value="{{ $user->profile_contact_email }}">
                <div class="form-text">
                    {{ translate('Add your email to enable the contact form in your profile.') }}
                </div>
            </div>
        </div>
        <div class="dashboard-card card-v mb-3">
            <div class="form-section">
                <div class="row align-items-center g-2">
                    <div class="col">
                        <h5 class="mb-1">{{ translate('Profile Social Links') }}</h5>
                        <p class="text-muted small mb-0">
                            {{ translate('Add up to :count links for your storefront card.', ['count' => $profileSocialLimit]) }}
                        </p>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-light text-muted border" data-profile-social-count></span>
                    </div>
                </div>
            </div>
            <div class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md">
                    <label class="form-label">{{ translate('Add link') }}</label>
                    <div class="dashboard-picker drop-down" data-dropdown data-dropdown-position="top">
                        <button type="button" class="drop-down-btn form-control form-control-md"
                            data-profile-social-picker-btn>
                            <span data-profile-social-picker-label>{{ translate('Choose a platform') }}</span>
                            <i class="fa-solid fa-angle-down ms-auto"></i>
                        </button>
                        <div class="drop-down-menu" data-profile-social-picker-menu>
                            <button type="button" class="drop-down-item" data-profile-social-option=""
                                data-label="{{ translate('Choose a platform') }}">
                                {{ translate('Choose a platform') }}
                            </button>
                            @foreach ($profileSocialPlatforms as $platformKey => $platform)
                                <button type="button" class="drop-down-item" data-profile-social-option="{{ $platformKey }}"
                                    data-label="{{ $platform['label'] }}">
                                    <i class="bi {{ $platform['icon'] }}"></i>
                                    <span>{{ $platform['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" data-profile-social-select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="button" class="btn btn-outline-secondary btn-md w-100"
                        data-profile-social-add>
                        <i class="fa-solid fa-plus me-1"></i>
                        {{ translate('Add') }}
                    </button>
                </div>
            </div>

            <div class="vstack gap-2" data-profile-social-list
                data-max-links="{{ $profileSocialLimit }}">
                @foreach ($selectedProfileSocialLinks as $platformKey => $socialLink)
                    @continue(!isset($profileSocialPlatforms[$platformKey]))
                    <div class="border rounded-2 p-2" data-profile-social-row
                        data-platform="{{ $platformKey }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <span class="social-btn {{ $profileSocialPlatforms[$platformKey]['class'] }}">
                                    <i class="bi {{ $profileSocialPlatforms[$platformKey]['icon'] }}"></i>
                                </span>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="fw-medium">{{ $profileSocialPlatforms[$platformKey]['label'] }}</div>
                            </div>
                            <div class="col">
                                <input type="text" name="profile_social_links[{{ $platformKey }}]"
                                    class="form-control form-control-md"
                                    placeholder="{{ $profileSocialPlatforms[$platformKey]['placeholder'] }}"
                                    value="{{ $socialLink }}">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-outline-secondary btn-md btn-padding"
                                    data-profile-social-remove
                                    aria-label="{{ translate('Remove') }}">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-muted small {{ $selectedProfileSocialLinks->count() ? 'd-none' : '' }}"
                data-profile-social-empty>
                {{ translate('No social links added yet. Choose a platform above to add one.') }}
            </div>
        </div>
        <template id="profileSocialRowTemplate">
            <div class="border rounded-2 p-2" data-profile-social-row data-platform="__key__">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <span class="social-btn __class__">
                            <i class="bi __icon__"></i>
                        </span>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="fw-medium">__label__</div>
                    </div>
                    <div class="col">
                        <input type="text" name="profile_social_links[__key__]"
                            class="form-control form-control-md" placeholder="__placeholder__">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-secondary btn-md btn-padding"
                            data-profile-social-remove aria-label="{{ translate('Remove') }}">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <div class="dashboard-card card-v">
            <button class="btn btn-primary btn-md">{{ translate('Save Changes') }}</button>
        </div>
    </form>
    @include('themes.basic.workspace.partials.ckeditor')
    <script>
        (() => {
            const platforms = @json($profileSocialPlatforms);
            const select = document.querySelector('[data-profile-social-select]');
            const addButton = document.querySelector('[data-profile-social-add]');
            const list = document.querySelector('[data-profile-social-list]');
            const template = document.getElementById('profileSocialRowTemplate');
            const count = document.querySelector('[data-profile-social-count]');
            const empty = document.querySelector('[data-profile-social-empty]');
            const picker = document.querySelector('[data-profile-social-picker-menu]');
            const pickerLabel = document.querySelector('[data-profile-social-picker-label]');

            if (!select || !addButton || !list || !template) {
                return;
            }

            const maxLinks = Number(list.dataset.maxLinks || 7);
            const selectedPlatforms = () => Array.from(list.querySelectorAll('[data-profile-social-row]'))
                .map((row) => row.dataset.platform);
            const refreshSocialBuilder = () => {
                const selected = selectedPlatforms();
                if (selected.includes(select.value) || selected.length >= maxLinks) {
                    select.value = '';
                }
                addButton.disabled = !select.value || selected.length >= maxLinks;
                if (picker) {
                    picker.querySelectorAll('[data-profile-social-option]').forEach((option) => {
                        const optionValue = option.dataset.profileSocialOption;
                        option.classList.toggle('disabled', optionValue !== '' && selected.includes(optionValue));
                        option.disabled = optionValue !== '' && selected.includes(optionValue);
                    });
                }
                if (pickerLabel) {
                    pickerLabel.textContent = select.value && platforms[select.value]
                        ? platforms[select.value].label
                        : '{{ translate('Choose a platform') }}';
                }
                if (count) {
                    count.textContent = `${selected.length}/${maxLinks}`;
                }
                if (empty) {
                    empty.classList.toggle('d-none', selected.length > 0);
                }
            };

            if (picker) {
                picker.addEventListener('click', (event) => {
                    const option = event.target.closest('[data-profile-social-option]');
                    if (!option || option.disabled) {
                        return;
                    }
                    select.value = option.dataset.profileSocialOption;
                    option.closest('[data-dropdown]')?.classList.remove('active', 'animated');
                    refreshSocialBuilder();
                });
            }

            addButton.addEventListener('click', () => {
                const platformKey = select.value;
                const platform = platforms[platformKey];

                if (!platform || selectedPlatforms().length >= maxLinks) {
                    return;
                }

                const row = template.innerHTML
                    .replaceAll('__key__', platformKey)
                    .replaceAll('__class__', platform.class)
                    .replaceAll('__icon__', platform.icon)
                    .replaceAll('__label__', platform.label)
                    .replaceAll('__placeholder__', platform.placeholder);

                list.insertAdjacentHTML('beforeend', row);
                const input = list.querySelector(`[data-platform="${platformKey}"] input`);
                if (input) {
                    input.focus();
                }
                select.value = '';
                refreshSocialBuilder();
            });

            list.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-profile-social-remove]');
                if (!removeButton) {
                    return;
                }
                removeButton.closest('[data-profile-social-row]').remove();
                refreshSocialBuilder();
            });

            refreshSocialBuilder();
        })();
    </script>
@endsection
