<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Satu-satunya tempat perhitungan benefit (reward member / promo) sebuah nota.
 *
 * Aturan (lihat README bagian "Promo dan Reward"):
 * - Satu nota hanya mendapat SATU benefit. Prioritas: Reward Member > Promo Opening > Promo Daily > Promo Sepatu.
 * - Perhitungan HANYA memakai data yang sudah terkunci di transaksi:
 *     reward_poin_dipakai + aturan_reward_gratis_qty  (reward yang sudah di-reserve saat nota dibuat)
 *     aturan_promo_*                                  (aturan promo yang aktif saat nota dibuat)
 *   dan item aktif nota. Poin pelanggan saat ini dan setting promo terbaru TIDAK dipakai,
 *   sehingga tagihan tidak berubah kecuali item nota diubah.
 * - Potongan selalu diambil dari item termurah yang memenuhi syarat.
 */
class Laundry_benefit_calculator
{
    const EPSILON = 0.0000001;

    private $rules;

    public function __construct($params = [])
    {
        if (is_array($params) && isset($params['rules'])) {
            $this->rules = $params['rules'];
            return;
        }

        $CI =& get_instance();
        $CI->load->library('laundry_service_rules');
        $this->rules = $CI->laundry_service_rules;
    }

    public function empty_ringkasan($total_normal = 0)
    {
        return [
            'total_normal' => (float) $total_normal,
            'total_akhir' => (float) $total_normal,
            'diskon_total' => 0,
            'benefit_tipe' => null,
            'benefit_keterangan' => null,
            'reward_tersedia' => false,
            'reward_preview' => false,
            'reward_dipakai' => false,
            'reward_gratis_qty' => 0,
            'reward_potongan' => 0,
            'reward_poin_dipakai' => 0,
            'reward_tanpa_item_eligible' => false,
            'promo_tersedia' => false,
            'promo_preview' => false,
            'promo_dipakai' => false,
            'promo_gratis_qty' => 0,
            'promo_gratis_potongan' => 0,
            'promo_gratis_label' => '',
            'promo_gratis_keterangan' => '',
            'promo_gratis_unit' => 'kg',
            'promo_gratis_scope_label' => 'Cuci Komplit',
            'promo_benefit_tipe' => null,
            'promo_kelipatan' => false,
            'promo_total_qty_eligible' => 0,
            'promo_total_kg_cuci_komplit' => 0,
        ];
    }

    public function is_nota_mitra($trx)
    {
        return $trx && (string) ($trx->jenis_pelanggan ?? 'umum') === 'mitra';
    }

    public function hitung_total_normal(array $details)
    {
        $total = 0.0;

        foreach ($details as $detail) {
            $total += $this->detail_subtotal($detail);
        }

        return $total;
    }

    /**
     * Hitung tagihan nota yang masih terbuka dari data terkunci + item aktif.
     */
    public function hitung($trx, array $details)
    {
        $ringkasan = $this->empty_ringkasan($this->hitung_total_normal($details));

        // Pelanggan mitra (villa/kerja sama) tidak pernah mendapat promo maupun reward.
        if (!$trx || $this->is_nota_mitra($trx)) {
            return $ringkasan;
        }

        $reward_poin = (int) ($trx->reward_poin_dipakai ?? 0);
        $reward_max_qty = (float) ($trx->aturan_reward_gratis_qty ?? 0);

        if ($reward_poin > 0 && $reward_max_qty > 0) {
            $reward = $this->hitung_reward($details, $reward_max_qty);

            if ($reward['gratis_qty'] > 0) {
                $ringkasan['reward_tersedia'] = true;
                $ringkasan['reward_dipakai'] = true;
                $ringkasan['reward_gratis_qty'] = $reward['gratis_qty'];
                $ringkasan['reward_potongan'] = $reward['potongan'];
                $ringkasan['reward_poin_dipakai'] = $reward_poin;
                $ringkasan['diskon_total'] = $reward['potongan'];
                $ringkasan['benefit_tipe'] = 'reward_member';
                $ringkasan['benefit_keterangan'] = 'Reward Member gratis ' . $this->rules->format_qty($reward['gratis_qty']) . ' kg';

                return $this->finalisasi($ringkasan);
            }

            $ringkasan['reward_tanpa_item_eligible'] = true;
        }

        $promo = $this->hitung_promo($trx, $details);
        if ($promo !== null && $promo['gratis_qty'] > 0) {
            $ringkasan['promo_tersedia'] = true;
            $ringkasan['promo_dipakai'] = true;
            $ringkasan['promo_benefit_tipe'] = $promo['tipe'];
            $ringkasan['promo_gratis_qty'] = $promo['gratis_qty'];
            $ringkasan['promo_gratis_potongan'] = $promo['potongan'];
            $ringkasan['promo_gratis_label'] = $promo['label'];
            $ringkasan['promo_gratis_keterangan'] = $promo['keterangan'];
            $ringkasan['promo_gratis_unit'] = $promo['unit'];
            $ringkasan['promo_gratis_scope_label'] = $promo['scope_label'];
            $ringkasan['promo_kelipatan'] = $promo['kelipatan'];
            $ringkasan['promo_total_qty_eligible'] = $promo['total_qty'];
            $ringkasan['promo_total_kg_cuci_komplit'] = $promo['unit'] === 'kg' ? $promo['total_qty'] : 0;
            $ringkasan['diskon_total'] = $promo['potongan'];
            $ringkasan['benefit_tipe'] = $promo['tipe'];
            $ringkasan['benefit_keterangan'] = $promo['keterangan'];
        }

        return $this->finalisasi($ringkasan);
    }

