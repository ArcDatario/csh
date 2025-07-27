<?php
ob_start();
require_once 'auth_check.php';
require_once '../db_connection.php';

if (!isLoggedIn()) {
    ob_end_clean();
    die("Unauthorized access");
}

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$report_type = $_GET['type'] ?? 'pdf';

// Validate dates
if (!strtotime($start_date) || !strtotime($end_date)) {
    ob_end_clean();
    die("Invalid date format");
}
if (strtotime($end_date) < strtotime($start_date)) {
    ob_end_clean();
    die("End date must be after start date");
}

// Fetch orders data
$query = "SELECT * FROM orders WHERE created_at BETWEEN ? AND ? ORDER BY created_at";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$orders_with_total = [];
$orders_without_total = [];
$total_sales = 0;

while ($row = $result->fetch_assoc()) {
    if ($row['total'] > 0) {
        $orders_with_total[] = $row;
        $total_sales += $row['total'];
    } else {
        $orders_without_total[] = $row;
    }
}
$conn->close();

if ($report_type === 'pdf') {
    ob_end_clean();
    
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('CSH Enterprises');
    $pdf->SetAuthor('CSH Enterprises');
    $pdf->SetTitle('Sales Report');
    $pdf->SetSubject('Sales Data');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    
    // Calculate if all data can fit on one page (approx 25-30 rows max)
    $total_rows = count($orders_with_total) + count($orders_without_total);
    $use_single_page = ($total_rows <= 25);
    
    $pdf->AddPage();
    
    // Report header (centered)
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'CSH ENTERPRISES', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'SALES REPORT', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Total Sales (centered)
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Total Revenue: ' . number_format($total_sales, 2), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Table setup - centered
    $col_widths = [15, 20, 40, 15, 25, 25, 25];
    $table_width = array_sum($col_widths);
    $page_width = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $table_start_x = ($page_width - $table_width) / 2 + $pdf->getMargins()['left'];
    
    // Completed Orders
    if (!empty($orders_with_total)) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'COMPLETED ORDERS', 0, 1, 'C');
        
        $pdf->SetX($table_start_x);
        $header = ['ID', 'Ticket', 'Service', 'Qty', 'Amount', 'Status', 'Date'];
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        foreach ($header as $key => $col) {
            $pdf->Cell($col_widths[$key], 7, $col, 1, 0, 'C', 1);
        }
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 9);
        foreach ($orders_with_total as $order) {
            $pdf->SetX($table_start_x);
            $pdf->Cell($col_widths[0], 6, $order['id'], 'LR', 0, 'C');
            $pdf->Cell($col_widths[1], 6, $order['ticket'], 'LR', 0, 'C');
            $pdf->Cell($col_widths[2], 6, $order['print_type'], 'LR', 0, 'L');
            $pdf->Cell($col_widths[3], 6, $order['quantity'], 'LR', 0, 'C');
            $pdf->Cell($col_widths[4], 6, number_format($order['total'], 2), 'LR', 0, 'R');
            $pdf->Cell($col_widths[5], 6, ucfirst($order['status']), 'LR', 0, 'C');
            $pdf->Cell($col_widths[6], 6, date('m/d/Y', strtotime($order['created_at'])), 'LR', 1, 'C');
            
            // Only check for page break if not in single page mode
            if (!$use_single_page && $pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetX($table_start_x);
            }
        }
        $pdf->SetX($table_start_x);
        $pdf->Cell($table_width, 0, '', 'T');
        $pdf->Ln(10);
    }
    
    // Pending Orders
    if (!empty($orders_without_total)) {
        // Only add new page if not in single page mode and we're running out of space
        if (!$use_single_page && $pdf->GetY() > 200) {
            $pdf->AddPage();
        }
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'PENDING ORDERS', 0, 1, 'C');
        
        $col_widths_pending = [15, 20, 40, 15, 25, 25];
        $table_width_pending = array_sum($col_widths_pending);
        $table_start_x_pending = ($page_width - $table_width_pending) / 2 + $pdf->getMargins()['left'];
        
        $pdf->SetX($table_start_x_pending);
        $header = ['ID', 'Ticket', 'Service', 'Qty', 'Status', 'Date'];
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(240, 240, 240);
        foreach ($header as $key => $col) {
            $pdf->Cell($col_widths_pending[$key], 7, $col, 1, 0, 'C', 1);
        }
        $pdf->Ln();
        
        $pdf->SetFont('helvetica', '', 9);
        foreach ($orders_without_total as $order) {
            $pdf->SetX($table_start_x_pending);
            $pdf->Cell($col_widths_pending[0], 6, $order['id'], 'LR', 0, 'C');
            $pdf->Cell($col_widths_pending[1], 6, $order['ticket'], 'LR', 0, 'C');
            $pdf->Cell($col_widths_pending[2], 6, $order['print_type'], 'LR', 0, 'L');
            $pdf->Cell($col_widths_pending[3], 6, $order['quantity'], 'LR', 0, 'C');
            $pdf->Cell($col_widths_pending[4], 6, ucfirst($order['status']), 'LR', 0, 'C');
            $pdf->Cell($col_widths_pending[5], 6, date('m/d/Y', strtotime($order['created_at'])), 'LR', 1, 'C');
            
            // Only check for page break if not in single page mode
            if (!$use_single_page && $pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetX($table_start_x_pending);
            }
        }
        $pdf->SetX($table_start_x_pending);
        $pdf->Cell($table_width_pending, 0, '', 'T');
    }
    
    $pdf->Output('sales_report_' . date('Ymd_His') . '.pdf', 'D');
    exit();
} else {
    // Excel report generation
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="sales_report_' . $start_date . '_to_' . $end_date . '.xls"');
    
    echo '<table border="1">';
    echo '<tr><th colspan="7" style="font-size:16px;font-weight:bold;">CSH ENTERPRISES</th></tr>';
    echo '<tr><th colspan="7" style="font-size:14px;">SALES REPORT</th></tr>';
    echo '<tr><th colspan="7">Period: ' . date('m/d/Y', strtotime($start_date)) . ' to ' . date('m/d/Y', strtotime($end_date)) . '</th></tr>';
    echo '<tr><th colspan="7">Total Revenue: ' . number_format($total_sales, 2) . '</th></tr>';
    
    // Completed Orders
    echo '<tr><th colspan="7" style="background-color:#ddd;">COMPLETED ORDERS</th></tr>';
    echo '<tr>
            <th style="background-color:#eee;">ID</th>
            <th style="background-color:#eee;">Ticket</th>
            <th style="background-color:#eee;">Service</th>
            <th style="background-color:#eee;">Qty</th>
            <th style="background-color:#eee;">Amount</th>
            <th style="background-color:#eee;">Status</th>
            <th style="background-color:#eee;">Date</th>
          </tr>';
    
    foreach ($orders_with_total as $order) {
        echo '<tr>
                <td>' . $order['id'] . '</td>
                <td>' . $order['ticket'] . '</td>
                <td>' . $order['print_type'] . '</td>
                <td>' . $order['quantity'] . '</td>
                <td>' . number_format($order['total'], 2) . '</td>
                <td>' . ucfirst($order['status']) . '</td>
                <td>' . date('m/d/Y', strtotime($order['created_at'])) . '</td>
              </tr>';
    }
    
    // Pending Orders
    if (!empty($orders_without_total)) {
        echo '<tr><th colspan="7" style="background-color:#ddd;">PENDING ORDERS</th></tr>';
        echo '<tr>
                <th style="background-color:#eee;">ID</th>
                <th style="background-color:#eee;">Ticket</th>
                <th style="background-color:#eee;">Service</th>
                <th style="background-color:#eee;">Qty</th>
                <th style="background-color:#eee;">Status</th>
                <th style="background-color:#eee;">Date</th>
              </tr>';
        
        foreach ($orders_without_total as $order) {
            echo '<tr>
                    <td>' . $order['id'] . '</td>
                    <td>' . $order['ticket'] . '</td>
                    <td>' . $order['print_type'] . '</td>
                    <td>' . $order['quantity'] . '</td>
                    <td>' . ucfirst($order['status']) . '</td>
                    <td>' . date('m/d/Y', strtotime($order['created_at'])) . '</td>
                  </tr>';
        }
    }
    
    echo '</table>';
    exit();
}
?>