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

// Anuncios de muestra
const SAMPLE_ADS = [
  { id: 1,  cat: "venta",   text: "Vendo Toyota Yaris 2019, color plata, 48,000 km, único dueño, mantenimientos al día en concesionario. Papeles en regla.", dep: "Lima",        prov: "Lima",        dist: "San Borja",          phone: "987 654 321", date: "2026-05-25" },
  { id: 2,  cat: "trabajo", text: "Empresa textil contrata operarios de costura con experiencia mínima 1 año. Sueldo + beneficios de ley. Turno mañana.",    dep: "Lima",        prov: "Lima",        dist: "San Juan de Lurigancho", phone: "956 213 487", date: "2026-05-25" },
  { id: 3,  cat: "compra",  text: "Compro laptop usada Core i5 o superior, 8GB RAM mínimo. Pago al contado. Que esté en buen estado, sin pantalla rota.",   dep: "Arequipa",    prov: "Arequipa",    dist: "Cayma",              phone: "923 117 458", date: "2026-05-24" },
  { id: 4,  cat: "busca",   text: "Se busca técnico electricista para instalación domiciliaria en proyecto residencial. Trabajo por 3 semanas, buena paga.", dep: "La Libertad", prov: "Trujillo",    dist: "Trujillo",           phone: "944 802 156", date: "2026-05-24" },
  { id: 5,  cat: "venta",   text: "Departamento en venta, 3 dormitorios, 2 baños, 95 m², estacionamiento, edificio con ascensor y vigilancia 24 horas.",     dep: "Lima",        prov: "Lima",        dist: "Miraflores",         phone: "998 442 165", date: "2026-05-23" },
  { id: 6,  cat: "trabajo", text: "Necesito repartidor con moto propia para distribución en zona norte. Pago semanal, gasolina incluida en el contrato.",   dep: "Lima",        prov: "Lima",        dist: "Los Olivos",         phone: "987 321 654", date: "2026-05-23" },
  { id: 7,  cat: "venta",   text: "Vendo refrigeradora LG no frost 420 litros, 2 años de uso, en excelente estado. Incluye garantía extendida vigente.",      dep: "Cusco",       prov: "Cusco",       dist: "Wanchaq",            phone: "974 856 213", date: "2026-05-22" },
  { id: 8,  cat: "busca",   text: "Familia busca empleada del hogar cama afuera, lunes a sábado, con experiencia en cocina criolla. Excelente trato.",        dep: "Lima",        prov: "Lima",        dist: "Surquillo",          phone: "956 478 213", date: "2026-05-22" },
  { id: 9,  cat: "compra",  text: "Compro celulares en buen estado para mi negocio. Pago al contado según modelo y estado. Reviso primero, luego compro.",   dep: "Piura",       prov: "Piura",       dist: "Piura",              phone: "968 214 753", date: "2026-05-21" },
  { id: 10, cat: "trabajo", text: "Restaurante turístico contrata mozos con experiencia y conocimiento básico de inglés. Buen ambiente laboral y propinas.", dep: "Cusco",       prov: "Cusco",       dist: "Cusco",              phone: "984 521 369", date: "2026-05-21" },
  { id: 11, cat: "venta",   text: "Lote de 200 m² en venta, urbanización consolidada, con todos los servicios. Apto para construcción inmediata, papeles ok.", dep: "Arequipa",   prov: "Arequipa",    dist: "Cerro Colorado",     phone: "959 482 167", date: "2026-05-20" },
  { id: 12, cat: "busca",   text: "Servicio nacional de mensajería busca conductores con licencia A-IIIc. Cobertura en todo el país, hospedaje cubierto.",   dep: "Nacional",    prov: null,          dist: null,                 phone: "01 555 8800",  date: "2026-05-20" },
  { id: 13, cat: "venta",   text: "Vendo bicicleta montañera aro 29, marca Trek, suspensión delantera, frenos de disco hidráulicos. Poco uso, como nueva.",  dep: "Lima",        prov: "Lima",        dist: "La Molina",          phone: "947 825 614", date: "2026-05-19" },
  { id: 14, cat: "trabajo", text: "Clínica veterinaria solicita médico veterinario titulado, jornada completa. CV al correo o llamar para entrevista.",      dep: "Lambayeque",  prov: "Chiclayo",    dist: "Chiclayo",           phone: "979 654 132", date: "2026-05-19" },
  { id: 15, cat: "compra",  text: "Compro chatarra de fierro y cobre al mejor precio del mercado. Recojo a domicilio en Lima Metropolitana sin compromiso.", dep: "Lima",        prov: "Lima",        dist: "Ate",                phone: "925 487 619", date: "2026-05-18" },
  { id: 16, cat: "venta",   text: "Set de muebles de sala 3-2-1, estilo moderno, tapiz gris claro. 2 años de uso, en perfecto estado, no fumadores.",         dep: "Junín",       prov: "Huancayo",    dist: "El Tambo",           phone: "936 245 781", date: "2026-05-18" },
  { id: 17, cat: "busca",   text: "Se busca diseñador gráfico freelance para proyectos puntuales de redes sociales y catálogos digitales. Pago por proyecto.", dep: "Nacional",   prov: null,          dist: null,                 phone: "987 124 365", date: "2026-05-17" },
  { id: 18, cat: "trabajo", text: "Hotel boutique en Paracas contrata recepcionistas con inglés intermedio. Trabajo en turnos rotativos, alimentación.",     dep: "Ica",         prov: "Pisco",       dist: "Paracas",            phone: "956 781 432", date: "2026-05-17" },
  { id: 19, cat: "venta",   text: "Vendo terreno agrícola 2 hectáreas con riego tecnificado y caseta. Apto para cultivo de palto o limón. Precio negociable.", dep: "Piura",      prov: "Sullana",     dist: "Sullana",            phone: "968 752 314", date: "2026-05-16" },
  { id: 20, cat: "compra",  text: "Compro autos chocados, fundidos o con papeles atrasados. Trámite y pago inmediato. Voy a domicilio sin compromiso.",        dep: "Lima",       prov: "Lima",        dist: "San Martín de Porres", phone: "947 836 152", date: "2026-05-16" },
  { id: 21, cat: "trabajo", text: "Constructora requiere maestro de obra con experiencia comprobada para proyecto en zona sur de la ciudad. Inicio inmediato.", dep: "Lima",      prov: "Lima",        dist: "Villa El Salvador",  phone: "974 521 836", date: "2026-05-15" },
  { id: 22, cat: "busca",   text: "Busco departamento en alquiler, 2 dormitorios, amoblado, zona segura, presupuesto hasta 1500 soles, contrato mínimo 1 año.", dep: "Lima",       prov: "Lima",        dist: "Jesús María",        phone: "938 762 145", date: "2026-05-15" },
  { id: 23, cat: "venta",   text: "Vendo PlayStation 5 con dos mandos, 4 juegos físicos originales y caja completa. Equipo en perfecto estado, 1 año de uso.", dep: "Callao",     prov: "Callao",      dist: "Bellavista",         phone: "925 367 814", date: "2026-05-14" },
  { id: 24, cat: "trabajo", text: "Empresa contrata personal de seguridad con SUCAMEC vigente. Turnos día/noche. Pago puntual quincenal, uniforme incluido.",  dep: "Lima",       prov: "Lima",        dist: "Callao",             phone: "987 412 369", date: "2026-05-14" }
];

const CAT_LABELS = {
  venta:   "Venta",
  compra:  "Compra",
  trabajo: "Trabajo",
  busca:   "Se busca"
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
  const today = new Date('2026-05-27');
  const d = new Date(isoDate);
  const diffDays = Math.floor((today - d) / (1000 * 60 * 60 * 24));
  if (diffDays === 0) return 'Hoy';
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
