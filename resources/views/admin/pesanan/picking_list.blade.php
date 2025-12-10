<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Picking List - {{ $transaksi->id_transaksi }}</title>
    <style>
        /* Desain Kertas Hemat Tinta */
        body { font-family: 'Courier New', monospace; color: #000; padding: 20px; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 2px 5px; vertical-align: top; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { border-bottom: 2px solid #000; text-align: left; padding: 5px; }
        .items-table td { border-bottom: 1px dotted #ccc; padding: 8px 5px; vertical-align: top; }
        
        .box-check { width: 15px; height: 15px; border: 1px solid #000; display: inline-block; margin-right: 5px; }
        .qty-box { font-weight: bold; font-size: 16px; border: 2px solid #000; padding: 2px 8px; display: inline-block; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 12px; }
        .ttd-box { height: 60px; }

        @media print {
            .btn-print { display: none; } /* Sembunyikan tombol saat print */
            @page { margin: 10mm; }
        }
    </style>
</head>
<body onload="window.print()">

    <button class="btn-print" onclick="window.print()" style="padding: 10px 20px; font-weight: bold; cursor: pointer; margin-bottom: 20px;">
        🖨️ Cetak Dokumen
    </button>

    <div class="header">
        <h2>PICKING LIST (GUDANG)</h2>
        <small>{{ config('app.name') }}</small>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">No. Order</td>
            <td>: <strong>{{ $transaksi->id_transaksi }}</strong></td>
            <td width="15%">Tanggal</td>
            <td>: {{ date('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>: {{ $transaksi->nama_pelanggan }}</td>
            <td>Metode</td>
            <td>: {{ $transaksi->metode_pengiriman == 'diantar' ? 'KIRIM KURIR' : 'AMBIL SENDIRI' }}</td>
        </tr>
        <tr>
            <td>Catatan</td>
            <td colspan="3">: {{ $transaksi->keterangan }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">Cek</th>
                <th width="15%" style="text-align: center;">Qty</th>
                <th width="10%">Satuan</th>
                <th>Nama Barang</th>
                <th width="20%">Ketersediaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->details as $item)
                <tr>
                    <td style="text-align: center;"><div class="box-check"></div></td>
                    <td style="text-align: center;"><span class="qty-box">{{ $item->jumlah }}</span></td>
                    <td>{{ $item->satuan }}</td>
                    <td>
                        <strong>{{ $item->produk->nama_produk }}</strong><br>
                        <small>Kode: {{ $item->id_produk }}</small>
                    </td>
                    <td>____ / {{ $item->jumlah }}</td> {{-- Kolom untuk tulis manual jika barang kurang --}}
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td align="center" width="50%">
                <div class="ttd-box"></div>
                ( Petugas Gudang / Picker )
            </td>
            <td align="center" width="50%">
                <div class="ttd-box"></div>
                ( Admin / Kasir )
            </td>
        </tr>
    </table>

</body>
</html>