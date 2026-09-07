/* ===== Shopora admin bell =====
   Reads /admin/notifications for whoever is signed in.

   Polled, not pushed: a mart takes a few orders an hour, so a socket held open
   all day would be a process to keep alive for almost no news. The poll asks
   only for the count - a couple of bytes - and stops while the tab is hidden,
   so a panel left open overnight is not a request a minute until morning.

   Every string here is either a customer's own name or something the shop
   typed, so rows are built as nodes and filled with textContent. Nothing from
   the server is ever handed to innerHTML. */
(function (window, document) {
    'use strict';

    var POLL_MS = 60000;
    var PAGE_SIZE = 10;

    /** boxicon and tone class per event. */
    var LOOK = {
        order_placed: ['bx-cart-alt', 'is-placed'],
        payment_received: ['bx-wallet', 'is-paid'],
        payment_failed: ['bx-x-circle', 'is-failed'],
        customer_cancelled: ['bx-user-x', 'is-cancelled'],
        stock_out: ['bx-package', 'is-failed'],
        stock_low: ['bx-error', 'is-cancelled'],
        review_posted: ['bx-star', 'is-placed'],
        review_poor: ['bx-message-alt-x', 'is-failed'],
    };

    var cfg = window.SHOPORA_BELL;
    if (!cfg) return;

    var root = document.getElementById('shoporaBell');
    var button = document.getElementById('shoporaBellBtn');
    var count = document.getElementById('shoporaBellCount');
    var panel = document.getElementById('shoporaBellPanel');
    var list = document.getElementById('shoporaBellList');
    var more = document.getElementById('shoporaBellMore');
    var readAll = document.getElementById('shoporaBellReadAll');
    if (!root || !button || !panel || !list) return;

    var page = 1;
    var hasMore = false;
    var loading = false;
    var unread = 0;
    var loadedOnce = false;

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function ask(url, options) {
        var opts = options || {};
        opts.headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token(),
            Accept: 'application/json',
        }, opts.headers || {});
        opts.credentials = 'same-origin';

        return window.fetch(url, opts).then(function (res) {
            if (!res.ok) throw new Error(String(res.status));
            return res.json();
        });
    }

    /** "3 hours ago", and past a week the date - by then the day is more use. */
    function when(iso) {
        if (!iso) return '';
        var then = new Date(iso);
        if (isNaN(then.getTime())) return '';

        var secs = Math.round((Date.now() - then.getTime()) / 1000);
        if (secs < 60) return 'just now';

        var mins = Math.round(secs / 60);
        if (mins < 60) return mins + ' min ago';

        var hours = Math.round(mins / 60);
        if (hours < 24) return hours + (hours === 1 ? ' hour ago' : ' hours ago');

        var days = Math.round(hours / 24);
        if (days < 7) return days + (days === 1 ? ' day ago' : ' days ago');

        return then.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function setUnread(value) {
        unread = Math.max(0, value || 0);
        count.textContent = unread > 9 ? '9+' : String(unread);
        count.hidden = unread === 0;
        button.setAttribute('aria-label', unread
            ? 'Notifications, ' + unread + ' unread'
            : 'Notifications');
        if (readAll) readAll.disabled = unread === 0;
    }

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined && text !== null) node.textContent = text;
        return node;
    }

    function row(item) {
        var look = LOOK[item.event] || ['bx-bell', ''];

        var li = el('li', 'shopora-bell-item ' + look[1]);
        li.dataset.id = item.id;
        if (!item.is_read) li.classList.add('is-unread');

        var icon = el('span', 'shopora-bell-icon');
        icon.appendChild(el('i', 'bx ' + look[0]));
        li.appendChild(icon);

        var open = el('button', 'shopora-bell-open');
        open.type = 'button';
        open.appendChild(el('span', 'shopora-bell-title', item.title || 'Update'));
        if (item.body) open.appendChild(el('span', 'shopora-bell-text', item.body));
        // "just now · ORD-2026-0097", "just now · 5 waiting", "just now · ★★☆☆☆
        // Wireless Mouse" - whatever the message's own trailing line is.
        var meta = when(item.created_at);
        if (item.meta) meta = meta ? meta + ' · ' + item.meta : item.meta;
        open.appendChild(el('span', 'shopora-bell-when', meta));
        open.addEventListener('click', function () { openItem(item); });
        li.appendChild(open);

        if (!item.is_read) li.appendChild(el('span', 'shopora-bell-dot'));

        var remove = el('button', 'shopora-bell-remove');
        remove.type = 'button';
        remove.title = 'Remove';
        remove.setAttribute('aria-label', 'Remove this notification');
        remove.appendChild(el('i', 'bx bx-x'));
        remove.addEventListener('click', function () { removeItem(item, li); });
        li.appendChild(remove);

        return li;
    }

    function render(items, append) {
        if (!append) list.textContent = '';

        if (!items.length && !append) {
            list.appendChild(el('li', 'shopora-bell-empty', 'Nothing new. Orders, stock and reviews show up here.'));
            return;
        }

        items.forEach(function (item) { list.appendChild(row(item)); });
    }

    function load(nextPage) {
        if (loading) return Promise.resolve();
        loading = true;
        if (more) more.disabled = true;

        var url = cfg.index + '?page=' + nextPage + '&per_page=' + PAGE_SIZE;

        return ask(url).then(function (data) {
            // Appending rather than replacing keeps the rows already read on
            // screen; a notification deleted between pages would otherwise
            // shift everything up and repeat one.
            render(data.notifications || [], nextPage > 1);
            page = data.page || nextPage;
            hasMore = !!data.has_more;
            setUnread(data.unread);
            loadedOnce = true;
        }).catch(function () {
            if (!loadedOnce) {
                list.textContent = '';
                list.appendChild(el('li', 'shopora-bell-empty', 'Could not load notifications.'));
            }
        }).then(function () {
            loading = false;
            if (more) {
                more.hidden = !hasMore;
                more.disabled = false;
            }
        });
    }

    /**
     * Marking read and going there are one action: an admin opens a row to
     * deal with the order, so making them mark it off afterwards is a second
     * job for something they have already done.
     */
    function openItem(item) {
        var go = function () {
            if (item.url) window.location.href = item.url;
        };

        if (item.is_read || !item.url) {
            go();
            return;
        }

        ask(cfg.read.replace(':id', item.id), { method: 'POST' })
            .then(function (data) { setUnread(data.unread); })
            .catch(function () { /* still take them to the order */ })
            .then(go);
    }

    function removeItem(item, li) {
        li.remove();
        if (!item.is_read) setUnread(unread - 1);

        ask(cfg.destroy.replace(':id', item.id), { method: 'DELETE' })
            .then(function (data) { setUnread(data.unread); })
            .catch(function () { load(1); });

        if (!list.children.length) {
            list.appendChild(el('li', 'shopora-bell-empty', 'Nothing new. Orders, stock and reviews show up here.'));
        }
    }

    function openPanel() {
        root.classList.add('is-open');
        panel.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        load(1);
    }

    function closePanel() {
        root.classList.remove('is-open');
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');
    }

    button.addEventListener('click', function (event) {
        event.stopPropagation();
        if (panel.hidden) openPanel(); else closePanel();
    });

    if (more) {
        more.addEventListener('click', function () {
            if (hasMore) load(page + 1);
        });
    }

    if (readAll) {
        readAll.addEventListener('click', function () {
            ask(cfg.readAll, { method: 'POST' }).then(function () {
                setUnread(0);
                load(1);
            });
        });
    }

    document.addEventListener('pointerdown', function (event) {
        if (!panel.hidden && !root.contains(event.target)) closePanel();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.hidden) closePanel();
    });

    // ---- the poll ----
    function tick() {
        if (document.visibilityState !== 'visible') return;

        ask(cfg.unreadCount).then(function (data) {
            var fresh = data.unread || 0;
            // Something landed while the panel was open - pull the rows in so
            // it is right without having to be closed and opened again.
            if (fresh > unread && !panel.hidden) load(1);
            setUnread(fresh);
        }).catch(function () { /* the next tick will do */ });
    }

    setUnread(Number(cfg.unread) || 0);
    window.setInterval(tick, POLL_MS);
    // A tab left in the background misses ticks; catch up when it comes back.
    document.addEventListener('visibilitychange', tick);
})(window, document);
