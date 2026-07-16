<?php

namespace App\Http\Controllers\Workspace;

use App\Events\WithdrawalSubmitted;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $author = authUser();

        $counters['pending_withdrawals'] = Withdrawal::where('author_id', $author->id)
            ->pending()->sum('amount');
        $counters['total_withdrawals'] = Withdrawal::where('author_id', $author->id)
            ->whereIn('status', [Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_COMPLETED])
            ->sum('amount');

        $withdrawals = Withdrawal::where('author_id', $author->id);

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $withdrawals->where(function ($query) use ($searchTerm) {
                $query->where('id', 'like', $searchTerm)
                    ->OrWhere('method', 'like', $searchTerm)
                    ->OrWhere('account', 'like', $searchTerm);
            });
        }

        if (request()->filled('status')) {
            $withdrawals->where('status', request('status'));
        }

        $withdrawals = $withdrawals->orderbyDesc('id')->paginate(20);
        $withdrawals->appends(request()->only(['search', 'status']));

        return theme_view('workspace.withdrawals', [
            'counters' => $counters,
            'withdrawals' => $withdrawals,
        ]);
    }

    public function withdraw(Request $request)
    {
        try {
            $withdrawal = app(WithdrawalService::class)->submit(authUser()->id);
        } catch (\Throwable $e) {
            toastr()->error(translate($e->getMessage()));
            return back();
        }

        event(new WithdrawalSubmitted($withdrawal));

        toastr()->success(translate('Your withdrawal request has been sent successfully'));
        return back();
    }

}
