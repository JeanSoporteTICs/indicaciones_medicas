<?php
// Exporta la planilla indicaciones usando la plantilla 11.11.25.xlsx
// y los marcadores {{campo}} ya incluidos en la hoja "Indicaciones médicas".

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function fail($msg, $code = 500) {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo "[ERROR] " . $msg;
    exit;
}

// --- Validar método y payload ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Método no permitido (use POST)', 405);
}

$raw = $_POST['payload'] ?? '';
if (!$raw) {
    fail('Payload vacío o no enviado', 400);
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    fail('JSON inválido en payload', 400);
}

// --- Autoload Composer ---
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fail('No se encontró vendor/autoload.php. Ejecuta composer install.', 500);
}
require $autoload;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// --- Cargar plantilla ---
$templatePath = __DIR__ . '/templates/11.11.25.xlsx';
if (!file_exists($templatePath)) {
    fail('No se encontró la plantilla en templates/11.11.25.xlsx', 500);
}

try {
    $spreadsheet = IOFactory::load($templatePath);
} catch (\Throwable $e) {
    fail('No se pudo abrir la plantilla: ' . $e->getMessage(), 500);
}

/* ===========================================================
   HELPERS PARA MARCADORES Y MEDICAMENTOS
   =========================================================== */

/**
 * Busca la PRIMERA celda cuyo valor sea exactamente $marker
 * (ej: "{{fechaIngreso}}") y devuelve su coordenada ("D6").
 */
function findCellByMarker(Worksheet $ws, string $marker, int $maxRow = 200, int $maxCol = 50): ?string {
    for ($r = 1; $r <= $maxRow; $r++) {
        for ($c = 1; $c <= $maxCol; $c++) {
            $cell = $ws->getCellByColumnAndRow($c, $r);
            $v = $cell->getValue();
            if ($v === $marker) {
                return $cell->getCoordinate();
            }
        }
    }
    return null;
}

/**
 * Rellena un marcador único (si existe).
 */
function fillByMarker(Worksheet $ws, string $marker, $value, int $maxRow = 200, int $maxCol = 50): void {
    $coord = findCellByMarker($ws, $marker, $maxRow, $maxCol);
    if ($coord !== null) {
        $ws->setCellValue($coord, $value);
    }
}

/**
 * Busca filas de la hoja "Receta" que contengan marcadores para medicamento/dosis.
 */
function getRecetaSlots(Worksheet $ws, int $startRow = 1, int $endRow = 200, int $maxCol = 15): array {
    $slots = [];
    for ($row = $startRow; $row <= $endRow; $row++) {
        $medCoord = null;
        $dosisCoord = null;
        for ($col = 1; $col <= $maxCol; $col++) {
            $value = $ws->getCellByColumnAndRow($col, $row)->getValue();
            if ($value === '{{MEDICAMENTO}}') {
                $medCoord = Coordinate::stringFromColumnIndex($col) . $row;
            } elseif ($value === '{{DOSIS}}') {
                $dosisCoord = Coordinate::stringFromColumnIndex($col) . $row;
            }
        }
        if ($medCoord !== null) {
            $slots[] = ['med' => $medCoord, 'dosis' => $dosisCoord];
        }
    }
    return $slots;
}

/**
 * Obtiene todos los "slots" de medicamentos en la hoja,
 * detectando bloques del tipo:
 *  fila n:   A="{{FI}}", B="{{MEDICAMENTO}}"
 *  fila n+1: A="{{VOLUMEN}}", B="{{DOSIS}}"
 */
