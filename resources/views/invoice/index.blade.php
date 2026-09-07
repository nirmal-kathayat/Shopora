@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />

@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Invoice</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Sales Invoice List</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="invoice-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
    @include('partials.invoice-modal')
</div>

@endsection

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    const STATUS_TONES = {
        placed: 'bg-secondary',
        confirmed: 'bg-info',
        shipped: 'bg-warning',
        delivered: 'bg-success',
        cancelled: 'bg-danger',
    };

    $(document).ready(function() {
        const esc = TableHelper.escape;

        new TableHelper({
            containerId: 'invoice-grid',
            apiUrl: "{{ route('admin.invoice.index') }}",
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'No bills match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Order By', field: 'order_by_name' },
                {
                    name: 'Bill No',
                    field: 'id',
                    // Worked out by the server, with the same helper the bill
                    // itself uses - the fiscal year was hard-coded here.
                    render: (row) => esc(row.bill_no)
                },
                { name: 'Customers', field: 'customer_title' },
                {
                    name: 'Payment',
                    field: 'payment_method',
                    render: function(row) {
                        // A counter sale leaves payment_method/payment_status at their
                        // defaults, so the bill was settled at the till and the
                        // storefront method/status pair says nothing useful here.
                        if (row.channel !== 'storefront') {
                            return '<span class="fw-medium">Counter</span>'
                                + '<div class="mt-1"><span class="badge bg-success">Paid</span></div>';
                        }

                        const label = row.payment_method === 'esewa' ? 'eSewa' : 'Cash on Delivery';
                        let badge;

                        if (row.payment_status === 'paid') {
                            badge = '<span class="badge bg-success">Paid</span>';
                        } else if (row.payment_status === 'failed') {
                            badge = '<span class="badge bg-danger">Failed</span>';
                        } else if (row.payment_method === 'cod') {
                            // COD is collected by the rider, so "unpaid" here just
                            // means the cash is due on delivery, not that anything failed.
                            badge = '<span class="badge bg-secondary">On delivery</span>';
                        } else {
                            badge = '<span class="badge bg-danger">Unpaid</span>';
                        }

                        return '<span class="fw-medium">' + esc(label) + '</span>'
                            + '<div class="mt-1">' + badge + '</div>';
                    }
                },
                {
                    name: 'Status',
                    field: 'status',
                    align: 'center',
                    render: (row) => '<span class="badge ' + (STATUS_TONES[row.status] || 'bg-secondary')
                        + '">' + esc(row.status) + '</span>'
                },
                { name: 'Created At', field: 'created_at' },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        {
                            type: 'view',
                            title: 'View bill',
                            showLabel: false,
                            // Straight to the shared bill loader. Its own
                            // .view-invoice listener sits on document, and an
                            // action button stops the click before it gets
                            // there - the same reason Purchase Inventory's
                            // Edit had to be called rather than delegated.
                            onClick: (row) => loadInvoiceModal(row.id)
                        }
                    ]
                }
            ],

            enableSortColumns: ['id', 'order_by_name', 'customer_title', 'status', 'created_at'],

            // No filter bar above this table, same as the other lists: the
            // header row and the search box are how you find a bill.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'order_by_name', type: 'text', param: 'order_by_name', placeholder: 'Order by' },
                    { field: 'customer_title', type: 'text', param: 'customer_title', placeholder: 'Customer' }
                ]
            },

            search: { placeholder: 'Search bill no, customer or who rang it up...' }
        });
    });
</script>



@endsection