<?php

namespace App\Http\Requests\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'login.required'    => 'Email atau Username wajib diisi dulu!',
            'password.required' => 'Password-nya jangan dikosongin dong!',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $input = $this->input('login');
        $password = $this->input('password');

        // 1. LOGIKA CARI USER (Cek apakah Username/Email ada di database?)
        $user = User::where('email', $input)
                    ->orWhere('username', $input)
                    ->first();

        // KONDISI 1: AKUN TIDAK DITEMUKAN (Username Salah)
        if (! $user) {
            RateLimiter::hit($this->throttleKey());
            
            throw ValidationException::withMessages([
                'login' => 'Akun tidak ditemukan. Username/Email salah.',
            ]);
        }

        // KONDISI 2: AKUN ADA, TAPI PASSWORD SALAH
        if (! Hash::check($password, $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Password salah.', // Pesan error spesifik
            ]);
        }
        
        // KONDISI 3: CEK STATUS AKUN (Opsional tapi bagus)
        if ($user->status_akun !== 'aktif') {
             RateLimiter::hit($this->throttleKey());
             throw ValidationException::withMessages([
                'login' => 'Akun ini telah dinonaktifkan oleh Admin.',
            ]);
        }

        // JIKA SEMUA BENAR -> LOGIN
        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}