function getMedicationSlots(Worksheet $ws, int $startRow = 28, int $endRow = 102): array {
    $slots = [];
    for ($row = $startRow; $row <= $endRow; $row++) {
        $fiVal   = $ws->getCell("A{$row}")->getValue();
        $medVal  = $ws->getCell("B{$row}")->getValue();
        $volVal  = $ws->getCell("A".($row+1))->getValue();
        $dosVal  = $ws->getCell("B".($row+1))->getValue();

        if ($fiVal === '{{FI}}' && $medVal === '{{MEDICAMENTO}}'
            && $volVal === '{{VOLUMEN}}' && $dosVal === '{{DOSIS}}') {

            $slots[] = [
                'fi'    => "A{$row}",
                'med'   => "B{$row}",
                'vol'   => "A".($row+1),
                'dosis' => "B".($row+1),
            ];

            $row++; // saltar la fila siguiente ya procesada
        }
    }
    return $slots;
}

/* ===========================================================
   HOJA: INDICACIONES MÉDICAS
   =========================================================== */

$ws = $spreadsheet->getSheetByName('Indicaciones médicas');
if (!$ws) {
    fail('La hoja "Indicaciones médicas" no existe en la plantilla.', 500);
}

/* ---- Datos básicos ---- */

// fecha "general" (puede ser igual a fechaIngreso)
$fecha = $data['fecha'] ?? ($data['fechaIngreso'] ?? '');
fillByMarker($ws, '{{fecha}}',            $fecha);
fillByMarker($ws, '{{fechaIngreso}}',     $data['fechaIngreso']     ?? '');
fillByMarker($ws, '{{fechaNacimiento}}',  $data['fechaNacimiento']  ?? '');
fillByMarker($ws, '{{hora}}',             $data['hora']             ?? '');
fillByMarker($ws, '{{edad}}',             $data['edad']             ?? '');
fillByMarker($ws, '{{cama}}',             $data['cama']             ?? '');
fillByMarker($ws, '{{sexo}}',             $data['sexo']             ?? '');
fillByMarker($ws, '{{nombrePaciente}}',   $data['nombrePaciente']   ?? '');
fillByMarker($ws, '{{rut}}',              $data['rut']              ?? '');
fillByMarker($ws, '{{ficha}}',            $data['ficha']            ?? '');
fillByMarker($ws, '{{diasHospitalizacion}}', $data['diasHospitalizacion'] ?? '');

/* ---- Datos antropométricos y cálculos ---- */

fillByMarker($ws, '{{peso}}',             $data['peso']             ?? '');
fillByMarker($ws, '{{pesoIdeal}}',        $data['pesoIdeal']        ?? '');
fillByMarker($ws, '{{talla}}',            $data['talla']            ?? '');
fillByMarker($ws, '{{SCTm2}}',            $data['sctm2']            ?? ($data['SCTm2'] ?? ''));

fillByMarker($ws, '{{VOLUMEN HOLLIDAY}}', $data['volumenHolliday']  ?? '');
fillByMarker($ws, '{{VOLUMEN *SC}}',      $data['volumenSC']        ?? '');
fillByMarker($ws, '{{CREA}}',             $data['crea']             ?? '');
fillByMarker($ws, '{{VFG}}',              $data['vfg']              ?? '');
fillByMarker($ws, '{{REB}}',              $data['reb']              ?? '');

// Médico y diagnóstico
fillByMarker($ws, '{{medicoResponsable}}', $data['medicoResponsable'] ?? '');
fillByMarker($ws, '{{diagnostico}}',       $data['diagnostico']       ?? '');

/* ---- Opciones / checkboxes ---- */

$marca = function ($v) {
    // Devuelve "X" si es true/1/"on", vacío si no
    if ($v === true)  return 'X';
    if ($v === 1)     return 'X';
    if ($v === '1')   return 'X';
    if ($v === 'on')  return 'X';
    if ($v === 'X')   return 'X';
    return '';
};

// REPOSO
fillByMarker($ws, '{{REPOSO}}', $marca($data['reposo'] ?? null));

