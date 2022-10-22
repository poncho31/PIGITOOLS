import formatDistanceStrict from 'date-fns/formatDistanceStrict'
window.formatDistanceStrict = formatDistanceStrict;

import { fr, enUS } from 'date-fns/locale'
window.dateLocal = {'fr': fr, 'en': enUS};

import * as bs from "bootstrap";

export default class Toast{
    show(){
        let hasMessage = "{{ Session::has('flashMessage') }}";
        if (hasMessage) {
            let message = 'Message';
            let type = "{{Session::get('alertMessage')}}";
            let title = type === 'danger' ? 'Erreur' : 'Information';

            Toast.showToast(message, title, type, 10000)
        }
    }

    static showToast (message, title = '', type = 'success',duration = 5000) {
        let icon = '';
        switch (type) {
            case "success": icon = 'bi-check-square-fill'; break;
            case "danger": icon = 'bi-x-square-fill'; break;
            case "warning": icon = 'bi-exclamation-square-fill'; break;
            case "info": icon = 'bi-info-square-fill'; break;
        }
        const toast = $(`<div class="toast" role="alert" style="width: 100%" aria-live="polite" aria-atomic="true" data-bs-delay="${duration}">
            <div class="toast-header bg-${type} bg-opacity-60" style="width: 100%">
                <div><span class="bi ${icon} text-${type} fs-5 me-3"/></div>
                <strong class="me-auto toast-title pt-1 d-none d-sm-block text-white">${title}</strong>&nbsp;&nbsp;
                <small class="text-light toast-time pt-1 d-none me-1 me-md-2 me-lg-3 d-sm-block">just now</small>
                <button type="button" class="btn-close ms-auto ms-sm-0" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <p>${message}</p>
            </div>
        </div>`);
        const bsToast = bs.Toast.getOrCreateInstance(toast);
        let timer = toast.find('.toast-time');
        let startTime = new Date();
        let interval = setInterval( function () {
            timer.empty();
            timer.append(formatDistanceStrict(startTime, new Date(), {locale: dateLocal.fr}));
        }, 1000);
        toast.on('hidden.bs.toast', function () {
            $(this).remove();
            clearInterval(interval);
        });
        $('.toast-container').append(toast);

        bsToast.show();
    }
}
