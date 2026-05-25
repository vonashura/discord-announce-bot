// ── Color palette ──────────────────────────────────────────────────
const COLORS = {
    azul:     { hex: '#5865F2', label: '🔵 Azul' },
    verde:    { hex: '#57F287', label: '🟢 Verde' },
    rojo:     { hex: '#ED4245', label: '🔴 Rojo' },
    amarillo: { hex: '#FEE75C', label: '🟡 Amarillo' },
    morado:   { hex: '#9B59B6', label: '🟣 Morado' },
    naranja:  { hex: '#E67E22', label: '🟠 Naranja' },
    cyan:     { hex: '#1ABC9C', label: '🩵 Cyan' },
    negro:    { hex: '#2C2F33', label: '⚫ Negro' },
};

const MODES = {
    construction:    '🏗️ Construcción',
    no_build:        '⚡ Sin Construcción',
    ranked_build:    '🏆 Ranked Construc.',
    ranked_no_build: '🏆 Ranked Sin Construc.',
};

const REGIONS = {
    'na-east': '🌎 NA Este',
    'na-west': '🌎 NA Oeste',
    'eu':      '🌍 Europa',
    'br':      '🌎 Brasil',
    'asia':    '🌏 Asia',
    'oce':     '🌏 Oceanía',
};

let currentTab = 'general';

// ── Render color pickers ────────────────────────────────────────────
function renderColorPicker(containerId, inputId, defaultColor = 'azul') {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = Object.entries(COLORS).map(([key, { hex, label }]) => `
        <button type="button"
            onclick="selectColor('${containerId}', '${inputId}', '${key}', '${hex}')"
            id="color-btn-${containerId}-${key}"
            title="${label}"
            class="h-10 rounded-lg border-2 transition-all ${key === defaultColor ? 'border-white scale-110' : 'border-transparent hover:border-gray-500'}"
            style="background-color: ${hex}">
        </button>
    `).join('');

    document.getElementById(inputId).value = defaultColor;
}

function selectColor(containerId, inputId, colorKey, hex) {
    // Update button states
    Object.keys(COLORS).forEach(k => {
        const btn = document.getElementById(`color-btn-${containerId}-${k}`);
        if (btn) btn.className = btn.className
            .replace('border-white scale-110', 'border-transparent hover:border-gray-500');
    });
    const selected = document.getElementById(`color-btn-${containerId}-${colorKey}`);
    if (selected) selected.className = selected.className
        .replace('border-transparent hover:border-gray-500', 'border-white scale-110');

    document.getElementById(inputId).value = colorKey;

    // Update preview border
    document.getElementById('embed-preview').style.borderLeftColor = hex;
    updatePreview();
}

// ── Tab switching ───────────────────────────────────────────────────
function switchTab(tab) {
    currentTab = tab;
    const tabs = ['general', 'fortnite'];
    tabs.forEach(t => {
        document.getElementById(`form-${t}`).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById(`tab-${t}`);
        if (t === tab) {
            btn.classList.remove('text-gray-400', 'hover:text-white');
            btn.classList.add('bg-indigo-600', 'text-white');
        } else {
            btn.classList.add('text-gray-400', 'hover:text-white');
            btn.classList.remove('bg-indigo-600', 'text-white');
        }
    });
    updatePreview();
}

// ── Webhook field toggle ────────────────────────────────────────────
function toggleWebhookField(form, value) {
    document.getElementById(`${form}-webhook-field`)
        ?.classList.toggle('hidden', value !== 'webhook');
}

// ── Live preview update ─────────────────────────────────────────────
function updatePreview() {
    const isFortnite = currentTab === 'fortnite';

    document.getElementById('preview-fields').classList.toggle('hidden', !isFortnite);
    document.getElementById('preview-body').classList.toggle('hidden', isFortnite);

    if (isFortnite) {
        const mode   = document.getElementById('f-mode')?.value   || 'construction';
        const region = document.getElementById('f-region')?.value || 'eu';
        document.getElementById('preview-title').textContent = '🎮 Partida Privada de Fortnite';
        document.getElementById('pf-mode').textContent   = MODES[mode]   || mode;
        document.getElementById('pf-region').textContent = REGIONS[region] || region;
        document.getElementById('preview-footer').textContent = 'Partida Privada • Fortnite';
    } else {
        const title   = document.getElementById('g-title')?.value   || 'Título del anuncio';
        const message = document.getElementById('g-message')?.value || 'Tu mensaje aquí...';
        document.getElementById('preview-title').textContent = title || 'Título del anuncio';
        document.getElementById('preview-body').textContent  = message || 'Tu mensaje aquí...';
        document.getElementById('preview-footer').textContent = '📢 Anuncio General';
    }
}

// ── Init ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderColorPicker('color-general',  'g-color', 'azul');
    renderColorPicker('color-fortnite', 'f-color', 'azul');
    updatePreview();
});
