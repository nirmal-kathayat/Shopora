@extends("layouts.app")
@section("style")
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

    /* ===== Shopora KPI cards (Figma, top accent, no shadow/hover) ===== */
    .shopora-stats-row {
        margin-bottom: 8px;
    }

    .shopora-stat-card {
        --accent: #14b8a6;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-top: 3px solid var(--accent);
        border-radius: 12px;
        padding: 18px 18px 16px;
        height: 100%;
        box-shadow: none !important;
        transition: none !important;
    }

    .shopora-stat-card:hover,
    .shopora-stat-card:focus {
        box-shadow: none !important;
        transform: none !important;
    }

    .shopora-stat-card.is-items { --accent: #14b8a6; }
    .shopora-stat-card.is-revenue { --accent: #008cff; }
    .shopora-stat-card.is-sales { --accent: #0d9488; }
    .shopora-stat-card.is-alerts { --accent: #f59e0b; }

    .shopora-stat-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .shopora-stat-label {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        line-height: 1.2;
    }

    .shopora-stat-value {
        margin: 0 0 8px;
        font-size: 1.55rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .shopora-stat-meta {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
    }

    .shopora-stat-meta.is-up { color: #0d9488; }
    .shopora-stat-meta.is-down { color: #dc2626; }
    .shopora-stat-meta.is-flat { color: #6b7280; }
    .shopora-stat-meta.is-warn { color: #ea580c; }

    .shopora-stat-link {
        display: inline-block;
        margin-top: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #008cff;
        text-decoration: none;
        background: none;
        border: 0;
        padding: 0;
        cursor: pointer;
    }

    .shopora-stat-link:hover {
        color: #006fc9;
        text-decoration: none;
    }

    .shopora-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 22px;
    }

    .shopora-stat-icon.is-teal {
        background: #e6f7f4;
        color: #0d9488;
    }

    .shopora-stat-icon.is-blue {
        background: #008cff;
        color: #ffffff;
        font-size: 18px;
        font-weight: 700;
    }

    .shopora-stat-icon.is-orange {
        background: #fff4e5;
        color: #ea580c;
    }

    /* ===== Revenue by Payment Method ===== */
    .shopora-pay-revenue {
        margin: 16px 0 20px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: stretch;
        gap: 0;
        box-shadow: none !important;
        overflow-x: auto;
    }

    .shopora-pay-title {
        flex: 0 0 112px;
        max-width: 120px;
        padding-right: 14px;
        margin-right: 4px;
        border-right: 1px solid #eef0f3;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1px;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        line-height: 1.3;
        white-space: nowrap;
    }

    .shopora-pay-items {
        display: flex;
        flex: 1 1 auto;
        align-items: stretch;
        min-width: 0;
    }

    .shopora-pay-item {
        flex: 1 1 0;
        min-width: 150px;
        padding: 2px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border-right: 1px solid #eef0f3;
    }

    .shopora-pay-item:last-child {
        border-right: 0;
    }

    .shopora-pay-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
        font-size: 18px;
        font-weight: 700;
    }

    .shopora-pay-icon.is-teal { background: #0d9488; }
    .shopora-pay-icon.is-blue { background: #008cff; }
    .shopora-pay-icon.is-gray { background: #9ca3af; }

    .shopora-pay-meta {
        flex: 1 1 auto;
        min-width: 0;
        padding-top: 1px;
    }

    .shopora-pay-name {
        margin: 0;
        font-size: 13px;
        font-weight: 500;
        color: #4b5563;
        line-height: 1.2;
    }

    .shopora-pay-amount {
        margin: 4px 0 2px;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .shopora-pay-pct {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 500;
        color: #9ca3af;
        line-height: 1.2;
    }

    .shopora-pay-bar {
        height: 4px;
        width: 100%;
        background: #eef0f3;
        border-radius: 999px;
        overflow: hidden;
    }

    .shopora-pay-bar > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        width: 0;
        transition: width 0.35s ease;
    }

    .shopora-pay-bar > span.is-teal { background: #0d9488; }
    .shopora-pay-bar > span.is-blue { background: #008cff; }
    .shopora-pay-bar > span.is-gray { background: #9ca3af; }

    .shopora-pay-empty {
        flex: 1;
        display: flex;
        align-items: center;
        padding: 0 18px;
        color: #9ca3af;
        font-size: 13px;
    }

    @media (max-width: 991px) {
        .shopora-pay-revenue {
            flex-direction: column;
            gap: 14px;
        }

        .shopora-pay-title {
            flex: none;
            max-width: none;
            border-right: 0;
            border-bottom: 1px solid #eef0f3;
            padding: 0 0 12px;
            margin: 0;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 4px;
            white-space: normal;
        }

        .shopora-pay-items {
            flex-wrap: wrap;
        }

        .shopora-pay-item {
            flex: 1 1 180px;
            border-right: 0;
            border-bottom: 1px solid #eef0f3;
            padding: 12px 8px;
        }

        .shopora-pay-item:last-child {
            border-bottom: 0;
        }
    }

    /* ===== Recent sales + low stock panels ===== */
    .shopora-panels-row {
        margin: 0 0 8px;
    }

    .shopora-panel-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 18px 18px 14px;
        height: 100%;
        box-shadow: none !important;
        display: flex;
        flex-direction: column;
    }

    .shopora-panel-card:hover {
        box-shadow: none !important;
        transform: none !important;
    }

    .shopora-panel-title {
        margin: 0 0 14px;
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.3;
    }

    .shopora-panel-table-wrap {
        width: 100%;
        overflow-x: auto;
        flex: 1 1 auto;
    }

    .shopora-panel-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 520px;
    }

    .shopora-panel-table.compact {
        min-width: 0;
    }

    .shopora-panel-table th {
        font-size: 12px;
        font-weight: 600;
        color: #9ca3af;
        text-align: left;
        padding: 0 10px 10px 0;
        border-bottom: 1px solid #eef0f3;
        white-space: nowrap;
    }

    .shopora-panel-table td {
        font-size: 13px;
        color: #374151;
        padding: 12px 10px 12px 0;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        white-space: nowrap;
    }

    .shopora-panel-table tr:last-child td {
        border-bottom: 0;
    }

    .shopora-panel-table .col-num,
    .shopora-panel-table .col-qty,
    .shopora-panel-table .col-stock {
        text-align: center;
    }

    .shopora-panel-table .col-amount {
        text-align: right;
        font-weight: 600;
        color: #111827;
    }

    .shopora-panel-table .col-name {
        white-space: normal;
        min-width: 120px;
    }

    .shopora-inv-link {
        color: #008cff;
        font-weight: 600;
        text-decoration: none;
        background: none;
        border: 0;
        padding: 0;
        cursor: pointer;
    }

    .shopora-inv-link:hover {
        color: #006fc9;
        text-decoration: underline;
    }

    .shopora-pill {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
    }

    .shopora-pill + .shopora-pill {
        margin-left: 4px;
    }

    .shopora-pill.is-cash { background: #ecfdf5; color: #059669; }
    .shopora-pill.is-esewa { background: #e6f7f4; color: #0d9488; }
    .shopora-pill.is-khalti { background: #eef2ff; color: #4f46e5; }
    .shopora-pill.is-card,
    .shopora-pill.is-bank,
    .shopora-pill.is-fonepay { background: #eff6ff; color: #2563eb; }
    .shopora-pill.is-paid { background: #ecfdf5; color: #059669; }
    .shopora-pill.is-low { background: #fff7ed; color: #ea580c; }
    .shopora-pill.is-out { background: #fef2f2; color: #dc2626; }
    .shopora-pill.is-default { background: #f3f4f6; color: #4b5563; }

    .shopora-panel-footer {
        margin-top: 12px;
        padding-top: 4px;
    }

    .shopora-panel-link {
        color: #008cff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .shopora-panel-link:hover {
        color: #006fc9;
        text-decoration: none;
    }

    .shopora-panel-empty {
        padding: 18px 0;
        text-align: center;
        color: #9ca3af;
        font-size: 13px;
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

        <!-- Stats Cards (Figma) -->
        @php
            $itemsChange = $data['itemsChangePercent'] ?? 0;
            $revenueChange = $data['revenueChangePercent'] ?? 0;
            $salesChange = $data['salesChangePercent'] ?? 0;
            $fmtChange = function ($pct) {
                $sign = $pct > 0 ? '+' : '';
                return $sign . number_format((float) $pct, 1) . '% vs previous period';
            };
            $changeClass = function ($pct) {
                if ($pct > 0) return 'is-up';
                if ($pct < 0) return 'is-down';
                return 'is-flat';
            };
        @endphp
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
            <div class="col">
                <div class="shopora-stat-card is-items">
                    <div class="shopora-stat-inner">
                        <div>
                            <p class="shopora-stat-label">Total Items</p>
                            <h3 class="shopora-stat-value" id="statTotalItems">{{ number_format($data['totalInventoryItems']) }}</h3>
                            <p class="shopora-stat-meta {{ $changeClass($itemsChange) }}" id="statItemsChange">{{ $fmtChange($itemsChange) }}</p>
                        </div>
                        <div class="shopora-stat-icon is-teal" aria-hidden="true"><i class='bx bx-package'></i></div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="shopora-stat-card is-revenue">
                    <div class="shopora-stat-inner">
                        <div>
                            <p class="shopora-stat-label">Total Revenue</p>
                            <h3 class="shopora-stat-value" id="statTotalRevenue">Rs {{ number_format($data['totalRevenue'], 2) }}</h3>
                            <p class="shopora-stat-meta {{ $changeClass($revenueChange) }}" id="statRevenueChange">{{ $fmtChange($revenueChange) }}</p>
                            <button type="button" class="shopora-stat-link" onclick="showPaymentMethodRevenue()">View revenue report →</button>
                        </div>
                        <div class="shopora-stat-icon is-blue" aria-hidden="true">₹</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="shopora-stat-card is-sales">
                    <div class="shopora-stat-inner">
                        <div>
                            <p class="shopora-stat-label">Total Sales (Qty)</p>
                            <h3 class="shopora-stat-value" id="statTotalSales">{{ number_format($data['totalSales']) }}</h3>
                            <p class="shopora-stat-meta {{ $changeClass($salesChange) }}" id="statSalesChange">{{ $fmtChange($salesChange) }}</p>
                        </div>
                        <div class="shopora-stat-icon is-teal" aria-hidden="true"><i class='bx bx-cart'></i></div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="shopora-stat-card is-alerts">
                    <div class="shopora-stat-inner">
                        <div>
                            <p class="shopora-stat-label">Stock Alerts</p>
                            <h3 class="shopora-stat-value" id="statStockAlerts">{{ number_format($data['stockAlerts'] ?? 0) }}</h3>
                            <p class="shopora-stat-meta is-warn">Low stock items</p>
                        </div>
                        <div class="shopora-stat-icon is-orange" aria-hidden="true"><i class='bx bxs-error'></i></div>
                    </div>
                </div>
            </div>
        </div><!--end row-->

        <!-- Revenue by Payment Method (Figma) -->
        <div class="shopora-pay-revenue" id="shoporaPaymentRevenue">
            <div class="shopora-pay-title">
                <span>Revenue by</span>
                <span>Payment Method</span>
            </div>
            <div class="shopora-pay-items" id="shoporaPaymentRevenueItems">
                <div class="shopora-pay-empty">Loading payment breakdown…</div>
            </div>
        </div>

        <!-- Recent Sales + Low Stock (Figma) -->
        <div class="row g-3 shopora-panels-row">
            <div class="col-12 col-xl-8">
                <div class="shopora-panel-card">
                    <h3 class="shopora-panel-title">Recent Sales / Invoices</h3>
                    <div class="shopora-panel-table-wrap">
                        <table class="shopora-panel-table">
                            <thead>
                                <tr>
                                    <th class="col-num">#</th>
                                    <th>Invoice No.</th>
                                    <th>Date &amp; Time</th>
                                    <th>Customer</th>
                                    <th>Payment Method</th>
                                    <th class="col-qty">Qty</th>
                                    <th class="col-amount">Amount (Rs)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentSalesBody">
                                <tr><td colspan="8" class="shopora-panel-empty">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="shopora-panel-footer">
                        <a href="{{ route('admin.invoice.index') }}" class="shopora-panel-link">View all invoices →</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="shopora-panel-card">
                    <h3 class="shopora-panel-title">Top Low Stock Items</h3>
                    <div class="shopora-panel-table-wrap">
                        <table class="shopora-panel-table compact">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th class="col-stock">In Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockBody">
                                <tr><td colspan="3" class="shopora-panel-empty">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="shopora-panel-footer">
                        <a href="{{ route('admin.reports.inventoryReport') }}" class="shopora-panel-link">View all low stock items →</a>
                    </div>
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
<!-- Flatpickr for Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // Create payment mode mapping globally
    window.paymentModeMap = {};
    window.paymentModesList = [];
    @foreach($paymentModes as $payment)
    window.paymentModeMap[{{ $payment->id }}] = '{{ $payment->payment_title }}';
    window.paymentModesList.push(@json($payment->payment_title));
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

        bindInvoiceViewer();
        updateDashboard({ loader: false });
    });

    function bindInvoiceViewer() {
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

    function paymentPillClass(title) {
        const name = String(title || '').toLowerCase();
        if (name.includes('cash')) return 'is-cash';
        if (name.includes('esewa')) return 'is-esewa';
        if (name.includes('khalti')) return 'is-khalti';
        if (name.includes('card')) return 'is-card';
        if (name.includes('bank')) return 'is-bank';
        if (name.includes('fone')) return 'is-fonepay';
        return 'is-default';
    }

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function renderRecentSales(rows) {
        const body = $('#recentSalesBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="8" class="shopora-panel-empty">No sales in this date range</td></tr>');
            return;
        }

        let html = '';
        rows.forEach(function(row, index) {
            const payments = (row.payment_methods || []).map(function(title) {
                return '<span class="shopora-pill ' + paymentPillClass(title) + '">' + escapeHtml(title) + '</span>';
            }).join('');

            html += '<tr>' +
                '<td class="col-num">' + (index + 1) + '</td>' +
                '<td><button type="button" class="shopora-inv-link view-invoice" data-id="' + row.id + '">' + escapeHtml(row.invoice_no) + '</button></td>' +
                '<td>' + escapeHtml(row.datetime) + '</td>' +
                '<td>' + escapeHtml(row.customer) + '</td>' +
                '<td>' + (payments || '<span class="shopora-pill is-default">—</span>') + '</td>' +
                '<td class="col-qty">' + Number(row.qty || 0).toLocaleString() + '</td>' +
                '<td class="col-amount">' + parseFloat(row.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '<td><span class="shopora-pill is-paid">' + escapeHtml(row.status || 'Paid') + '</span></td>' +
            '</tr>';
        });
        body.html(html);
    }

    function renderLowStockItems(rows) {
        const body = $('#lowStockBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="3" class="shopora-panel-empty">No low stock items</td></tr>');
            return;
        }

        let html = '';
        rows.forEach(function(row) {
            const isOut = String(row.status || '').toLowerCase().indexOf('out') !== -1;
            html += '<tr>' +
                '<td class="col-name">' + escapeHtml(row.name) + '</td>' +
                '<td class="col-stock">' + Number(row.in_stock || 0).toLocaleString() + '</td>' +
                '<td><span class="shopora-pill ' + (isOut ? 'is-out' : 'is-low') + '">' + escapeHtml(row.status) + '</span></td>' +
            '</tr>';
        });
        body.html(html);
    }
    
    function setStatChange(selector, percent) {
        const el = $(selector);
        if (!el.length) return;

        const pct = parseFloat(percent);
        const safePct = isNaN(pct) ? 0 : pct;
        const sign = safePct > 0 ? '+' : '';
        el.text(sign + safePct.toFixed(1) + '% vs previous period');
        el.removeClass('is-up is-down is-flat');
        if (safePct > 0) el.addClass('is-up');
        else if (safePct < 0) el.addClass('is-down');
        else el.addClass('is-flat');
    }

    function paymentMethodVisual(title) {
        const name = String(title || '').toLowerCase();

        if (name.includes('cash')) {
            return { tone: 'teal', iconHtml: '<i class="bx bx-money"></i>' };
        }
        if (name.includes('esewa')) {
            return { tone: 'teal', iconHtml: '<span>e</span>' };
        }
        if (name.includes('fonepay') || name.includes('fone')) {
            return { tone: 'teal', iconHtml: '<i class="bx bx-mobile"></i>' };
        }
        if (name.includes('khalti')) {
            return { tone: 'blue', iconHtml: '<span>K</span>' };
        }
        if (name.includes('card') || name.includes('bank')) {
            return { tone: 'blue', iconHtml: '<i class="bx bx-credit-card"></i>' };
        }
        if (name.includes('other')) {
            return { tone: 'gray', iconHtml: '<i class="bx bx-dots-horizontal-rounded"></i>' };
        }

        return { tone: 'gray', iconHtml: '<i class="bx bx-wallet"></i>' };
    }

    function formatRs(amount) {
        return 'Rs ' + parseFloat(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function renderPaymentMethodRevenue(payload) {
        const container = $('#shoporaPaymentRevenueItems');
        const apiModes = (payload && payload.payment_modes) ? payload.payment_modes : [];
        const amountByTitle = {};

        apiModes.forEach(function(item) {
            amountByTitle[item.payment_title] = parseFloat(item.total_amount || 0);
        });

        const titles = (window.paymentModesList && window.paymentModesList.length)
            ? window.paymentModesList.slice()
            : apiModes.map(function(item) { return item.payment_title; });

        apiModes.forEach(function(item) {
            if (titles.indexOf(item.payment_title) === -1) {
                titles.push(item.payment_title);
            }
        });

        let total = parseFloat((payload && payload.total_revenue) || 0);
        if (!total) {
            total = titles.reduce(function(sum, title) {
                return sum + (amountByTitle[title] || 0);
            }, 0);
        }

        if (!titles.length) {
            container.html('<div class="shopora-pay-empty">No payment methods configured</div>');
            return;
        }

        let html = '';
        titles.forEach(function(title) {
            const amount = amountByTitle[title] || 0;
            const pct = total > 0 ? (amount / total) * 100 : 0;
            const visual = paymentMethodVisual(title);
            const barWidth = Math.max(0, Math.min(100, pct));

            html += '<div class="shopora-pay-item">' +
                '<div class="shopora-pay-icon is-' + visual.tone + '">' + visual.iconHtml + '</div>' +
                '<div class="shopora-pay-meta">' +
                    '<p class="shopora-pay-name">' + $('<div>').text(title).html() + '</p>' +
                    '<p class="shopora-pay-amount">' + formatRs(amount) + '</p>' +
                    '<p class="shopora-pay-pct">' + pct.toFixed(1) + '%</p>' +
                    '<div class="shopora-pay-bar"><span class="is-' + visual.tone + '" style="width:' + barWidth + '%"></span></div>' +
                '</div>' +
            '</div>';
        });

        container.html(html);
    }

    function fetchPaymentMethodRevenue(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.paymentMethodRevenue') }}",
            method: 'GET',
            data: {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val()
            },
            success: function(response) {
                renderPaymentMethodRevenue(response.data || {});
            },
            error: function() {
                $('#shoporaPaymentRevenueItems').html(
                    '<div class="shopora-pay-empty">Unable to load payment breakdown</div>'
                );
            },
            complete: function() {
                if (typeof done === 'function') done();
            }
        });
    }

    function updateDashboard(options) {
        options = options || {};
        const withLoader = options.loader === true;
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();
        let pending = 2;

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
                $('#statTotalItems').text(Number(response.totalInventoryItems || 0).toLocaleString());
                $('#statTotalRevenue').text('Rs ' + parseFloat(response.totalRevenue || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#statTotalSales').text(Number(response.totalSales || 0).toLocaleString());
                $('#statStockAlerts').text(Number(response.stockAlerts || 0).toLocaleString());

                setStatChange('#statItemsChange', response.itemsChangePercent);
                setStatChange('#statRevenueChange', response.revenueChangePercent);
                setStatChange('#statSalesChange', response.salesChangePercent);

                renderRecentSales(response.recentSales || []);
                renderLowStockItems(response.lowStockItems || []);
            },
            error: function() {
                $('#recentSalesBody').html('<tr><td colspan="8" class="shopora-panel-empty">Unable to load recent sales</td></tr>');
                $('#lowStockBody').html('<tr><td colspan="3" class="shopora-panel-empty">Unable to load low stock items</td></tr>');
            },
            complete: markDone
        });

        fetchPaymentMethodRevenue(markDone);
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