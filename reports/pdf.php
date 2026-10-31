<?php
require __DIR__ . '/../includes/init.php';
require_login();
require __DIR__ . '/../lib/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !csrf_ok()) {
    flash('danger', 'Invalid request. Please try again.');
    redirect('reports/index.php');
}

$type = post_text('type');
if ($type != 'crop' && $type != 'harvest') {
    flash('danger', 'Invalid request. Please try again.');
    redirect('reports/index.php');
}

function pdf_text($text, $limit) {
    $text = str_replace(array("\r\n", "\r", "\n"), ' ', (string)$text);
    if ($limit > 0 && strlen($text) > $limit) {
        $text = substr($text, 0, $limit);
    }
    if (function_exists('iconv')) {
        $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        if ($out !== false) {
            return $out;
        }
    }
    $clean = preg_replace('/[^\x20-\x7E]/', '?', $text);
    if ($clean === null) {
        return '';
    }
    return $clean;
}

$from = '';
$to = '';
$rows = array();
if ($type == 'harvest') {
    $from = post_text('from');
    $to = post_text('to');
    if ($from != '' && !valid_date($from)) {
        flash('danger', 'Please enter a valid start date');
        redirect('reports/index.php?type=harvest');
    }
    if ($to != '' && !valid_date($to)) {
        flash('danger', 'Please enter a valid end date');
        redirect('reports/index.php?type=harvest');
    }
    $sql = 'SELECT h.harvest_date, h.quantity, h.unit, h.quality, h.notes, c.crop_name
            FROM harvests h
            JOIN crops c ON c.id = h.crop_id
            WHERE 1 = 1';
    $types = '';
    $params = array();
    if ($from != '') {
        $sql .= ' AND h.harvest_date >= ?';
        $types .= 's';
        $params[] = $from;
    }
    if ($to != '') {
        $sql .= ' AND h.harvest_date <= ?';
        $types .= 's';
        $params[] = $to;
    }
    $sql .= ' ORDER BY h.harvest_date DESC, h.id DESC';
    $rows = stmt_all($conn, $sql, $types, $params);
} else {
    $rows = stmt_all(
        $conn,
        'SELECT c.crop_name, c.variety, c.area, c.area_unit, c.status,
                (SELECT COUNT(*) FROM irrigations i WHERE i.crop_id = c.id) AS irrigation_count,
                (SELECT COUNT(*) FROM fertilizers f WHERE f.crop_id = c.id) AS fertilizer_count,
                (SELECT COUNT(*) FROM harvests h WHERE h.crop_id = c.id) AS harvest_count
         FROM crops c
         ORDER BY c.crop_name, c.id',
        '',
        array()
    );
}

if ($rows === null) {
    flash('danger', 'Could not build the report.');
    redirect('reports/index.php?type=' . $type);
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, pdf_text($type == 'harvest' ? 'Harvest Report' : 'Crop Report', 0), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, pdf_text('Generated ' . date('Y-m-d'), 0), 0, 1);
if ($type == 'harvest' && ($from != '' || $to != '')) {
    $pdf->Cell(0, 6, pdf_text('From ' . ($from == '' ? 'start' : $from) . ' to ' . ($to == '' ? 'end' : $to), 0), 0, 1);
}
$pdf->Ln(2);

if ($type == 'crop') {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(36, 8, 'Crop', 1, 0);
    $pdf->Cell(32, 8, 'Variety', 1, 0);
    $pdf->Cell(30, 8, 'Area', 1, 0);
    $pdf->Cell(22, 8, 'Status', 1, 0);
    $pdf->Cell(23, 8, 'Irrigations', 1, 0);
    $pdf->Cell(24, 8, 'Fertilizers', 1, 0);
    $pdf->Cell(23, 8, 'Harvests', 1, 1);
    $pdf->SetFont('Arial', '', 8);
    if (!$rows) {
        $pdf->Cell(190, 8, 'No records', 1, 1);
    }
    foreach ($rows as $row) {
        $pdf->Cell(36, 8, pdf_text($row['crop_name'], 0), 1, 0);
        $pdf->Cell(32, 8, pdf_text($row['variety'], 0), 1, 0);
        $pdf->Cell(30, 8, pdf_text($row['area'] . ' ' . $row['area_unit'], 0), 1, 0);
        $pdf->Cell(22, 8, pdf_text($row['status'], 0), 1, 0);
        $pdf->Cell(23, 8, pdf_text($row['irrigation_count'], 0), 1, 0);
        $pdf->Cell(24, 8, pdf_text($row['fertilizer_count'], 0), 1, 0);
        $pdf->Cell(23, 8, pdf_text($row['harvest_count'], 0), 1, 1);
    }
    $filename = 'crop-report.pdf';
} else {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(32, 8, 'Crop', 1, 0);
    $pdf->Cell(26, 8, 'Date', 1, 0);
    $pdf->Cell(20, 8, 'Qty', 1, 0);
    $pdf->Cell(22, 8, 'Unit', 1, 0);
    $pdf->Cell(22, 8, 'Quality', 1, 0);
    $pdf->Cell(40, 8, 'Notes', 1, 1);
    $pdf->SetFont('Arial', '', 8);
    if (!$rows) {
        $pdf->Cell(162, 8, 'No records', 1, 1);
    }
    foreach ($rows as $row) {
        $pdf->Cell(32, 8, pdf_text($row['crop_name'], 0), 1, 0);
        $pdf->Cell(26, 8, pdf_text($row['harvest_date'], 0), 1, 0);
        $pdf->Cell(20, 8, pdf_text($row['quantity'], 0), 1, 0);
        $pdf->Cell(22, 8, pdf_text($row['unit'], 0), 1, 0);
        $pdf->Cell(22, 8, pdf_text($row['quality'], 0), 1, 0);
        $pdf->Cell(40, 8, pdf_text($row['notes'], 50), 1, 1);
    }
    $filename = 'harvest-report.pdf';
}

$pdf->Output('D', $filename);
exit;
