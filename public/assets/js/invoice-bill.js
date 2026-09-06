/* ===== Shopora bill =====
   Renders one bill from /admin/invoice/viewInvoice/{id} and prints it.

   The server works out the bill number, the totals and how the sale was paid,
   so this file only lays them out. That is deliberate: the screen, the print
   and anything added later cannot disagree about what the customer owes.

   Used by the Sales, Invoice and Dashboard screens through
   partials/invoice-modal.blade.php. */
(function (window, document) {
    'use strict';

    var current = null;

    function esc(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function money(value) {
        var n = Number(value || 0);
        return 'Rs. ' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /** "2026-09-06 10:25:22" -> "06 Sep 2026, 10:25 am" */
    function readableDate(value) {
        if (!value) return '';
        var parsed = new Date(String(value).replace(' ', 'T'));
        if (isNaN(parsed.getTime())) return String(value);

        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var hours = parsed.getHours();
        var suffix = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12 || 12;

        return ('0' + parsed.getDate()).slice(-2) + ' ' + months[parsed.getMonth()] + ' ' + parsed.getFullYear()
            + ', ' + hours + ':' + ('0' + parsed.getMinutes()).slice(-2) + ' ' + suffix;
    }

    function chipClass(payment) {
        if (payment.is_paid) return 'is-paid';
        if (payment.status === 'Payment failed') return 'is-failed';
        return 'is-due';
    }

    /** The shop's own details. A blank line is skipped rather than printed empty. */
    function head(shop) {
        var lines = [];
        if (shop.address) lines.push(esc(shop.address));

        var registry = [];
        if (shop.pan) registry.push('PAN: ' + esc(shop.pan));
        if (shop.phone) registry.push('Tel: ' + esc(shop.phone));
        if (registry.length) lines.push(registry.join(' &middot; '));

        return '<header class="bill-head">'
            + '<h2 class="bill-shop-name">' + esc(shop.name) + '</h2>'
            + lines.map(function (line) { return '<p class="bill-shop-line">' + line + '</p>'; }).join('')
            + '<span class="bill-kind">Abbreviated Tax Invoice</span>'
            + '</header>';
    }

    function meta(bill) {
        // A counter sale names whoever rang it up; an online order has no one at
        // a till, so it names the channel instead.
        var type = bill.served_by
            ? esc(bill.channel_label) + '<small>Served by ' + esc(bill.served_by) + '</small>'
            : esc(bill.channel_label) + '<small>' + esc(bill.status) + '</small>';

        return '<dl class="bill-meta">'
            + '<div><dt>Bill No</dt><dd>' + esc(bill.bill_no) + '</dd></div>'
            + '<div><dt>Date</dt><dd>' + esc(readableDate(bill.date))
            + (bill.nepali_date ? '<small>' + esc(bill.nepali_date) + ' B.S.</small>' : '') + '</dd></div>'
            + '<div><dt>Type</dt><dd>' + type + '</dd></div>'
            + '</dl>';
    }

    function parties(bill) {
        var customer = bill.customer || {};
        var blocks = ['<div class="bill-party"><h4>Billed to</h4>'
            + '<p class="is-name">' + esc(customer.name || 'Walk-in customer') + '</p>'
            + (customer.phone ? '<p class="is-sub">' + esc(customer.phone) + '</p>' : '')
            + '</div>'];

        var delivery = bill.delivery;
        if (delivery && (delivery.address || delivery.recipient)) {
            blocks.push('<div class="bill-party"><h4>Deliver to</h4>'
                + (delivery.recipient ? '<p class="is-name">' + esc(delivery.recipient) + '</p>' : '')
                + (delivery.address ? '<p class="is-sub">' + esc(delivery.address) + '</p>' : '')
                // the landmark is the customer's own wording, often already
                // starting with "Near" - print it as they wrote it
                + (delivery.landmark ? '<p class="is-sub">' + esc(delivery.landmark) + '</p>' : '')
                + (delivery.phone ? '<p class="is-sub">' + esc(delivery.phone) + '</p>' : '')
                + '</div>');
        }

        return '<div class="bill-parties' + (blocks.length === 1 ? ' is-single' : '') + '">'
            + blocks.join('') + '</div>';
    }

    function items(rows) {
        if (!rows || !rows.length) {
            return '<p class="bill-empty">This bill has no items.</p>';
        }

        var body = rows.map(function (row, index) {
            return '<tr>'
                + '<td class="is-sn">' + (index + 1) + '</td>'
                + '<td>' + esc(row.item)
                + (row.code ? '<span class="is-item-code">' + esc(row.code) + '</span>' : '')
                + '</td>'
                + '<td class="is-num">' + row.qty + (row.unit ? ' ' + esc(row.unit) : '') + '</td>'
                + '<td class="is-num">' + money(row.rate) + '</td>'
                + '<td class="is-num">' + money(row.amount) + '</td>'
                + '</tr>';
        }).join('');

        return '<table class="bill-items">'
            + '<thead><tr><th class="is-sn">#</th><th>Item</th>'
            + '<th class="is-num">Qty</th><th class="is-num">Rate</th><th class="is-num">Amount</th>'
            + '</tr></thead><tbody>' + body + '</tbody></table>';
    }

    function totals(figures) {
        var rows = '<div><span>Subtotal</span><span>' + money(figures.subtotal) + '</span></div>';

        // Discount and delivery only earn a line when they are not zero.
        if (figures.discount > 0) {
            rows += '<div><span>Discount</span><span>&minus; ' + money(figures.discount) + '</span></div>';
        }
        if (figures.delivery > 0) {
            rows += '<div><span>Delivery charge</span><span>' + money(figures.delivery) + '</span></div>';
        }

        rows += '<div class="is-grand"><span>Total</span><span>' + money(figures.grand_total) + '</span></div>';

        return '<div class="bill-totals">' + rows + '</div>';
    }

    function payment(bill) {
        var pay = bill.payment || {};
        var lines = pay.lines || [];

        // A split counter payment shows what went on each mode.
        var detail = lines.length > 1
            ? lines.map(function (line) {
                return esc(line.title) + (line.amount ? ' ' + money(line.amount) : '');
            }).join(' &middot; ')
            : esc(pay.method || '');

        return '<div class="bill-pay">'
            + '<span class="bill-pay-label">Payment</span>'
            + '<span class="bill-pay-method">' + detail + '</span>'
            + '<span class="bill-chip ' + chipClass(pay) + '">' + esc(pay.status || '') + '</span>'
            + '</div>';
    }

    function render(bill) {
        return head(bill.shop || {})
            + meta(bill)
            + parties(bill)
            + items(bill.items)
            + totals(bill.totals || {})
            + payment(bill)
            + (bill.shop && bill.shop.footer_note
                ? '<p class="bill-note">' + esc(bill.shop.footer_note) + '</p>' : '');
    }

    /** Fetch one bill and show it. Exposed globally: the three screens call it. */
    function loadInvoiceModal(invoiceId) {
        var target = document.getElementById('shoporaBill');
        if (!target) return;

        var template = window.SHOPORA_INVOICE_URL;
        if (!template) {
            console.error('invoice-bill: window.SHOPORA_INVOICE_URL is not set.');
            return;
        }

        target.innerHTML = '<p class="bill-empty">Loading bill&hellip;</p>';
        window.jQuery('#invoiceModal').modal('show');

        window.jQuery.getJSON(template.replace(':id', invoiceId))
            .done(function (data) {
                current = data.bill;
                target.innerHTML = render(current);
            })
            .fail(function () {
                current = null;
                target.innerHTML = '<p class="bill-empty">This bill could not be loaded.</p>';
            });
    }

    /**
     * Prints through a hidden iframe carrying the same markup and stylesheet,
     * so what comes out of the printer is the bill on screen at roll width -
     * not a second, separately maintained version of it.
     */
    function printInvoice() {
        if (!current) return;

        var frame = document.getElementById('printFrame');
        if (!frame) return;

        var css = document.querySelector('link[href*="invoice-bill.css"]');
        var doc = frame.contentWindow.document;

        doc.open();
        doc.write('<!doctype html><html><head><meta charset="utf-8">'
            + '<title>' + esc(current.bill_no) + '</title>'
            + (css ? '<link rel="stylesheet" href="' + esc(css.getAttribute('href')) + '">' : '')
            + '<style>body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;}</style>'
            + '</head><body><div class="shopora-bill">' + render(current) + '</div></body></html>');
        doc.close();

        // Give the stylesheet a moment to land before the print dialog opens.
        var go = function () {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        };
        if (doc.readyState === 'complete') setTimeout(go, 120);
        else frame.onload = function () { setTimeout(go, 120); };
    }

    window.loadInvoiceModal = loadInvoiceModal;
    window.printInvoice = printInvoice;

    // Any button marked up as a bill trigger opens it, on every screen.
    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('.view-invoice') : null;
        if (!trigger) return;
        event.preventDefault();
        loadInvoiceModal(trigger.getAttribute('data-id'));
    });
})(window, document);
