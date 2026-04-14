<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3"></div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h5 class="">
            <i class="fas fa-chart-line me-2"></i> Dashboard Omset
        </h5>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="row mb-4">
        <!-- Card Omset Bulan Ini -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Omset Bulan Ini</p>
                            <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($bulan_ini['total_omset'], 0, ',', '.'); ?></h4>
                            <small class="text-muted"><?= $bulan_ini['label']; ?></small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-coins fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Omset Bulan Lalu -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Omset Bulan Lalu</p>
                            <h4 class="fw-bold text-secondary mb-0">Rp <?= number_format($bulan_lalu['total_omset'], 0, ',', '.'); ?></h4>
                            <small class="text-muted"><?= $bulan_lalu['label']; ?></small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 rounded-3 p-3">
                            <i class="fas fa-history fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Perubahan -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">Perubahan</p>
                            <?php
                            $persen = $bulan_ini['persentase'];
                            $trend  = $bulan_ini['trend'];
                            if ($trend == 'up') {
                                $icon  = 'fa-arrow-up';
                                $color = 'success';
                            } elseif ($trend == 'down') {
                                $icon  = 'fa-arrow-down';
                                $color = 'danger';
                            } else {
                                $icon  = 'fa-minus';
                                $color = 'secondary';
                            }
                            ?>
                            <h4 class="fw-bold text-<?= $color; ?> mb-0">
                                <i class="fas <?= $icon; ?> me-1"></i> <?= abs($persen); ?>%
                            </h4>
                            <small class="text-muted">
                                <?php if ($trend == 'up') : ?>
                                    Naik dari bulan lalu
                                <?php elseif ($trend == 'down') : ?>
                                    Turun dari bulan lalu
                                <?php else : ?>
                                    Sama dengan bulan lalu
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="bg-<?= $color; ?> bg-opacity-10 rounded-3 p-3">
                            <i class="fas <?= $icon; ?> fa-2x text-<?= $color; ?>"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHART -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-chart-area me-2 text-primary"></i> Tren Omset 12 Bulan Terakhir
                </div>
                <div class="card-body">
                    <canvas id="chartOmset" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL RINGKASAN BULANAN -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="fas fa-table me-2 text-primary"></i> Ringkasan Per Bulan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-center">Jumlah Transaksi</th>
                                    <th class="text-end">Total Omset</th>
                                    <th class="text-center">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Tampilkan dari bulan terbaru ke terlama
                                $reversed = array_reverse($data_bulanan);
                                foreach ($reversed as $row) :
                                    $p = $row['persentase'];
                                    $t = $row['trend'];
                                    if ($t == 'up') {
                                        $badge_class = 'bg-success';
                                        $arrow = '▲';
                                    } elseif ($t == 'down') {
                                        $badge_class = 'bg-danger';
                                        $arrow = '▼';
                                    } else {
                                        $badge_class = 'bg-secondary';
                                        $arrow = '―';
                                    }
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?= $row['label']; ?></td>
                                        <td class="text-center"><?= $row['jml_transaksi']; ?></td>
                                        <td class="text-end">Rp <?= number_format($row['total_omset'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $badge_class; ?>">
                                                <?= $arrow; ?> <?= abs($p); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('chartOmset').getContext('2d');

        var labels = <?= json_encode(array_column($data_bulanan, 'label_short')); ?>;
        var dataOmset = <?= json_encode(array_map('intval', array_column($data_bulanan, 'total_omset'))); ?>;
        var dataTrend = <?= json_encode(array_column($data_bulanan, 'trend')); ?>;

        // Warna titik berdasarkan trend
        var pointColors = dataTrend.map(function(t, i) {
            if (i === 0) return '#6c757d';
            return t === 'up' ? '#198754' : (t === 'down' ? '#dc3545' : '#6c757d');
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Omset (Rp)',
                    data: dataOmset,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.08)',
                    borderWidth: 3,
                    pointBackgroundColor: pointColors,
                    pointBorderColor: pointColors,
                    pointRadius: 6,
                    pointHoverRadius: 9,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var val = context.parsed.y;
                                return 'Rp ' + val.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + ' Rb';
                                return 'Rp ' + value;
                            }
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>