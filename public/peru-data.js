/* =========================================================
   Datos de Perú (subset representativo) + anuncios de muestra
   ========================================================= */

// Estructura: departamento -> { provincia: [distritos] }
// Subset realista para demostrar el funcionamiento de los selects encadenados.
const PERU = {
  "Amazonas": {
    "Chachapoyas": ["Chachapoyas", "Huancas", "La Jalca", "Levanto", "Molinopampa"],
    "Bagua":      ["Bagua", "Aramango", "Copallín", "El Parco", "Imaza"],
    "Utcubamba":  ["Bagua Grande", "Cajaruro", "Cumba", "El Milagro", "Lonya Grande"]
  },
  "Áncash": {
    "Huaraz":   ["Huaraz", "Independencia", "Cochabamba", "Olleros", "Pira"],
    "Santa":    ["Chimbote", "Nuevo Chimbote", "Coishco", "Samanco", "Santa"],
    "Huari":    ["Huari", "Anra", "Cajay", "Chavín de Huántar", "San Marcos"]
  },
  "Apurímac": {
    "Abancay":     ["Abancay", "Curahuasi", "Tamburco", "Lambrama", "Pichirhua"],
    "Andahuaylas": ["Andahuaylas", "Talavera", "San Jerónimo", "Pacucha", "Chiara"]
  },
  "Arequipa": {
    "Arequipa":  ["Arequipa", "Cayma", "Cerro Colorado", "Yanahuara", "Miraflores", "José Luis Bustamante y Rivero", "Paucarpata", "Sachaca"],
    "Camaná":    ["Camaná", "José María Quimper", "Mariano Nicolás Valcárcel", "Mariscal Cáceres", "Nicolás de Piérola"],
    "Caylloma":  ["Chivay", "Achoma", "Cabanaconde", "Callalli", "Coporaque"],
    "Islay":     ["Mollendo", "Cocachacra", "Dean Valdivia", "Islay", "Mejía", "Punta de Bombón"]
  },
  "Ayacucho": {
    "Huamanga": ["Ayacucho", "Carmen Alto", "Jesús Nazareno", "San Juan Bautista", "Tambillo"],
    "Huanta":   ["Huanta", "Ayahuanco", "Huamanguilla", "Iguaín", "Luricocha"]
  },
  "Cajamarca": {
    "Cajamarca":      ["Cajamarca", "Baños del Inca", "Jesús", "Llacanora", "Magdalena", "Namora"],
    "Jaén":           ["Jaén", "Bellavista", "Chontalí", "Colasay", "Huabal", "Las Pirias"],
    "Chota":          ["Chota", "Anguía", "Chadín", "Cochabamba", "Lajas"]
  },
  "Callao": {
    "Callao":     ["Callao", "Bellavista", "Carmen de la Legua-Reynoso", "La Perla", "La Punta", "Ventanilla", "Mi Perú"]
  },
  "Cusco": {
    "Cusco":        ["Cusco", "San Sebastián", "Santiago", "San Jerónimo", "Wanchaq", "Saylla", "Poroy"],
    "Urubamba":     ["Urubamba", "Chinchero", "Huayllabamba", "Machupicchu", "Maras", "Ollantaytambo", "Yucay"],
    "La Convención":["Quillabamba", "Echarate", "Maranura", "Ocobamba", "Quellouno", "Santa Ana"]
  },
  "Huancavelica": {
    "Huancavelica": ["Huancavelica", "Acobambilla", "Acoria", "Conayca", "Cuenca"],
    "Tayacaja":     ["Pampas", "Acostambo", "Acraquia", "Ahuaycha", "Colcabamba"]
  },
  "Huánuco": {
    "Huánuco":      ["Huánuco", "Amarilis", "Pillco Marca", "Chinchao", "Margos"],
    "Leoncio Prado":["Tingo María", "Daniel Alomía Robles", "Hermilio Valdizán", "José Crespo y Castillo", "Mariano Dámaso Beraún"]
  },
  "Ica": {
    "Ica":       ["Ica", "La Tinguiña", "Los Aquijes", "Parcona", "Pueblo Nuevo", "Salas", "San Juan Bautista", "Subtanjalla"],
    "Chincha":   ["Chincha Alta", "Alto Larán", "Chincha Baja", "El Carmen", "Grocio Prado", "Pueblo Nuevo"],
    "Pisco":     ["Pisco", "Humay", "Independencia", "Paracas", "San Andrés", "San Clemente", "Tupac Amaru Inca"],
    "Nazca":     ["Nazca", "Changuillo", "El Ingenio", "Marcona", "Vista Alegre"]
  },
  "Junín": {
    "Huancayo":  ["Huancayo", "Chilca", "El Tambo", "Pilcomayo", "San Agustín", "Sapallanga", "Viques"],
    "Concepción":["Concepción", "Aco", "Andamarca", "Chambará", "Cochas", "Comas"],
    "Jauja":     ["Jauja", "Acolla", "Apata", "Ataura", "Canchayllo", "Curicaca"]
  },
  "La Libertad": {
    "Trujillo":   ["Trujillo", "El Porvenir", "Florencia de Mora", "Huanchaco", "La Esperanza", "Laredo", "Moche", "Salaverry", "Víctor Larco Herrera"],
    "Ascope":     ["Ascope", "Casa Grande", "Chicama", "Chocope", "Magdalena de Cao", "Paiján", "Rázuri", "Santiago de Cao"],
    "Pacasmayo":  ["San Pedro de Lloc", "Guadalupe", "Jequetepeque", "Pacasmayo", "San José"]
  },
  "Lambayeque": {
    "Chiclayo":   ["Chiclayo", "Cayaltí", "Chongoyape", "Eten", "José Leonardo Ortiz", "La Victoria", "Lagunas", "Monsefú", "Pimentel", "Pomalca", "Reque", "Santa Rosa", "Saña", "Tumán"],
    "Lambayeque": ["Lambayeque", "Chóchope", "Íllimo", "Jayanca", "Mochumí", "Mórrope", "Motupe", "Olmos", "Pacora", "Salas", "San José", "Túcume"],
    "Ferreñafe":  ["Ferreñafe", "Cañaris", "Incahuasi", "Manuel Antonio Mesones Muro", "Pítipo", "Pueblo Nuevo"]
  },
  "Lima": {
    "Lima":          ["Lima", "Ancón", "Ate", "Barranco", "Breña", "Carabayllo", "Chaclacayo", "Chorrillos", "Cieneguilla", "Comas", "El Agustino", "Independencia", "Jesús María", "La Molina", "La Victoria", "Lince", "Los Olivos", "Lurigancho", "Lurín", "Magdalena del Mar", "Miraflores", "Pachacámac", "Pucusana", "Pueblo Libre", "Puente Piedra", "Punta Hermosa", "Punta Negra", "Rímac", "San Bartolo", "San Borja", "San Isidro", "San Juan de Lurigancho", "San Juan de Miraflores", "San Luis", "San Martín de Porres", "San Miguel", "Santa Anita", "Santa María del Mar", "Santa Rosa", "Santiago de Surco", "Surquillo", "Villa El Salvador", "Villa María del Triunfo"],
    "Huaral":        ["Huaral", "Atavillos Alto", "Atavillos Bajo", "Aucallama", "Chancay", "Lampián", "Pacaraos"],
    "Cañete":        ["San Vicente de Cañete", "Asia", "Calango", "Cerro Azul", "Chilca", "Coayllo", "Imperial", "Lunahuaná", "Mala", "Nuevo Imperial", "Pacarán", "Quilmaná", "San Antonio", "San Luis", "Santa Cruz de Flores"],
    "Huarochirí":    ["Matucana", "Antioquía", "Callahuanca", "Carampoma", "Chicla", "Cuenca", "Huachupampa", "Huanza", "Huarochirí", "Ricardo Palma", "San Antonio", "San Mateo", "Santa Cruz de Cocachacra", "Santa Eulalia"],
    "Barranca":      ["Barranca", "Paramonga", "Pativilca", "Supe", "Supe Puerto"]
  },
  "Loreto": {
    "Maynas":           ["Iquitos", "Alto Nanay", "Belén", "Fernando Lores", "Indiana", "Las Amazonas", "Mazán", "Napo", "Punchana", "Putumayo", "San Juan Bautista", "Torres Causana"],
    "Alto Amazonas":    ["Yurimaguas", "Balsapuerto", "Jeberos", "Lagunas", "Santa Cruz", "Teniente César López Rojas"]
  },
  "Madre de Dios": {
    "Tambopata": ["Puerto Maldonado", "Inambari", "Las Piedras", "Laberinto", "Tambopata"]
  },
  "Moquegua": {
    "Mariscal Nieto": ["Moquegua", "Carumas", "Cuchumbaya", "Samegua", "San Cristóbal", "Torata"],
    "Ilo":            ["Ilo", "El Algarrobal", "Pacocha"]
  },
  "Pasco": {
    "Pasco":       ["Cerro de Pasco", "Chaupimarca", "Huachón", "Huariaca", "Huayllay", "Ninacaca", "Pallanchacra", "Paucartambo", "San Francisco de Asís de Yarusyacán", "Simón Bolívar", "Ticlacayán", "Tinyahuarco", "Vicco", "Yanacancha"]
  },
  "Piura": {
    "Piura":        ["Piura", "Castilla", "Catacaos", "Cura Mori", "El Tallán", "La Arena", "La Unión", "Las Lomas", "Tambo Grande", "Veintiséis de Octubre"],
    "Sullana":      ["Sullana", "Bellavista", "Ignacio Escudero", "Lancones", "Marcavelica", "Miguel Checa", "Querecotillo", "Salitral"],
    "Talara":       ["Pariñas", "El Alto", "La Brea", "Lobitos", "Los Órganos", "Máncora"],
    "Paita":        ["Paita", "Amotape", "Arenal", "Colán", "La Huaca", "Tamarindo", "Vichayal"]
  },
  "Puno": {
    "Puno":      ["Puno", "Acora", "Amantaní", "Atuncolla", "Capachica", "Chucuito", "Coata", "Huata", "Mañazo", "Paucarcolla", "Pichacani", "Plateria", "San Antonio", "Tiquillaca", "Vilque"],
    "San Román": ["Juliaca", "Cabana", "Cabanillas", "Caracoto", "San Miguel"]
  },
  "San Martín": {
    "Moyobamba": ["Moyobamba", "Calzada", "Habana", "Jepelacio", "Soritor", "Yantaló"],
    "San Martín":["Tarapoto", "Alberto Leveau", "Cacatachi", "Chazuta", "Chipurana", "El Porvenir", "Huimbayoc", "Juan Guerra", "La Banda de Shilcayo", "Morales", "Papaplaya", "San Antonio", "Sauce", "Shapaja"]
  },
  "Tacna": {
    "Tacna": ["Tacna", "Alto de la Alianza", "Calana", "Ciudad Nueva", "Inclán", "Pachía", "Palca", "Pocollay", "Sama", "Coronel Gregorio Albarracín Lanchipa", "La Yarada-Los Palos"]
  },
  "Tumbes": {
    "Tumbes":          ["Tumbes", "Corrales", "La Cruz", "Pampas de Hospital", "San Jacinto", "San Juan de la Virgen"],
    "Zarumilla":       ["Zarumilla", "Aguas Verdes", "Matapalo", "Papayal"],
    "Contralmirante Villar": ["Zorritos", "Canoas de Punta Sal", "Casitas"]
  },
  "Ucayali": {
    "Coronel Portillo": ["Pucallpa", "Callería", "Campoverde", "Iparía", "Manantay", "Masisea", "Yarinacocha", "Nueva Requena"]
  }
};

