<?php
// Recibe un archivo Excel previamente exportado y devuelve los datos del paciente en JSON.

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function fail_import($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail_import('Método no permitido', 405);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    fail_import('Archivo no enviado o con errores');
}

$tmpPath = $_FILES['file']['tmp_name'];

$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fail_import('vendor/autoload.php no encontrado en el servidor', 500);
}
require $autoload;

use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load($tmpPath);
} catch (\Throwable $e) {
    fail_import('No se pudo leer el archivo proporcionado: '.$e->getMessage(), 400);
}

$sheetJson = $spreadsheet->getSheetByName('__DATA_JSON');
if (!$sheetJson) {
    fail_import('El archivo no contiene datos embebidos compatibles');
}

$json = (string)$sheetJson->getCell('A1')->getValue();
$data = json_decode($json, true);
if (!is_array($data)) {
    fail_import('Los datos embebidos están corruptos o vacíos');
}

header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit;
