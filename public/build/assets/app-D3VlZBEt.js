const c={azul:{hex:"#5865F2",label:"🔵 Azul"},verde:{hex:"#57F287",label:"🟢 Verde"},rojo:{hex:"#ED4245",label:"🔴 Rojo"},amarillo:{hex:"#FEE75C",label:"🟡 Amarillo"},morado:{hex:"#9B59B6",label:"🟣 Morado"},naranja:{hex:"#E67E22",label:"🟠 Naranja"},cyan:{hex:"#1ABC9C",label:"🩵 Cyan"},negro:{hex:"#2C2F33",label:"⚫ Negro"}};let i="general";function a(e,t,n="azul"){const l=document.getElementById(e);l&&(l.innerHTML=Object.entries(c).map(([o,{hex:r,label:d}])=>`
        <button type="button"
            onclick="selectColor('${e}', '${t}', '${o}', '${r}')"
            id="color-btn-${e}-${o}"
            title="${d}"
            class="h-10 rounded-lg border-2 transition-all ${o===n?"border-white scale-110":"border-transparent hover:border-gray-500"}"
            style="background-color: ${r}">
        </button>
    `).join(""),document.getElementById(t).value=n)}function u(){var t,n;const e=i==="fortnite";document.getElementById("preview-fields").classList.toggle("hidden",!e),document.getElementById("preview-body").classList.toggle("hidden",e);{const l=((t=document.getElementById("g-title"))==null?void 0:t.value)||"Título del anuncio",o=((n=document.getElementById("g-message"))==null?void 0:n.value)||"Tu mensaje aquí...";document.getElementById("preview-title").textContent=l,document.getElementById("preview-body").textContent=o,document.getElementById("preview-footer").textContent="📢 Anuncio General"}}document.addEventListener("DOMContentLoaded",()=>{a("color-general","g-color","azul"),a("color-fortnite","f-color","azul"),u()});
