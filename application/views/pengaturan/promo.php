<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="flash-data-success" data-flashdata="<?= $this->session->flashdata('success'); ?>"></div>
    <div class="flash-data-error" data-flashdata="<?= $this->session->flashdata('error'); ?>"></div>

    <div class="row">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header app-section-header py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2"></i> Pengaturan Promo
                    </h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-warning border-0 shadow-sm small mb-4">
                        <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Aturan promo aktif</div>
                        <div>Promo Opening dipakai khusus masa opening cabang. Saat Promo Opening aktif, Promo Daily harus nonaktif. Reward Member tetap berjalan mengikuti poin member.</div>
                    </div>

                    <form action="<?= base_url('pengaturan/update_promo'); ?>" method="post" id="form-promo">

                        <div class="border rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Promo Opening Laundry</h6>
                                    <small class="text-muted">Promo sementara saat masa opening laundry.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="promo_opening_aktif"
                                        name="promo_opening_aktif"
                                        value="1"
                                        <?= (string) $promo['promo_opening_aktif'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="promo_opening_aktif">Aktif</label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="promo_opening_gratis_qty" class="form-label fw-bold">Gratis Cuci</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="0.1"
                                            step="0.1"
                                            class="form-control"
                                            id="promo_opening_gratis_qty"
                                            name="promo_opening_gratis_qty"
                                            value="<?= html_escape($promo['promo_opening_gratis_qty']); ?>"
                                            required>
                                        <span class="input-group-text">Kg</span>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <label for="promo_opening_label" class="form-label fw-bold">Nama Promo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="promo_opening_label"
                                        name="promo_opening_label"
                                        value="<?= html_escape($promo['promo_opening_label']); ?>"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Promo Daily</h6>
                                    <small class="text-muted">Promo harian untuk transaksi laundry kiloan.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="promo_daily_aktif"
                                        name="promo_daily_aktif"
                                        value="1"
                                        <?= (string) $promo['promo_daily_aktif'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="promo_daily_aktif">Aktif</label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="promo_daily_min_kg" class="form-label fw-bold">Minimal Transaksi</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="0.1"
                                            step="0.1"
                                            class="form-control"
                                            id="promo_daily_min_kg"
                                            name="promo_daily_min_kg"
                                            value="<?= html_escape($promo['promo_daily_min_kg']); ?>"
                                            required>
                                        <span class="input-group-text">Kg</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="promo_daily_gratis_qty" class="form-label fw-bold">Gratis Cuci</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="0.1"
                                            step="0.1"
                                            class="form-control"
                                            id="promo_daily_gratis_qty"
                                            name="promo_daily_gratis_qty"
                                            value="<?= html_escape($promo['promo_daily_gratis_qty']); ?>"
                                            required>
                                        <span class="input-group-text">Kg</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="promo_daily_label" class="form-label fw-bold">Nama Promo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="promo_daily_label"
                                        name="promo_daily_label"
                                        value="<?= html_escape($promo['promo_daily_label']); ?>"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Promo Cuci Sepatu</h6>
                                    <small class="text-muted">Promo untuk paket sepatu satuan khusus. Nota sepatu tetap dibuat terpisah secara manual oleh kasir.</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="promo_sepatu_aktif"
                                        name="promo_sepatu_aktif"
                                        value="1"
                                        <?= (string) $promo['promo_sepatu_aktif'] === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-semibold" for="promo_sepatu_aktif">Aktif</label>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="promo_sepatu_min_pasang" class="form-label fw-bold">Minimal Transaksi</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="1"
                                            step="1"
                                            class="form-control"
                                            id="promo_sepatu_min_pasang"
                                            name="promo_sepatu_min_pasang"
                                            value="<?= html_escape($promo['promo_sepatu_min_pasang']); ?>"
                                            required>
                                        <span class="input-group-text">Pasang</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="promo_sepatu_gratis_qty" class="form-label fw-bold">Gratis Cuci</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="1"
                                            step="1"
                                            class="form-control"
                                            id="promo_sepatu_gratis_qty"
                                            name="promo_sepatu_gratis_qty"
                                            value="<?= html_escape($promo['promo_sepatu_gratis_qty']); ?>"
                                            required>
                                        <span class="input-group-text">Pasang</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="promo_sepatu_label" class="form-label fw-bold">Nama Promo</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="promo_sepatu_label"
                                        name="promo_sepatu_label"
                                        value="<?= html_escape($promo['promo_sepatu_label']); ?>"
                                        required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger d-none" id="promo-conflict-warning">
                            Promo Opening dan Promo Daily tidak boleh aktif bersamaan.
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('form-promo');
        var promoOpening = document.getElementById('promo_opening_aktif');
        var promoDaily = document.getElementById('promo_daily_aktif');
        var conflictWarning = document.getElementById('promo-conflict-warning');

        if (!form || typeof Swal === 'undefined') {
            return;
        }

        function hasPromoConflict() {
            return promoOpening && promoDaily && promoOpening.checked && promoDaily.checked;
        }

        function syncPromoConflictWarning() {
            if (!conflictWarning) {
                return;
            }

            conflictWarning.classList.toggle('d-none', !hasPromoConflict());
        }

        if (promoOpening) {
            promoOpening.addEventListener('change', syncPromoConflictWarning);
        }

        if (promoDaily) {
            promoDaily.addEventListener('change', syncPromoConflictWarning);
        }

        syncPromoConflictWarning();

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            if (hasPromoConflict()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Promo Bentrok',
                    text: 'Promo Opening dan Promo Daily tidak boleh aktif bersamaan. Matikan salah satunya terlebih dahulu.',
                    confirmButtonColor: '#0d6efd'
                });
                syncPromoConflictWarning();
                return;
            }

            Swal.fire({
                title: 'Simpan Pengaturan Promo?',
                text: 'Perubahan ini akan langsung dipakai pada transaksi berikutnya.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
