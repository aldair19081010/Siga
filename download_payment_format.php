<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados del formato
$sheet->setCellValue('A1', 'Dni');
$sheet->setCellValue('B1', 'Código Concepto');

// Ejemplo de datos
$sheet->setCellValue('A2', '12345678');
$sheet->setCellValue('B2', '1');

// Configurar encabezados HTTP para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="formato_pagos.xlsx"');
header('Cache-Control: max-age=0');

// Crear y enviar el archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
