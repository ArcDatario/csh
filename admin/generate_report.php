<?php
ob_start();
require_once 'auth_check.php';
require_once '../db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../vendor/autoload.php';

if (!isLoggedIn()) {
    ob_end_clean();
    die("Unauthorized access");
}

$start_date  = $_GET['start_date'] ?? date('Y-m-01');
$end_date    = $_GET['end_date'] ?? date('Y-m-t');
$status      = $_GET['status'] ?? 'all';
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

// Ensure full day coverage
$start_date .= " 00:00:00";
$end_date   .= " 23:59:59";

// --- Collect Orders ---
$where  = "created_at BETWEEN ? AND ?";
$params = [$start_date, $end_date];
$types  = "ss";

$query = "SELECT * FROM orders WHERE $where ORDER BY created_at";
$stmt  = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Separate groups
$orders = [
    'pending'   => [],
    'approved'  => [],
    'active'    => [],
    'completed' => [],
];

$total_sales     = 0;
$estimated_sales = 0;

while ($row = $result->fetch_assoc()) {
    $subtotal = $row['subtotal'] ?? 0;

    switch ($row['status']) {
        case 'pending':
            $orders['pending'][] = $row;
            break;
        case 'approved':
            $orders['approved'][] = $row;
            break;
        case 'processing':
        case 'to-pick-up':
        case 'to_ship':
            $orders['active'][] = $row;
            if ($subtotal > 0) $estimated_sales += $subtotal;
            break;
        case 'completed':
            $orders['completed'][] = $row;
            $total_sales += $row['total'] ?? 0;
            break;
    }
}

$conn->close();

// --- Helper for PDF tables ---
function renderPdfTable($pdf, $title, $headers, $rows, $col_widths, $start_x, $table_width) {
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->Cell(0, 10, strtoupper($title), 0, 1, 'C');

    $pdf->SetX($start_x);
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);

    foreach ($headers as $k => $col) {
        $pdf->Cell($col_widths[$k], 7, $col, 1, 0, 'C', 1);
    }
    $pdf->Ln();

    $pdf->SetFont('dejavusans', '', 9);
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $pdf->SetX($start_x);
            foreach ($row as $k => $cell) {
                if (in_array($headers[$k], ['Qty', 'Amount'])) {
                    $align = 'R';
                } elseif (in_array($headers[$k], ['ID', 'Ticket', 'Status', 'Date'])) {
                    $align = 'C';
                } else {
                    $align = 'L';
                }
                $pdf->Cell($col_widths[$k], 6, $cell, 1, 0, $align);
            }
            $pdf->Ln();
        }
    } else {
        $pdf->SetX($start_x);
        $pdf->Cell($table_width, 6, 'No records found', 1, 1, 'C');
    }

    $pdf->Ln(10);
}

// --- PDF Generation ---
if ($report_type === 'pdf') {
    ob_end_clean();

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();

    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'CSH ENTERPRISES', 0, 1, 'C');

    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 8, 'Sales Report', 0, 1, 'C');

    $pdf->SetFont('dejavusans', '', 10);
    $pdf->Cell(0, 6, date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)), 0, 1, 'C');
    $pdf->Ln(3);

    if ($status === 'all') {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Revenue Summary', 0, 1, 'C');

        $pdf->SetFont('dejavusans', '', 10);
        $pdf->Cell(0, 6, '• Total Revenue (Completed): ₱' . number_format($total_sales, 2), 0, 1, 'C');
        $pdf->Cell(0, 6, '• Estimated Revenue (Active): ₱' . number_format($estimated_sales, 2), 0, 1, 'C');
        $pdf->Ln(4);
    } elseif ($status === 'completed') {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Total Revenue (Completed): ₱' . number_format($total_sales, 2), 0, 1, 'C');
        $pdf->Ln(4);
    } elseif ($status === 'active') {
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Estimated Revenue (Active): ₱' . number_format($estimated_sales, 2), 0, 1, 'C');
        $pdf->Ln(4);
    }

    $page_width = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $groups_to_show = $status === 'all' ? ['completed', 'active', 'approved', 'pending'] : [$status];

    foreach ($groups_to_show as $group) {
        if ($group === 'active' || $group === 'completed') {
            $headers = ['ID','Ticket','Service','Qty','Amount','Status','Date'];
            $col_widths = [15,25,40,15,25,25,25];
        } else {
            $headers = ['ID','Ticket','Service','Qty','Status','Date'];
            $col_widths = [15,25,50,15,32,33];
        }

        $table_width = array_sum($col_widths);
        $start_x = ($page_width - $table_width)/2 + $pdf->getMargins()['left'];

        $rows = [];
        foreach ($orders[$group] as $o) {
            $row = [$o['id'],$o['ticket'],$o['print_type'],$o['quantity']];
            if ($group === 'active') $row[] = number_format($o['subtotal'] ?? 0,2);
            elseif ($group === 'completed') $row[] = number_format($o['total'] ?? 0,2);
            $row[] = ucfirst($o['status']);
            $date = ($group === 'completed' && isset($o['completion_date'])) ? $o['completion_date'] : $o['created_at'];
            $row[] = isset($date) ? date('m/d/Y', strtotime($date)) : '';
            $rows[] = $row;
        }
        renderPdfTable($pdf, ucfirst($group) . ' Orders', $headers, $rows, $col_widths, $start_x, $table_width);
    }

    $pdf->Output('sales_report_' . date('Ymd_His') . '.pdf', 'D');
    exit();
}



