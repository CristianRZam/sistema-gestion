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
