import '@fortawesome/fontawesome-free/css/all.min.css';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

window.tippy = tippy;


import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

window.toastr = toastr;

toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-center",
    "timeOut": "3000",
    "toastClass": "custom-toastr toast",
};


import 'virtual-select-plugin/dist/virtual-select.min.css'

// VirtualSelect está cargado externamente, así que nos aseguramos de que esté definido
document.addEventListener('DOMContentLoaded', () => {
    window.inicializarVirtualSelect = function (elementId, optionsData, config = {}) {
        if (typeof VirtualSelect === 'undefined') {
            console.warn('VirtualSelect no está disponible todavía.');
            return;
        }

        const defaultConfig = {
            multiple: true,
            search: true,
            placeholder: '--Seleccionar--',
            searchPlaceholderText: 'Buscar...',
            allOptionsSelectedText: 'Todos',
            optionsSelectedText: 'opciones seleccionadas',
            noResultsElement: 'No hay opciones disponibles',
            noOptionsText: 'No hay opciones disponibles',
            showValueAsTags: false,
            options: optionsData,
        };

        const finalConfig = { ...defaultConfig, ...config };

        VirtualSelect.init({
            ele: `#${elementId}`,
            ...finalConfig
        });

        // Personalización de textos
        document.querySelectorAll('.vscomp-no-search-results').forEach(el => {
            el.innerText = 'No hay opciones disponibles';
        });

        document.querySelectorAll('.vscomp-toggle-all-label').forEach(el => {
            el.innerText = 'Seleccionar todo';
        });
    };
});



if (localStorage.getItem('theme') === 'dark') {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}