// --- Generate Excel Report ---
if ($report_type === 'excel') {
    ob_end_clean();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Sales Report');
    $rowNum = 1;

    // --- Content columns ---
    $contentCols = ['B','C','D','E','F','G','H']; 
    // --- Top spacing row ---
    $sheet->setCellValue("A{$rowNum}", "");
    $rowNum++;

    // --- Report Header ---
    $sheet->mergeCells("B{$rowNum}:H{$rowNum}");
    $sheet->setCellValue("B{$rowNum}", "CSH ENTERPRISES");
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum++;

    $sheet->mergeCells("B{$rowNum}:H{$rowNum}");
    $sheet->setCellValue("B{$rowNum}", "Sales Report");
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum++;

    $sheet->mergeCells("B{$rowNum}:H{$rowNum}");
    $sheet->setCellValue("B{$rowNum}", date('F j, Y', strtotime($start_date)) . ' to ' . date('F j, Y', strtotime($end_date)));
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getFont()->setSize(10);
    $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum += 2;

    // --- Revenue Summary ---
    $summaryText = [];
    if ($status === 'all') {
        $summaryText = [
            "Total Revenue (Completed): ₱" . number_format($total_sales,2),
            "Estimated Revenue (Active): ₱" . number_format($estimated_sales,2)
        ];
    } elseif ($status === 'completed') {
        $summaryText = ["Total Revenue (Completed): ₱" . number_format($total_sales,2)];
    } elseif ($status === 'active') {
        $summaryText = ["Estimated Revenue (Active): ₱" . number_format($estimated_sales,2)];
    }

    foreach ($summaryText as $text) {
        $sheet->mergeCells("B{$rowNum}:H{$rowNum}");
        $sheet->setCellValue("B{$rowNum}", $text);
        $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getFont()->setBold(true);
        $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;
    }
    $rowNum++;

    // --- Orders by group ---
    $groups_to_show = $status === 'all' ? ['completed','active','approved','pending'] : [$status];

    foreach ($groups_to_show as $group) {
        $rowNum++; // spacing
        $sheet->mergeCells("B{$rowNum}:H{$rowNum}");
        $sheet->setCellValue("B{$rowNum}", strtoupper($group) . " ORDERS");
        $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("B{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        // Headers
        $headers = ($group === 'active' || $group === 'completed') 
                    ? ['ID','Ticket','Service','Qty','Amount','Status','Date'] 
                    : ['ID','Ticket','Service','Qty','Status','Date']; 
        $col = 'B';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$rowNum}", $header);
            $sheet->getStyle("{$col}{$rowNum}")->getFont()->setBold(true);
            $sheet->getStyle("{$col}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $rowNum++;

        // Rows
        foreach ($orders[$group] as $o) {
            $col = 'B';
            $sheet->setCellValue("{$col}{$rowNum}", $o['id']); $col++;
            $sheet->setCellValue("{$col}{$rowNum}", $o['ticket']); $col++;
            $sheet->setCellValue("{$col}{$rowNum}", $o['print_type']); $col++;
            $sheet->setCellValue("{$col}{$rowNum}", $o['quantity']); $col++;

            if ($group === 'active') {
                $sheet->setCellValue("{$col}{$rowNum}", '₱' . number_format($o['subtotal'] ?? 0, 2)); $col++;
            } elseif ($group === 'completed') {
                $sheet->setCellValue("{$col}{$rowNum}", '₱' . number_format($o['total'] ?? 0, 2)); $col++;
            }

            $sheet->setCellValue("{$col}{$rowNum}", ucfirst($o['status'])); $col++;
            $date = ($group === 'completed' && isset($o['completion_date'])) ? $o['completion_date'] : $o['created_at'];
            $sheet->setCellValue("{$col}{$rowNum}", date('m/d/Y', strtotime($date))); $col++;

            // Center all cells in this row
            $currentCol = 'B';
            while ($currentCol !== $col) {
                $sheet->getStyle("{$currentCol}{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $currentCol++;
            }
            $rowNum++;
        }
        $rowNum++;
    }

    // --- Fill A to I with white, apply left/right borders ---
    $highestRow = $sheet->getHighestRow();
    foreach (range(1, $highestRow) as $r) {
        foreach (range('A','I') as $c) {
            $sheet->getStyle("{$c}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFFFF');
        }
        // Left border for A, right border for I
        $sheet->getStyle("A{$r}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("I{$r}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    }

    // --- Add 2 extra white rows immediately after content ---
    for ($i = 0; $i < 2; $i++) {
        $rowNum = $sheet->getHighestRow() + 1;
        foreach (range('A','I') as $c) {
            $sheet->setCellValue("{$c}{$rowNum}", "");
            $sheet->getStyle("{$c}{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFFFF');
        }
        $sheet->getStyle("A{$rowNum}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("I{$rowNum}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    }

    // --- Bottom border for the last row ---
    $finalRow = $sheet->getHighestRow();
    foreach (range('A','I') as $c) {
        $sheet->getStyle("{$c}{$finalRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }

    // Auto-size content columns B-H
    foreach (range('B','H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // --- Output Excel ---
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Ymd_His') . '.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
