<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaksi;

class PesananSelesaiMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaksi;

    // Terima data transaksi saat kelas dipanggil
    public function __construct(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    public function build()
    {
        return $this->subject('Hore! Pesanan Anda Telah Selesai - Toko Fadly Fajar')
                    ->view('emails.pesanan_selesai'); // Nama file tampilan email
    }
}