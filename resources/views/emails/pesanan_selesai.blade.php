<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #3b82f6; margin: 0; }
        .content { color: #333; line-height: 1.6; }
        .box-info { background: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #3b82f6; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pesanan Selesai! 🎉</h1>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $transaksi->nama_pelanggan }}</strong>,</p>
            
            <p>Kabar gembira! Pesanan Anda dengan nomor <strong>#{{ $transaksi->id_transaksi }}</strong> telah selesai diproses dan siap.</p>
            
            <div class="box-info">
                <p style="margin: 0;"><strong>Total Belanja:</strong> Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</p>
                <p style="margin: 5px 0 0;"><strong>Status:</strong> Selesai & Lunas</p>
                @if($transaksi->metode_pengiriman == 'ambil_sendiri')
                    <p style="margin: 5px 0 0; color: #d97706;"><strong>Info:</strong> Silakan datang ke toko untuk mengambil barang Anda.</p>
                @else
                    <p style="margin: 5px 0 0; color: #7c3aed;"><strong>Info:</strong> Barang sedang dalam perjalanan oleh kurir kami.</p>
                @endif
            </div>

            <p>Anda dapat melihat detail pesanan dan mengunduh struk belanja melalui tombol di bawah ini:</p>
            
            <center>
                <a href="{{ route('pelanggan.riwayat') }}" class="btn">Lihat Pesanan Saya</a>
            </center>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Toko Grosir Fadly Fajar. Melayani dengan Hati.</p>
        </div>
    </div>
</body>
</html>