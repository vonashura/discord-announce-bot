const COLORS = {
    azul:     { hex: '#5865F2', label: 'Azul' },
    verde:    { hex: '#57F287', label: 'Verde' },
    rojo:     { hex: '#ED4245', label: 'Rojo' },
    amarillo: { hex: '#FEE75C', label: 'Amarillo' },
    morado:   { hex: '#9B59B6', label: 'Morado' },
    naranja:  { hex: '#E67E22', label: 'Naranja' },
    cyan:     { hex: '#1ABC9C', label: 'Cyan' },
    negro:    { hex: '#2C2F33', label: 'Negro' },
};

const MODES = {
    construction:    'Construccion',
    no_build:        'Sin Construccion',
    ranked_build:    'Ranked Construccion',
    ranked_no_build: 'Ranked Sin Construccion',
};

const REGIONS = {
    'na-east': 'NA Este',
    'na-west': 'NA Oeste',
    'eu':      'Europa',
    'br':      'Brasil',
    'asia':    'Asia',
    'oce':     'Oceania',
};

let currentTab = 'general';

function renderColorPicker(containerId, inputId, defaultColor = 'azul') {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = Object.entries(COLORS).map(([key, { hex, label }]) => `
        <button type="button"
            onclick="window.selectColor('${containerId}', '${inputId}', '${key}', '${hex}')"
            id="color-btn-${containerId}-${key}"
            title="${label}"
            style="background-color:${hex};width:100%;height:40px;border-radius:8px;border:2px solid ${key === defaultColor ? '#fff' : 'transparent'};transform:${key === defaultColor ? 'scale(1.1)' : 'scale(1)'};cursor:pointer;transition:all .15s;">
        </button>
    `).join('');
    document.getElementById(inputId).value = defaultColor;
}

window.selectColor = function(containerId, inputId, colorKey, hex) {
    Object.keys(COLORS).forEach(k => {
        const btn = document.getElementById(`color-btn-${containerId}-${k}`);
        if (!btn) return;
        btn.style.borderColor = 'transparent';
        btn.style.transform   = 'scale(1)';
    });
    const sel = document.getElementById(`color-btn-${containerId}-${colorKey}`);
    if (sel) { sel.style.borderColor = '#fff'; sel.style.transform = 'scale(1.1)'; }
    document.getElementById(inputId).value = colorKey;
    const preview = document.getElementById('embed-preview');
    if (preview) preview.style.borderLeftColor = hex;
    window.updatePreview();
};

window.switchTab = function(tab) {
    currentTab = tab;
    ['general', 'fortnite'].forEach(t => {
        const form = document.getElementById(`form-${t}`);
        const btn  = document.getElementById(`tab-${t}`);
        if (!form || !btn) return;
        if (t === tab) {
            form.style.display = 'block';
            btn.style.background = '#4f46e5';
            btn.style.color = '#fff';
        } else {
            form.style.display = 'none';
            btn.style.background = 'transparent';
            btn.style.color = '#9ca3af';
        }
    });
    window.updatePreview();
};

window.toggleWebhookField = function(form, value) {
    const el = document.getElementById(`${form}-webhook-field`);
    if (el) el.style.display = value === 'webhook' ? 'block' : 'none';
};

window.updatePreview = function() {
    const isFortnite = currentTab === 'fortnite';
    const fields = document.getElementById('preview-fields');
    const body   = document.getElementById('preview-body');
    if (fields) fields.style.display = isFortnite ? 'block' : 'none';
    if (body)   body.style.display   = isFortnite ? 'none'  : 'block';

    if (isFortnite) {
        const mode   = document.getElementById('f-mode')?.value   || 'construction';
        const region = document.getElementById('f-region')?.value || 'eu';
        const title  = document.getElementById('preview-title');
        const footer = document.getElementById('preview-footer');
        const pfMode = document.getElementById('pf-mode');
        const pfReg  = document.getElementById('pf-region');
        if (title)  title.textContent  = 'Partida Privada de Fortnite';
        if (pfMode) pfMode.textContent = MODES[mode]   || mode;
        if (pfReg)  pfReg.textContent  = REGIONS[region] || region;
        if (footer) footer.textContent = 'Partida Privada | Fortnite';
    } else {
        const titleVal = document.getElementById('g-title')?.value   || 'Titulo del anuncio';
        const msgVal   = document.getElementById('g-message')?.value || 'Tu mensaje aqui...';
        const title    = document.getElementById('preview-title');
        const bodyEl   = document.getElementById('preview-body');
        const footer   = document.getElementById('preview-footer');
        if (title)  title.textContent  = titleVal;
        if (bodyEl) bodyEl.textContent = msgVal;
        if (footer) footer.textContent = 'Anuncio General';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    renderColorPicker('color-general',  'g-color', 'azul');
    renderColorPicker('color-fortnite', 'f-color', 'azul');

    // Init hidden state via JS instead of relying on Tailwind 'hidden' class
    const fortniteForm = document.getElementById('form-fortnite');
    if (fortniteForm) fortniteForm.style.display = 'none';
    const generalForm = document.getElementById('form-general');
    if (generalForm) generalForm.style.display = 'block';

    ['general-webhook-field', 'fortnite-webhook-field'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });

    window.updatePreview();
});
