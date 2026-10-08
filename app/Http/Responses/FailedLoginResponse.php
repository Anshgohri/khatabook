<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedPasswordAttemptResponse;

class FailedLoginResponse implements FailedPasswordAttemptResponse
{
    public function toResponse($request)
    {
        throw ValidationException::withMessages([
            \Laravel\Fortify\Fortify::username() => [trans('auth.failed')],
        ])->redirectTo(route('login'));
    }
}
