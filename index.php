<?php
// index.php: Formulario sin base de datos, cálculos en front y exportación a plantilla por POST.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Hospitalización</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="header text-center">
        <h1><i class="fas fa-hospital me-2"></i>Sistema de Hospitalización</h1>
        <p class="lead">Registro y control de pacientes — Sin base de datos</p>
    </div>

    <div class="container">
        <div class="data-management">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">ID/Nombre/RUT:</span>
                        <input type="text" class="form-control" id="patientSearch" placeholder="Buscar paciente...">
                        <button class="btn btn-primary" type="button" id="loadBtn">Cargar</button>
                        <button class="btn btn-secondary" type="button" id="newBtn">Nuevo</button>
                    </div>
                    <div class="search-container">
                        <div class="patient-list card d-none" id="patientList">
                            <div class="card-body p-0"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" id="saveBtn">
                            <i class="fas fa-save me-1"></i> Guardar local
                        </button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-info" id="exportExcelBtn">
                                <i class="fas fa-file-excel me-1"></i> Exportar Excel (simple)
                            </button>
                            <button class="btn btn-outline-secondary" id="exportCsvBtn">
                                <i class="fas fa-file-csv me-1"></i> CSV
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-warning w-100 mt-2" id="exportBtn">
                        <i class="fas fa-file-export me-1"></i> Exportar planilla (plantilla Excel)
                    </button>
                </div>
            </div>
        </div>

        <form id="hospitalizacionForm">
            <!-- Datos Básicos -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Datos Básicos del Paciente
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Fecha</label>
                                <input type="date" class="form-control" id="fecha" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Fecha de Ingreso</label>
                                <input type="date" class="form-control" id="fechaIngreso" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fechaNacimiento" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label>Hora</label>
                                <input type="time" class="form-control" id="hora">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label>Edad</label>
                                <input type="text" class="form-control calculated-field" id="edad" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label>Días Hosp.</label>
                                <input type="text" class="form-control calculated-field" id="diasHospitalizacion" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Cama</label>
                                <select class="form-select" id="cama" required>
                                    <option value="">Seleccione una cama</option>
                                    <option value="Cama 1">Cama 1</option>
                                    <option value="Cama 2">Cama 2</option>
                                    <option value="Cama 3">Cama 3</option>
                                    <option value="Cama 4">Cama 4</option>
                                    <option value="Cama 5">Cama 5</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Sexo</label>
                                <select class="form-select" id="sexo" required>
                                    <option value="">Seleccione sexo</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="required-field">Nombre del Paciente</label>
                                <input type="text" class="form-control" id="nombrePaciente" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="required-field">RUT</label>
                                <input type="text" class="form-control" id="rut" required placeholder="12.345.678-9">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="required-field">Ficha</label>
                                <input type="text" class="form-control" id="ficha" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="required-field">Médico Responsable</label>
                                <input type="text" class="form-control" id="medicoResponsable" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Diagnóstico</label>
                                <textarea class="form-control" id="diagnostico" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Antropométricos -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-weight me-2"></i>Datos Antropométricos
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Peso (kg)</label>
                                <input type="number" class="form-control" id="peso" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="required-field">Talla (cm)</label>
                                <input type="number" class="form-control" id="talla" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Peso Ideal</label>
                                <input type="text" class="form-control calculated-field" id="pesoIdeal" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>SC T/M2</label>
                                <input type="text" class="form-control calculated-field" id="sctm2" readonly>
                                <small class="form-text text-muted">Superficie Corporal (Mosteller)</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Volumen Holliday</label>
                                <input type="text" class="form-control calculated-field" id="volumenHolliday" readonly>
                                <small class="form-text text-muted">Mantenimiento de líquidos (pediátrico)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Volumen *SC</label>
                                <input type="text" class="form-control calculated-field" id="volumenSC" readonly>
                                <small class="form-text text-muted">1500 mL × SC</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>CREA</label>
                                <input type="number" class="form-control" id="crea" step="0.01">
                                <small class="form-text text-muted">Creatinina (mg/dL)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>VFG</label>
                                <input type="text" class="form-control calculated-field" id="vfg" readonly>
                                <small class="form-text text-muted">Tasa de filtración glomerular</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>REB</label>
                                <input type="text" class="form-control calculated-field" id="reb" readonly>
                                <small class="form-text text-muted">Requerimiento Energético en Reposo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opciones -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-check-square me-2"></i>Opciones
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="reposo">
                                    <label class="form-check-label ms-2" for="reposo">Reposo</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="la">
                                    <label class="form-check-label ms-2" for="la">LA</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="sng">
                                    <label class="form-check-label ms-2" for="sng">SNG</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="sf">
                                    <label class="form-check-label ms-2" for="sf">SF</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="du">
                                    <label class="form-check-label ms-2" for="du">DU</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="bh">
                                    <label class="form-check-label ms-2" for="bh">BH</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="cvc">
                                    <label class="form-check-label ms-2" for="cvc">CVC</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="bis">
                                    <label class="form-check-label ms-2" for="bis">BIS</label>
                                </div>
                                <div class="checkbox-item">
                                    <input class="form-check-input" type="checkbox" id="tof">
                                    <label class="form-check-label ms-2" for="tof">TOF</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Aislamiento</label>
                                <select class="form-select" id="aislamiento">
                                    <option value="">Seleccione tipo</option>
                                    <option value="Ninguno">Ninguno</option>
                                    <option value="Contacto">Contacto</option>
                                    <option value="Aéreo">Aéreo</option>
                                    <option value="Gotículas">Gotículas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>Régimen</label>
                                <input type="text" class="form-control" id="regimen">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>VM</label>
                                <select class="form-select" id="vm">
                                    <option value="">Seleccione VM</option>
                                    <option value="Ninguno">Ninguno</option>
                                    <option value="VM Standard">VM Standard</option>
                                    <option value="VM Avanzado">VM Avanzado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>SA</label>
                                <select class="form-select" id="sa">
                                    <option value="">Seleccione SA</option>
                                    <option value="Ninguno">Ninguno</option>
                                    <option value="SA Básico">SA Básico</option>
                                    <option value="SA Avanzado">SA Avanzado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>ESC</label>
                                <select class="form-select" id="esc">
                                    <option value="">Seleccione ESC</option>
                                    <option value="Ninguno">Ninguno</option>
                                    <option value="ESC 1">ESC 1</option>
                                    <option value="ESC 2">ESC 2</option>
                                    <option value="ESC 3">ESC 3</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label>RASS</label>
                                <select class="form-select" id="rass">
                                    <option value="">Seleccione RASS</option>
                                    <option value="-5">-5 (Sin respuesta)</option>
                                    <option value="-4">-4 (Solo a dolor)</option>
                                    <option value="-3">-3 (Solo a verbal)</option>
                                    <option value="-2">-2 (Despierto con estímulo)</option>
                                    <option value="-1">-1 (Alerta breve)</option>
                                    <option value="0">0 (Alerta y calmado)</option>
                                    <option value="+1">+1 (Ansioso)</option>
                                    <option value="+2">+2 (Agitado)</option>
                                    <option value="+3">+3 (Muy agitado)</option>
                                    <option value="+4">+4 (Combativo)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medicamentos (única) -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-pills me-2"></i>Medicamentos
                </div>
                <div class="card-body">
                    <table class="table table-bordered medicamento-table" id="medicamentos">
    <thead>
        <tr>
            <th width="40%">Medicamento</th>
            <th width="20%">Dosis</th>
            <th width="15%">Volumen</th>
            <th width="15%">Fecha Indicación</th>
            <th width="10%">Acción</th>
        </tr>
    </thead>
   <td><input type="text" class="form-control" name="m_medicamento"></td>
