@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="{{ asset('assets/css/date-filter.css') }}?v=1" rel="stylesheet" />
<style>
    /* ===== Order modal =====
       Two tinted panels for the people, a plain table for the goods, and the
       totals boxed off to the right so the figure that matters is the last
       thing on the page. */
    #orderModal .modal-content { border: 0; border-radius: 16px; overflow: hidden; }
    #orderModal .modal-header { align-items: center; gap: 14px; padding: 20px 24px; border-bottom: 1px solid #eef0f3; }
    #orderModal .modal-body { padding: 20px 24px; background: #fff; }
    #orderModal .modal-footer { padding: 16px 24px; border-top: 1px solid #eef0f3; background: #fafbfc; }

    .order-head-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: #e8f1ff;
        color: #1f6fd0;
        font-size: 24px;
        flex: 0 0 auto;
    }

    .order-head-title { margin: 0; font-size: 20px; font-weight: 700; color: #14181c; }
    .order-head-sub { margin: 2px 0 0; font-size: 13px; color: #7a838c; }

    /* one pill, coloured by where the order actually is */
    .order-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .order-pill.is-placed { background: #eef1f4; color: #55606a; }
    .order-pill.is-confirmed { background: #e7f1ff; color: #1f6fd0; }
    .order-pill.is-shipped { background: #fdf3e2; color: #97650f; }
    .order-pill.is-delivered { background: #e7f6ee; color: #1a7f4b; }
    .order-pill.is-cancelled { background: #fdecec; color: #b3261e; }

    .order-panels { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 640px) { .order-panels { grid-template-columns: 1fr; } }

    .order-panel { display: flex; gap: 14px; padding: 16px; border-radius: 14px; }
    .order-panel.is-customer { background: #f2f7ff; }
    .order-panel.is-delivery { background: #f0f9f3; }

    .order-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
        flex: 0 0 auto;
    }

    .is-customer .order-panel-icon { background: #dbe8fb; color: #1f6fd0; }
    .is-delivery .order-panel-icon { background: #d9f0e2; color: #1a7f4b; }

    .order-panel h6 { margin: 0 0 6px; font-size: 14px; font-weight: 600; }
    .is-customer h6 { color: #1f6fd0; }
    .is-delivery h6 { color: #1a7f4b; }

    .order-panel-name { margin: 0 0 8px; font-size: 16px; font-weight: 600; color: #14181c; }
    .order-panel-line { display: flex; align-items: center; gap: 8px; margin: 0 0 4px; font-size: 14px; color: #48525c; }
    .order-panel-line i { font-size: 16px; opacity: .75; }
    .order-panel-line:last-child { margin-bottom: 0; }

    /* --- goods --- */
    .order-items { width: 100%; margin-top: 18px; border-collapse: separate; border-spacing: 0; }
    .order-items thead th {
        padding: 12px 14px;
        background: #f5f7f9;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: #6b7580;
        text-align: left;
    }
    .order-items thead th:first-child { border-radius: 10px 0 0 0; }
    .order-items thead th:last-child { border-radius: 0 10px 0 0; }
    .order-items td { padding: 12px 14px; border-bottom: 1px solid #eef0f3; vertical-align: middle; }
    .order-items .is-num { text-align: right; white-space: nowrap; }

    .order-item-cell { display: flex; align-items: center; gap: 12px; }
    .order-item-thumb {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        background: #f5f7f9;
        object-fit: cover;
        flex: 0 0 auto;
    }

    /* the totals sit under the money columns, not across the whole table */
    .order-totals { display: flex; justify-content: flex-end; }
    .order-totals table { width: min(340px, 100%); border-collapse: separate; border-spacing: 0; }
    .order-totals td { padding: 11px 14px; border-bottom: 1px solid #eef0f3; font-size: 14px; }
    .order-totals .is-num { text-align: right; }
    .order-totals .is-grand td {
        border-bottom: 0;
        background: #eef4ff;
        font-size: 16px;
        font-weight: 700;
        color: #14181c;
    }
    .order-totals .is-grand td:first-child { border-radius: 0 0 0 10px; }
    .order-totals .is-grand td:last-child { border-radius: 0 0 10px 0; }

    .order-placed {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-top: 18px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #f7f8fa;
        font-size: 13.5px;
        color: #48525c;
    }
    .order-placed i { font-size: 18px; color: #7a838c; }
    .order-placed .sep { color: #c8cdd3; }

    #orderModal .modal-footer .form-select { min-width: 230px; border-radius: 10px; padding: 10px 14px; }
    #orderModal .modal-footer .btn {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        white-space: nowrap;
    }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Orders</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Storefront Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        {{-- Same bar as Inventory Items, with the two things an order is
             looked up by: how it was paid and where it has got to. --}}
        <div class="shopora-date-filter">
            <div class="date-field">
                <label for="fromDate">From Date</label>
                <div class="date-input-wrap">
                    <input type="text" id="fromDate" class="date-picker" placeholder="Select From Date" readonly />
                    <i class='bx bx-calendar cal-icon'></i>
                </div>
            </div>
            <div class="date-field">
                <label for="toDate">To Date</label>
                <div class="date-input-wrap">
                    <input type="text" id="toDate" class="date-picker" placeholder="Select To Date" readonly />
                    <i class='bx bx-calendar cal-icon'></i>
                </div>
            </div>
            <div class="date-field">
                <label for="paymentFilter">Payment Method</label>
                <select id="paymentFilter" class="form-select form-control">
                    <option value="">All Payments</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="esewa">eSewa</option>
                </select>
            </div>
            <div class="date-field">
                <label for="statusFilter">Status</label>
                <select id="statusFilter" class="form-select form-control">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn-clear-filter" id="clearFilters">Clear</button>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here. --}}
                <div id="order-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>

<!-- one order: what is in it, where it goes, and where it can go next -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <span class="order-head-icon"><i class="bx bx-clipboard"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <h5 class="order-head-title" id="orderModalLabel">Order</h5>
                    <p class="order-head-sub" id="orderModalPlaced"></p>
                </div>
                <span class="order-pill" id="orderModalStatus"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <p class="text-muted mb-0">Loading...</p>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <select id="orderStatusSelect" class="form-select"></select>
                    <button type="button" class="btn btn-primary" id="orderStatusSave" disabled>
                        <i class="bx bx-refresh me-1"></i> Update Status
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    /** The glyph beside the status pill in the order modal's header. */
    const STATUS_ICONS = {
        placed: 'bx-receipt',
        confirmed: 'bx-check-circle',
        shipped: 'bx-package',
        delivered: 'bx-check-double',
        cancelled: 'bx-x-circle',
    };

    const STATUS_TONES = {
        placed: 'bg-secondary',
        confirmed: 'bg-info',
        shipped: 'bg-warning',
        delivered: 'bg-success',
        cancelled: 'bg-danger',
    };

    function money(value) {
        return 'Rs. ' + Number(value || 0).toLocaleString('en-IN');
    }

    function escapeText(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    let table;

    $(document).ready(function() {
        let openOrderId = null;

        const esc = TableHelper.escape;

        table = new TableHelper({
            containerId: 'order-grid',
            apiUrl: "{{ route('admin.order') }}",
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'No orders match these filters',

            // Opens on the month so far, and Clear comes back to it - the
            // range someone asking about orders almost always wants.
            dateRangeDefaults: {
                type: 'thisMonth',
                fromSelector: '#fromDate',
                toSelector: '#toDate',
            },

            onClear: function() {
                table.applyDefaultDateRange();
            },

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Order', field: 'code' },
                {
                    name: 'Customer',
                    field: 'customer_name',
                    render: (row) => esc(row.customer_name)
                        + '<div class="text-muted small">' + esc(row.customer_phone) + '</div>'
                },
                { name: 'Items', field: 'item_count', align: 'center' },
                { name: 'Total', field: 'total', align: 'right', render: (row) => money(row.total) },
                {
                    name: 'Payment',
                    field: 'payment_method',
                    render: function(row) {
                        const label = row.payment_method === 'esewa' ? 'eSewa' : 'Cash on Delivery';
                        let badge;

                        if (row.payment_status === 'paid') {
                            badge = '<span class="badge bg-success">Paid</span>';
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
                { name: 'Placed', field: 'created_at' },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        {
                            type: 'view',
                            title: 'View order',
                            showLabel: false,
                            class: 'btn btn-sm btn-primary',
                            onClick: (row) => openOrder(row.id)
                        }
                    ]
                }
            ],

            enableSortColumns: ['code', 'customer_name', 'item_count', 'total', 'status', 'created_at'],

            filters: {
                dateRange: { fromId: 'fromDate', toId: 'toDate' },
                additional: [
                    { id: 'paymentFilter', param: 'payment_method' },
                    { id: 'statusFilter', param: 'status' }
                ],
                autoReload: ['#paymentFilter', '#statusFilter', '#fromDate', '#toDate'],
                autoGenerateColumnFilters: false
            },

            search: { placeholder: 'Search order no, customer or phone...' }
        });

        function openOrder(id) {
            openOrderId = id;
            const url = "{{ route('admin.order.show', ['id' => ':id']) }}".replace(':id', openOrderId);

            $('#orderModalBody').html('<p class="text-muted mb-0">Loading...</p>');
            $('#orderStatusSelect').html('');
            $('#orderStatusSave').prop('disabled', true);
            new bootstrap.Modal(document.getElementById('orderModal')).show();

            $.get(url).done(function(order) {
                $('#orderModalLabel').text('Order ' + order.code);
                $('#orderModalPlaced').text('Placed on ' + (order.placed_at || ''));
                $('#orderModalStatus')
                    .attr('class', 'order-pill is-' + order.status)
                    .html('<i class="bx ' + (STATUS_ICONS[order.status] || 'bx-time-five') + '"></i>' + escapeText(order.status));

                const rows = order.items.map(function(item) {
                    // A product with no photo yet keeps the same box, so the
                    // rows do not jump about mid-list.
                    const thumb = item.image
                        ? '<img class="order-item-thumb" src="' + escapeText(item.image) + '" alt="">'
                        : '<span class="order-item-thumb"></span>';

                    return '<tr>' +
                        '<td><span class="order-item-cell">' + thumb + escapeText(item.name) + '</span></td>' +
                        '<td class="is-num">' + item.qty + '</td>' +
                        '<td class="is-num">' + money(item.price_per_unit) + '</td>' +
                        '<td class="is-num">' + money(item.line_total) + '</td>' +
                        '</tr>';
                }).join('');

                $('#orderModalBody').html(
                    '<div class="order-panels">' +
                        '<div class="order-panel is-customer">' +
                            '<span class="order-panel-icon"><i class="bx bx-user"></i></span>' +
                            '<div class="min-w-0">' +
                                '<h6>Customer Details</h6>' +
                                '<p class="order-panel-name">' + escapeText(order.customer.name) + '</p>' +
                                '<p class="order-panel-line"><i class="bx bx-phone"></i>' + escapeText(order.customer.phone) + '</p>' +
                                (order.customer.email
                                    ? '<p class="order-panel-line"><i class="bx bx-envelope"></i>' + escapeText(order.customer.email) + '</p>'
                                    : '') +
                            '</div>' +
                        '</div>' +
                        '<div class="order-panel is-delivery">' +
                            '<span class="order-panel-icon"><i class="bx bx-map"></i></span>' +
                            '<div class="min-w-0">' +
                                '<h6>Delivering to</h6>' +
                                '<p class="order-panel-name">' + escapeText(order.delivery.address) + '</p>' +
                                (order.delivery.landmark
                                    ? '<p class="order-panel-line"><i class="bx bx-map-pin"></i>' + escapeText(order.delivery.landmark) + '</p>'
                                    : '') +
                                '<p class="order-panel-line"><i class="bx bx-user"></i>' + escapeText(order.delivery.recipient) + '</p>' +
                                '<p class="order-panel-line"><i class="bx bx-phone"></i>' + escapeText(order.delivery.phone) + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="table-responsive">' +
                        '<table class="order-items">' +
                            '<thead><tr><th>Item</th><th class="is-num">Qty</th>' +
                            '<th class="is-num">Rate</th><th class="is-num">Amount</th></tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                    '</div>' +
                    '<div class="order-totals"><table>' +
                        '<tr><td>Subtotal</td><td class="is-num">' + money(order.subtotal) + '</td></tr>' +
                        '<tr><td>Delivery</td><td class="is-num">' + money(order.delivery_fee) + '</td></tr>' +
                        '<tr class="is-grand"><td>Total</td><td class="is-num">' + money(order.total) + '</td></tr>' +
                    '</table></div>' +
                    '<div class="order-placed">' +
                        '<i class="bx bx-calendar"></i>' +
                        '<span>Placed ' + escapeText(order.placed_at) + '</span>' +
                        '<span class="sep">|</span>' +
                        '<span>Status:</span>' +
                        '<span class="order-pill is-' + order.status + '">' + escapeText(order.status) + '</span>' +
                    '</div>'
                );

                if (!order.next_statuses.length) {
                    $('#orderStatusSelect').html('<option value="">No further steps</option>').prop('disabled', true);
                    $('#orderStatusSave').prop('disabled', true);
                    return;
                }

                $('#orderStatusSelect')
                    .prop('disabled', false)
                    .html(order.next_statuses.map(function(status) {
                        return '<option value="' + status + '">Mark as ' + status + '</option>';
                    }).join(''));
                $('#orderStatusSave').prop('disabled', false);
            }).fail(function() {
                $('#orderModalBody').html('<p class="text-danger mb-0">Could not load that order.</p>');
            });
        }

        const wanted = new URLSearchParams(window.location.search).get('order');
        if (wanted) {
            openOrder(wanted);
            // Drop it from the address bar, so a refresh does not reopen a
            // modal the admin has already dealt with and closed.
            window.history.replaceState({}, '', window.location.pathname);
        }

        $('#orderStatusSave').on('click', function() {
            if (!openOrderId) return;
            const url = "{{ route('admin.order.status', ['id' => ':id']) }}".replace(':id', openOrderId);
            const button = $(this).prop('disabled', true);

            $.post(url, {
                _token: "{{ csrf_token() }}",
                status: $('#orderStatusSelect').val()
            }).done(function(response) {
                bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
                table.refresh();
                shoporaToast.info(response.message);
            }).fail(function(xhr) {
                shoporaToast.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not update that order.');
            }).always(function() {
                button.prop('disabled', false);
            });
        });
    });
</script>
@endsection