// LA / SNG / SF están mezclados con texto "LA: {{LA}}", etc.
// mejor sobreescribir toda la celda:
$ws->setCellValue('B15', 'LA: '  . $marca($data['la']  ?? null));
$ws->setCellValue('C15', 'SNG: ' . $marca($data['sng'] ?? null));
$ws->setCellValue('D15', 'SF: '  . $marca($data['sf']  ?? null));

// DU, BH, CVC
fillByMarker($ws, '{{DU}}',  $marca($data['du']  ?? null));
fillByMarker($ws, '{{BH}}',  $marca($data['bh']  ?? null));
fillByMarker($ws, '{{CVC}}', $marca($data['cvc'] ?? null));

// AISLAMIENTO, REGIMEN, VMI, SA, ESC, RASS, BIS, TOF
fillByMarker($ws, '{{AISLAMIENTO}}', $data['aislamiento'] ?? '');
fillByMarker($ws, '{{REGIMEN}}',     $data['regimen']     ?? '');


fillByMarker($ws, '{{VM}}',         $data['vm']          ?? ($data['VM'] ?? ''));

// SA, ESC, RASS, BIS, TOF
fillByMarker($ws, '{{SA}}',   $data['sa']   ?? '');
fillByMarker($ws, '{{ESC}}',  $data['esc']  ?? '');
fillByMarker($ws, '{{RASS}}', $data['rass'] ?? '');
fillByMarker($ws, '{{BIS}}',  $marca($data['bis'] ?? null));
fillByMarker($ws, '{{TOF}}',  $marca($data['tof'] ?? null));

// SVC: si no tienes campo en el formulario, se queda vacío (o usa cvc si quieres)
fillByMarker($ws, '{{SVC}}', $data['svc'] ?? '');

/* ===========================================================
   MEDICAMENTOS
   =========================================================== */

// Soportar dos formatos posibles:
// 1) "medicamentos": [ {fi, medicamento, volumen, dosis}, ... ]
// 2) "medicamentosSuero1"/"medicamentosSuero2" como antes.
$meds = [];

// Formato nuevo preferido
if (!empty($data['medicamentos']) && is_array($data['medicamentos'])) {
    $meds = $data['medicamentos'];
} else {
    // Compat: combinar suero1 + suero2, solo con nombre + volumen
    foreach (['medicamentosSuero1', 'medicamentosSuero2'] as $key) {
        if (!empty($data[$key]) && is_array($data[$key])) {
            foreach ($data[$key] as $m) {
                $meds[] = [
                    'fi'         => $m['fi']         ?? '',
                    'medicamento'=> $m['medicamento'] ?? '',
                    'volumen'    => $m['volumen']    ?? '',
                    'dosis'      => $m['dosis']      ?? '',
                ];
            }
        }
    }
}

// Detectar slots disponibles en la planilla
$slots = getMedicationSlots($ws, 28, 102);

// Rellenar slots en orden
$max = min(count($slots), count($meds));
for ($i = 0; $i < $max; $i++) {
    $slot = $slots[$i];
    $m    = $meds[$i];

    $ws->setCellValue($slot['fi'],    $m['fi']         ?? '');
    $ws->setCellValue($slot['med'],   $m['medicamento'] ?? '');
    $ws->setCellValue($slot['vol'],   $m['volumen']    ?? '');
    $ws->setCellValue($slot['dosis'], $m['dosis']      ?? '');
}

// Si sobran slots no usados, los dejamos con los {{...}} para que no se vea feo:
for ($i = $max; $i < count($slots); $i++) {
    $slot = $slots[$i];
    $ws->setCellValue($slot['fi'],    '');
    $ws->setCellValue($slot['med'],   '');
    $ws->setCellValue($slot['vol'],   '');
    $ws->setCellValue($slot['dosis'], '');
}

/* ===========================================================
   HOJA: RECETA MÉDICA
   =========================================================== */

