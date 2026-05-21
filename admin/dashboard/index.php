<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../utils/protected_route.php';
require_once __DIR__ . '/../lib/services/monitoring_service.php';

use Lib\services\monitoring_service;

// Handle AJAX request for real-time stats
if (isset($_GET['ajax']) && $_GET['ajax'] === 'stats') {
    header('Content-Type: application/json');
    $monitoring = new monitoring_service();
    echo json_encode(array_merge($monitoring->getQuickStats(), ['charts' => $monitoring->getChartData()]));
    exit;
}

$monitoring = new monitoring_service();
$view = $_GET['view'] ?? 'overview';
$stats = $monitoring->getQuickStats();
$chartData = $monitoring->getChartData();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - ReTrade</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php require_once __DIR__ . '/../templates/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>System Overview</h1>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-label">Live Server Load</h3>
                <p id="stat-load" class="stat-value primary"><?= $stats['load_percent'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">System Memory</h3>
                <p id="stat-ram" class="stat-value"><?= $stats['memory_usage'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">Total Revenue</h3>
                <p id="stat-revenue" class="stat-value success"><?= $stats['total_revenue'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">Orders Processed</h3>
                <p id="stat-orders" class="stat-value"><?= $stats['total_orders'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">API Status</h3>
                <p id="stat-api" class="stat-value" style="color: <?= $stats['api_health'] === 'HEALTHY' ? 'var(--success)' : 'var(--error)' ?>;"><?= $stats['api_health'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">DB Connection</h3>
                <p id="stat-db" class="stat-value" style="color: <?= $stats['db_status'] === 'ONLINE' ? 'var(--success)' : 'var(--error)' ?>;"><?= $stats['db_status'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">Active Users</h3>
                <p id="stat-users" class="stat-value"><?= $stats['active_users'] ?></p>
            </div>
            <div class="stat-card">
                <h3 class="stat-label">Pending Disputes</h3>
                <p id="stat-disputes" class="stat-value" style="color: <?= $stats['pending_disputes'] > 0 ? 'var(--error)' : 'var(--success)' ?>;"><?= $stats['pending_disputes'] ?></p>
            </div>
        </div>

        <div class="tab-menu">
            <a href="?view=overview" class="tab-item <?= $view === 'overview' ? 'active' : '' ?>">Live Metrics</a>
            <a href="?view=grafana" class="tab-item <?= $view === 'grafana' ? 'active' : '' ?>">Grafana (Metrics)</a>
            <a href="?view=prometheus" class="tab-item <?= $view === 'prometheus' ? 'active' : '' ?>">Prometheus (Raw)</a>
        </div>

        <?php if ($view === 'overview'): ?>
            <div class="chart-grid">
                <div class="chart-container">
                    <h3 class="chart-title">Revenue Analysis</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3 class="chart-title">User Activity</h3>
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            
            <div class="integrity-card">
                <h3 class="chart-title">System Integrity Scan</h3>
                <p class="text-muted">Real-time health monitoring of all interconnected microservices.</p>
                <div class="integrity-grid">
                    <div class="integrity-item">
                        <span class="integrity-label">FastAPI</span>
                        <span class="integrity-status">
                            <i id="dot-api" class="status-dot <?= $stats['api_health'] === 'HEALTHY' ? 'healthy' : 'error' ?>"></i>
                            <span id="integrity-api"><?= $stats['api_health'] ?></span>
                        </span>
                    </div>
                    <div class="integrity-item">
                        <span class="integrity-label">MySQL</span>
                        <span class="integrity-status">
                            <i id="dot-db" class="status-dot <?= $stats['db_status'] === 'ONLINE' ? 'healthy' : 'error' ?>"></i>
                            <span id="integrity-db"><?= $stats['db_status'] ?></span>
                        </span>
                    </div>
                    <div class="integrity-item">
                        <span class="integrity-label">Redis</span>
                        <span class="integrity-status">
                            <i class="status-dot healthy"></i>
                            <span>UP</span>
                        </span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="iframe-container">
                <?php if ($view === 'grafana'): ?>
                    <iframe src="<?= $monitoring->getGrafanaUrl() ?>"></iframe>
                <?php elseif ($view === 'prometheus'): ?>
                    <iframe src="<?= $monitoring->getPrometheusUrl() ?>"></iframe>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const initialChartData = <?= json_encode($chartData) ?>;

        // Initialize Charts
        const revCtx = document.getElementById('revenueChart').getContext('2d');
        const activityCtx = document.getElementById('activityChart').getContext('2d');

        const revenueChart = new Chart(revCtx, {
            type: 'line',
            data: {
                labels: initialChartData.revenue.labels,
                datasets: [{
                    label: 'Revenue (ZAR)',
                    data: initialChartData.revenue.data,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: { maintainAspectRatio: false }
        });

        const activityChart = new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: initialChartData.users.labels,
                datasets: [{
                    label: 'Daily New Users',
                    data: initialChartData.users.data,
                    backgroundColor: '#007bff'
                }]
            },
            options: { maintainAspectRatio: false }
        });

        function updateStats() {
            fetch('?ajax=stats&_t=' + new Date().getTime(), { cache: "no-store" })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stat-load').textContent = data.load_percent;
                    document.getElementById('stat-ram').textContent = data.memory_usage;
                    document.getElementById('stat-revenue').textContent = data.total_revenue;
                    document.getElementById('stat-orders').textContent = data.total_orders;
                    
                    const apiEl = document.getElementById('stat-api');
                    apiEl.textContent = data.api_health;
                    apiEl.style.color = data.api_health === 'HEALTHY' ? 'var(--success)' : 'var(--error)';
                    
                    const dbEl = document.getElementById('stat-db');
                    dbEl.textContent = data.db_status;
                    dbEl.style.color = data.db_status === 'ONLINE' ? 'var(--success)' : 'var(--error)';

                    document.getElementById('integrity-api').textContent = data.api_health;
                    const dotApi = document.getElementById('dot-api');
                    dotApi.className = 'status-dot ' + (data.api_health === 'HEALTHY' ? 'healthy' : 'error');

                    document.getElementById('integrity-db').textContent = data.db_status;
                    const dotDb = document.getElementById('dot-db');
                    dotDb.className = 'status-dot ' + (data.db_status === 'ONLINE' ? 'healthy' : 'error');
                    
                    document.getElementById('stat-users').textContent = data.active_users;
                    
                    document.getElementById('stat-disputes').textContent = data.pending_disputes;
                    document.getElementById('stat-disputes').style.color = data.pending_disputes > 0 ? 'var(--error)' : 'var(--success)';

                    // Update Charts
                    revenueChart.data.labels = data.charts.revenue.labels;
                    revenueChart.data.datasets[0].data = data.charts.revenue.data;
                    revenueChart.update('none');

                    activityChart.data.labels = data.charts.users.labels;
                    activityChart.data.datasets[0].data = data.charts.users.data;
                    activityChart.update('none');
                })
                .catch(error => console.error('Error fetching live stats:', error));
        }

        // Update every 5 seconds
        setInterval(updateStats, 5000);
    </script>
</body>
</html>