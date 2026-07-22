<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\Item;
use App\Models\ItemReview;
use App\Models\User;
use App\Services\UsernameResolver;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct(protected UsernameResolver $usernameResolver)
    {
    }

    public function index($username)
    {
        $user = $this->resolvePublicUser($username, 'profile.index');
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $items = Item::where('author_id', $user->id)
            ->approved()
            ->with(['author', 'category'])
            ->orderByDesc('id')
            ->paginate(12);

        $followers = Follower::where('following_id', $user->id)
            ->with('follower')
            ->orderbyDesc('id')
            ->paginate(12);

        return theme_view('profile.index', [
            'user' => $user,
            'items' => $items,
            'followers' => $followers,
        ]);
    }

    public function portfolio($username)
    {
        $user = $this->resolvePublicUser($username, 'profile.portfolio', true);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $items = Item::where('author_id', $user->id)
            ->approved();

        $searchTerm = request()->input('search');
        if ($searchTerm) {
            $items->where(function (Builder $query) use ($searchTerm) {
                $query->where('id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('slug', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%')
                    ->orWhere('options', 'like', '%' . $searchTerm . '%')
                    ->orWhere('demo_link', 'like', '%' . $searchTerm . '%')
                    ->orWhere('tags', 'like', '%' . $searchTerm . '%');
            });
        }

        $items = $items->orderByDesc('id')->paginate(21);
        $items->appends(request()->only(['search']));

        return theme_view('profile.portfolio', [
            'user' => $user,
            'items' => $items,
        ]);
    }

    public function followers($username)
    {
        $user = $this->resolvePublicUser($username, 'profile.followers');
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $followers = Follower::where('following_id', $user->id)
            ->with('follower')
            ->orderbyDesc('id')
            ->paginate(21);

        return theme_view('profile.followers', [
            'user' => $user,
            'followers' => $followers,
        ]);
    }

    public function following($username)
    {
        $user = $this->resolvePublicUser($username, 'profile.following');
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $followings = Follower::where('follower_id', $user->id)
            ->with('following')
            ->orderbyDesc('id')
            ->paginate(21);

        return theme_view('profile.following', [
            'user' => $user,
            'followings' => $followings,
        ]);
    }

    public function reviews($username)
    {
        $user = $this->resolvePublicUser($username, 'profile.reviews', true);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $reviews = ItemReview::where('author_id', $user->id)
            ->with('user')
            ->orderByDesc('id')
            ->paginate(20);

        return theme_view('profile.reviews', [
            'user' => $user,
            'reviews' => $reviews,
        ]);
    }

    public function sendMail(Request $request, $username)
    {
        $user = $this->usernameResolver->owner($username);
        abort_unless($user && $user->isDataCompleted(), 404);

        abort_if(!authUser(), 401);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        if (authUser()->id == $user->id) {
            toastr()->error(translate('You cannot send a message to yourself'));
            return back()->withInput();
        }

        if (!settings('smtp')->status) {
            toastr()->error(translate('Failed to send the message'));
            return back()->withInput();
        }

        try {
            $subject = translate('Message sent via your :website_name profile from :username', [
                'website_name' => @settings('general')->site_name,
                'username' => authUser()->username,
            ]);

            $email = $user->email;
            $replyTo = authUser()->email;
            $msg = nl2br($request->message);

            Mail::send([], [], function ($message) use ($msg, $email, $subject, $replyTo) {
                $message->to($email)
                    ->replyTo($replyTo)
                    ->subject($subject)
                    ->html($msg);
            });

            toastr()->success(translate('Your message has been sent successfully'));
            return back();

        } catch (Exception $e) {
            toastr()->error(translate('Failed to send the message'));
            return back();
        }
    }

    protected function resolvePublicUser($username, string $routeName, bool $authorCheck = false)
    {
        [$user, $historical] = $this->usernameResolver->publicProfile($username, $authorCheck);
        abort_unless($user, 404);

        if ($historical) {
            $url = route($routeName, $user->username);
            if (request()->getQueryString()) {
                $url .= '?' . request()->getQueryString();
            }

            return redirect()->away($url, 301);
        }

        $user->load(['badges' => function ($query) {
            $query->with('badge');
        }]);

        return $user;
    }
}
