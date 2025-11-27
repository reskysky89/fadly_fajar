<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaksi->id_transaksi }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 0; }
        .container { width: 80mm; margin: auto; padding: 10px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .info { border-bottom: 1px dashed #000; margin-bottom: 5px; padding-bottom: 5px; }
        .items { width: 100%; margin-bottom: 5px; }
        .items th { text-align: left; border-bottom: 1px dashed #000; }
        .items td { vertical-align: top; }
        .total-section { border-top: 1px dashed #000; padding-top: 5px; text-align: right; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        
        /* Auto Print saat dibuka */
        @media print { 
            @page { margin: 0; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h2>TOKO FADLY FAJAR</h2>
            <p>Jl. Contoh No. 123, Kota Parepare</p>
        </div>

        <div class="info">
            No: {{ $transaksi->id_transaksi }}<br>
            Kasir: {{ $transaksi->nama_kasir }}<br>
            Tgl: {{ date('d/m/Y H:i', strtotime($transaksi->waktu_transaksi)) }}
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 40%">Item</th>
                    <th style="width: 20%">Qty</th>
                    <th style="width: 40%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi->details as $item)
                <tr>
                    {{-- Tampilkan Nama Produk (Fallback ke ID jika produk dihapus) --}}
                    <td colspan="3" style="font-weight: bold;">
                        {{ $item->produk->nama_produk ?? $item->id_produk }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>{{ $item->jumlah }} {{ $item->satuan }} x {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <div class="row-total grand-total">
                <span>TOTAL TAGIHAN</span>
                <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
            </div>
            
            {{-- TAMPILKAN BAYAR DAN KEMBALIAN --}}
            <div class="row-total" style="margin-top: 5px;">
                <span>Tunai / Bayar</span>
                <span>Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</span>
            </div>
            
            <div class="row-total">
                <span>Kembali</span>
                <span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Terima Kasih atas kunjungan Anda!</p>
        </div>
    </div>
</body>
</html>