// =========================
// Clase Paciente
// =========================
class Paciente {
  constructor(){
    this._data = {
      id: null,
      fecha: '',
      fechaIngreso: '',
      fechaNacimiento: '',
      hora: '',
      edad: 0,
      diasHospitalizacion: 0,
      cama: '',
      sexo: '',
      nombrePaciente: '',
      rut: '',
      ficha: '',
      peso: 0,
      pesoIdeal: 0,
      talla: 0,
      sctm2: 0,
      medicoResponsable: '',
      diagnostico: '',
      volumenHolliday: 0,
      volumenSC: 0,
      crea: 0,
      vfg: 0,
      reb: 0,
      reposo: false,
      la: false,
      sng: false,
      sf: false,
      du: false,
      bh: false,
      cvc: false,
      aislamiento: '',
      regimen: '',
      vm: '',
      sa: '',
      esc: '',
      bis: false,
      tof: false,
      rass: '',
      fechaReceta: '',
      medicamentos: []      // ← solo una vez
    };
  }

  getData(){ return this._data; }
  setData(d){ this._data = { ...this._data, ...d }; }

  // =========================
  // Carga datos desde el formulario
  // =========================
  cargarDesdeFormulario(){
    const val = id => document.getElementById(id)?.value || '';
    const chk = id => document.getElementById(id)?.checked || false;

    Object.assign(this._data, {
      fecha:           val('fecha'),
      fechaIngreso:    val('fechaIngreso'),
      fechaNacimiento: val('fechaNacimiento'),
      hora:            val('hora'),
      cama:            val('cama'),
      sexo:            val('sexo'),
      nombrePaciente:  val('nombrePaciente'),
      rut:             val('rut'),
      ficha:           val('ficha'),
      peso:            parseFloat(val('peso')) || 0,
      talla:           parseFloat(val('talla')) || 0,
      medicoResponsable: val('medicoResponsable'),
      diagnostico:       val('diagnostico'),
      crea:              parseFloat(val('crea')) || 0,
      reposo:   chk('reposo'),
      la:       chk('la'),
      sng:      chk('sng'),
      sf:       chk('sf'),
      du:       chk('du'),
      bh:       chk('bh'),
      cvc:      chk('cvc'),
      aislamiento: val('aislamiento'),
      regimen:     val('regimen'),
      vm:         val('vm'),
      sa:         val('sa'),
      esc:        val('esc'),
      bis:        chk('bis'),
      tof:        chk('tof'),
      rass:       val('rass'),
      fechaReceta: val('fechaReceta')
    });

    // Medicamentos: ahora viene de la tabla única #medicamentos
    this._data.medicamentos = [];
    document.querySelectorAll('#medicamentos tbody tr').forEach(row => {
      const get = name => row.querySelector(`[name="${name}"]`)?.value || '';
      const medicamento = get('m_medicamento');
      if (medicamento) {
        this._data.medicamentos.push({
          medicamento: medicamento,
          dosis:       get('m_dosis'),
          volumen:     get('m_volumen'),
          fi:          get('m_fi')
        });
      }
    });
  }

