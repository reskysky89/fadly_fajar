<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // --- PERUBAHAN 1: Mengganti 'email' menjadi 'login' ---
        // Kita tidak lagi memvalidasi 'email', tapi 'login'
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
        // --- AKHIR PERUBAHAN 1 ---
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // --- PERUBAHAN 2: Logika utama untuk 'email' atau 'username' ---
        // Mengganti baris "if (! Auth::attempt...)" yang lama dengan logika ini

        // 2a. Deteksi apakah input 'login' adalah email atau username
        $loginField = filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2b. Buat array credentials berdasarkan hasil deteksi
        $credentials = [
            $loginField => $this->input('login'),
            'password' => $this->input('password')
        ];

        // 2c. Coba login menggunakan credentials tersebut
        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'), // <-- 'email' diubah menjadi 'login'
            ]);
        }
        // --- AKHIR PERUBAHAN 2 ---

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        // --- PERUBAHAN 3: Ganti 'email' di pesan error ---
        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [ // <-- 'email' diubah menjadi 'login'
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
        // --- AKHIR PERUBAHAN 3 ---
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // --- PERUBAHAN 4: Ganti 'email' di throttle key ---
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip()); // <-- 'email' diubah menjadi 'login'
        // --- AKHIR PERUBAHAN 4 ---
    }
}