    /**
     * Ringkasan untuk nota yang sudah lunas/dibatalkan: dibaca apa adanya dari snapshot tersimpan.
     */
    public function ringkasan_dari_snapshot($trx, array $details)
    {
        $total_detail = $this->hitung_total_normal($details);
        $ringkasan = $this->empty_ringkasan($total_detail);

        if (!$trx) {
            return $ringkasan;
        }

        $punya_snapshot = !empty($trx->snapshot_tagihan_at);
        $ringkasan['total_normal'] = $punya_snapshot ? (float) ($trx->subtotal_normal ?? 0) : $total_detail;
        $ringkasan['total_akhir'] = $punya_snapshot ? (float) ($trx->total_akhir ?? 0) : $total_detail;
        $ringkasan['diskon_total'] = $punya_snapshot ? (int) ($trx->diskon_total ?? 0) : 0;
        $ringkasan['benefit_tipe'] = $trx->benefit_tipe ?? null;
        $ringkasan['benefit_keterangan'] = $trx->benefit_keterangan ?? null;

        $reward_qty = (float) ($trx->reward_gratis_qty ?? 0);
        if (!empty($trx->reward_member_dipakai) && $reward_qty > 0) {
            $ringkasan['reward_tersedia'] = true;
            $ringkasan['reward_dipakai'] = true;
            $ringkasan['reward_gratis_qty'] = $reward_qty;
            $ringkasan['reward_potongan'] = (int) ($trx->reward_potongan ?? 0);
            $ringkasan['reward_poin_dipakai'] = (int) ($trx->reward_poin_dipakai ?? 0);

            if (!$punya_snapshot) {
                $ringkasan['diskon_total'] = $ringkasan['reward_potongan'];
                $ringkasan['total_akhir'] = max(0, $total_detail - $ringkasan['reward_potongan']);
            }

            return $ringkasan;
        }

        $promo_qty = (float) ($trx->promo_gratis_qty ?? 0);
        if (!empty($trx->promo_gratis_dipakai) && $promo_qty > 0) {
            $tipe = (string) ($trx->benefit_tipe ?? '');
            if (!in_array($tipe, ['promo_opening', 'promo_daily', 'promo_sepatu'], true)) {
                $tipe = 'promo_daily';
            }
            $is_sepatu = $tipe === 'promo_sepatu';
            $keterangan = trim((string) ($trx->promo_gratis_keterangan ?? ''));

            $ringkasan['promo_tersedia'] = true;
            $ringkasan['promo_dipakai'] = true;
            $ringkasan['promo_benefit_tipe'] = $tipe;
            $ringkasan['promo_gratis_qty'] = $promo_qty;
            $ringkasan['promo_gratis_potongan'] = (int) ($trx->promo_gratis_potongan ?? 0);
            $ringkasan['promo_gratis_keterangan'] = $keterangan;
            $ringkasan['promo_gratis_label'] = $keterangan !== '' ? $keterangan : (string) ($trx->aturan_promo_label ?? 'Promo Gratis');
            $ringkasan['promo_gratis_unit'] = $is_sepatu ? 'pasang' : 'kg';
            $ringkasan['promo_gratis_scope_label'] = $is_sepatu ? 'Sepatu' : 'Cuci Komplit';
            $ringkasan['promo_kelipatan'] = $tipe !== 'promo_opening';
            $ringkasan['promo_total_qty_eligible'] = $this->total_qty_promo($tipe, $details);
            $ringkasan['promo_total_kg_cuci_komplit'] = $is_sepatu ? 0 : $ringkasan['promo_total_qty_eligible'];

            if (!$punya_snapshot) {
                $ringkasan['diskon_total'] = $ringkasan['promo_gratis_potongan'];
                $ringkasan['total_akhir'] = max(0, $total_detail - $ringkasan['promo_gratis_potongan']);
            }
        }

        return $ringkasan;
    }

