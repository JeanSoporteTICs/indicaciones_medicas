<?php
// Genera un Excel (XML Spreadsheet 2003) sin librerías externas.
// Recibe POST 'payload' con JSON del paciente.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido'); }
$data = json_decode($_POST['payload'] ?? '[]', true);
if (!$data) { http_response_code(400); exit('Payload vacío/ inválido'); }

$filename = 'paciente_'.preg_replace('/[^A-Za-z0-9_-]+/','_', $data['nombrePaciente'] ?? 'datos').'.xml';

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Cache-Control: max-age=0');

function x($v){ return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

$datos = [
  ['Campo','Valor'],
  ['Fecha',$data['fecha']??''],
  ['FechaIngreso',$data['fechaIngreso']??''],
  ['FechaNacimiento',$data['fechaNacimiento']??''],
  ['Hora',$data['hora']??''],
  ['Edad',$data['edad']??''],
  ['DiasHospitalizacion',$data['diasHospitalizacion']??''],
  ['Cama',$data['cama']??''],
  ['Sexo',$data['sexo']??''],
  ['NombrePaciente',$data['nombrePaciente']??''],
  ['RUT',$data['rut']??''],
  ['Ficha',$data['ficha']??''],
  ['Peso',$data['peso']??''],
  ['PesoIdeal',$data['pesoIdeal']??''],
  ['Talla',$data['talla']??''],
  ['SCTM2',$data['sctm2']??''],
  ['MedicoResponsable',$data['medicoResponsable']??''],
  ['Diagnostico',$data['diagnostico']??''],
  ['VolumenHolliday',$data['volumenHolliday']??''],
  ['VolumenSC',$data['volumenSC']??''],
  ['CREA',$data['crea']??''],
  ['VFG',$data['vfg']??''],
  ['REB',$data['reb']??''],
  ['Reposo',!empty($data['reposo'])?'SI':'NO'],
  ['LA',!empty($data['la'])?'SI':'NO'],
  ['SNG',!empty($data['sng'])?'SI':'NO'],
  ['SF',!empty($data['sf'])?'SI':'NO'],
  ['DU',!empty($data['du'])?'SI':'NO'],
  ['BH',!empty($data['bh'])?'SI':'NO'],
  ['CVC',!empty($data['cvc'])?'SI':'NO'],
  ['Aislamiento',$data['aislamiento']??''],
  ['Regimen',$data['regimen']??''],
  ['VM',$data['vm']??''],
  ['SA',$data['sa']??''],
  ['ESC',$data['esc']??''],
  ['BIS',!empty($data['bis'])?'SI':'NO'],
  ['TOF',!empty($data['tof'])?'SI':'NO'],
  ['RASS',$data['rass']??''],
  ['FechaReceta',$data['fechaReceta']??'']
];
$ms1 = $data['medicamentosSuero1'] ?? [];
$ms2 = $data['medicamentosSuero2'] ?? [];
$rec = array_merge($ms1, $ms2);
?>
<?xml version="1.0"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Styles>
  <Style ss:ID="h"><Font ss:Bold="1"/></Style>
 </Styles>

 <Worksheet ss:Name="Datos">
  <Table>
   <?php foreach($datos as $i=>$row): ?>
   <Row>
     <Cell ss:StyleID="<?php echo $i===0?'h':''; ?>"><Data ss:Type="String"><?php echo x($row[0]); ?></Data></Cell>
     <Cell><Data ss:Type="String"><?php echo x($row[1]); ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>

 <Worksheet ss:Name="MedSueros1">
  <Table>
   <Row>
     <Cell ss:StyleID="h"><Data ss:Type="String">Medicamento</Data></Cell>
     <Cell ss:StyleID="h"><Data ss:Type="String">Volumen</Data></Cell>
   </Row>
   <?php foreach($ms1 as $m): ?>
   <Row>
     <Cell><Data ss:Type="String"><?php echo x($m['medicamento']??''); ?></Data></Cell>
     <Cell><Data ss:Type="String"><?php echo x($m['volumen']??''); ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>

 <Worksheet ss:Name="MedSueros2">
  <Table>
   <Row>
     <Cell ss:StyleID="h"><Data ss:Type="String">Medicamento</Data></Cell>
     <Cell ss:StyleID="h"><Data ss:Type="String">Volumen</Data></Cell>
   </Row>
   <?php foreach($ms2 as $m): ?>
   <Row>
     <Cell><Data ss:Type="String"><?php echo x($m['medicamento']??''); ?></Data></Cell>
     <Cell><Data ss:Type="String"><?php echo x($m['volumen']??''); ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>

 <Worksheet ss:Name="Receta">
  <Table>
   <Row>
     <Cell ss:StyleID="h"><Data ss:Type="String">Medicamento</Data></Cell>
     <Cell ss:StyleID="h"><Data ss:Type="String">Dosis</Data></Cell>
     <Cell ss:StyleID="h"><Data ss:Type="String">Volumen</Data></Cell>
   </Row>
   <?php foreach($rec as $m): ?>
   <Row>
     <Cell><Data ss:Type="String"><?php echo x($m['medicamento']??''); ?></Data></Cell>
     <Cell><Data ss:Type="String">Dosis estándar</Data></Cell>
     <Cell><Data ss:Type="String"><?php echo x($m['volumen']??''); ?></Data></Cell>
   </Row>
   <?php endforeach; ?>
  </Table>
 </Worksheet>
</Workbook>