// Lista plana de departamentos
const DEPARTAMENTOS = Object.keys(PERU);

const CAT_LABELS = {
  venta:   "Venta",
  compra:  "Compra",
  trabajo: "Empleo",
  busca:   "Busco"
};

/* Utilidades */
function fillDepartamentos(selectEl, includeNacional = false, placeholder = "Seleccionar departamento") {
  selectEl.innerHTML = '';
  const opt0 = document.createElement('option');
  opt0.value = ''; opt0.textContent = placeholder; opt0.disabled = true; opt0.selected = true;
  selectEl.appendChild(opt0);
  if (includeNacional) {
    const optN = document.createElement('option');
    optN.value = 'Nacional'; optN.textContent = 'Nacional';
    selectEl.appendChild(optN);
  }
  DEPARTAMENTOS.forEach(d => {
    const o = document.createElement('option');
    o.value = d; o.textContent = d;
    selectEl.appendChild(o);
  });
}

function fillProvincias(selectEl, departamento, placeholder = "Seleccionar provincia") {
  selectEl.innerHTML = '';
  const opt0 = document.createElement('option');
  opt0.value = ''; opt0.textContent = placeholder; opt0.disabled = true; opt0.selected = true;
  selectEl.appendChild(opt0);
  if (!departamento || !PERU[departamento]) { selectEl.disabled = true; return; }
  selectEl.disabled = false;
  Object.keys(PERU[departamento]).forEach(p => {
    const o = document.createElement('option');
    o.value = p; o.textContent = p;
    selectEl.appendChild(o);
  });
}

