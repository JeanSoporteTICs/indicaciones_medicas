<?php
// test_export.php: solo para diagnosticar qué está fallando

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);   // que lo muestre en pantalla

echo "<h2>Test export</h2>";

// 1) Probar autoload
$autoload = __DIR__ . '/vendor/autoload.php';
echo "autoload path: {$autoload}<br>";
if (!file_exists($autoload)) {
    echo "<strong>ERROR:</strong> no existe vendor/autoload.php<br>";
    exit;
}
require $autoload;
echo "autoload OK<br>";

// 2) Probar que existe la plantilla
$template = __DIR__ . '/templates/11.11.25.xlsm';
echo "template path: {$template}<br>";
if (!file_exists($template)) {
    echo "<strong>ERROR:</strong> no existe la plantilla<br>";
    exit;
}

// 3) Probar que PhpSpreadsheet puede abrir la plantilla
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $spreadsheet = IOFactory::load($template);
    echo "Plantilla cargada OK<br>";
} catch (Throwable $e) {
    echo "<strong>ERROR al cargar plantilla:</strong><br>";
    echo nl2br(htmlspecialchars($e->getMessage()));
    exit;
}

echo "<strong>Todo OK, el problema ya no está en vendor ni en la plantilla.</strong>";