  // =========================
  // Mostrar datos en formulario
  // =========================
  mostrarEnFormulario(){
    const set = (id,v)=>{ const el=document.getElementById(id); if (el) el.value = (v ?? ''); };
    const setChk=(id,v)=>{ const el=document.getElementById(id); if (el) el.checked = !!v; };

    set('fecha',this._data.fecha);
    set('fechaIngreso',this._data.fechaIngreso);
    set('fechaNacimiento',this._data.fechaNacimiento);
    set('hora',this._data.hora);
    set('edad',this._data.edad);
    set('diasHospitalizacion',this._data.diasHospitalizacion);
    set('cama',this._data.cama);
    set('sexo',this._data.sexo);
    set('nombrePaciente',this._data.nombrePaciente);
    set('rut',this._data.rut);
    set('ficha',this._data.ficha);

    set('peso',this._data.peso);
    set('pesoIdeal',(this._data.pesoIdeal||0).toFixed(2));
    set('talla',this._data.talla);
    set('sctm2',(this._data.sctm2||0).toFixed(2));

    set('medicoResponsable',this._data.medicoResponsable);
    set('diagnostico',this._data.diagnostico);

    set('volumenHolliday',(this._data.volumenHolliday||0).toFixed(2));
    set('volumenSC',(this._data.volumenSC||0).toFixed(2));
    set('crea',this._data.crea);
    set('vfg',(this._data.vfg||0).toFixed(2));
    set('reb',(typeof this._data.reb==='string') ? this._data.reb : (this._data.reb||0).toFixed(2));

    setChk('reposo',this._data.reposo);
    setChk('la',this._data.la);
    setChk('sng',this._data.sng);
    setChk('sf',this._data.sf);
    setChk('du',this._data.du);
    setChk('bh',this._data.bh);
    setChk('cvc',this._data.cvc);

    set('aislamiento',this._data.aislamiento);
    set('regimen',this._data.regimen);
    set('vm',this._data.vm);
    set('sa',this._data.sa);
    set('esc',this._data.esc);

    setChk('bis',this._data.bis);
    setChk('tof',this._data.tof);
    set('rass',this._data.rass);
    set('fechaReceta',this._data.fechaReceta);

    // Datos de receta derivados
    set('nombrePacienteReceta',this._data.nombrePaciente);
    set('fichaReceta',this._data.ficha);
    set('camaReceta',this._data.cama);
    set('pesoReceta',this._data.peso);
    set('edadReceta',this._data.edad);
    set('diagnosticoReceta',this._data.diagnostico);

    // Tabla de recetas: usar SOLO this._data.medicamentos (ya no duplicar)
    const tbodyRecetas = document.querySelector('#tablaRecetas tbody');
    if (tbodyRecetas){
      tbodyRecetas.innerHTML = '';
      (this._data.medicamentos || []).forEach(m => {
        const row = tbodyRecetas.insertRow();
        row.innerHTML = `
          <td>${m.medicamento || ''}</td>
          <td>${m.dosis || ''}</td>
          <td>${m.volumen || ''}</td>
        `;
      });
    }

    // Opcional: también podrías repoblar la tabla #medicamentos desde _data.medicamentos
    const tbodyMeds = document.querySelector('#medicamentos tbody');
    if (tbodyMeds){
      tbodyMeds.innerHTML = '';
      if ((this._data.medicamentos || []).length){
        this._data.medicamentos.forEach(m => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="medicamento-cell"></td>
            <td><input type="text" class="form-control" name="m_dosis" value="${m.dosis || ''}"></td>
            <td><input type="text" class="form-control" name="m_volumen" value="${m.volumen || ''}"></td>
            <td><input type="date" class="form-control" name="m_fi" value="${m.fi || ''}"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
          `;
          tbodyMeds.appendChild(tr);
          ensureMedicamentoInput(tr, m.codigo || '', m.medicamento || '');
        });
      } else {
        // si no hay meds guardados, dejar una fila vacía
        addRowMedic();
      }
    }
  }

  // =========================
  // Cálculos
  // =========================
  calcularEdad(){
    if (!this._data.fechaNacimiento) return 0;
    const hoy = new Date();
    const n = new Date(this._data.fechaNacimiento);
    let e = hoy.getFullYear() - n.getFullYear();
    const m = hoy.getMonth() - n.getMonth();
    if (m<0 || (m===0 && hoy.getDate()<n.getDate())) e--;
    this._data.edad = e;
    return e;
  }

  calcularDiasHospitalizacion(){
    if (!this._data.fechaIngreso) return 0;
    const hoy = new Date();
    const ing = new Date(this._data.fechaIngreso);
    const diff = Math.abs(hoy - ing);
    const d = Math.ceil(diff / (1000*60*60*24));
    this._data.diasHospitalizacion = d;
    return d;
  }

  calcularPesoIdeal(){
    const {sexo,talla} = this._data;
    if (!sexo || !talla) return 0;
    if (sexo === 'M') this._data.pesoIdeal = 50 + 0.91*(talla-152.4);
    else if (sexo === 'F') this._data.pesoIdeal = 45.5 + 0.91*(talla-152.4);
    else this._data.pesoIdeal = 0;
    return this._data.pesoIdeal;
  }

  calcularSCTM2(){
    const {peso,talla} = this._data;
    if (!peso || !talla) return 0;
    this._data.sctm2 = Math.sqrt((talla*peso)/3600);
    return this._data.sctm2;
  }

  calcularVolumenHolliday(){
    const {peso} = this._data;
    if (!peso) return 0;
    if (peso<=10) this._data.volumenHolliday = peso*100;
    else if (peso<=20) this._data.volumenHolliday = 1000 + (peso-10)*50;
    else this._data.volumenHolliday = 1500 + (peso-20)*20;
    return this._data.volumenHolliday;
  }

  calcularVolumenSC(){
    this._data.volumenSC = 1500*(this._data.sctm2 || 0);
    return this._data.volumenSC;
  }

  calcularVFG(){
    const {talla,crea,edad,sexo} = this._data;
    if (!talla || !crea || crea===0) return 0;
    let k = 0.55;
    if (edad<1) k=0.45;
    else if (sexo==='M' && edad>12) k=0.7;
    else if (sexo==='F' && edad>12) k=0.55;
    this._data.vfg = (k*talla)/crea;
    return this._data.vfg;
  }

  calcularREB(){
    const {sexo,fechaNacimiento,peso,talla} = this._data;
    if (!sexo || !fechaNacimiento || !peso || !talla) return 0;
    const e = this.calcularEdad();
    if (e>=18){
      this._data.reb = (sexo==='M')
        ? (66.5 + 13.75*peso + 5.003*talla - 6.755*e)
        : (655.1 + 9.563*peso + 1.850*talla - 4.676*e);
      return this._data.reb;
    }
    if (e<3)      this._data.reb = (peso*60)-54;
    else if(e<=10) this._data.reb = (peso*22)+504;
    else           this._data.reb = (sexo==='M') ? ((peso*17.5)+651) : ((peso*12.2)+746);
    return this._data.reb;
  }

  calcularTodo(){
    this.calcularEdad();
    this.calcularDiasHospitalizacion();
    this.calcularPesoIdeal();
    this.calcularSCTM2();
    this.calcularVolumenHolliday();
    this.calcularVolumenSC();
    this.calcularVFG();
    this.calcularREB();
  }

  // =========================
  // LocalStorage
  // =========================
  guardarLocal(){
    const pacientes = JSON.parse(localStorage.getItem('pacientes') || '{}');
    const id = this._data.id || Date.now();
    this._data.id = id;
    pacientes[id] = this._data;
    localStorage.setItem('pacientes', JSON.stringify(pacientes));
    return id;
  }

  cargarLocalPorId(id){
    const pacientes = JSON.parse(localStorage.getItem('pacientes') || '{}');
    if (pacientes[id]){ this.setData(pacientes[id]); return true; }
    return false;
  }

  buscarLocal(term){
    const pacientes = JSON.parse(localStorage.getItem('pacientes') || '{}');
    const t = term.toLowerCase();
    for (const id in pacientes){
      const p = pacientes[id];
      if (id===term || (p.nombrePaciente||'').toLowerCase().includes(t) || (p.rut||'').includes(term)) return p;
    }
    return null;
  }
}

let pacienteActual = new Paciente();
const importState = { isImported: false, fileName: '' };

function setImportedState(flag, fileName = ''){
  importState.isImported = !!flag;
  importState.fileName = fileName || '';
}

function populateSelectsFromConfig(){
  if (!window.SelectConfig) return;
  Object.entries(window.SelectConfig).forEach(([id, options])=>{
    const select = document.getElementById(id);
    if (!select) return;
    select.innerHTML = '';
    options.forEach(opt=>{
      const option = document.createElement('option');
      option.value = opt.value ?? '';
      option.textContent = opt.label ?? '';
      if (opt.disabled) option.disabled = true;
      if (opt.selected) option.selected = true;
      select.appendChild(option);
    });
  });
}

function getMedicamentoCatalog(){
  return Array.isArray(window.ArsenalCatalog?.medicamentos) ? window.ArsenalCatalog.medicamentos : [];
}

const MEDIC_LIST_ID = 'medicamentosDatalist';

function getMedicamentoDatalist(){
  let datalist = document.getElementById(MEDIC_LIST_ID);
  if (!datalist){
    datalist = document.createElement('datalist');
    datalist.id = MEDIC_LIST_ID;
    document.body.appendChild(datalist);
  }
  return datalist;
}

function updateMedicamentoDatalist(filter = ''){
  const datalist = getMedicamentoDatalist();
  const catalog = getMedicamentoCatalog();
  const normalized = (filter || '').trim().toLowerCase();
  datalist.innerHTML = '';
  let list = catalog;
  if (normalized){
    list = catalog.filter(item => (item.nombre || '').toLowerCase().includes(normalized));
  }
  if (!list.length){
    const opt = document.createElement('option');
    opt.value = 'Sin resultados';
    datalist.appendChild(opt);
    return;
  }
  list.slice(0,50).forEach(item=>{
    const opt = document.createElement('option');
    opt.value = item.nombre || item.codigo || '';
    opt.setAttribute('data-codigo', item.codigo || '');
    datalist.appendChild(opt);
  });
}

function findMedicamentoByCode(code){
  if (!code) return null;
  return getMedicamentoCatalog().find(m => m.codigo === code) || null;
}

function findMedicamentoByName(nombre){
  if (!nombre) return null;
  const normalized = nombre.trim().toLowerCase();
  return getMedicamentoCatalog().find(m => (m.nombre || '').toLowerCase() === normalized) || null;
}

function setMedicamentoInputValue(input, codigo = '', nombre = ''){
  let targetName = nombre;
  let targetCode = codigo;
  if (codigo){
    const item = findMedicamentoByCode(codigo);
    if (item){
      targetName = item.nombre || targetName;
      targetCode = item.codigo || targetCode;
    }
  } else if (nombre){
    const item = findMedicamentoByName(nombre);
    if (item){
      targetName = item.nombre || targetName;
      targetCode = item.codigo || targetCode;
    }
  }
  input.value = targetName || '';
  input.dataset.codigo = targetCode || '';
}

function handleMedicamentoInput(e){
  const input = e.target;
  updateMedicamentoDatalist(input.value);
  const match = findMedicamentoByName(input.value);
  input.dataset.codigo = match?.codigo || '';
}

function handleMedicamentoChange(e){
  const input = e.target;
  const val = input.value.trim();
  if (!val){
    input.dataset.codigo = '';
    return;
  }
  const exact = findMedicamentoByName(val);
  if (exact){
    setMedicamentoInputValue(input, exact.codigo, exact.nombre);
    return;
  }
  const partial = getMedicamentoCatalog().find(item => (item.nombre || '').toLowerCase().includes(val.toLowerCase()));
  if (partial){
    setMedicamentoInputValue(input, partial.codigo, partial.nombre);
  } else {
    input.dataset.codigo = '';
  }
}

function ensureMedicamentoInput(row, selectedCode = '', fallbackName = ''){
  if (!row) return;
  let cell = row.querySelector('.medicamento-cell');
  if (!cell){
    cell = row.querySelector('td');
    if (!cell){
      cell = document.createElement('td');
      row.prepend(cell);
    }
    cell.classList.add('medicamento-cell');
  }
  let input = cell.querySelector('input[name="m_medicamento"]');
  if (!input){
    input = document.createElement('input');
    input.type = 'text';
    input.name = 'm_medicamento';
    input.className = 'form-control medicamento-input';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('list', MEDIC_LIST_ID);
    cell.innerHTML = '';
    cell.appendChild(input);
    input.addEventListener('input', handleMedicamentoInput);
    input.addEventListener('change', handleMedicamentoChange);
    input.addEventListener('blur', handleMedicamentoChange);
  }
  updateMedicamentoDatalist('');
  setMedicamentoInputValue(input, selectedCode, fallbackName);
}

// =========================
// Utilidades UI
// =========================
function removeRow(btn){
  const tr = btn.closest('tr');
  tr?.parentNode.removeChild(tr);
}

function showAppModal(message, title='Aviso'){
  const modalEl = document.getElementById('appModal');
  if (!modalEl){
    alert(message);
    return;
  }
  const titleEl = document.getElementById('appModalLabel');
  const bodyEl = document.getElementById('appModalBody');
  if (titleEl) titleEl.textContent = title;
  if (bodyEl) bodyEl.innerHTML = (message || '').split('\n').map(line=>line.trim()).filter(Boolean).join('<br>');
  if (window.bootstrap?.Modal){
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  } else {
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
  }
}

function calcularYActualizar(){
  pacienteActual.cargarDesdeFormulario();
  pacienteActual.calcularTodo();
  pacienteActual.mostrarEnFormulario();
}

function guardarDatos(){
  const req = ['fecha','fechaIngreso','fechaNacimiento','cama','sexo','nombrePaciente','rut','ficha','peso','talla','medicoResponsable'];
  const miss = [];
  req.forEach(id=>{
    const el=document.getElementById(id);
    el?.classList.remove('is-invalid');
    if(!el || !el.value){
      miss.push(el?.previousElementSibling?.textContent?.replace(' *','') || id);
      el?.classList.add('is-invalid');
    }
  });
  if (miss.length){
    showAppModal('Complete los obligatorios:<br>'+miss.join('<br>'), 'Datos requeridos');
    return;
  }
  if (!importState.isImported){
    alert('Esta acción solo aplica a archivos cargados.');
    return;
  }
  pacienteActual.cargarDesdeFormulario();
  pacienteActual.calcularTodo();
  const data = pacienteActual.getData();
  readMedicamentosTo(data);
  data.archivoOriginal = importState.fileName || '';
  postDownload('export.php', data);
}

async function cargarExcelDesdeArchivo(file){
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);
  try {
    const resp = await fetch('import_excel.php', { method: 'POST', body: formData });
    if (!resp.ok){
      let msg = await resp.text();
      try {
        const err = JSON.parse(msg);
        msg = err.error || msg;
      } catch(e){}
      throw new Error(msg || ('Error '+resp.status));
    }
    const data = await resp.json();
    pacienteActual.setData(data);
    pacienteActual.mostrarEnFormulario();
    setImportedState(true, file.name || '');
    alert('Archivo importado correctamente');
  } catch(err){
    console.error(err);
    alert('No se pudo importar el archivo:\n'+err.message);
  }
}

function nuevoFormulario(){
  if(!confirm('¿Crear nuevo formulario?')) return;
  pacienteActual = new Paciente();
  document.getElementById('hospitalizacionForm').reset();

  // Reiniciar tabla de medicamentos con una fila vacía
  const tbody = document.querySelector('#medicamentos tbody');
  if (tbody){
    tbody.innerHTML = '';
    addRowMedic();
  }

  const hoy = new Date();
  document.getElementById('fecha').value = hoy.toISOString().split('T')[0];
  document.getElementById('fechaIngreso').value = hoy.toISOString().split('T')[0];
  document.getElementById('fechaReceta').value = hoy.toISOString().split('T')[0];
  document.getElementById('hora').value = hoy.toTimeString().substring(0,5);
  setImportedState(false);
}

// =========================
// Medicamentos (tabla única)
// =========================
function addRowMedic() {
    const tbody = document.querySelector('#medicamentos tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="medicamento-cell"></td>
        <td><input type="text" class="form-control" name="m_dosis"></td>
        <td><input type="text" class="form-control" name="m_volumen"></td>
        <td><input type="date" class="form-control" name="m_fi"></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    ensureMedicamentoInput(tr);
}

// Si quieres reutilizarla fuera de la clase:
function readMedicamentosTo(p) {
  p.medicamentos = [];
  document.querySelectorAll('#medicamentos tbody tr').forEach(row => {
    const get = name => row.querySelector(`[name="${name}"]`)?.value || '';
    const input = row.querySelector('input[name="m_medicamento"]');
    let codigo = input?.dataset?.codigo || '';
    let nombre = input?.value || '';
    if (!codigo && nombre){
      const match = findMedicamentoByName(nombre);
      if (match){
        codigo = match.codigo || '';
        nombre = match.nombre || nombre;
      }
    }
    const fi      = get('m_fi');
    const volumen = get('m_volumen');
    const dosis   = get('m_dosis');

    if (!codigo && !nombre) return;
    const nombreMedicamento = nombre || codigo;

    p.medicamentos.push({
      codigo:      codigo,
      medicamento: nombreMedicamento,
      dosis:       dosis,
      volumen:     volumen,
      fi:          fi,
      MEDICAMENTO: nombreMedicamento,
      DOSIS:       dosis,
      VOLUMEN:     volumen,
      FI:          fi,
      CODIGO:      codigo
    });
  });
}

// =========================
// Exportaciones
// =========================
function postDownload(url, payload){
  // OJO: aquí ya NO llamamos readMedicamentosTo, para no pisar nada
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = url;
  form.style.display = 'none';
  const input = document.createElement('input');
  input.type = 'hidden';
  input.name = 'payload';
  input.value = JSON.stringify(payload);
  form.appendChild(input);
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

// Export que usa la plantilla 11.11.25.xlsx (multi-hoja)
function exportAll(){
  pacienteActual.cargarDesdeFormulario();
  pacienteActual.calcularTodo();
  const data = pacienteActual.getData();

  // Fecha general para la planilla (si tu export.php la usa)
  if (!data.fecha){
    data.fecha = data.fechaIngreso || '';
  }

  // Medicamentos en formato que export.php espera
  readMedicamentosTo(data);

  postDownload('export.php', data);
  setImportedState(false);
}

// Si sigues usando export_tpl.php (opcional)
function exportTpl(){
  pacienteActual.cargarDesdeFormulario();
  pacienteActual.calcularTodo();
  const data = pacienteActual.getData();
  readMedicamentosTo(data);
  postDownload('export_tpl.php', data);
}

// =========================
// Listeners
// =========================
['fechaNacimiento','fechaIngreso','sexo','peso','talla','crea'].forEach(id=>{
  const el=document.getElementById(id);
  if(el) el.addEventListener('change', calcularYActualizar);
});

document.getElementById('calculateBtn')?.addEventListener('click', calcularYActualizar);
document.getElementById('saveBtn')?.addEventListener('click', guardarDatos);
document.getElementById('newBtn')?.addEventListener('click', nuevoFormulario);
document.getElementById('exportBtn')?.addEventListener('click', exportAll);
document.getElementById('exportTplBtn')?.addEventListener('click', exportTpl);

const excelInput = document.getElementById('excelFileInput');
document.getElementById('loadBtn')?.addEventListener('click', ()=>{
  excelInput?.click();
});
excelInput?.addEventListener('change', e=>{
  const file = e.target.files?.[0];
  if (file){
    cargarExcelDesdeArchivo(file).finally(()=>{ e.target.value=''; });
  }
});

document.addEventListener('DOMContentLoaded',()=>{
  populateSelectsFromConfig();
  updateMedicamentoDatalist('');
  const hoy = new Date();
  document.getElementById('fecha').value = hoy.toISOString().split('T')[0];
  document.getElementById('fechaIngreso').value = hoy.toISOString().split('T')[0];
  document.getElementById('fechaReceta').value = hoy.toISOString().split('T')[0];
  document.getElementById('hora').value = hoy.toTimeString().substring(0,5);
  setImportedState(false);

  // Si la tabla de medicamentos está vacía, dejar una fila
  const tbody = document.querySelector('#medicamentos tbody');
  if (tbody){
    if (!tbody.querySelector('tr')){
      addRowMedic();
    } else {
      tbody.querySelectorAll('tr').forEach(tr=>ensureMedicamentoInput(tr));
    }
  }

});
