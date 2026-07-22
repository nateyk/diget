<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Models\Badge;
use App\Models\Referral;
use App\Services\UsernameResolver;
use Illuminate\Support\Facades\Cookie;

class ProcessReferralRegistration
{
    public function handle(Registered $event)
    {
        $user = $event->user;

        if (@settings('referral')->status) {
            if (request()->hasCookie('_ref')) {
                $author = app(UsernameResolver::class)->owner(request()->cookie('_ref'));
                if ($author) {
                    $referral = new Referral();
                    $referral->author_id = $author->id;
                    $referral->user_id = $user->id;
                    $referral->save();

                    Cookie::queue(Cookie::forget('_ref'));

                    $badge = Badge::where('alias', Badge::REFERRER_BADGE_ALIAS)->first();
                    if ($badge) {
                        $author->addBadge($badge);
                    }
                }
            }
        }
    }
}
