<?php
// export_tpl.php: Rellena la plantilla /templates/11.11.25.xlsm con los datos y una (o más) hojas de medicamentos para impresión a doble cara.
// Requiere PhpSpreadsheet (composer). Guardará como XLSX (los macros no se preservan al guardar).
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido'); }
$data = json_decode($_POST['payload'] ?? '[]', true);
if (!$data) { http_response_code(400); exit('Payload vacío/ inválido'); }

$templatePath = __DIR__ . '/templates/11.11.25.xlsm';
if (!file_exists($templatePath)) { http_response_code(500); exit('No se encontró la plantilla'); }

$reader = IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(false);
$spreadsheet = $reader->load($templatePath);

// Helpers
function findCellByText(Worksheet $ws, string $needle, int $maxRow = 60, int $maxCol = 20){
    for ($r=1; $r<=$maxRow; $r++){
        for ($c=1; $c<=$maxCol; $c++){
            $v = $ws->getCellByColumnAndRow($c,$r)->getValue();
            if (is_string($v) && mb_stripos($v, $needle) !== false){
                return [$r,$c,$v];
            }
        }
    }
    return null;
}
function putNext(Worksheet $ws, int $row, int $col, $val){ $ws->setCellValueByColumnAndRow($col+1, $row, $val); }

// --- Indicaciones médicas ---
$wsInd = $spreadsheet->getSheetByName('Indicaciones médicas');
if ($wsInd){
    if ($p = findCellByText($wsInd, 'Fecha ', 30)) putNext($wsInd, $p[0], $p[1], $data['fechaIngreso'] ?? '');
    if ($p = findCellByText($wsInd, 'Fecha Ingreso', 30)) putNext($wsInd, $p[0], $p[1], $data['fechaIngreso'] ?? '');
    if ($p = findCellByText($wsInd, 'F.Nac.', 30)) putNext($wsInd, $p[0], $p[1], $data['fechaNacimiento'] ?? '');
    if ($p = findCellByText($wsInd, 'Hora', 30)) putNext($wsInd, $p[0], $p[1], $data['hora'] ?? '');
    if ($p = findCellByText($wsInd, 'Nombre', 30)) putNext($wsInd, $p[0], $p[1], $data['nombrePaciente'] ?? '');
    if ($p = findCellByText($wsInd, 'Días Hospitalización', 40)) putNext($wsInd, $p[0], $p[1]+2, $data['diasHospitalizacion'] ?? '');
    if ($p = findCellByText($wsInd, 'Peso (kg):', 40)) putNext($wsInd, $p[0], $p[1], $data['peso'] ?? '');
    if ($p = findCellByText($wsInd, 'Peso Ideal', 40)) putNext($wsInd, $p[0], $p[1], $data['pesoIdeal'] ?? '');
    if ($p = findCellByText($wsInd, 'Médico Responsable', 40)) putNext($wsInd, $p[0], $p[1], $data['medicoResponsable'] ?? '');
    if ($p = findCellByText($wsInd, 'Diagnósticos', 60)) putNext($wsInd, $p[0], $p[1]+1, $data['diagnostico'] ?? '');
}
// --- Receta médica ---
$wsRec = $spreadsheet->getSheetByName('Receta médica');
if ($wsRec){
    // Cabecera
    if ($p = findCellByText($wsRec, 'N° Ficha clínica', 30)) putNext($wsRec, $p[0], $p[1], $data['ficha'] ?? '');
    if ($p = findCellByText($wsRec, 'Servicio / Cama', 30)) putNext($wsRec, $p[0], $p[1], ($data['cama'] ?? ''));
    if ($p = findCellByText($wsRec, 'Peso (kg)', 30)) putNext($wsRec, $p[0], $p[1], $data['peso'] ?? '');
    if ($p = findCellByText($wsRec, 'Edad', 30)) putNext($wsRec, $p[0], $p[1], $data['edad'] ?? '');
    if ($p = findCellByText($wsRec, 'Diagnósticos', 60)) putNext($wsRec, $p[0], $p[1], $data['diagnostico'] ?? '');

    // Ubicar fila de inicio para medicamentos (debajo de la cabecera 'MEDICAMENTOS')
    $anchor = findCellByText($wsRec, 'MEDICAMENTOS', 80);
    $startRow = $anchor ? ($anchor[0] + 1) : 13;
    $cols = ['A'=>'medicamento','B'=>'dosis','C'=>'unidad','D'=>'via','E'=>'intervalo','F'=>null,'G'=>null];

    $meds = $data['medicamentos'] ?? [];
    $perSheet = 24; // para impresión dúplex
    $sheetIndex = 1; $i = 0;
    while ($i < count($meds)){
        $ws = $sheetIndex==1 ? $wsRec : $spreadsheet->addSheet(clone $wsRec);
        if ($sheetIndex>1){ $ws->setTitle('Receta médica '.$sheetIndex); }
        // escribir cabecera de nuevo (ya copiada con clone)
        $row = $startRow;
        $limit = min($i+$perSheet, count($meds));
        for (; $i < $limit; $i++, $row++){
            $m = $meds[$i];
            $ws->setCellValue('A'.$row, $m['medicamento'] ?? '');
            $ws->setCellValue('B'.$row, $m['dosis'] ?? '');
            $ws->setCellValue('C'.$row, $m['unidad'] ?? '');
            $ws->setCellValue('D'.$row, $m['via'] ?? '');
            $ws->setCellValue('E'.$row, $m['intervalo'] ?? '');
            // Las columnas F/G se dejan para validación/observaciones si la plantilla las usa
        }
        $sheetIndex++;
    }
}

// Descargar
$filename = 'receta_' . preg_replace('/[^A-Za-z0-9_-]+/','_', $data['nombrePaciente'] ?? 'paciente') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');
exit;