function fillDistritos(selectEl, departamento, provincia, placeholder = "Seleccionar distrito") {
  selectEl.innerHTML = '';
  const opt0 = document.createElement('option');
  opt0.value = ''; opt0.textContent = placeholder; opt0.disabled = true; opt0.selected = true;
  selectEl.appendChild(opt0);
  if (!departamento || !provincia || !PERU[departamento] || !PERU[departamento][provincia]) {
    selectEl.disabled = true; return;
  }
  selectEl.disabled = false;
  PERU[departamento][provincia].forEach(d => {
    const o = document.createElement('option');
    o.value = d; o.textContent = d;
    selectEl.appendChild(o);
  });
}

/** Conecta tres selects de departamento -> provincia -> distrito */
function wireCascadingSelects(depSel, provSel, distSel, { includeNacional = false } = {}) {
  fillDepartamentos(depSel, includeNacional);
  provSel.disabled = true;
  distSel.disabled = true;
  depSel.addEventListener('change', () => {
    if (depSel.value === 'Nacional') {
      provSel.innerHTML = '<option value="">No aplica</option>';
      distSel.innerHTML = '<option value="">No aplica</option>';
      provSel.disabled = true;
      distSel.disabled = true;
      return;
    }
    fillProvincias(provSel, depSel.value);
    distSel.innerHTML = '<option value="" disabled selected>Seleccionar distrito</option>';
    distSel.disabled = true;
  });
  provSel.addEventListener('change', () => {
    fillDistritos(distSel, depSel.value, provSel.value);
  });
}

/* Formato de fechas relativas */
function relativeDate(isoDate) {
  // Fecha de hoy a medianoche (local)
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Fecha del anuncio a medianoche local (parseo seguro, sin desfase de zona horaria)
  const p = String(isoDate).slice(0, 10).split('-');
  const d = new Date(+p[0], (+p[1] || 1) - 1, +p[2] || 1);

  const diffDays = Math.floor((today - d) / (1000 * 60 * 60 * 24));

  if (diffDays <= 0)  return 'Hoy';
  if (diffDays === 1) return 'Ayer';
  if (diffDays < 7)   return `Hace ${diffDays} días`;
  if (diffDays < 30)  return `Hace ${Math.floor(diffDays / 7)} semana${Math.floor(diffDays / 7) > 1 ? 's' : ''}`;
  return d.toLocaleDateString('es-PE', { day: 'numeric', month: 'short' });
}

function formatLocation(ad) {
  if (ad.dep === 'Nacional') return 'Nacional';
  const parts = [ad.dist, ad.prov, ad.dep].filter(Boolean);
  return parts.join(', ');
}
