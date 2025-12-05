<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Transaksi;

class PesananSelesaiNotification extends Notification
{
    use Queueable;

    public $transaksi;

    public function __construct(Transaksi $transaksi)
    {
        $this->transaksi = $transaksi;
    }

    // Kita simpan ke DATABASE saja (Email sudah kita buat terpisah kemarin)
    public function via($notifiable)
    {
        return ['database'];
    }

    // Format data yang disimpan ke database
    public function toArray($notifiable)
    {
        return [
            'id_transaksi' => $this->transaksi->id_transaksi,
            'pesan' => 'Pesanan #' . $this->transaksi->id_transaksi . ' telah selesai diproses.',
            'waktu' => now(),
            'link' => route('pelanggan.riwayat') // Link tujuan saat diklik
        ];
    }
}