$wsReceta = $spreadsheet->getSheetByName('Receta Médica') ?? $spreadsheet->getSheetByName('Receta Medica');
if ($wsReceta) {
    fillByMarker($wsReceta, '{{fecha}}',           $fecha);
    fillByMarker($wsReceta, '{{nombrePaciente}}',  $data['nombrePaciente'] ?? '');
    fillByMarker($wsReceta, '{{ficha}}',           $data['ficha'] ?? '');
    fillByMarker($wsReceta, '{{cama}}',            $data['cama'] ?? '');
    fillByMarker($wsReceta, '{{peso}}',            $data['peso'] ?? '');
    fillByMarker($wsReceta, '{{edad}}',            $data['edad'] ?? '');
    fillByMarker($wsReceta, '{{diagnostico}}',     $data['diagnostico'] ?? '');

    $slotsReceta = getRecetaSlots($wsReceta, 1, 200, 20);
    $maxReceta = min(count($slotsReceta), count($meds));
    for ($i = 0; $i < $maxReceta; $i++) {
        $slot = $slotsReceta[$i];
        $m    = $meds[$i];
        if (!empty($slot['med'])) {
            $wsReceta->setCellValue($slot['med'], $m['medicamento'] ?? '');
        }
        if (!empty($slot['dosis'])) {
            $wsReceta->setCellValue($slot['dosis'], $m['dosis'] ?? '');
        }
    }
    for ($i = $maxReceta; $i < count($slotsReceta); $i++) {
        $slot = $slotsReceta[$i];
        if (!empty($slot['med'])) {
            $wsReceta->setCellValue($slot['med'], '');
        }
        if (!empty($slot['dosis'])) {
            $wsReceta->setCellValue($slot['dosis'], '');
        }
    }
}

// Adjuntar hoja oculta con los datos en JSON para futuras importaciones
$jsonSheetName = '__DATA_JSON';
$existingJson = $spreadsheet->getSheetByName($jsonSheetName);
if ($existingJson) {
    $idx = $spreadsheet->getIndex($existingJson);
    $spreadsheet->removeSheetByIndex($idx);
}
$jsonSheet = new Worksheet($spreadsheet, $jsonSheetName);
$jsonSheet->setCellValue('A1', json_encode($data, JSON_UNESCAPED_UNICODE));
$jsonSheet->setSheetState(Worksheet::SHEETSTATE_VERYHIDDEN);
$spreadsheet->addSheet($jsonSheet);

/* ===========================================================
   GENERAR XLSX Y ENVIAR AL NAVEGADOR
   =========================================================== */

try {
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

    // No forzar recálculo de fórmulas aquí (lo hará Excel al abrir)
    if (method_exists($writer, 'setPreCalculateFormulas')) {
        $writer->setPreCalculateFormulas(false);
    }
} catch (\Throwable $e) {
    fail('Error al crear writer Xlsx: ' . $e->getMessage(), 500);
}

// Guardar en archivo temporal
$tmpFile = tempnam(sys_get_temp_dir(), 'ind_');
try {
    $writer->save($tmpFile);
} catch (\Throwable $e) {
    @unlink($tmpFile);
    fail('Error al generar archivo XLSX: ' . $e->getMessage(), 500);
}

$size = filesize($tmpFile);
if ($size === false || $size === 0) {
    @unlink($tmpFile);
    fail('El archivo generado está vacío o no se pudo leer.', 500);
}

// Limpiar buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

if (!empty($data['archivoOriginal'])) {
    $cleanName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $data['archivoOriginal']);
    if ($cleanName === '') {
        $cleanName = 'indicaciones.xlsx';
    }
    if (!preg_match('/\.xlsx$/i', $cleanName)) {
        $cleanName .= '.xlsx';
    }
    $filename = $cleanName;
} else {
    $filename = 'indicaciones_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $data['nombrePaciente'] ?? 'paciente') . '.xlsx';
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.$size);
header('Cache-Control: max-age=0');

readfile($tmpFile);
@unlink($tmpFile);
exit;