    /**
     * Kolom snapshot yang disimpan ke tabel transaksi.
     */
    public function snapshot_fields(array $ringkasan)
    {
        return [
            'subtotal_normal' => (int) round((float) $ringkasan['total_normal']),
            'diskon_total' => max(0, (int) $ringkasan['diskon_total']),
            'total_akhir' => max(0, (int) round((float) $ringkasan['total_akhir'])),
            'benefit_tipe' => $ringkasan['benefit_tipe'],
            'benefit_keterangan' => $ringkasan['benefit_keterangan'],
            'reward_member_dipakai' => !empty($ringkasan['reward_dipakai']) ? 1 : 0,
            'reward_gratis_qty' => (float) $ringkasan['reward_gratis_qty'],
            'reward_potongan' => (int) $ringkasan['reward_potongan'],
            'promo_gratis_dipakai' => !empty($ringkasan['promo_dipakai']) ? 1 : 0,
            'promo_gratis_qty' => (float) $ringkasan['promo_gratis_qty'],
            'promo_gratis_potongan' => (int) $ringkasan['promo_gratis_potongan'],
            'promo_gratis_keterangan' => !empty($ringkasan['promo_dipakai']) ? $ringkasan['promo_gratis_keterangan'] : null,
            'snapshot_tagihan_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function finalisasi(array $ringkasan)
    {
        $ringkasan['diskon_total'] = max(0, (int) $ringkasan['diskon_total']);
        $ringkasan['total_akhir'] = max(0, (float) $ringkasan['total_normal'] - $ringkasan['diskon_total']);

        return $ringkasan;
    }

    private function hitung_reward(array $details, $max_qty)
    {
        $eligible = array_values(array_filter($details, function ($detail) {
            return $this->rules->is_reward_member_service_eligible(
                $this->field($detail, 'nama_satuan'),
                $this->field($detail, 'nama_tipe'),
                $this->field($detail, 'nama_paket')
            ) && $this->detail_qty($detail) > 0;
        }));

        $alokasi = $this->alokasi_termurah($eligible, (float) $max_qty);

        return [
            'gratis_qty' => $alokasi['qty'],
            'potongan' => (int) round($alokasi['potongan']),
        ];
    }

    private function hitung_promo($trx, array $details)
    {
        $tipe = (string) ($trx->aturan_promo_tipe ?? '');
        $min_qty = (float) ($trx->aturan_promo_min_qty ?? 0);
        $gratis_setting = (float) ($trx->aturan_promo_gratis_qty ?? 0);
        $label = trim((string) ($trx->aturan_promo_label ?? ''));

        if (!in_array($tipe, ['promo_opening', 'promo_daily', 'promo_sepatu'], true) || $gratis_setting <= 0) {
            return null;
        }

        $eligible = $this->detail_eligible_promo($tipe, $details);
        $total_qty = 0.0;
        foreach ($eligible as $detail) {
            $total_qty += $this->detail_qty($detail);
        }

        if (empty($eligible) || $total_qty <= 0) {
            return null;
        }

        $is_sepatu = $tipe === 'promo_sepatu';
        $unit = $is_sepatu ? 'pasang' : 'kg';
        $scope = $is_sepatu ? 'Sepatu' : 'Cuci Komplit';

        if ($tipe === 'promo_opening') {
            // Opening: berat Cuci Komplit harus LEBIH dari jumlah gratis (<= gratis tidak dapat), tanpa pembulatan.
            if ($total_qty <= $gratis_setting + self::EPSILON) {
                return null;
            }
            $gratis_qty = $gratis_setting;
            $kelipatan = false;
        } else {
            if ($min_qty <= 0 || $total_qty + self::EPSILON < $min_qty) {
                return null;
            }
            $jumlah_kelipatan = (int) floor(($total_qty + self::EPSILON) / $min_qty);
            $gratis_qty = min($total_qty, $jumlah_kelipatan * $gratis_setting);
            $kelipatan = true;
        }

        if ($gratis_qty <= 0) {
            return null;
        }

        $alokasi = $this->alokasi_termurah($eligible, $gratis_qty);
        $label = $label !== '' ? $label : ($is_sepatu ? 'Promo Sepatu' : 'Promo Gratis');

        $keterangan_parts = [$label];
        if ($kelipatan) {
            $keterangan_parts[] = 'Berlaku kelipatan';
        }
        $keterangan_parts[] = 'Total ' . $scope . ' ' . $this->rules->format_qty($total_qty) . ' ' . $unit;
        $keterangan_parts[] = 'Gratis ' . $this->rules->format_qty($alokasi['qty']) . ' ' . $unit;

        return [
            'tipe' => $tipe,
            'label' => $label,
            'unit' => $unit,
            'scope_label' => $scope,
            'kelipatan' => $kelipatan,
            'total_qty' => $total_qty,
            'gratis_qty' => $alokasi['qty'],
            'potongan' => (int) round($alokasi['potongan']),
            'keterangan' => implode(' | ', $keterangan_parts),
        ];
    }

    private function total_qty_promo($tipe, array $details)
    {
        $total = 0.0;
        foreach ($this->detail_eligible_promo($tipe, $details) as $detail) {
            $total += $this->detail_qty($detail);
        }

        return $total;
    }

    private function detail_eligible_promo($tipe, array $details)
    {
        return array_values(array_filter($details, function ($detail) use ($tipe) {
            if ($this->detail_qty($detail) <= 0) {
                return false;
            }

            if ($tipe === 'promo_sepatu') {
                return $this->rules->is_promo_sepatu_service_eligible(
                    $this->field($detail, 'nama_satuan'),
                    $this->field($detail, 'nama_kategori'),
                    $this->field($detail, 'nama_paket')
                );
            }

            return $this->rules->is_promo_gratis_service_eligible(
                $this->field($detail, 'nama_satuan'),
                $this->field($detail, 'nama_tipe'),
                $this->field($detail, 'nama_paket')
            );
        }));
    }

    /**
     * Ambil qty gratis dari item termurah dulu (harga sama -> id terkecil dulu).
     */
    private function alokasi_termurah(array $details, $qty_gratis)
    {
        usort($details, function ($a, $b) {
            $harga_a = (float) $this->field($a, 'harga', 0);
            $harga_b = (float) $this->field($b, 'harga', 0);

            if (abs($harga_a - $harga_b) < self::EPSILON) {
                return (int) $this->field($a, 'id', 0) <=> (int) $this->field($b, 'id', 0);
            }

            return $harga_a <=> $harga_b;
        });

        $sisa = (float) $qty_gratis;
        $terpakai = 0.0;
        $potongan = 0.0;

        foreach ($details as $detail) {
            if ($sisa <= self::EPSILON) {
                break;
            }

            $qty = min($sisa, $this->detail_qty($detail));
            if ($qty <= 0) {
                continue;
            }

            $terpakai += $qty;
            $potongan += $qty * (float) $this->field($detail, 'harga', 0);
            $sisa -= $qty;
        }

        return [
            'qty' => round($terpakai, 4),
            'potongan' => $potongan,
        ];
    }

    private function detail_qty($detail)
    {
        return max(0, (float) $this->field($detail, 'qty', 0));
    }

    private function detail_subtotal($detail)
    {
        $subtotal = $this->field($detail, 'subtotal', null);
        if ($subtotal !== null) {
            return (float) $subtotal;
        }

        return $this->detail_qty($detail) * (float) $this->field($detail, 'harga', 0);
    }

    private function field($detail, $key, $default = '')
    {
        if (is_array($detail)) {
            return $detail[$key] ?? $default;
        }

        return $detail->$key ?? $default;
    }
}
