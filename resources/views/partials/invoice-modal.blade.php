{{--
    The one bill. Included by Sales, Invoice and the Dashboard so a counter
    sale and a storefront order are printed by the same code from the same
    payload - the three hand-copied versions had already drifted into two
    different shop names and two different fiscal years.

    Open it with loadInvoiceModal(id), or put class="view-invoice" data-id="…"
    on any button and the shared script picks it up.
--}}
<div class="modal fade shopora-bill-modal" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="shopora-bill" id="shoporaBill"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<iframe id="printFrame" title="Bill print" style="position:absolute;width:0;height:0;border:0;"></iframe>

<script>
    // The one endpoint every screen reads a bill from.
    window.SHOPORA_INVOICE_URL = "{{ route('admin.invoice.viewInvoice', ['id' => ':id']) }}";
</script>

@once
    @push('style')
        <link href="{{ asset('assets/css/invoice-bill.css') }}?v=1" rel="stylesheet" />
    @endpush
    @push('scripts')
        <script src="{{ asset('assets/js/invoice-bill.js') }}?v=1"></script>
    @endpush
@endonce
