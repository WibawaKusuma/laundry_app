<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Laundry_service_rules
{
    public function format_qty($qty)
    {
        return rtrim(rtrim(number_format((float) $qty, 2, '.', ''), '0'), '.');
    }

    public function is_kg_unit($unit_name)
    {
        $unit = strtolower(trim((string) $unit_name));
        return in_array($unit, ['kg', 'kgs', 'kilo', 'kilogram'], true);
    }

    public function sanitize_qty($qty)
    {
        $qty = (float) $qty;
        return $qty > 0 ? $qty : 0;
    }

    public function is_promo_service_eligible($nama_tipe, $nama_paket = '')
    {
        $service_name = strtolower(trim((string) $nama_tipe . ' ' . $nama_paket));
        $service_name = preg_replace('/\s+/', ' ', $service_name);

        if (strpos($service_name, 'setrika') !== false && strpos($service_name, 'cuci') === false) {
            return false;
        }

        return strpos($service_name, 'cuci komplit') !== false || strpos($service_name, 'cuci setrika') !== false;
    }

    public function is_layanan_cuci_komplit($nama_tipe, $nama_paket = '')
    {
        $service_name = strtolower(trim((string) $nama_tipe . ' ' . $nama_paket));
        $service_name = preg_replace('/\s+/', ' ', $service_name);

        return strpos($service_name, 'cuci komplit') !== false;
    }

    public function is_layanan_reward_member($nama_tipe, $nama_paket = '')
    {
        if (!$this->is_layanan_cuci_komplit($nama_tipe, $nama_paket)) {
            return false;
        }

        $paket = strtolower(trim((string) $nama_paket));
        $paket = preg_replace('/\s+/', ' ', $paket);

        if ($paket === '' || strpos($paket, 'express') !== false) {
            return false;
        }

        return strpos($paket, 'reguler') !== false || strpos($paket, 'satu hari') !== false;
    }

    public function is_reward_member_service_eligible($nama_satuan, $nama_tipe, $nama_paket = '')
    {
        return $this->is_kg_unit($nama_satuan) && $this->is_layanan_reward_member($nama_tipe, $nama_paket);
    }

    public function is_promo_gratis_service_eligible($nama_satuan, $nama_tipe, $nama_paket = '')
    {
        return $this->is_kg_unit($nama_satuan) && $this->is_layanan_cuci_komplit($nama_tipe, $nama_paket);
    }

    public function is_pair_unit($unit_name)
    {
        $unit = strtolower(trim((string) $unit_name));
        return in_array($unit, ['pasang', 'pair', 'pairs'], true);
    }

    public function is_promo_sepatu_service_eligible($nama_satuan, $nama_kategori, $nama_paket = '')
    {
        $kategori = strtolower(trim((string) $nama_kategori));
        $paket = strtolower(trim((string) $nama_paket));

        return $this->is_pair_unit($nama_satuan)
            && strpos($kategori, 'satuan khusus') !== false
            && strpos($paket, 'sepatu') !== false;
    }

    public function build_promo_gratis_keterangan($promo_preview)
    {
        $label = trim((string) ($promo_preview['promo_gratis_label'] ?? 'Promo Gratis'));
        $total_qty = $this->format_qty((float) ($promo_preview['total_qty_eligible'] ?? $promo_preview['total_kg_cuci_komplit'] ?? 0));
        $gratis_qty = $this->format_qty((float) ($promo_preview['promo_gratis_qty'] ?? 0));
        $unit = trim((string) ($promo_preview['promo_gratis_unit'] ?? 'kg'));
        $scope = trim((string) ($promo_preview['promo_gratis_scope_label'] ?? 'Cuci Komplit'));

        if ($label === '') {
            $label = 'Promo Gratis';
        }

        return $label . ' | Berlaku kelipatan | Total ' . $scope . ' ' . $total_qty . ' ' . $unit . ' | Gratis ' . $gratis_qty . ' ' . $unit;
    }

    public function calculate_cart_pricing(array $settings, $harga, $qty, $nama_satuan, $promo_requested = false, $nama_tipe = '', $nama_paket = '')
    {
        $actual_qty = $this->sanitize_qty($qty);
        $promo_applied = $promo_requested
            && !empty($settings['is_enabled'])
            && $this->is_kg_unit($nama_satuan)
            && $this->is_promo_service_eligible($nama_tipe, $nama_paket)
            && $actual_qty > 0;

        $free_qty_setting = (float) ($settings['free_qty'] ?? 0);
        $rounded_qty = $promo_applied ? (float) ceil($actual_qty) : $actual_qty;
        $charged_qty = $promo_applied ? max(0, $rounded_qty - $free_qty_setting) : $actual_qty;
        $free_qty = $promo_applied ? min($free_qty_setting, $rounded_qty) : 0;

        return [
            'actual_qty' => $actual_qty,
            'rounded_qty' => $rounded_qty,
            'charged_qty' => $charged_qty,
            'free_qty' => $free_qty,
            'subtotal' => (float) $harga * $charged_qty,
            'promo_requested' => (bool) $promo_requested,
            'promo_applied' => $promo_applied,
            'promo_label' => $settings['label'] ?? 'Promo Gratis Cuci 3 Kg',
        ];
    }

    public function build_promo_keterangan($item)
    {
        $catatan = trim((string) ($item['customer_notes'] ?? ''));

        if (empty($item['promo_applied']) && $catatan === '') {
            return '';
        }

        return json_encode([
            'promo_type' => !empty($item['promo_applied']) ? 'free_3kg' : null,
            'promo_label' => !empty($item['promo_applied']) ? ($item['promo_label'] ?? 'Promo Gratis Cuci 3 Kg') : null,
            'actual_qty' => !empty($item['promo_applied']) ? (float) ($item['qty'] ?? 0) : null,
            'rounded_qty' => !empty($item['promo_applied']) ? (float) ($item['rounded_qty'] ?? 0) : null,
            'charged_qty' => !empty($item['promo_applied']) ? (float) ($item['charged_qty'] ?? 0) : null,
            'free_qty' => !empty($item['promo_applied']) ? (float) ($item['promo_free_qty'] ?? 0) : null,
            'unit' => !empty($item['promo_applied']) ? strtolower((string) ($item['nama_satuan'] ?? 'kg')) : null,
            'customer_notes' => $catatan !== '' ? $catatan : null,
        ]);
    }

    public function parse_promo_keterangan($keterangan)
    {
        if (empty($keterangan)) {
            return null;
        }

        $decoded = json_decode($keterangan, true);
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    public function extract_customer_notes($keterangan)
    {
        $decoded = $this->parse_promo_keterangan($keterangan);

        if (is_array($decoded) && isset($decoded['customer_notes'])) {
            return trim((string) $decoded['customer_notes']);
        }

        return '';
    }

    public function merge_keterangan_notes($keterangan, $notes)
    {
        $notes = trim((string) $notes);
        $decoded = $this->parse_promo_keterangan($keterangan);

        if (!is_array($decoded)) {
            return $notes;
        }

        $decoded['customer_notes'] = $notes !== '' ? $notes : null;

        $has_promo = !empty($decoded['promo_type']);
        $has_notes = !empty($decoded['customer_notes']);

        if (!$has_promo && !$has_notes) {
            return '';
        }

        return json_encode($decoded);
    }

    public function build_item_note_text($promo_applied, $charged_qty, $unit_name, $customer_notes = '')
    {
        $notes = [];

        if ($promo_applied) {
            $notes[] = 'Promo 3 Kg gratis, dibayar ' . $this->format_qty($charged_qty) . ' ' . strtoupper((string) $unit_name);
        }

        $customer_notes = trim((string) $customer_notes);
        if ($customer_notes !== '') {
            $notes[] = $customer_notes;
        }

        if (empty($notes)) {
            return '-';
        }

        return implode(' | ', $notes);
    }

    public function enrich_detail_rows($details)
    {
        foreach ($details as $detail) {
            $promo = $this->parse_promo_keterangan($detail->keterangan ?? '');
            $detail->batal = !empty($detail->batal);

            $detail->actual_qty = (float) $detail->qty;
            $detail->rounded_qty = (float) $detail->qty;
            $detail->charged_qty = (float) $detail->qty;
            $detail->promo_free_qty = 0;
            $detail->promo_applied = false;
            $detail->promo_label = '';
            $detail->qty_label = $this->format_qty($detail->qty);
            $detail->subtotal = (float) $detail->harga * (float) $detail->qty;
            $detail->customer_notes = $this->extract_customer_notes($detail->keterangan ?? '');
            $detail->item_note_text = '-';

            if ($promo) {
                $detail->actual_qty = (float) ($promo['actual_qty'] ?? $detail->qty);
                $detail->rounded_qty = (float) ($promo['rounded_qty'] ?? $detail->actual_qty);
                $detail->charged_qty = (float) $detail->qty;
                $detail->promo_free_qty = (float) ($promo['free_qty'] ?? 0);
                $detail->promo_applied = !empty($promo['promo_type']);
                $detail->promo_label = $promo['promo_label'] ?? 'Promo Gratis Cuci 3 Kg';
                $detail->qty_label = $this->format_qty($detail->actual_qty);
                $detail->subtotal = (float) $detail->harga * (float) $detail->qty;
                $detail->customer_notes = trim((string) ($promo['customer_notes'] ?? $detail->customer_notes));
            }

            $detail->item_note_text = $this->build_item_note_text(
                !empty($detail->promo_applied),
                $detail->charged_qty,
                'Kg',
                $detail->customer_notes
            );
        }

        return $details;
    }
}
