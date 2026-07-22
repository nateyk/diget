<?php

namespace App\Rules;

use App\Models\User;
use App\Services\UsernamePolicy;
use Illuminate\Contracts\Validation\Rule;

class Username implements Rule
{
    protected string $message = '';

    public function __construct(protected ?User $exceptUser = null)
    {
    }

    public function passes($attribute, $value)
    {
        $errors = app(UsernamePolicy::class)->errors($value, $this->exceptUser);
        $this->message = $errors[0] ?? '';

        return $errors === [];
    }

    public function message()
    {
        return $this->message ?: __('validation.username');
    }
}
