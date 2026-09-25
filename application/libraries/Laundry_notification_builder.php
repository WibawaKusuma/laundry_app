<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laundry_notification_builder
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('laundry_service_rules');
    }

    public function normalize_whatsapp_number($phone_number)
    {
        $nomor = trim((string) $phone_number);
        $nomor = str_replace([' ', '-', '+'], '', $nomor);

        if ($nomor === '') {
            return '';
        }

        if (substr($nomor, 0, 1) === '0') {
            return '62' . substr($nomor, 1);
        }

        if (substr($nomor, 0, 2) !== '62') {
            return '62' . $nomor;
        }

        return $nomor;
    }

    public function hitung_total_akhir_wa($total_tagihan, $reward_potongan = 0, $promo_gratis_potongan = 0)
    {
        return max(0, (float) $total_tagihan - (int) $reward_potongan - (int) $promo_gratis_potongan);
    }

    /**
     * Baris diskon & poin di nota WhatsApp. Semua angka berasal dari snapshot tagihan nota
     * (lihat Laundry_benefit_calculator), bukan dari perhitungan ulang.
     */
    public function build_benefit_customer_message_lines($meta = [], $batas_poin_member = 8)
    {
        $lines = [
            'discount' => [],
            'points' => [],
        ];
        $rules = $this->CI->laundry_service_rules;
        $batas_poin_member = max(1, (int) ($meta['batas_poin'] ?? $batas_poin_member));
        $poin_pelanggan = max(0, (int) ($meta['poin_member_pelanggan'] ?? 0));
        $reward_dipakai = !empty($meta['reward_member_dipakai']) && (float) ($meta['reward_gratis_qty'] ?? 0) > 0;
        $promo_dipakai = !empty($meta['promo_gratis_dipakai']) && (float) ($meta['promo_gratis_qty'] ?? 0) > 0;
        $poin_sudah_masuk = !empty($meta['poin_diberikan_pada']);
        $dapat_poin = !empty($meta['dapat_poin']);
        $show_potential_poin = !empty($meta['show_potential_poin']);

        // Nota pelanggan mitra: tanpa diskon dan tanpa informasi poin member.
        if (!empty($meta['is_mitra'])) {
            return $lines;
        }

        if ($reward_dipakai) {
            $lines['discount'][] = 'Reward Member ' . $rules->format_qty((float) ($meta['reward_gratis_qty'] ?? 0)) . ' kg: - Rp ' . number_format((int) ($meta['reward_potongan'] ?? 0), 0, ',', '.');
            $poin_dipakai = (int) ($meta['reward_poin_dipakai'] ?? 0);
            $lines['points'][] = 'Reward member dipakai di nota ini' . ($poin_dipakai > 0 ? ' (' . $poin_dipakai . ' poin)' : '') . '.';
            $lines['points'][] = 'Sisa poin: ' . $poin_pelanggan;

            return $lines;
        }

        if ($promo_dipakai) {
            $unit = trim((string) ($meta['promo_gratis_unit'] ?? 'kg'));
            $lines['discount'][] = 'Promo gratis ' . $rules->format_qty((float) ($meta['promo_gratis_qty'] ?? 0)) . ' ' . $unit . ': - Rp ' . number_format((int) ($meta['promo_gratis_potongan'] ?? 0), 0, ',', '.');
        }

        if ($poin_sudah_masuk) {
            $lines['points'][] = 'Poin didapat: +1';
            $lines['points'][] = 'Total poin sekarang: ' . $poin_pelanggan;

            return $lines;
        }

        if ($poin_pelanggan >= $batas_poin_member) {
            $lines['points'][] = 'Poin saat ini: ' . $poin_pelanggan;
            $lines['points'][] = 'Reward member (' . $batas_poin_member . ' poin) otomatis dipakai di nota cucian Cuci Komplit Reguler / Satu Hari berikutnya.';

            return $lines;
        }

        if ($show_potential_poin && $dapat_poin) {
            $lines['points'][] = 'Poin saat ini: ' . $poin_pelanggan;

            if ($poin_pelanggan + 1 >= $batas_poin_member) {
                $lines['points'][] = 'Setelah cucian ini selesai dan lunas, poin menjadi ' . ($poin_pelanggan + 1) . ' dan reward member bisa dipakai di nota berikutnya.';
            } else {
                $lines['points'][] = 'Poin +1 otomatis setelah cucian selesai dan lunas.';
            }
        }

        return $lines;
    }

    public function build_confirmation_message($company, $batas_poin_member, $invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta = [])
    {
        $list_item_wa = '';
        $list_biaya_wa = '';

        foreach ($details as $item) {
            $nama_tipe = strtoupper(trim((string) ($item->nama_tipe ?? $item['nama_tipe'] ?? '')));
            $nama_paket = trim((string) ($item->nama_paket ?? $item['nama_paket'] ?? ''));
            $harga_satuan = (float) ($item->harga ?? $item['harga'] ?? 0);
            $satuan_wa = trim((string) ($item->nama_satuan ?? $item['nama_satuan'] ?? ''));
            $promo_applied = !empty($item->promo_applied ?? $item['promo_applied'] ?? false);
            $charged_qty = (float) ($item->charged_qty ?? $item['charged_qty'] ?? $item->qty ?? $item['qty'] ?? 0);
            $customer_notes = trim((string) ($item->customer_notes ?? $item['customer_notes'] ?? ''));
            $item_note_text = trim((string) ($item->item_note_text ?? $item['item_note_text'] ?? ''));
            if ($item_note_text === '') {
                $item_note_text = $this->CI->laundry_service_rules->build_item_note_text(
                    $promo_applied,
                    $charged_qty,
                    $satuan_wa !== '' ? $satuan_wa : 'Kg/Pcs',
                    $customer_notes
                );
            }
            $qty_for_message = $item->qty_label ?? $item['qty_label'] ?? $item->qty ?? $item['qty'] ?? 0;
            $subtotal_item = (float) ($item->subtotal ?? $item['subtotal'] ?? ($harga_satuan * $qty_for_message));

            $list_item_wa .= '- ' . $nama_tipe . ' / ' . strtoupper($nama_paket) . ', ' . $qty_for_message . ' ' . strtoupper($satuan_wa) . "%0A";
            $list_item_wa .= '@ Rp' . number_format($harga_satuan, 0, ',', '.') . ', Total Rp' . number_format($subtotal_item, 0, ',', '.') . "%0A";
            $list_item_wa .= 'Ket : ' . rawurlencode($item_note_text) . "%0A";

            $list_biaya_wa .= '- ' . $nama_tipe . ' / ' . strtoupper($nama_paket) . ': Rp' . number_format($subtotal_item, 0, ',', '.') . "%0A";
        }

        $tgl_terima_fmt = date('d/m/Y H:i', strtotime($tgl_terima));
        $tgl_selesai_fmt = !empty($tgl_selesai) ? date('d/m/Y H:i', strtotime($tgl_selesai)) : '-';
        $mode = strtolower(trim((string) ($payment_meta['mode'] ?? 'detail')));
        if (!in_array($mode, ['awal', 'detail', 'pengambilan'], true)) {
            $mode = 'detail';
        }
        $reward_dipakai = !empty($payment_meta['reward_member_dipakai']) && (float) ($payment_meta['reward_gratis_qty'] ?? 0) > 0;
        $reward_potongan = $reward_dipakai ? (int) ($payment_meta['reward_potongan'] ?? 0) : 0;
        $promo_dipakai = !$reward_dipakai && !empty($payment_meta['promo_gratis_dipakai']) && (float) ($payment_meta['promo_gratis_qty'] ?? 0) > 0;
        $promo_gratis_potongan = $promo_dipakai ? (int) ($payment_meta['promo_gratis_potongan'] ?? 0) : 0;
        $total_akhir = $this->hitung_total_akhir_wa($total_tagihan, $reward_potongan, $promo_gratis_potongan);
        if (array_key_exists('total_akhir', $payment_meta)) {
            $total_akhir = max(0, (float) $payment_meta['total_akhir']);
        }
        $total_fmt = number_format((float) $total_tagihan, 0, ',', '.');
        $total_akhir_fmt = number_format((float) $total_akhir, 0, ',', '.');
        $is_paid = (string) ($payment_meta['dibayar'] ?? '') === 'Sudah Dibayar';
        $paid_amount_fmt = number_format($is_paid ? (float) $total_akhir : 0, 0, ',', '.');
        $remaining_amount_fmt = number_format($is_paid ? 0 : (float) $total_akhir, 0, ',', '.');
        $payment_method = trim((string) ($payment_meta['nama_metode_bayar'] ?? ''));
        $paid_at = !empty($payment_meta['tgl_bayar']) ? date('d/m/Y H:i', strtotime($payment_meta['tgl_bayar'])) : '';
        $diambil_at = !empty($payment_meta['tgl_diambil']) ? date('d/m/Y H:i', strtotime($payment_meta['tgl_diambil'])) : '';
        $nama_kasir = trim((string) ($payment_meta['nama_kasir'] ?? 'Admin'));
        $benefit_lines = $this->build_benefit_customer_message_lines(array_merge($payment_meta, [
            'reward_member_dipakai' => $reward_dipakai,
            'promo_gratis_dipakai' => $promo_dipakai,
        ]), $batas_poin_member);

        $company_name = $company['company_name'] ?? 'APP Laundry';
        $company_address = $company['company_address'] ?? 'Jalan';
        $company_phone = $company['company_phone'] ?? '08000000000';

        $pesan = ($mode === 'pengambilan' ? 'FAKTUR BUKTI PENGAMBILAN' : 'FAKTUR ELEKTRONIK TRANSAKSI REGULER') . "%0A";
        $pesan .= "{$company_name}%0A";
        $pesan .= "{$company_address}%0A";
        $pesan .= "{$company_phone}%0A%0A";
        $pesan .= "Nomor Nota :%0A";
        $pesan .= "$invoice%0A%0A";
        $pesan .= "Pelanggan Yth :%0A";
        $pesan .= "$pelanggan_nama%0A%0A";
        $pesan .= "Terima : $tgl_terima_fmt%0A";
        if ($mode === 'pengambilan') {
            if ($diambil_at !== '') {
                $pesan .= "Diambil : $diambil_at%0A";
            }
        } else {
            $pesan .= "Selesai : $tgl_selesai_fmt%0A";
        }
        $pesan .= "%0A======================%0A";
        $pesan .= ($mode === 'pengambilan' ? 'Detail pengambilan:' : 'Detail pesanan:') . "%0A";
        $pesan .= "Layanan:%0A";
        $pesan .= $list_item_wa;
        $pesan .= "%0A==============%0A";
        $pesan .= "Detail biaya:%0A";
        $pesan .= "Paket:%0A";
        $pesan .= $list_biaya_wa;

        if (!empty($benefit_lines['discount'])) {
            $pesan .= "Total sebelum diskon: Rp$total_fmt%0A";
            $pesan .= "Diskon:%0A";
            foreach ($benefit_lines['discount'] as $line) {
                $pesan .= $line . "%0A";
            }
            $pesan .= "Total yang harus dibayar: Rp$total_akhir_fmt%0A";
        } else {
            $pesan .= "Total tagihan: Rp$total_akhir_fmt%0A";
        }

        if (!empty($benefit_lines['points'])) {
            $pesan .= "%0APoin member:%0A";
            foreach ($benefit_lines['points'] as $line) {
                $pesan .= $line . "%0A";
            }
        }

        $pesan .= "%0APembayaran:%0A";
        if ($is_paid) {
            $pesan .= "Dibayar : Rp$paid_amount_fmt%0A";
            if ($payment_method !== '') {
                $pesan .= "Metode : $payment_method%0A";
            }
            if ($paid_at !== '') {
                $pesan .= "Tanggal bayar : $paid_at%0A";
            }
            $pesan .= "Sisa tagihan : Rp$remaining_amount_fmt%0A";
            $pesan .= "Status: Lunas%0A%0A";
        } else {
            $pesan .= "Sisa tagihan : Rp$remaining_amount_fmt%0A";
            $pesan .= "Status: Belum lunas%0A%0A";
        }

        if ($mode === 'pengambilan') {
            if ($diambil_at !== '') {
                $pesan .= "Pengambilan:%0A";
                $pesan .= "Diserahkan pada : $diambil_at%0A";
                $pesan .= "Oleh : $nama_kasir%0A";
                $pesan .= "Status Laundry : Sudah Diambil%0A%0A";
            }

            $pesan .= "=================%0A";
            $pesan .= "Kami telah menyerahkan barang dan diterima dengan kondisi baik%0A";
            $pesan .= "Terima kasih";

            return $pesan;
        }

        $pesan .= "=================%0A";
        $pesan .= "Syarat dan ketentuan:%0A";
        $pesan .= "PERHATIAN :%0A";
        $pesan .= "1. Pengambilan barang harap disertai nota%0A";
        $pesan .= "2. Barang yang tidak diambil selama 1 bulan, hilang / rusak tidak diganti%0A";
        $pesan .= "3. Barang hilang/rusak karena proses pengerjaan diganti maksimal 5x biaya.%0A";
        $pesan .= "4. Klaim luntur tidak dipisah diluar tanggungan%0A";
        $pesan .= "5. Hak klaim berlaku 2 jam setelah barang diambil%0A";
        $pesan .= "6. Setiap konsumen dianggap setuju dengan isi perhitungan tersebut diatas%0A";
        $pesan .= "%0ATerima kasih";

        return $pesan;
    }

    public function build_confirmation_wa_link($company, $batas_poin_member, $phone_number, $invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta = [])
    {
        $nomor = $this->normalize_whatsapp_number($phone_number);

        if ($nomor === '') {
            return '';
        }

        $pesan = $this->build_confirmation_message($company, $batas_poin_member, $invoice, $pelanggan_nama, $tgl_terima, $tgl_selesai, $details, $total_tagihan, $payment_meta);

        return "https://wa.me/$nomor?text=$pesan";
    }
}
