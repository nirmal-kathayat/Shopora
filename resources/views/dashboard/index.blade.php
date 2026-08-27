@extends("layouts.app")
@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .shopora-date-filter {
        background: #fff;
        border: 1px solid #e4e4e4;
        border-radius: 12px;
        padding: 16px 18px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 14px 16px;
    }

    .shopora-date-filter .date-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 180px;
    }

    .shopora-date-filter .date-field label {
        margin: 0;
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
    }

    .shopora-date-filter .date-input-wrap {
        position: relative;
    }

    .shopora-date-filter .date-input-wrap input {
        width: 100%;
        height: 42px;
        padding: 8px 40px 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        color: #1f2937;
        background: #fff;
    }

    .shopora-date-filter .date-input-wrap input:focus {
        outline: none;
        border-color: #008cff;
        box-shadow: 0 0 0 3px rgba(0, 140, 255, 0.12);
    }

    .shopora-date-filter .date-input-wrap .cal-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 18px;
        pointer-events: none;
    }

    .shopora-date-filter .btn-clear-filter {
        height: 42px;
        padding: 0 18px;
        border: 0;
        border-radius: 8px;
        background: #008cff;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        line-height: 1;
    }

    .shopora-date-filter .btn-clear-filter:hover {
        background: #0077db;
        color: #fff;
    }

    .shopora-date-filter .range-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    .shopora-date-filter .range-tab {
        height: 36px;
        padding: 0 14px;
        border: 1px solid #dbe3ef;
        border-radius: 999px;
        background: #fff;
        color: #4b5563;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .shopora-date-filter .range-tab:hover {
        border-color: #008cff;
        color: #008cff;
    }

    .shopora-date-filter .range-tab.active {
        background: #e8f1ff;
        border-color: #008cff;
        color: #008cff;
    }

    @media (max-width: 768px) {
        .shopora-date-filter .range-tabs {
            margin-left: 0;
            width: 100%;
        }
    }

    .page-content.shopora-dash-page {
        position: relative;
    }

    .shopora-dash-loader {
        position: absolute;
        inset: 0;
        z-index: 40;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        min-height: 220px;
    }

    .shopora-dash-loader.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: all;
    }

    .shopora-spin {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 3px solid rgba(0, 140, 255, 0.18);
        border-top-color: #008cff;
        animation: shopora-spin 0.7s linear infinite;
    }

    @keyframes shopora-spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content shopora-dash-page">
        <div id="shoporaDashLoader" class="shopora-dash-loader" aria-hidden="true">
            <div class="shopora-spin" role="status" aria-label="Loading"></div>
        </div>
        <!-- Date Range Filter (Figma) -->
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
            <button type="button" class="btn-clear-filter" id="clearDateFilter">Clear</button>
            <div class="range-tabs" role="tablist">
                <button type="button" class="range-tab active" data-range="today">Today</button>
                <button type="button" class="range-tab" data-range="7days">7 days</button>
                <button type="button" class="range-tab" data-range="1month">1 Month</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Items</p>
                                <h4 class="my-1 text-info">{{$data['totalInventoryItems']}}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto"><i class='bx bxs-cart'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-danger" style="cursor: pointer;" onclick="showPaymentMethodRevenue()">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Revenue</p>
                                <h4 class="my-1 text-red">Rs {{ number_format($data['totalRevenue'], 2) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class='bx bxs-wallet'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Sales</p>
                                <h4 class="my-1 text-success">{{$data['totalSales']}}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class='bx bxs-bar-chart-alt-2'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Customers</p>
                                <h4 class="my-1 text-warning">{{$data['totalCustomer']}}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto"><i class='bx bxs-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end row-->
        <!-- invoice lists -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Sales Invoice History</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Sales Invoice</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="invoiceTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Order By</th>
                                <th>Customers</th>
                                <th>Created_at</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <!-- Invoice details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printInvoice()">Print</button>
                </div>
            </div>
        </div>
    </div>
    <iframe id="printFrame" style="display:none;"></iframe>
</div>
@endsection
@section("script")
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<!-- Flatpickr for Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Create payment mode mapping globally
    window.paymentModeMap = {};
    @foreach($paymentModes as $payment)
    window.paymentModeMap[{{ $payment->id }}] = '{{ $payment->payment_title }}';
    @endforeach
    
    let currentInvoice = null;
    let currentDetails = [];
    let fromPicker = null;
    let toPicker = null;
    let applyingRange = false;
    let dashLoaderPending = 0;
    let dashLoaderShownAt = 0;
    const DASH_LOADER_MIN_MS = 400;

    function startOfDay(date) {
        const d = new Date(date);
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function getRangeDates(range) {
        const today = startOfDay(new Date());
        const to = new Date(today);
        const from = new Date(today);

        if (range === '7days') {
            from.setDate(from.getDate() - 6);
        } else if (range === '1month') {
            from.setMonth(from.getMonth() - 1);
        }

        return { from: from, to: to };
    }

    function setActiveTab(range) {
        $('.range-tab').removeClass('active');
        if (range) {
            $('.range-tab[data-range="' + range + '"]').addClass('active');
        }
    }

    function showDashboardLoader() {
        dashLoaderPending++;
        if (dashLoaderPending > 1) return;

        dashLoaderShownAt = Date.now();
        $('#shoporaDashLoader').addClass('is-visible').attr('aria-hidden', 'false');
    }

    function hideDashboardLoader() {
        dashLoaderPending = Math.max(0, dashLoaderPending - 1);
        if (dashLoaderPending > 0) return;

        const elapsed = Date.now() - dashLoaderShownAt;
        const wait = Math.max(0, DASH_LOADER_MIN_MS - elapsed);

        setTimeout(function() {
            if (dashLoaderPending === 0) {
                $('#shoporaDashLoader').removeClass('is-visible').attr('aria-hidden', 'true');
            }
        }, wait);
    }

    function applyDateRange(fromDate, toDate, tab) {
        applyingRange = true;
        fromPicker.setDate(fromDate, false);
        toPicker.setDate(toDate, false);
        applyingRange = false;
        setActiveTab(tab);
        updateDashboard({ loader: true });
    }
    
    $(document).ready(function() {
        const today = startOfDay(new Date());

        fromPicker = flatpickr("#fromDate", {
            dateFormat: "Y-m-d",
            defaultDate: today,
            onChange: function() {
                if (applyingRange) return;
                setActiveTab(null);
                updateDashboard({ loader: true });
            }
        });

        toPicker = flatpickr("#toDate", {
            dateFormat: "Y-m-d",
            defaultDate: today,
            onChange: function() {
                if (applyingRange) return;
                setActiveTab(null);
                updateDashboard({ loader: true });
            }
        });

        $('.range-tab').on('click', function() {
            const range = $(this).data('range');
            const dates = getRangeDates(range);
            applyDateRange(dates.from, dates.to, range);
        });

        $('#clearDateFilter').on('click', function() {
            const dates = getRangeDates('today');
            applyDateRange(dates.from, dates.to, 'today');
        });

        initializeInvoiceTable();
        updateDashboard({ loader: false });
    });
    
    function initializeInvoiceTable() {
        var table = $('#invoiceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.invoice.index') }}",
                data: function(d) {
                    d.from_date = $('#fromDate').val();
                    d.to_date = $('#toDate').val();
                }
            },
            pageLength: 25,
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'order_by_name',
                    name: 'admins.name',
                    orderable: false
                },
                {
                    data: 'customer_title',
                    name: 'customers.name',
                    orderable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var viewButton = '<a class="btn btn-info btn-sm view-invoice" data-id="' + full.id + '"><i class="bx bx-show"></i></a>';
                        var actionButton = '<div class="d-flex gap-sm-2">' + viewButton +  '</div>';
                        return actionButton;
                    }
                }
            ]
        });
        
        // Handle view button for invoice
        $(document).on('click', '.view-invoice', function(e) {
            e.preventDefault();
            var invoiceId = $(this).data('id');
            var url = "{{ route('admin.invoice.viewInvoice', ['id' => ':id']) }}".replace(':id', invoiceId);

            $.get(url, function(data) {
                var invoice = data.invoice;
                var details = data.details;
                currentInvoice = data.invoice;
                currentDetails = data.details;
                var itemsHtml = '';
                var total = 0;
                details.forEach(function(item, index) {
                    total += parseFloat(item.amount);
                    itemsHtml += `
                    <tr>
                        <td style="border: 1px dashed #111;">${index + 1}</td>
                        <td style="border: 1px dashed #111;">${item.item}</td>
                        <td style="border: 1px dashed #111;">${item.qty}</td>
                        <td style="border: 1px dashed #111;">${parseFloat(item.rate).toFixed(2)}</td>
                        <td style="border: 1px dashed #111;">${parseFloat(item.amount).toFixed(2)}</td>
                    </tr>
                `;
                });

                $('#invoiceModal .modal-body').html(`
                <div class="bill-header" style="padding-top:20px">
                    <div style="margin-bottom:10px;text-align:center">
                        <h2 style="font-size:15px;margin-bottom:5px">SangamShree Inventory</h2>
                        <h5 style="font-size:14px;color:#000;margin-bottom:5px;">Kathmandu</h5>
                        <h5 style="font-size:14px;color:#000;margin-bottom:5px">Vat No : 1234567</h5>
                    </div>
                    <ul style="margin-left:-18px;">
                        <li>Bill No : T${invoice.id}-80/81</li>
                        <li>Date : ${invoice.created_at}</li>
                        <li>Name : ${invoice.order_by_name}</li>
                           <li>Payment Mode : ${(() => {
    const paymentModes = String(details[0]?.payment_mode).split(',').map(id => window.paymentModeMap[id.trim()] || id.trim());
    return paymentModes.join(', ');
})()}</li>
                    </ul>
                </div>
                <div class="order-details-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="border: 1px dashed #111;">S.No</th>
                                <th style="border: 1px dashed #111;">Item</th>
                                <th style="border: 1px dashed #111;">Qty</th>
                                <th style="border: 1px dashed #111;">Rate</th>
                                <th style="border: 1px dashed #111;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}
                        </tbody>
                    </table>
                    <table style="width:60%;margin-left:auto">
                        <tfoot>
                            <tr>
                                <th colspan="4" style="border: 1px dashed #111;">Initial Amount</th>
                                <th style="border: 1px dashed #111;text-align: center">Rs.${parseFloat(total).toFixed(2)}</th>
                            </tr>
                            <tr>
                                <th colspan="4" style="border: 1px dashed #111;">Discount Amount</th>
                                <th style="border: 1px dashed #111;text-align: center">Rs.${parseFloat(invoice.discount || 0).toFixed(2)}</th>
                            </tr>
                            <tr>
                                <th colspan="4" style="border: 1px dashed #111;">Final Amount</th>
                                <th style="border: 1px dashed #111;text-align: center">Rs.${parseFloat(total - (invoice.discount || 0)).toFixed(2)}</th>
                            </tr>
                        </tfoot>
                    </table>
                    <h5 style="border-bottom:1px dashed #111 !important;font-size:14px;padding-bottom:10px;">Thank you for visiting</h5>
                </div>
            `);
                $('#invoiceModal').modal('show');
            });
        });
    }
    
    function updateDashboard(options) {
        options = options || {};
        const withLoader = options.loader === true;
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();
        let pending = 1;

        if (withLoader) {
            showDashboardLoader();
        }

        function markDone() {
            pending--;
            if (pending <= 0 && withLoader) {
                hideDashboardLoader();
            }
        }

        $.ajax({
            url: "{{ route('admin.dashboardStats') }}",
            data: {
                from_date: fromDate,
                to_date: toDate
            },
            success: function(response) {
                $('.text-info').text(response.totalInventoryItems);
                $('.text-red').text('Rs ' + parseFloat(response.totalRevenue).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('.text-success').text(response.totalSales);
                $('.text-warning').text(response.totalCustomer);
            },
            complete: markDone
        });

        if ($.fn.DataTable.isDataTable('#invoiceTable')) {
            pending++;
            $('#invoiceTable').DataTable().ajax.reload(function() {
                markDone();
            }, false);
        }
    }
</script>

<!-- Payment Method Revenue Modal -->
<div class="modal fade" id="paymentMethodRevenueModal" tabindex="-1" aria-labelledby="paymentMethodRevenueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodRevenueModalLabel">Total Revenue by Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentMethodRevenueContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showPaymentMethodRevenue() {
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();

        $('#paymentMethodRevenueContent').html(`
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

        $('#paymentMethodRevenueModal').modal('show');

        $.ajax({
            url: '/admin/dashboard/payment-method-revenue',
            method: 'GET',
            data: {
                from_date: fromDate,
                to_date: toDate
            },
            success: function(response) {
    let html = '<div class="table-responsive">';
    html += '<table class="table table-striped">';
    html += '<thead><tr><th>Payment Method</th><th>Revenue (Rs)</th></tr></thead><tbody>';

    if (response.data.payment_modes && response.data.payment_modes.length > 0) {
        response.data.payment_modes.forEach(function(item) {
            html += `
                <tr>
                    <td>${item.payment_title}</td>
                    <td>${parseFloat(item.total_amount).toFixed(2)}</td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="2" class="text-center">No data available</td></tr>';
    }

    html += `
    <div class="mt-3 d-flex justify-content-end">
        <table class="table mb-0">
            <tr>
                <td class="pe-4 text-muted text-end">Total Revenue:</td>
                <td class="text-end">Rs ${parseFloat(response.data.total_revenue || 0).toFixed(2)}</td>
            </tr>
            <tr>
                <td class="pe-4 text-muted text-end">Total Discount:</td>
                <td class="text-end">Rs ${parseFloat(response.data.total_discount || 0).toFixed(2)}</td>
            </tr>
            <tr class="border-top">
                <td class="pe-4 fw-bold text-end">Net Revenue:</td>
                <td class="fw-bold text-end ">
                    Rs ${parseFloat(response.data.net_revenue || 0).toFixed(2)}
                </td>
            </tr>
        </table>
    </div>
`;


    $('#paymentMethodRevenueContent').html(html);
}

        });
    }

    function centerText(text, lineLength = 32) {
        if (text.length >= lineLength) return text; // too long, don’t pad
        let spaces = Math.floor((lineLength - text.length) / 2);
        return ' '.repeat(spaces) + text;
    }
    function wrapText(text, width) {
        let result = [];
        while (text.length > width) {
            result.push(text.slice(0, width));
            text = text.slice(width);
        }
        if (text.length > 0) result.push(text);
        return result;
    }

    // Left-align (for item text)
    function padText(text, width) {
        text = text.toString();
        if (text.length > width) return text.slice(0, width);
        return text + ' '.repeat(width - text.length);
    }

    // Right-align (for Qty, Rate, Amount)
    function padTextRight(text, width) {
        text = text.toString();
        if (text.length > width) return text.slice(0, width);
        return ' '.repeat(width - text.length) + text;
    }


    function padTextLeft(text, width) {
        text = text.toString();
        if (text.length > width) return text.slice(0, width);
        return text + ' '.repeat(width - text.length); // left align
    }





    function tableRow(sn, item, qty, rate, amount) {
        const colWidths = { sn:3, item:14, qty:3, rate:6, amount:8 }; // 32 chars total
        let lines = [];

        // Split item into first line + remaining lines
        let firstLine = item.slice(0, colWidths.item);
        let remaining = item.slice(colWidths.item);

        // First line: SN + first part of item + Qty + Rate + Amount (right-aligned)
        lines.push(
            padText(sn, colWidths.sn) +
            padText(firstLine, colWidths.item) +
            padTextRight(qty, colWidths.qty) +
            padTextRight(rate, colWidths.rate) +
            padTextRight(amount, colWidths.amount)
        );

        // Remaining lines of item: only item column
        if (remaining.length > 0) {
            const wrapped = wrapText(remaining, colWidths.item);
            wrapped.forEach(line => {
                lines.push(
                    padText('', colWidths.sn) +
                    padText(line, colWidths.item) +
                    padText('', colWidths.qty) +
                    padText('', colWidths.rate) +
                    padText('', colWidths.amount)
                );
            });
        }

        return lines.join('\n');
    }

    function padLineRight(text, width) {
        if (text.length > width) return text.slice(0, width);
        return ' '.repeat(width - text.length) + text;
    }
    function padLineRightWithFixedColon(label, value, totalWidth = 34, colonPos = 16) {
        // Pad label so colon is at colonPos
        let paddedLabel = label;
        if (label.length < colonPos) {
            paddedLabel += ' '.repeat(colonPos - label.length);
        }
        // Text before number
        const textBeforeNumber = `${paddedLabel}: `;
        const numberStr = value.toString();
        // Pad spaces so number ends at totalWidth
        const spaces = totalWidth - textBeforeNumber.length - numberStr.length;
        return ' '.repeat(spaces > 0 ? spaces : 0) + textBeforeNumber + numberStr;
    }




    function buildPrintText(invoice, details) {
        let total = 0;
        let lines = [];
        lines.push(centerText('TWELVE SEVEN GROCERY &'));
        lines.push(centerText('LIQUORLAND PVT. LTD.'));
        lines.push(centerText('KATHMANDU, NAREPHAT'));
        lines.push(centerText('PAN No. : 622494670'));
        lines.push(centerText('CONTACT : 01-5149303'));
        lines.push(centerText('ABBREVIATED INVOICE'));
        lines.push(`Bill No: T${invoice.id}-82/83`);
        lines.push(`Date: ${invoice.created_at}`);
        lines.push(`Payment: ${details[0]?.payment_mode}`);
        lines.push('----------------------------------');
        lines.push('SN  PARTICULARS   QTY RATE  AMOUNT');
        lines.push('----------------------------------');
        details.forEach((item, index) => {
            total += parseInt(item.amount); // keep total as integer

            lines.push(
                tableRow(
                    index + 1,
                    item.item,
                    parseInt(item.qty),
                    parseInt(item.rate),
                    parseInt(item.amount)
                )
            );
        });
        // Example: calculate discount and net amount
        let discount = invoice.discount ? parseInt(invoice.discount) : 0; // assume discount field exists
        let netAmount = total - discount;

        // Right-aligned amounts
        const colWidth = 34; // 58mm printer, 32 chars per line
        lines.push('----------------------------------');
        lines.push(padLineRightWithFixedColon('Gross Amount', total));
        lines.push(padLineRightWithFixedColon('Discount', discount));
        lines.push(padLineRightWithFixedColon('Net Amount', netAmount));
        lines.push('----------------------------------');
        lines.push('Exchange within 24 Hours .');
        lines.push('Thank you for visiting us .');
        lines.push('----------------------------------');
        return lines.join('\n');
    }
    function printInvoice() {
        if (!currentInvoice || !currentDetails.length) {
            alert('No invoice data');
            return;
        }

        const text = buildPrintText(currentInvoice, currentDetails);
        const frame = document.getElementById('printFrame');
        const doc = frame.contentWindow.document;

        doc.open();
        doc.write(`
            <html>
            <head>
                <style>
                    @page { size: 80mm auto; margin: 0; }
                    body {
                        margin: 0;
                        font-family: monospace;
                        font-size: 12px;
                        color: #000;
                        font-weight: normal; /* ensure text is not bold */
                        white-space: pre-wrap;
                    }
                </style>
            </head>
            <body>${text}</body>
            </html>
        `);
        doc.close();

        frame.contentWindow.focus();
        frame.contentWindow.print();
    }
</script>
@endsection