</table>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addRowMedic()">
                        <i class="fas fa-plus me-1"></i> Agregar Fila
                    </button>
                </div>
            </div>

            <!-- Recetas (solo visual, no se usa directo para la plantilla nueva) -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-prescription me-2"></i>Recetas</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Fecha Receta</label>
                                <input type="date" class="form-control" id="fechaReceta">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Nombre Paciente Receta</label>
                                <input type="text" class="form-control" id="nombrePacienteReceta" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>N° Ficha Clínica Receta</label>
                                <input type="text" class="form-control" id="fichaReceta" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Servicio / Cama Receta</label>
                                <input type="text" class="form-control" id="camaReceta" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Peso (kg) Receta</label>
                                <input type="text" class="form-control" id="pesoReceta" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Edad Receta</label>
                                <input type="text" class="form-control" id="edadReceta" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Diagnósticos Receta</label>
                                <textarea class="form-control" id="diagnosticoReceta" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                    <h5>Medicamentos Recetados</h5>
                    <table class="table table-bordered" id="tablaRecetas">
                        <thead>
                            <tr>
                                <th width="50%">Medicamento</th>
                                <th width="25%">Dosis</th>
                                <th width="25%">Volumen</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="action-buttons no-print">
                <button type="button" class="btn btn-info" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Imprimir
                </button>
                <button type="reset" class="btn btn-secondary">Limpiar</button>
                <button type="button" class="btn btn-primary" id="calculateBtn">Calcular</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
