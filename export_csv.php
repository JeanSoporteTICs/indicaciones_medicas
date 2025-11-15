<?php
// Exporta a CSV (UTF-8 con BOM) unificado por secciones.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido'); }
$data = json_decode($_POST['payload'] ?? '[]', true);
if (!$data) { http_response_code(400); exit('Payload vacío/ inválido'); }

$filename = 'paciente_'.preg_replace('/[^A-Za-z0-9_-]+/','_', $data['nombrePaciente'] ?? 'datos').'.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.$filename.'"');

// BOM para Excel Windows
echo "\xEF\xBB\xBF";

$fp = fopen('php://output','w');
$w = function($row) use ($fp){ fputcsv($fp, $row, ';'); };

$w(['Sección: Datos']);
$w(['Campo','Valor']);
$map = [
  'Fecha'=>'fecha',
  'FechaIngreso'=>'fechaIngreso','FechaNacimiento'=>'fechaNacimiento','Hora'=>'hora','Edad'=>'edad',
  'DiasHospitalizacion'=>'diasHospitalizacion','Cama'=>'cama','Sexo'=>'sexo','NombrePaciente'=>'nombrePaciente',
  'RUT'=>'rut','Ficha'=>'ficha','Peso'=>'peso','PesoIdeal'=>'pesoIdeal','Talla'=>'talla','SCTM2'=>'sctm2',
  'MedicoResponsable'=>'medicoResponsable','Diagnostico'=>'diagnostico','VolumenHolliday'=>'volumenHolliday',
  'VolumenSC'=>'volumenSC','CREA'=>'crea','VFG'=>'vfg','REB'=>'reb','Reposo'=>'reposo','LA'=>'la','SNG'=>'sng',
  'SF'=>'sf','DU'=>'du','BH'=>'bh','CVC'=>'cvc','Aislamiento'=>'aislamiento','Regimen'=>'regimen',
  'VM'=>'vm','SA'=>'sa','ESC'=>'esc','BIS'=>'bis','TOF'=>'tof','RASS'=>'rass','FechaReceta'=>'fechaReceta'
];
foreach($map as $k=>$field){
  $val = $data[$field] ?? '';
  if (in_array($field,['reposo','la','sng','sf','du','bh','cvc','bis','tof'], true)) $val = !empty($val)?'SI':'NO';
  $w([$k, $val]);
}

$w([]); $w(['Sección: Medicamentos Sueros 1']); $w(['Medicamento','Volumen']);
foreach(($data['medicamentosSuero1'] ?? []) as $m){ $w([$m['medicamento'] ?? '', $m['volumen'] ?? '']); }

$w([]); $w(['Sección: Medicamentos Sueros 2']); $w(['Medicamento','Volumen']);
foreach(($data['medicamentosSuero2'] ?? []) as $m){ $w([$m['medicamento'] ?? '', $m['volumen'] ?? '']); }

$w([]); $w(['Sección: Receta (combinado)']); $w(['Medicamento','Dosis','Volumen']);
foreach(array_merge(($data['medicamentosSuero1'] ?? []), ($data['medicamentosSuero2'] ?? [])) as $m){
  $w([$m['medicamento'] ?? '', 'Dosis estándar', $m['volumen'] ?? '']);
}

fclose($fp);
