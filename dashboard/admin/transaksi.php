<?php
require_once '../../includes/auth.php';
require_once '../../includes/layout.php';
require_once '../../config/database.php';

checkLogin();
checkRole(['admin']);

$stmt = $pdo->prepare("SELECT nama_admin FROM admin WHERE id_admin = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin_name = $stmt->fetchColumn();

$stmt = $pdo->query("
    SELECT p.*, d.nama_distributor, t.nama_tanaman, dhp.jumlah as jumlah_panen
    FROM pembelian p
    JOIN distributor d ON p.id_distributor = d.id_distributor
    JOIN data_hasil_panen dhp ON p.id_panen = dhp.id_panen
    JOIN tanaman t ON dhp.id_tanaman = t.id_tanaman
    ORDER BY p.tanggal_pembelian DESC
");
$transactions = $stmt->fetchAll();

$total_transactions = count($transactions);
$total_value = array_sum(array_column($transactions, 'total_harga'));
$total_weight = array_sum(array_column($transactions, 'jumlah_pembelian'));
$pending_transactions = count(array_filter($transactions, function($t) { return $t['status'] == 'menunggu'; }));

$content = '
<div class="container-fluid p-4">
    <h2 style="color: #43A047;">Transaksi</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" style="color: #43A047;">Dashboard</a></li>
            <li class="breadcrumb-item active">Transaksi</li>
        </ol>
    </nav>


    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body" style="background: linear-gradient(45deg, #2E7D32, #43A047); border-radius: 0.5rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2">Total Transaksi</div>
                            <h3 class="text-white mb-0">Rp ' . number_format($total_value, 0, ",", ".") . '</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header py-3" style="background: linear-gradient(45deg, #2E7D32, #43A047);">
            <div class="d-flex align-items-center">
                <i class="fas fa-list text-white me-2"></i>
                <h5 class="text-white mb-0">Data Transaksi</h5>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <span class="me-2">Show</span>
                        <select class="form-select form-select-sm w-auto" id="table-length">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-2">entries</span>
                    </div>
                    <div class="search-box">
                        <input type="search" class="form-control" placeholder="Search...">
                    </div>
                </div>

                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">TANGGAL</th>
                            <th class="py-3">DISTRIBUTOR</th>
                            <th class="py-3">PETANI</th>
                            <th class="py-3">TANAMAN</th>
                            <th class="py-3">JUMLAH</th>
                            <th class="py-3">TOTAL</th>
                            <th class="py-3">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($transactions as $transaction) {
    $status_class = "";
    switch ($transaction["status"]) {
        case "selesai":
            $status_class = "success";
            break;
        case "menunggu":
            $status_class = "warning";
            break;
        case "ditolak":
            $status_class = "danger";
            break;
    }
    
    $content .= '
                        <tr>
                            <td>' . date("d/m/Y", strtotime($transaction["tanggal"])) . '</td>
                            <td>' . htmlspecialchars($transaction["nama_distributor"]) . '</td>
                            <td>' . htmlspecialchars($transaction["nama_petani"]) . '</td>
                            <td>' . htmlspecialchars($transaction["nama_tanaman"]) . '</td>
                            <td>' . number_format($transaction["jumlah"], 0, ",", ".") . ' kg</td>
                            <td>Rp ' . number_format($transaction["total"], 0, ",", ".") . '</td>
                            <td><span class="badge bg-' . $status_class . '">' . ucfirst($transaction["status"]) . '</span></td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>Showing 0 to 0 of 0 entries</div>
                    <nav>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Previous</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>';

$content .= '
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Export Data Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="export_transaction.php">
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format File</label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_range" class="form-label">Rentang Tanggal</label>
                        <select class="form-select" id="date_range" name="date_range">
                            <option value="all">Semua Data</option>
                            <option value="this_month">Bulan Ini</option>
                            <option value="last_month">Bulan Lalu</option>
                            <option value="this_year">Tahun Ini</option>
                            <option value="custom">Kustom</option>
                        </select>
                    </div>
                    <div id="custom_dates" class="d-none">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Export</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>';

$content .= "
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#transactionTable').DataTable({
        'order': [[0, 'desc']],
        'pageLength': 10,
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
        }
    });

    $('#date_range').change(function() {
        if ($(this).val() === 'custom') {
            $('#custom_dates').removeClass('d-none');
        } else {
            $('#custom_dates').addClass('d-none');
        }
    });
});
</script>";

echo renderPage('Data Transaksi', 'transaksi', $content);
?> 