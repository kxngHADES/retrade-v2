<?php
namespace Lib\services;

class monitoring_service{

    # Grafana
    public function getGrafanaUrl(): string {
        return $_ENV['GRAFANA_URL'] ?? "http://localhost:3002";
    }

    # Prometheus
    public function getPrometheusUrl(): string {
        return $_ENV['PROMETHEUS_URL'] ?? "http://localhost:9090";
    }

    # Kibana (Logging)
    public function getKibanaUrl(): string {
        return $_ENV['KIBANA_URL'] ?? "http://localhost:5601";
    }

    /**
     * Get Real-time Systems Stats using system calls
     */
    public function getQuickStats(): array {
        // Load (1 min average)
        $load = sys_getloadavg();
        $server_load = isset($load[0]) ? round($load[0] * 100 / 2, 1) . '%' : '0.0%';

        // Memory usage (Cross-platform compatibility)
        $mem_usage = '0.0%';
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free');
            $free_arr = explode("\n", (string)$free);
            $mem = isset($free_arr[1]) ? preg_split('/\s+/', $free_arr[1]) : [];
            if (count($mem) >= 3 && $mem[1] > 0) {
                $mem_usage = round($mem[2] / $mem[1] * 100, 1) . '%';
            }
        } else {
            // Windows fallback using memory_get_usage
            $mem_usage = round(memory_get_usage(true) / (1024 * 1024), 1) . ' MB';
        }

        // Database Metrics
        $db_status = 'OFFLINE';
        $total_orders = 0;
        $total_revenue = "R 0.00";
        try {
            $conn = \Lib\db\Database::getConnection();
            $db_status = $conn ? 'ONLINE' : 'OFFLINE';
            
            // Total Orders (New Metric)
            $orderStmt = $conn->query("SELECT COUNT(*) as count FROM orders");
            $total_orders = $orderStmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;

            // Total Revenue (New Metric) - Fixed table name from payments to payment
            $revStmt = $conn->query("SELECT SUM(amount) as total FROM payment WHERE status = 1");
            $total_revenue = "R " . number_format($revStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0, 2);
        } catch (\Exception $e) {}

        // Active Users
        $active_users = 0;
        try {
            $conn = \Lib\db\Database::getConnection();
            $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_banned = 0");
            $active_users = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (\Exception $e) {}

        // Disputes - Fixed table name from disputes to payment_disputes
        $pending_disputes = 0;
        try {
            $conn = \Lib\db\Database::getConnection();
            $stmt = $conn->query("SELECT COUNT(*) as count FROM payment_disputes WHERE status = 'open'");
            $pending_disputes = $stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (\Exception $e) {}

        return [
            'load_percent' => $server_load,
            'memory_usage' => $mem_usage,
            'db_status' => $db_status,
            'active_users' => $active_users,
            'pending_disputes' => $pending_disputes,
            'total_orders' => $total_orders,
            'total_revenue' => $total_revenue,
            'api_health' => $this->checkBackendHealth()
        ];
    }

    /**
     * Get Real-time Analytics for Charts
     */
    public function getChartData(): array {
        $conn = \Lib\db\Database::getConnection();
        
        // Revenue over last 6 days
        $revenueData = [];
        $revenueLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            // Fixed table name and status column for chart data
            $stmt = $conn->prepare("SELECT SUM(amount) as total FROM payment WHERE status = 1 AND DATE(paid_at) = :date");
            $stmt->execute(['date' => $date]);
            $revenueData[] = (float)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);
            $revenueLabels[] = date('D', strtotime($date));
        }

        // New Users over last 7 days
        $userData = [];
        $userLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = :date");
            $stmt->execute(['date' => $date]);
            $userData[] = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['count'] ?? 0);
            $userLabels[] = date('D', strtotime($date));
        }

        return [
            'revenue' => ['labels' => $revenueLabels, 'data' => $revenueData],
            'users' => ['labels' => $userLabels, 'data' => $userData]
        ];
    }

    private function checkBackendHealth(): string {
        $url = ($_ENV['BACKEND_INTERNAL_URL'] ?? 'http://fastapi:8000') . "/health";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($http_code >= 200 && $http_code < 300) ? 'HEALTHY' : 'DOWN';
    }
}