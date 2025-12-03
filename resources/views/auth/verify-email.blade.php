<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Email - Toko Fadly Fajar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 antialiased flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        {{-- Header Icon (Amplop Biru) --}}
        <div class="bg-blue-700 p-8 text-center">
            <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-white">Verifikasi Email Anda</h2>
            <p class="text-blue-100 mt-1 text-sm">Satu langkah lagi untuk mulai berbelanja!</p>
        </div>

        <div class="p-8">
            {{-- Pesan Utama --}}
            <div class="mb-6 text-gray-600 text-center leading-relaxed">
                Terima kasih telah mendaftar di <strong>Toko Fadly Fajar</strong>! <br><br>
                Sebelum memulai, mohon verifikasi akun Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda.
            </div>

            {{-- Pesan Sukses (Jika Klik Kirim Ulang) --}}
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 font-medium text-sm text-green-700 bg-green-100 p-4 rounded-lg text-center border border-green-200 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Tautan verifikasi baru telah dikirim! Silakan cek Inbox atau folder Spam Anda.
                </div>
            @endif

            <div class="flex flex-col gap-4 mt-4">
                {{-- Tombol Kirim Ulang --}}
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                {{-- Tombol Logout (Jika salah email/ingin ganti akun) --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-sm text-gray-400 hover:text-gray-600 underline decoration-gray-300 hover:decoration-gray-500 transition text-center">
                        Salah email? Keluar (Logout)
                    </button>
                </form>
            </div>
        </div>
        
        {{-- Footer Kecil --}}
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-xs text-gray-400">© {{ date('Y') }} Toko Fadly Fajar</p>
        </div>

    </div>

</body>
</html>