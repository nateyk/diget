@extends('themes.basic.workspace.layouts.app')
@section('title', translate('Settings'))
@section('breadcrumbs', Breadcrumbs::render('workspace.settings'))
@section('content')
    @include('themes.basic.workspace.settings.includes.tabs')
    <div class="dashboard-card card-v mb-3 workspace-account-card">
        <div class="form-section">
            <h4 class="mb-0">{{ translate('Account details') }}</h4>
        </div>
        <form action="{{ route('workspace.settings.update') }}" method="POST" class="workspace-account-form">
            @csrf
            <div class="row g-3 mb-3">
                <div class="col-12 col-lg-6">
                    <label for="accountFirstName" class="form-label">{{ translate('First Name') }}</label>
                    <input id="accountFirstName" type="text" name="firstname" class="form-control form-control-md"
                        value="{{ $user->firstname }}" autocomplete="given-name" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountLastName" class="form-label">{{ translate('Last Name') }}</label>
                    <input id="accountLastName" type="text" name="lastname" class="form-control form-control-md"
                        value="{{ $user->lastname }}" autocomplete="family-name" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountEmail" class="form-label">{{ translate('Email address') }}</label>
                    <input id="accountEmail" type="email" name="email" class="form-control form-control-md"
                        value="{{ $user->email }}" autocomplete="email" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountCountry" class="form-label">{{ translate('Country') }}</label>
                    <select id="accountCountry" name="country" class="selectpicker selectpicker-md"
                        autocomplete="country" required>
                        <option value="">--</option>
                        @foreach (countries() as $countryCode => $countryName)
                            <option value="{{ $countryCode }}" @selected($countryCode == @$user->address->country)>
                                {{ $countryName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountAddressLine1" class="form-label">{{ translate('Address line 1') }}</label>
                    <input id="accountAddressLine1" type="text" name="address_line_1"
                        class="form-control form-control-md" value="{{ @$user->address->line_1 }}"
                        autocomplete="address-line1" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountAddressLine2" class="form-label">{{ translate('Address line 2') }}</label>
                    <input id="accountAddressLine2" type="text" name="address_line_2"
                        class="form-control form-control-md" value="{{ @$user->address->line_2 }}"
                        autocomplete="address-line2">
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountCity" class="form-label">{{ translate('City') }}</label>
                    <input id="accountCity" type="text" name="city" class="form-control form-control-md"
                        value="{{ @$user->address->city }}" autocomplete="address-level2" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountState" class="form-label">{{ translate('State') }}</label>
                    <input id="accountState" type="text" name="state" class="form-control form-control-md"
                        value="{{ @$user->address->state }}" autocomplete="address-level1" required>
                </div>
                <div class="col-12 col-lg-6">
                    <label for="accountPostalCode" class="form-label">{{ translate('Postal code') }}</label>
                    <input id="accountPostalCode" type="text" name="zip" class="form-control form-control-md"
                        value="{{ @$user->address->zip }}" autocomplete="postal-code" required>
                </div>
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
@endsection
