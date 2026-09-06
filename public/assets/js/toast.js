/* ===== Shopora admin toast =====
   shoporaToast.success('Saved.'), .error(...), .info(...), .warning(...)

   For results only. A question still needs a dialog the admin has to answer,
   so the "are you sure?" confirmations stay on SweetAlert - a toast that
   disappears on its own is the wrong place to ask whether to delete something. */
(function (window, document) {
    'use strict';

    var ICONS = {
        success: 'bx bx-check',
        error: 'bx bx-x',
        warning: 'bx bx-error',
        info: 'bx bx-info-circle',
    };

    var DEFAULT_TITLE = {
        success: 'Done',
        error: 'Something went wrong',
        warning: 'Careful',
        info: 'Note',
    };

    var stack = null;

    function host() {
        if (stack && document.body.contains(stack)) return stack;

        stack = document.createElement('div');
        stack.className = 'shopora-toasts';
        stack.setAttribute('role', 'status');
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);

        return stack;
    }

    function dismiss(toast) {
        if (!toast || toast.dataset.leaving) return;
        toast.dataset.leaving = '1';
        toast.classList.remove('is-in');
        toast.classList.add('is-out');
        window.setTimeout(function () { toast.remove(); }, 220);
    }

    function show(options) {
        var opts = options || {};
        var type = ICONS[opts.type] ? opts.type : 'info';
        var timeout = typeof opts.timeout === 'number' ? opts.timeout : 4000;

        // A message on its own reads as the headline; a title alongside it
        // makes the message the detail underneath.
        var title = opts.title || (opts.message ? DEFAULT_TITLE[type] : '');
        var text = opts.title ? opts.message : (opts.message || DEFAULT_TITLE[type]);

        var toast = document.createElement('div');
        toast.className = 'shopora-toast is-' + type;
        toast.innerHTML =
            '<span class="shopora-toast-icon"><i class="' + ICONS[type] + '"></i></span>' +
            '<div class="shopora-toast-body">' +
            (title ? '<p class="shopora-toast-title"></p>' : '') +
            (text ? '<p class="shopora-toast-text"></p>' : '') +
            '</div>' +
            '<button type="button" class="shopora-toast-close" aria-label="Dismiss">&times;</button>' +
            (timeout > 0 ? '<span class="shopora-toast-timer"></span>' : '');

        // Set by textContent, never innerHTML: these messages carry server text
        // and, on a validation error, whatever the admin just typed.
        if (title) toast.querySelector('.shopora-toast-title').textContent = title;
        if (text) toast.querySelector('.shopora-toast-text').textContent = text;

        var bar = toast.querySelector('.shopora-toast-timer');
        if (bar) bar.style.animation = 'shopora-toast-timer ' + timeout + 'ms linear forwards';

        toast.addEventListener('click', function () { dismiss(toast); });

        host().appendChild(toast);
        // next frame, so the entry transition has a state to move from
        window.requestAnimationFrame(function () { toast.classList.add('is-in'); });

        if (timeout > 0) {
            var timer = window.setTimeout(function () { dismiss(toast); }, timeout);
            // Hovering holds it open; the bar pauses with it.
            toast.addEventListener('mouseenter', function () { window.clearTimeout(timer); });
            toast.addEventListener('mouseleave', function () {
                timer = window.setTimeout(function () { dismiss(toast); }, 1200);
            });
        }

        return toast;
    }

    window.shoporaToast = {
        show: show,
        success: function (message, title) { return show({ type: 'success', message: message, title: title }); },
        error: function (message, title) { return show({ type: 'error', message: message, title: title }); },
        warning: function (message, title) { return show({ type: 'warning', message: message, title: title }); },
        info: function (message, title) { return show({ type: 'info', message: message, title: title }); },
    };
})(window, document);
