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

    /** "1,450.00" - the bill writes its own "Rs." where it wants one. */
    function amount(value) {
        return Number(value || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function money(value) {
        return 'Rs.' + amount(value);
    }

    /** The shop, centred. A line the settings leave blank is not printed. */
    function head(shop) {
        var lines = [];
        if (shop.address) lines.push(esc(shop.address));
        if (shop.pan) lines.push('Vat No : ' + esc(shop.pan));
        if (shop.phone) lines.push('Contact : ' + esc(shop.phone));

        return '<div class="bill-head">'
            + '<h2>' + esc(shop.name) + '</h2>'
            + lines.map(function (line) { return '<p>' + line + '</p>'; }).join('')
            + '</div>';
    }

    /**
     * The detail lines under the header. A counter sale names whoever rang it
     * up; an online order names the customer and where it is going, which is
     * the whole reason the bill has to serve both.
     */
    function lines(bill) {
        var rows = [
            'Bill No : ' + esc(bill.bill_no),
            'Date : ' + esc(bill.date) + (bill.nepali_date ? ' (' + esc(bill.nepali_date) + ' B.S.)' : ''),
            'Name : ' + esc((bill.customer || {}).name || 'Walk-in customer'),
        ];

        if ((bill.customer || {}).phone) {
            rows.push('Contact : ' + esc(bill.customer.phone));
        }

        if (bill.served_by) {
            rows.push('Served By : ' + esc(bill.served_by));
        }

        var delivery = bill.delivery;
        if (delivery && delivery.address) {
            rows.push('Deliver To : ' + esc(delivery.address)
                + (delivery.landmark ? ', ' + esc(delivery.landmark) : ''));
        }

        var pay = bill.payment || {};
        // A split counter payment names every mode it was settled across.
        var modes = (pay.lines || []).length > 1
            ? pay.lines.map(function (line) {
                return esc(line.title) + (line.amount ? ' ' + money(line.amount) : '');
            }).join(', ')
            : esc(pay.method || '');

        rows.push('Payment Mode : ' + modes);

        // Kept off the printed slip: the customer copy carries what was bought,
        // not where the order has reached inside the shop.
        var status = '<li class="no-print">Status : ' + esc(bill.status)
            + ' &middot; ' + esc(pay.status || '') + '</li>';

        return '<ul class="bill-lines">'
            + rows.map(function (row) { return '<li>' + row + '</li>'; }).join('')
            + status
            + '</ul>';
    }

    function items(rows) {
        if (!rows || !rows.length) {
            return '<p class="bill-empty">This bill has no items.</p>';
        }

        var body = rows.map(function (row, index) {
            return '<tr>'
                + '<td>' + (index + 1) + '</td>'
                + '<td>' + esc(row.item) + '</td>'
                + '<td>' + row.qty + '</td>'
                + '<td>' + amount(row.rate) + '</td>'
                + '<td>' + amount(row.amount) + '</td>'
                + '</tr>';
        }).join('');

        return '<table class="bill-items">'
            + '<thead><tr><th>S.No</th><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead>'
            + '<tbody>' + body + '</tbody></table>';
    }

    function totals(figures) {
        var rows = [['Initial Amount', figures.subtotal], ['Discount Amount', figures.discount]];

        // Delivery is only charged on an online order, so the line only appears
        // on one - and leaving it out is what used to under-state the total.
        if (figures.delivery > 0) {
            rows.push(['Delivery Charge', figures.delivery]);
        }

        rows.push(['Final Amount', figures.grand_total]);

        return '<table class="bill-totals"><tfoot>'
            + rows.map(function (row) {
                return '<tr><th>' + row[0] + '</th>'
                    + '<th class="is-amount">Rs.' + amount(row[1]) + '</th></tr>';
            }).join('')
            + '</tfoot></table>';
    }

    function render(bill) {
        var shop = bill.shop || {};

        return head(shop)
            + lines(bill)
            + items(bill.items)
            + totals(bill.totals || {})
            + '<h5 class="bill-note">' + esc(shop.footer_note || 'Thank you for visiting.') + '</h5>';
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
