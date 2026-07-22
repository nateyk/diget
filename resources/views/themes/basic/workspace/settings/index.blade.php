@extends('themes.basic.workspace.layouts.app')
@section('title', translate('Settings'))
@section('breadcrumbs', Breadcrumbs::render('workspace.settings'))
@section('content')
    @include('themes.basic.workspace.settings.includes.tabs')
    <div class="dashboard-card card-v mb-3">
        <div class="form-section">
            <h5 class="mb-0">{{ translate('Account details') }}</h5>
        </div>
        <form action="{{ route('workspace.settings.update') }}" method="POST">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-4">
                    <label class="form-label">{{ translate('First Name') }}</label>
                    <input type="firstname" name="firstname" class="form-control form-control-md"
                        value="{{ $user->firstname }}" required>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label">{{ translate('Last Name') }}</label>
                    <input type="lastname" name="lastname" class="form-control form-control-md"
                        value="{{ $user->lastname }}" required>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label">{{ translate('Username') }}</label>
                    <input type="text" name="username" class="form-control form-control-md" value="{{ $user->username }}"
                        disabled>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ translate('Email address') }}</label>
                    <input type="email" name="email" class="form-control form-control-md" value="{{ $user->email }}">
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Address line 1') }}</label>
                    <input type="text" name="address_line_1" class="form-control form-control-md"
                        value="{{ @$user->address->line_1 }}" required>
                </div>
                <div class="col-lg-6">
                    <label class="form-label">{{ translate('Address line 2') }}</label>
                    <input type="text" name="address_line_2" class="form-control form-control-md"
                        value="{{ @$user->address->line_2 }}">
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('City') }}</label>
                        <input type="text" name="city" class="form-control form-control-md"
                            value="{{ @$user->address->city }}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('State') }}</label>
                        <input type="text" name="state" class="form-control form-control-md"
                            value="{{ @$user->address->state }}" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="mb-3">
                        <label class="form-label">{{ translate('Postal code') }}</label>
                        <input type="text" name="zip" class="form-control form-control-md"
                            value="{{ @$user->address->zip }}" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <label class="form-label">{{ translate('Country') }}</label>
                    <select name="country" class="form-select form-select-md" required>
                        <option value="">--</option>
                        @foreach (countries() as $countryCode => $countryName)
                            <option value="{{ $countryCode }}" @selected($countryCode == @$user->address->country)>
                                {{ $countryName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($user->isAuthor())
                    <div class="col-lg-12">
                        <label class="form-label">{{ translate('Exclusivity of Your Items') }}</label>
                        <select name="exclusivity" class="form-select form-select-md">
                            <option value="">--</option>
                            <option value="exclusive" @selected($user->isExclusiveAuthor())>
                                {{ translate('Exclusive') }}
                            </option>
                            <option value="non_exclusive" @selected($user->isNonExclusiveAuthor())>
                                {{ translate('Non Exclusive') }}
                            </option>
                        </select>
                        <div class="form-text">{{ translate('You will be awarded an exclusive author badge') }}
                        </div>
                    </div>
                @endif
            </div>
            <button class="btn btn-primary btn-md">{{ translate('Save Changes') }}</button>
        </form>
    </div>
    <div class="dashboard-card card-v mb-3">
        <div class="form-section">
            <h5 class="mb-0">{{ translate('Public username') }}</h5>
        </div>
        <form action="{{ route('workspace.settings.username.update') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <label for="username" class="form-label">{{ translate('Username') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input id="username" type="text" name="username"
                            class="form-control form-control-md @error('username') is-invalid @enderror"
                            value="{{ old('username', $user->username) }}" minlength="6" maxlength="50"
                            aria-describedby="usernameHelp usernameWarning" @disabled(!$canChangeUsername) required>
                    </div>
                    @error('username')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div id="usernameHelp" class="form-text">
                        {{ translate('Your public storefront is :url', ['url' => $user->getProfileLink()]) }}
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <label for="current_password" class="form-label">{{ translate('Current password') }}</label>
                    <input id="current_password" type="password" name="current_password"
                        class="form-control form-control-md @error('current_password') is-invalid @enderror"
                        autocomplete="current-password" @disabled(!$canChangeUsername) required>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <p id="usernameWarning" class="small text-muted mt-3 mb-3">
                @if ($canChangeUsername)
                    {{ translate('Changing your username changes your public link. Your old link will redirect here, and you can change again after 30 days.') }}
                @else
                    {{ translate('You can change your username again on :date.', ['date' => $nextUsernameChangeAt->toDayDateTimeString()]) }}
                @endif
            </p>
            <button class="btn btn-primary btn-md" @disabled(!$canChangeUsername)>
                {{ translate('Change username') }}
            </button>
        </form>
    </div>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/bootstrap/select/bootstrap-select.min.js') }}"></script>
    @endpush
@endsection
