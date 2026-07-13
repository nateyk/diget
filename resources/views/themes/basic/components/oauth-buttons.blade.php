@if ($oauthProviders->count() > 0)
    <div class="login-with auth-oauth mt-3">
        <div class="login-with-divider">
            <span>{{ translate('Or continue with') }}</span>
        </div>
        <div class="auth-oauth-list">
            @foreach ($oauthProviders as $oauthProvider)
                <a href="{{ route('oauth.login', $oauthProvider->alias) }}"
                    class="btn btn-social btn-md auth-oauth-btn">
                    <img src="{{ asset($oauthProvider->logo) }}" alt="" width="22" height="22">
                    <span>
                        {{ translate($oauthProvider->name) }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
@endif
