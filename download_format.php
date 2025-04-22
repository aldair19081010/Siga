
<?php
require_once __DIR__ . '/vendor/autoload.php'; // Asegúrate de que la ruta sea correcta
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados del formato
$sheet->setCellValue('A1', 'Dni');
$sheet->setCellValue('B1', 'Nombre');
$sheet->setCellValue('C1', 'Correo');
$sheet->setCellValue('D1', 'Contacto');
$sheet->setCellValue('E1', 'Dirección');
$sheet->setCellValue('F1', 'Nivel');
$sheet->setCellValue('G1', 'Grado');

// Ejemplo de datos
$sheet->setCellValue('A2', '12345678');
$sheet->setCellValue('B2', 'Juan Pérez');
$sheet->setCellValue('C2', 'juan.perez@example.com');
$sheet->setCellValue('D2', '987654321');
$sheet->setCellValue('E2', 'Av. Siempre Viva 123');
$sheet->setCellValue('F2', 'Primaria');
$sheet->setCellValue('G2', '5°');

// Configurar encabezados HTTP para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="formato_estudiantes.xlsx"');
header('Cache-Control: max-age=0');

// Crear y enviar el archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
