function renderDiscordMarkdown(text) {
    let html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Code blocks (before inline code)
    html = html.replace(/```([\s\S]+?)```/g, (_, code) =>
        `<span style="display:block;background:#2b2d31;border-radius:4px;padding:4px 8px;font-family:monospace;font-size:11px;color:#c9d1d9;margin:2px 0;white-space:pre-wrap">${code}</span>`
    );
    // Inline code
    html = html.replace(/`([^`\n]+)`/g,
        '<code style="background:#2b2d31;border-radius:3px;padding:1px 4px;font-family:monospace;font-size:11px;color:#c9d1d9">$1</code>'
    );
    // Bold+italic
    html = html.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');
    // Bold
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    // Underline (before italic _)
    html = html.replace(/__(.+?)__/g, '<u>$1</u>');
    // Italic *
    html = html.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
    // Italic _
    html = html.replace(/_([^_\n]+)_/g, '<em>$1</em>');
    // Strikethrough
    html = html.replace(/~~(.+?)~~/g, '<s>$1</s>');
    // Spoiler
    html = html.replace(/\|\|(.+?)\|\|/g,
        '<span style="background:#202225;border-radius:3px;padding:0 3px;color:#202225;cursor:pointer" title="Spoiler (oculto)">$1</span>'
    );
    // Blockquote
    html = html.replace(/^&gt; (.+)$/gm,
        '<span style="display:block;border-left:3px solid #4f545c;padding-left:8px;color:#8e9297;margin:1px 0">$1</span>'
    );
    // Lists
    html = html.replace(/^[-•] (.+)$/gm,
        '<span style="display:block;padding-left:8px">• $1</span>'
    );
    // @everyone / @here
    html = html.replace(/@(everyone|here)/g,
        '<span style="background:#414675;color:#dee0fc;border-radius:3px;padding:0 3px;font-size:11px;font-weight:600">@$1</span>'
    );
    // Newlines
    html = html.replace(/\n/g, '<br>');

    return html;
}

window.insertFormatting = function(taId, syntax) {
    const ta = document.getElementById(taId);
    if (!ta) return;
    const s = ta.selectionStart, e = ta.selectionEnd;
    const sel    = ta.value.substring(s, e);
    const before = ta.value.substring(0, s);
    const after  = ta.value.substring(e);

    const wrap = (l, r, placeholder) => {
        const inner = sel || placeholder;
        ta.value = before + l + inner + r + after;
        const ns = sel ? s + l.length + inner.length + r.length : s + l.length + inner.length;
        ta.setSelectionRange(ns, ns);
    };

    switch (syntax) {
        case 'bold':      wrap('**', '**', 'texto'); break;
        case 'italic':    wrap('*', '*', 'texto'); break;
        case 'underline': wrap('__', '__', 'texto'); break;
        case 'strike':    wrap('~~', '~~', 'texto'); break;
        case 'code':      wrap('`', '`', 'código'); break;
        case 'spoiler':   wrap('||', '||', 'spoiler'); break;
        case 'quote':     wrap('> ', '', 'cita'); break;
        case 'codeblock': {
            const inner = sel || 'código';
            ta.value = before + '```\n' + inner + '\n```' + after;
            const ns = s + 4 + inner.length + 5;
            ta.setSelectionRange(ns, ns);
            break;
        }
        case 'list': {
            if (sel) {
                const listed = sel.split('\n').map(l => `- ${l}`).join('\n');
                ta.value = before + listed + after;
                ta.setSelectionRange(s + listed.length, s + listed.length);
            } else {
                ta.value = before + '- elemento' + after;
                ta.setSelectionRange(s + 10, s + 10);
            }
            break;
        }
        case 'everyone':
        case 'here': {
            const mention = `@${syntax}`;
            ta.value = before + mention + after;
            ta.setSelectionRange(s + mention.length, s + mention.length);
            break;
        }
    }

    ta.focus();
    window.updatePreview();
};

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
    zero_build:    'Cero Construccion',
    battle_royale: 'Battle Royale',
    reload_build:  'Recarga (Construccion)',
    reload_zero:   'Recarga (Cero Build)',
};

const MODALIDADES = {
    solo:  'Solitario',
    duo:   'Duo',
    trio:  'Trio',
    squad: 'Escuadron',
};

const REGIONS = {
    'eu':      'Europa',
    'na-east': 'NA Este',
    'na-west': 'NA Oeste',
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
        const mode          = document.getElementById('f-mode')?.value          || 'zero_build';
        const modalidad     = document.getElementById('f-modalidad')?.value     || 'solo';
        const clasificatoria= document.getElementById('f-clasificatoria')?.value|| 'no';
        const region        = document.getElementById('f-region')?.value        || 'eu';
        const title  = document.getElementById('preview-title');
        const footer = document.getElementById('preview-footer');
        const pfMode = document.getElementById('pf-mode');
        const pfMod  = document.getElementById('pf-modalidad');
        const pfClas = document.getElementById('pf-clasificatoria');
        const pfReg  = document.getElementById('pf-region');
        if (title)  title.textContent  = 'Partida Privada de Fortnite';
        if (pfMode) pfMode.textContent = MODES[mode]          || mode;
        if (pfMod)  pfMod.textContent  = MODALIDADES[modalidad] || modalidad;
        if (pfClas) pfClas.textContent = clasificatoria === 'si' ? 'Si' : 'No';
        if (pfReg)  pfReg.textContent  = REGIONS[region]       || region;
        if (footer) footer.textContent = 'Partida Privada | Fortnite';
    } else {
        const titleVal = document.getElementById('g-title')?.value   || 'Titulo del anuncio';
        const msgVal   = document.getElementById('g-message')?.value || 'Tu mensaje aqui...';
        const title    = document.getElementById('preview-title');
        const bodyEl   = document.getElementById('preview-body');
        const footer   = document.getElementById('preview-footer');
        if (title)  title.textContent  = titleVal;
        if (bodyEl) bodyEl.innerHTML   = renderDiscordMarkdown(msgVal);
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
