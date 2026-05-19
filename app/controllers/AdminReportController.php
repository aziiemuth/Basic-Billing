<?php
class AdminReportController extends Controller {

    private $reportModel;

    public function __construct() {
        AuthAdminMiddleware::check();
        $this->reportModel = $this->model('ReportModel');
    }

    public function index() {
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $summary = $this->reportModel->getSummary($month, $year);
        $cashflow = $this->reportModel->getCashflow($month, $year);
        
        // Fetch data for charts
        $incomeTrend = $this->reportModel->getMonthlyIncomeTrend($year);
        $paymentMethods = $this->reportModel->getPaymentMethodsSummary($month, $year);
        $customerGrowth = $this->reportModel->getCustomerGrowthSummary();

        $data = [
            'title' => 'Laporan Keuangan & Kas',
            'month' => $month,
            'year'  => $year,
            'summary' => $summary,
            'cashflow' => $cashflow,
            'incomeTrend' => $incomeTrend,
            'paymentMethods' => $paymentMethods,
            'customerGrowth' => $customerGrowth
        ];

        $this->view('admin/report/index', $data);
    }
    
    public function pdf() {
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $summary = $this->reportModel->getSummary($month, $year);
        $cashflow = $this->reportModel->getCashflow($month, $year);
        
        $data = [
            'title' => 'Laporan Keuangan & Kas',
            'month' => $month,
            'year'  => $year,
            'summary' => $summary,
            'cashflow' => $cashflow,
            'settings' => $this->model('SettingsModel')->getSettings()
        ];

        $this->view('admin/report/pdf', $data);
    }
    
    public function export() {
        $month = isset($_GET['month']) ? $_GET['month'] : date('m');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

        $cashflow = $this->reportModel->getCashflow($month, $year);

        $filename = "Laporan_Keuangan_" . $year . "_" . $month . ".csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen("php://output", "w");
        
        // Menambahkan karakter BOM (Byte Order Mark) agar format string terbaca sempurna di MS Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, array('Tanggal Transaksi', 'No. Invoice', 'Periode Tagihan', 'Nama Pelanggan', 'Paket Internet', 'Nominal Pembayaran (Rp)', 'Metode'));

        foreach ($cashflow as $row) {
            $formatted_date = date('d M Y H:i:s', strtotime($row->updated_at));
            $formatted_period = date('F Y', strtotime($row->billing_month . '-01'));
            $method = $row->payment_method ? strtoupper(str_replace('_', ' ', $row->payment_method)) : 'MANUAL';
            
            fputcsv($output, array(
                $formatted_date,
                $row->invoice_number,
                $formatted_period,
                $row->customer_name,
                $row->package_name,
                $row->amount,
                $method
            ));
        }
        fclose($output);
        exit;
    }
}
