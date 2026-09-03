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
        transition: none;
    }

    .shopora-stat-card.is-clickable {
        cursor: pointer;
    }

    .shopora-stat-card.is-clickable:focus-visible {
        outline: 2px solid #008cff;
        outline-offset: 2px;
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
    .shopora-stat-card.is-profit { --accent: #16a34a; }
    .shopora-stat-card.is-purchase { --accent: #4f46e5; }
    .shopora-stat-card.is-inventory { --accent: #0d9488; }
    .shopora-stat-card.is-info { --accent: #94a3b8; }

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

    .shopora-dash-modal-period {
        margin: 0 0 14px;
        font-size: 13px;
        color: #6b7280;
    }

    .shopora-dash-modal-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .shopora-dash-modal-stat {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 14px;
        background: #f9fafb;
    }

    .shopora-dash-modal-stat-label {
        margin: 0 0 4px;
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }

    .shopora-dash-modal-stat-value {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .shopora-dash-modal-stat.is-highlight {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .shopora-dash-modal-stat.is-highlight .shopora-dash-modal-stat-value {
        color: #008cff;
    }

    .shopora-dash-modal-table th {
        font-size: 12px;
        font-weight: 600;
        color: #9ca3af;
        border-bottom: 1px solid #eef0f3;
    }

    .shopora-dash-modal-table td {
        font-size: 13px;
        color: #374151;
        vertical-align: middle;
    }

    .shopora-dash-modal-table .col-amount {
        text-align: right;
        white-space: nowrap;
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

    .shopora-stat-icon.is-green {
        background: #e7f7ec;
        color: #16a34a;
    }

    .shopora-stat-icon.is-indigo {
        background: #eef0ff;
        color: #4f46e5;
    }

    .shopora-stat-icon.is-red {
        background: #fdecec;
        color: #dc2626;
    }

    .shopora-stat-card.is-danger { --accent: #dc2626; }

    .shopora-stat-value.shopora-stat-value-sm,
    .shopora-stat-value-sm {
        font-size: 19px;
        line-height: 1.25;
        word-break: break-word;
    }

    /* ===== Dashboard tabs ===== */
    .shopora-tabs {
        display: flex;
        gap: 4px;
        border-bottom: 1px solid #e4e4e4;
        margin: 18px 0 0;
        padding: 0;
        list-style: none;
        flex-wrap: wrap;
    }

    .shopora-tab {
        border: 0;
        background: transparent;
        padding: 10px 16px;
        font-size: 14.5px;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: color 0.15s ease, border-color 0.15s ease;
    }

    .shopora-tab i {
        font-size: 18px;
    }

    .shopora-tab:hover {
        color: #008cff;
    }

    .shopora-tab.active {
        color: #008cff;
        border-bottom-color: #008cff;
    }

    .shopora-tab-content {
        margin-bottom: 8px;
        padding-top: 20px;
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

    .shopora-pay-icon.is-logo {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 5px;
        color: inherit;
    }

    .shopora-pay-icon.is-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

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

    .shopora-charts-row {
        margin: 0 0 8px;
    }

    .shopora-chart-head {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-bottom: 6px;
    }

    .shopora-chart-sub {
        font-size: 12.5px;
        color: #9ca3af;
    }

    .shopora-chart-body {
        position: relative;
        flex: 1 1 auto;
        min-height: 300px;
    }

    .shopora-chart-empty {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 14px;
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

    /* second line under a name cell: contact, joined date, that sort of thing */
    .shopora-cell-sub {
        display: block;
        font-size: 11px;
        font-weight: 400;
        color: #9ca3af;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .shopora-panel-link {
        color: #008cff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        background: none;
        border: 0;
        padding: 0;
        cursor: pointer;
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

    /* ===================== Dark mode ===================== */
    html.dark-theme .shopora-stat-card,
    html.dark-theme .shopora-panel-card,
    html.dark-theme .shopora-date-filter {
        background: #12181a;
        border-color: #2a3236;
    }

    html.dark-theme .shopora-stat-label {
        color: #aab3bb;
    }

    html.dark-theme .shopora-stat-value,
    html.dark-theme .shopora-stat-value-sm,
    html.dark-theme .shopora-panel-title {
        color: #eef1f3;
    }

    html.dark-theme .shopora-chart-sub,
    html.dark-theme .shopora-stat-meta.is-flat {
        color: #8b949c;
    }

    html.dark-theme .shopora-date-filter .date-field label {
        color: #cbd2d8;
    }

    html.dark-theme .shopora-date-filter .date-input-wrap input {
        background: #0d1315;
        border-color: #2a3236;
        color: #eef1f3;
    }

    html.dark-theme .shopora-date-filter .date-input-wrap .cal-icon {
        color: #8b949c;
    }

    html.dark-theme .shopora-date-filter .range-tab {
        background: #0d1315;
        border-color: #2a3236;
        color: #cbd2d8;
    }

    html.dark-theme .shopora-date-filter .range-tab.active {
        background: rgba(0, 140, 255, 0.16);
        border-color: #008cff;
        color: #4aa8ff;
    }

    html.dark-theme .shopora-tabs {
        border-bottom-color: #2a3236;
    }

    html.dark-theme .shopora-tab {
        color: #8b949c;
    }

    html.dark-theme .shopora-tab.active,
    html.dark-theme .shopora-tab:hover {
        color: #4aa8ff;
    }

    html.dark-theme .shopora-panel-table th {
        color: #8b949c;
        border-color: #2a3236;
    }

    html.dark-theme .shopora-panel-table td {
        color: #d4d8db;
        border-color: #232a2e;
    }

    html.dark-theme .shopora-panel-table tbody tr:hover td {
        background: rgba(255, 255, 255, 0.03);
    }

    html.dark-theme .shopora-panel-empty {
        color: #7a848c;
    }

    html.dark-theme .modal-content {
        background: #12181a;
        color: #eef1f3;
    }

    html.dark-theme .modal-header,
    html.dark-theme .modal-footer {
        border-color: #2a3236;
    }

    html.dark-theme .shopora-dash-modal-period {
        color: #8b949c;
    }

    html.dark-theme .shopora-dash-modal-stat {
        background: #0d1315;
        border-color: #2a3236;
    }

    html.dark-theme .shopora-dash-modal-stat-label {
        color: #8b949c;
    }

    html.dark-theme .shopora-dash-modal-stat-value {
        color: #eef1f3;
    }

    html.dark-theme .shopora-dash-modal-table th {
        color: #8b949c;
    }

    html.dark-theme .shopora-dash-modal-table td {
        color: #d4d8db;
        border-color: #232a2e;
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
            $profitChange = $data['profitChangePercent'] ?? 0;
            $purchasesChange = $data['purchasesChangePercent'] ?? 0;
            $newCustomersChange = $data['newCustomersChangePercent'] ?? 0;
            $fmtChange = function ($pct) {
                $sign = $pct > 0 ? '+' : '';
                return $sign . number_format((float) $pct, 1) . '% vs previous period';
            };
            $changeClass = function ($pct) {
                if ($pct > 0) return 'is-up';
                if ($pct < 0) return 'is-down';
                return 'is-flat';
            };
            $rs = fn ($v) => 'Rs ' . number_format((float) $v, 2);
            // reusable KPI card renderer
            $card = function ($label, $valueHtml, $valueClass, $iconClass, $iconHtml, $action = null, $metaHtml = '', $link = null, $typeClass = '') {
                $role = in_array($action, ['purchases', 'inventory', 'customers'], true) ? 'link' : 'button';
                $clickable = $action ? ' is-clickable" data-stat-action="' . $action . '" role="' . $role . '" tabindex="0' : '';
                $html = '<div class="col"><div class="shopora-stat-card ' . $typeClass . $clickable . '">';
                $html .= '<div class="shopora-stat-inner"><div>';
                $html .= '<p class="shopora-stat-label">' . $label . '</p>';
                $html .= '<h3 class="shopora-stat-value ' . $valueClass . '">' . $valueHtml . '</h3>';
                $html .= $metaHtml;
                if ($link) {
                    $html .= '<span class="shopora-stat-link">' . $link . '</span>';
                }
                $html .= '</div><div class="shopora-stat-icon ' . $iconClass . '" aria-hidden="true">' . $iconHtml . '</div>';
                $html .= '</div></div></div>';
                return $html;
            };
            $metaChange = fn ($pct, $cls) => '<p class="shopora-stat-meta ' . $changeClass($pct) . ' ' . $cls . '">' . $fmtChange($pct) . '</p>';
            $metaText = fn ($html, $cls = 'is-flat') => '<p class="shopora-stat-meta ' . $cls . '">' . $html . '</p>';
        @endphp
        <!-- Dashboard tabs -->
        <ul class="nav shopora-tabs" id="dashTabs" role="tablist">
            <li role="presentation">
                <button class="shopora-tab active" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">
                    <i class='bx bx-line-chart'></i> Overview
                </button>
            </li>
            <li role="presentation">
                <button class="shopora-tab" data-bs-toggle="tab" data-bs-target="#tab-sales" type="button" role="tab">
                    <i class='bx bx-cart'></i> Sales
                </button>
            </li>
            <li role="presentation">
                <button class="shopora-tab" data-bs-toggle="tab" data-bs-target="#tab-customers" type="button" role="tab">
                    <i class='bx bx-group'></i> Customers
                </button>
            </li>
            <li role="presentation">
                <button class="shopora-tab" data-bs-toggle="tab" data-bs-target="#tab-inventory" type="button" role="tab">
                    <i class='bx bx-package'></i> Inventory
                </button>
            </li>
            <li role="presentation">
                <button class="shopora-tab" data-bs-toggle="tab" data-bs-target="#tab-purchases" type="button" role="tab">
                    <i class='bx bx-receipt'></i> Purchases
                </button>
            </li>
        </ul>

        <div class="tab-content shopora-tab-content">
            <!-- ===== Overview: charts ===== -->
            <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
                    {!! $card('Total Revenue', $rs($data['totalRevenue']), 'js-revenue', 'is-blue', '₹', 'revenue', $metaChange($revenueChange, 'js-revenue-change'), 'View revenue breakdown →', 'is-revenue') !!}
                    {!! $card('Gross Profit', $rs($data['grossProfit'] ?? 0), 'js-profit', 'is-green', "<i class='bx bx-trending-up'></i>", 'profit', $metaChange($profitChange, 'js-profit-change'), '<span class="js-margin">' . number_format($data['profitMargin'] ?? 0, 1) . '</span>% margin · View breakdown →', 'is-profit') !!}
                    {!! $card('Total Sales (Qty)', number_format($data['totalSales']), 'js-sales', 'is-teal', "<i class='bx bx-cart'></i>", 'sales', $metaChange($salesChange, 'js-sales-change'), 'View sales summary →', 'is-sales') !!}
                    {!! $card('Stock Alerts', number_format($data['stockAlerts'] ?? 0), 'js-alerts', 'is-orange', "<i class='bx bxs-error'></i>", 'stock', $metaText('Low stock items', 'is-warn'), 'View low stock items →', 'is-alerts') !!}
                </div>
                <div class="row g-3 shopora-charts-row">
                    <div class="col-12 col-xl-8">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Sales Trend</h3>
                                <span class="shopora-chart-sub" id="salesTrendSub">Revenue over the selected period</span>
                            </div>
                            <div class="shopora-chart-body">
                                <div id="salesTrendChart"></div>
                                <div class="shopora-chart-empty" id="salesTrendEmpty" hidden>No sales in this period</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Sales by Category</h3>
                                <span class="shopora-chart-sub">Revenue share by category</span>
                            </div>
                            <div class="shopora-chart-body">
                                <div id="categoryChart"></div>
                                <div class="shopora-chart-empty" id="categoryEmpty" hidden>No category sales in this period</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Sales: recent invoices + best sellers + top customers ===== -->
            <div class="tab-pane fade" id="tab-sales" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
                    {!! $card('Total Revenue', $rs($data['totalRevenue']), 'js-revenue', 'is-blue', '₹', 'revenue', $metaChange($revenueChange, 'js-revenue-change'), 'View revenue breakdown →', 'is-revenue') !!}
                    {!! $card('Gross Profit', $rs($data['grossProfit'] ?? 0), 'js-profit', 'is-green', "<i class='bx bx-trending-up'></i>", 'profit', $metaChange($profitChange, 'js-profit-change'), '<span class="js-margin">' . number_format($data['profitMargin'] ?? 0, 1) . '</span>% margin · View breakdown →', 'is-profit') !!}
                    {!! $card('Total Sales (Qty)', number_format($data['totalSales']), 'js-sales', 'is-teal', "<i class='bx bx-cart'></i>", 'sales', $metaChange($salesChange, 'js-sales-change'), 'View sales summary →', 'is-sales') !!}
                    {!! $card('Avg Order Value', $rs($data['avgOrderValue'] ?? 0), 'js-aov', 'is-indigo', "<i class='bx bx-receipt'></i>", null, $metaText('<span class="js-invcount">' . number_format($data['invoiceCount'] ?? 0) . '</span> invoices'), null, 'is-purchase') !!}
                </div>
                <div class="row g-3">
                    <div class="col-12">
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
                    <div class="col-12 col-lg-6">
                        <div class="shopora-panel-card">
                            <h3 class="shopora-panel-title">Best-Selling Products</h3>
                            <div class="shopora-panel-table-wrap">
                                <table class="shopora-panel-table compact">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="col-qty">Qty Sold</th>
                                            <th class="col-amount">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bestSellersBody">
                                        <tr><td colspan="3" class="shopora-panel-empty">Loading…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="shopora-panel-card">
                            <h3 class="shopora-panel-title">Top Customers</h3>
                            <div class="shopora-panel-table-wrap">
                                <table class="shopora-panel-table compact">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th class="col-qty">Orders</th>
                                            <th class="col-amount">Spent (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topCustomersBody">
                                        <tr><td colspan="3" class="shopora-panel-empty">Loading…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="shopora-panel-footer">
                                <a href="{{ route('admin.customer') }}" class="shopora-panel-link">View all customers →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Customers: who is buying, and who just arrived ===== -->
            <div class="tab-pane fade" id="tab-customers" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
                    {!! $card('Total Customers', number_format($data['totalCustomers'] ?? 0), 'js-custtotal', 'is-blue', "<i class='bx bx-group'></i>", 'customers', $metaText('<span class="js-custregistered">' . number_format($data['registeredCustomers'] ?? 0) . '</span> with a storefront account'), 'View customers &rarr;', 'is-revenue') !!}
                    {!! $card('New Customers', number_format($data['newCustomers'] ?? 0), 'js-custnew', 'is-teal', "<i class='bx bx-user-plus'></i>", null, $metaChange($newCustomersChange, 'js-custnew-change'), null, 'is-sales') !!}
                    {!! $card('Repeat Rate', '<span class="js-repeatrate">' . number_format($data['repeatRate'] ?? 0, 1) . '</span>%', '', 'is-green', "<i class='bx bx-refresh'></i>", null, $metaText('<span class="js-repeatcount">' . number_format($data['repeatCustomers'] ?? 0) . '</span> of <span class="js-buyercount">' . number_format($data['buyingCustomers'] ?? 0) . '</span> buyers came back'), null, 'is-profit') !!}
                    {!! $card('Avg Customer Value', $rs($data['avgCustomerValue'] ?? 0), 'js-custvalue', 'is-indigo', "<i class='bx bx-wallet'></i>", null, $metaText('<span class="js-activecust">' . number_format($data['activeCustomers'] ?? 0) . '</span> bought in this period'), null, 'is-purchase') !!}
                </div>
                <div class="row g-3">
                    <div class="col-12 col-xl-5">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">New Customers</h3>
                                <span class="shopora-chart-sub" id="customerTrendSub">Sign-ups over the selected period</span>
                            </div>
                            <div class="shopora-chart-body">
                                <div id="customerTrendChart"></div>
                                <div class="shopora-chart-empty" id="customerTrendEmpty" hidden>No new customers in this period</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Newest Customers</h3>
                                <span class="shopora-chart-sub">Lifetime orders and spend</span>
                            </div>
                            <div class="shopora-panel-table-wrap">
                                <table class="shopora-panel-table compact">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Type</th>
                                            <th class="col-qty">Orders</th>
                                            <th class="col-amount">Spent (Rs)</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentCustomersBody">
                                        <tr><td colspan="5" class="shopora-panel-empty">Loading&hellip;</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="shopora-panel-footer">
                                <a href="{{ route('admin.customer') }}" class="shopora-panel-link">Go to customers &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Inventory: low stock ===== -->
            <div class="tab-pane fade" id="tab-inventory" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
                    {!! $card('Inventory Value', $rs($data['inventoryValue'] ?? 0), 'js-invvalue', 'is-teal', "<i class='bx bx-package'></i>", 'inventory', $metaText('Stock on hand at sell price'), 'View inventory items →', 'is-inventory') !!}
                    {!! $card('Units in Stock', number_format($data['inventoryUnits'] ?? 0), 'js-invunits', 'is-blue', "<i class='bx bx-cube'></i>", null, $metaText('Across all products'), null, 'is-revenue') !!}
                    {!! $card('Low Stock', number_format($data['stockAlerts'] ?? 0), 'js-alerts', 'is-orange', "<i class='bx bxs-error'></i>", 'stock', $metaText('At or below reorder level', 'is-warn'), 'View low stock items →', 'is-alerts') !!}
                    {!! $card('Out of Stock', number_format($data['outOfStock'] ?? 0), 'js-outofstock', 'is-red', "<i class='bx bx-x-circle'></i>", null, $metaText('Items with zero stock', 'is-down'), null, 'is-danger') !!}
                </div>
                <div class="row g-3">
                    <div class="col-12 col-xl-5">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Stock Value by Category</h3>
                                <span class="shopora-chart-sub">Current stock on hand at sell price</span>
                            </div>
                            <div class="shopora-chart-body">
                                <div id="invCategoryChart"></div>
                                <div class="shopora-chart-empty" id="invCategoryEmpty" hidden>No stock on hand</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Low Stock Items</h3>
                                <span class="shopora-chart-sub">Items at or below the {{ 10 }}-unit reorder level</span>
                            </div>
                            <div class="shopora-panel-table-wrap">
                                <table class="shopora-panel-table">
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
                                <button type="button" class="shopora-panel-link" id="viewAllLowStockBtn">View all low stock items →</button>
                                <a href="{{ route('admin.inventoryItem') }}" class="shopora-panel-link">Go to inventory →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Purchases: recent purchases ===== -->
            <div class="tab-pane fade" id="tab-purchases" role="tabpanel">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 shopora-stats-row">
                    {!! $card('Total Purchases', $rs($data['totalPurchases'] ?? 0), 'js-purchases', 'is-indigo', "<i class='bx bx-receipt'></i>", 'purchases', $metaChange($purchasesChange, 'js-purchases-change'), 'View purchases →', 'is-purchase') !!}
                    {!! $card('Purchase Bills', number_format($data['purchaseCount'] ?? 0), 'js-purchasecount', 'is-blue', "<i class='bx bx-file'></i>", null, $metaText('In selected period'), null, 'is-revenue') !!}
                    {!! $card('Avg per Bill', $rs($data['avgPurchase'] ?? 0), 'js-avgpurchase', 'is-teal', "<i class='bx bx-calculator'></i>", null, $metaText('Average purchase value'), null, 'is-sales') !!}
                    {!! $card('Top Vendor', '<span class="js-topvendor">' . e($data['topVendorName'] ?? '—') . '</span>', 'shopora-stat-value-sm', 'is-green', "<i class='bx bx-store'></i>", null, $metaText('Rs <span class="js-topvendor-amount">' . number_format($data['topVendorAmount'] ?? 0, 2) . '</span>'), null, 'is-profit') !!}
                </div>
                <div class="row g-3">
                    <div class="col-12 col-xl-5">
                        <div class="shopora-panel-card">
                            <div class="shopora-chart-head">
                                <h3 class="shopora-panel-title mb-0">Top Vendors</h3>
                                <span class="shopora-chart-sub">Purchase amount by vendor</span>
                            </div>
                            <div class="shopora-chart-body">
                                <div id="vendorChart"></div>
                                <div class="shopora-chart-empty" id="vendorEmpty" hidden>No purchases in this period</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="shopora-panel-card">
                            <h3 class="shopora-panel-title">Recent Purchases</h3>
                            <div class="shopora-panel-table-wrap">
                                <table class="shopora-panel-table">
                                    <thead>
                                        <tr>
                                            <th class="col-num">#</th>
                                            <th>Vendor</th>
                                            <th>Bill Date</th>
                                            <th class="col-amount">Total (Rs)</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentPurchasesBody">
                                        <tr><td colspan="5" class="shopora-panel-empty">Loading…</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="shopora-panel-footer">
                                <a href="{{ route('admin.purchaseInventory') }}" class="shopora-panel-link">View all purchases →</a>
                            </div>
                        </div>
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

    <!-- Low stock items modal -->
    <div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lowStockModalLabel">Low Stock Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="shopora-panel-table-wrap px-3 pt-2">
                        <table class="shopora-panel-table compact w-100">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th class="col-stock">In Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="lowStockModalBody">
                                <tr><td colspan="3" class="shopora-panel-empty">Loading…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="{{ route('admin.purchaseInventory') }}" class="shopora-panel-link">Add purchase stock →</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section("script")
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Flatpickr for Date Range Picker -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- ApexCharts (bundled, offline) -->
<script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>

<script>
    // Create payment mode mapping globally
    window.paymentModeMap = {};
    window.paymentModesList = [];
    window.paymentLogoBase = "{{ asset('assets/images/payment-methods') }}";
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

        $('#viewAllLowStockBtn').on('click', function() {
            openLowStockModal();
        });

        $('.shopora-stat-card[data-stat-action]').on('click', function() {
            handleStatCardAction($(this).data('stat-action'));
        });

        $('.shopora-stat-card[data-stat-action]').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleStatCardAction($(this).data('stat-action'));
            }
        });

        // render lazy charts when their tab first becomes visible (correct width)
        $('#dashTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).data('bs-target');
            if (target === '#tab-inventory') {
                renderInvCategoryChart();
            } else if (target === '#tab-purchases') {
                renderVendorChart();
            } else if (target === '#tab-customers') {
                renderCustomerTrend();
            }
        });

        // recolour charts when dark mode is toggled
        $('.dark-mode').on('click', function () {
            setTimeout(reThemeCharts, 60);
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

    function renderLowStockItems(rows, targetBody) {
        const body = targetBody || $('#lowStockBody');
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

    function renderBestSellers(rows) {
        const body = $('#bestSellersBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="3" class="shopora-panel-empty">No sales in this period</td></tr>');
            return;
        }
        let html = '';
        rows.forEach(function(r) {
            html += '<tr>' +
                '<td class="col-name">' + escapeHtml(r.name) + '</td>' +
                '<td class="col-qty">' + Number(r.qty || 0).toLocaleString() + '</td>' +
                '<td class="col-amount">' + Number(r.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '</tr>';
        });
        body.html(html);
    }

    function renderTopCustomers(rows) {
        const body = $('#topCustomersBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="3" class="shopora-panel-empty">No customer sales in this period</td></tr>');
            return;
        }
        let html = '';
        rows.forEach(function(r) {
            html += '<tr>' +
                '<td class="col-name">' + escapeHtml(r.name) + '</td>' +
                '<td class="col-qty">' + Number(r.orders || 0).toLocaleString() + '</td>' +
                '<td class="col-amount">' + Number(r.spend || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '</tr>';
        });
        body.html(html);
    }

    function renderRecentCustomers(rows) {
        const body = $('#recentCustomersBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="5" class="shopora-panel-empty">No customers yet</td></tr>');
            return;
        }
        let html = '';
        rows.forEach(function(r) {
            const pill = r.registered
                ? '<span class="shopora-pill is-paid">Online</span>'
                : '<span class="shopora-pill is-default">Walk-in</span>';
            html += '<tr>' +
                '<td class="col-name">' + escapeHtml(r.name) +
                    '<span class="shopora-cell-sub">' + escapeHtml(r.contact || '') + '</span></td>' +
                '<td>' + pill + '</td>' +
                '<td class="col-qty">' + Number(r.orders || 0).toLocaleString() + '</td>' +
                '<td class="col-amount">' + Number(r.spend || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '<td>' + escapeHtml(r.joined || '-') + '</td>' +
            '</tr>';
        });
        body.html(html);
    }

    function renderRecentPurchases(rows) {
        const body = $('#recentPurchasesBody');
        if (!rows || !rows.length) {
            body.html('<tr><td colspan="5" class="shopora-panel-empty">No purchases in this period</td></tr>');
            return;
        }
        const base = "{{ url('admin/purchaseInventory/view') }}";
        let html = '';
        rows.forEach(function(r, i) {
            html += '<tr>' +
                '<td class="col-num">' + (i + 1) + '</td>' +
                '<td class="col-name">' + escapeHtml(r.vendor) + '</td>' +
                '<td>' + escapeHtml(r.bill_date || '-') + '</td>' +
                '<td class="col-amount">' + Number(r.total || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '<td><a href="' + base + '/' + r.id + '" class="shopora-panel-link">View →</a></td>' +
            '</tr>';
        });
        body.html(html);
    }

    function showProfitModal() {
        const modalEl = document.getElementById('profitModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        showModalLoader('#profitContent');
        modal.show();

        $.ajax({
            url: "{{ route('admin.dashboard.profitBreakdown') }}",
            method: 'GET',
            data: { from_date: $('#fromDate').val(), to_date: $('#toDate').val() },
            success: function(response) { renderProfitModal(response.data || {}); },
            error: function() { $('#profitContent').html('<p class="text-center text-muted py-4">Unable to load profit summary</p>'); }
        });
    }

    function renderProfitModal(data) {
        const cats = data.categories || [];
        const revenue = parseFloat(data.total_revenue || 0);
        const cost = parseFloat(data.total_cost || 0);
        const profit = parseFloat(data.gross_profit || 0);
        const margin = parseFloat(data.margin || 0);
        const from = data.from_date || $('#fromDate').val();
        const to = data.to_date || $('#toDate').val();

        let html = '<p class="shopora-dash-modal-period">Period: <strong>' + escapeHtml(from) + '</strong> to <strong>' + escapeHtml(to) + '</strong></p>';
        html += '<div class="shopora-dash-modal-summary">';
        html += dashModalSummaryStat('Revenue', formatRs(revenue));
        html += dashModalSummaryStat('Cost of Goods', formatRs(cost));
        html += dashModalSummaryStat('Gross Profit', formatRs(profit), true);
        html += dashModalSummaryStat('Margin', margin.toFixed(1) + '%', true);
        html += '</div>';

        html += '<div class="table-responsive"><table class="table shopora-dash-modal-table mb-0">';
        html += '<thead><tr><th>Category</th><th class="col-amount">Revenue</th><th class="col-amount">Cost</th><th class="col-amount">Profit</th><th class="col-amount">Margin</th></tr></thead><tbody>';
        if (cats.length) {
            cats.forEach(function(c) {
                html += '<tr>' +
                    '<td>' + escapeHtml(c.category) + '</td>' +
                    '<td class="col-amount">' + Number(c.revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td class="col-amount">' + Number(c.cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td class="col-amount">' + Number(c.profit || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td class="col-amount">' + Number(c.margin || 0).toFixed(1) + '%</td>' +
                '</tr>';
            });
        } else {
            html += '<tr><td colspan="5" class="text-center text-muted py-3">No sales in this period</td></tr>';
        }
        html += '</tbody></table></div>';
        html += '<p class="mt-3 mb-0 small text-muted">Gross profit = revenue − cost of goods sold. Cost is the average purchase rate per item × quantity sold.</p>';

        $('#profitContent').html(html);
    }

    function handleStatCardAction(action) {
        if (action === 'revenue') {
            showPaymentMethodRevenue();
            return;
        }
        if (action === 'profit') {
            showProfitModal();
            return;
        }
        if (action === 'sales') {
            showSalesSummaryModal();
            return;
        }
        if (action === 'purchases') {
            window.location.href = "{{ route('admin.purchaseInventory') }}";
            return;
        }
        if (action === 'inventory') {
            window.location.href = "{{ route('admin.inventoryItem') }}";
            return;
        }
        if (action === 'customers') {
            window.location.href = "{{ route('admin.customer') }}";
            return;
        }
        if (action === 'stock') {
            openLowStockModal();
        }
    }

    function dashModalSummaryStat(label, value, highlight) {
        return '<div class="shopora-dash-modal-stat' + (highlight ? ' is-highlight' : '') + '">' +
            '<p class="shopora-dash-modal-stat-label">' + escapeHtml(label) + '</p>' +
            '<p class="shopora-dash-modal-stat-value">' + value + '</p>' +
        '</div>';
    }

    function showModalLoader(targetSelector) {
        $(targetSelector).html(
            '<div class="text-center py-4"><div class="spinner-border text-primary" role="status">' +
            '<span class="visually-hidden">Loading...</span></div></div>'
        );
    }

    function openLowStockModal() {
        const modalEl = document.getElementById('lowStockModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        renderLowStockItems(null, $('#lowStockModalBody'));
        modal.show();

        $.ajax({
            url: "{{ route('admin.dashboard.lowStockItems') }}",
            method: 'GET',
            success: function(response) {
                renderLowStockItems(response.items || [], $('#lowStockModalBody'));
            },
            error: function() {
                $('#lowStockModalBody').html('<tr><td colspan="3" class="shopora-panel-empty">Unable to load low stock items</td></tr>');
            }
        });
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
        const base = window.paymentLogoBase || '';
        let logoUrl = null;
        let tone = 'gray';

        if (name.includes('cash')) {
            logoUrl = base + '/cash.svg';
            tone = 'logo';
        } else if (name.includes('esewa')) {
            logoUrl = base + '/esewa.ico';
            tone = 'logo';
        } else if (name.includes('fonepay') || name.includes('fone')) {
            logoUrl = base + '/fonepay.png';
            tone = 'logo';
        } else if (name.includes('khalti')) {
            logoUrl = base + '/khalti.png';
            tone = 'logo';
        } else if (name.includes('card') || name.includes('bank')) {
            logoUrl = base + '/bank.svg';
            tone = 'logo';
        }

        if (logoUrl) {
            const alt = escapeHtml(title || 'Payment');
            return {
                tone: tone === 'logo' ? 'logo' : tone,
                logoUrl: logoUrl,
                iconHtml: '<img src="' + logoUrl + '" alt="' + alt + '" loading="lazy">'
            };
        }

        if (name.includes('other')) {
            return { tone: 'gray', logoUrl: null, iconHtml: '<i class="bx bx-dots-horizontal-rounded"></i>' };
        }

        return { tone: 'gray', logoUrl: null, iconHtml: '<i class="bx bx-wallet"></i>' };
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
            const iconClass = visual.logoUrl
                ? 'shopora-pay-icon is-logo'
                : 'shopora-pay-icon is-' + visual.tone;

            html += '<div class="shopora-pay-item">' +
                '<div class="' + iconClass + '">' + visual.iconHtml + '</div>' +
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

    // ---------- Charts (ApexCharts) ----------
    let salesTrendChart = null;
    let categoryChart = null;
    const CHART_BRAND = '#008cff';

    function fmtRsShort(n) {
        n = Number(n) || 0;
        if (n >= 1000000) return 'Rs ' + (n / 1000000).toFixed(1) + 'M';
        if (n >= 1000) return 'Rs ' + (n / 1000).toFixed(1) + 'K';
        return 'Rs ' + Math.round(n);
    }

    function isDarkTheme() {
        return document.documentElement.classList.contains('dark-theme');
    }

    function chartTheme() {
        return isDarkTheme()
            ? { grid: '#2a3236', tooltip: 'dark' }
            : { grid: '#eef0f3', tooltip: 'light' };
    }

    function reThemeCharts() {
        const t = chartTheme();
        [salesTrendChart, categoryChart, invCategoryChart, vendorChart, customerTrendChart].forEach(function (c) {
            if (c) {
                c.updateOptions({ grid: { borderColor: t.grid }, tooltip: { theme: t.tooltip } }, false, false);
            }
        });
    }

    function renderSalesTrend(payload) {
        const points = (payload && payload.points) || [];
        const hasData = points.some(p => Number(p.revenue) > 0);
        $('#salesTrendEmpty').prop('hidden', hasData);
        $('#salesTrendSub').text(payload && payload.granularity === 'monthly'
            ? 'Monthly revenue over the selected period'
            : 'Daily revenue over the selected period');

        const categories = points.map(p => p.label);
        const data = points.map(p => Math.round(Number(p.revenue) || 0));

        const options = {
            chart: { type: 'area', height: 300, fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false }, animations: { easing: 'easeinout', speed: 400 } },
            series: [{ name: 'Revenue', data: data }],
            colors: [CHART_BRAND],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.03, stops: [0, 90, 100] } },
            dataLabels: { enabled: false },
            grid: { borderColor: chartTheme().grid, strokeDashArray: 4, padding: { left: 8, right: 8 } },
            xaxis: { categories: categories, labels: { style: { colors: '#9ca3af', fontSize: '11px' }, rotate: -35, hideOverlappingLabels: true }, axisBorder: { show: false }, axisTicks: { show: false }, tooltip: { enabled: false } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' }, formatter: v => fmtRsShort(v) } },
            markers: { size: 0, hover: { size: 5 } },
            tooltip: { theme: chartTheme().tooltip, y: { formatter: v => 'Rs ' + Number(v).toLocaleString() } },
        };

        if (salesTrendChart) {
            salesTrendChart.updateOptions({ xaxis: { categories: categories } }, false, false);
            salesTrendChart.updateSeries([{ name: 'Revenue', data: data }], true);
        } else {
            salesTrendChart = new ApexCharts(document.querySelector('#salesTrendChart'), options);
            salesTrendChart.render();
        }
    }

    function renderCategoryChart(payload) {
        const cats = (payload && payload.categories) || [];
        const hasData = cats.some(c => Number(c.revenue) > 0);
        $('#categoryEmpty').prop('hidden', hasData);

        const data = cats.map(c => ({ x: c.category, y: Math.round(Number(c.revenue) || 0) }));

        const options = {
            chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Revenue', data: data }],
            colors: [CHART_BRAND],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '62%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: chartTheme().grid, strokeDashArray: 4 },
            xaxis: { tickAmount: 3, labels: { style: { colors: '#9ca3af', fontSize: '11px' }, formatter: v => fmtRsShort(v) }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#4b5563', fontSize: '12px' } } },
            tooltip: { theme: chartTheme().tooltip, y: { formatter: v => 'Rs ' + Number(v).toLocaleString() } },
        };

        if (categoryChart) {
            categoryChart.updateSeries([{ name: 'Revenue', data: data }], true);
        } else {
            categoryChart = new ApexCharts(document.querySelector('#categoryChart'), options);
            categoryChart.render();
        }
    }

    function fetchSalesTrend(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.salesTrend') }}",
            data: { from_date: $('#fromDate').val(), to_date: $('#toDate').val() },
            success: function (res) { renderSalesTrend(res.data || {}); },
            error: function () { $('#salesTrendEmpty').prop('hidden', false).text('Unable to load sales trend'); },
            complete: function () { if (done) done(); }
        });
    }

    function fetchCategoryBreakdown(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.categoryBreakdown') }}",
            data: { from_date: $('#fromDate').val(), to_date: $('#toDate').val() },
            success: function (res) { renderCategoryChart(res.data || {}); },
            error: function () { $('#categoryEmpty').prop('hidden', false).text('Unable to load category data'); },
            complete: function () { if (done) done(); }
        });
    }

    // ---- Inventory & Purchases bar charts (lazy: containers start in hidden tabs) ----
    let invCategoryChart = null, invCategoryData = null;
    let vendorChart = null, vendorData = null;
    let customerTrendChart = null, customerTrendData = null;

    function chartHidden(sel) {
        const el = document.querySelector(sel);
        return !el || el.offsetParent === null;
    }

    function horizontalBarOptions(data) {
        return {
            chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'Amount', data: data }],
            colors: [CHART_BRAND],
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '62%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: chartTheme().grid, strokeDashArray: 4 },
            xaxis: { tickAmount: 3, labels: { style: { colors: '#9ca3af', fontSize: '11px' }, formatter: v => fmtRsShort(v) }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#4b5563', fontSize: '12px' } } },
            tooltip: { theme: chartTheme().tooltip, y: { formatter: v => 'Rs ' + Number(v).toLocaleString() } },
        };
    }

    function renderInvCategoryChart() {
        if (!invCategoryData) return;
        const cats = invCategoryData.categories || [];
        const hasData = cats.some(c => Number(c.value) > 0);
        $('#invCategoryEmpty').prop('hidden', hasData);
        if (chartHidden('#invCategoryChart')) return;

        const data = cats.map(c => ({ x: c.category, y: Math.round(Number(c.value) || 0) }));
        if (invCategoryChart) {
            invCategoryChart.updateSeries([{ name: 'Stock Value', data: data }], true);
        } else {
            invCategoryChart = new ApexCharts(document.querySelector('#invCategoryChart'), horizontalBarOptions(data));
            invCategoryChart.render();
        }
    }

    function renderVendorChart() {
        if (!vendorData) return;
        const vendors = vendorData.vendors || [];
        const hasData = vendors.some(v => Number(v.total) > 0);
        $('#vendorEmpty').prop('hidden', hasData);
        if (chartHidden('#vendorChart')) return;

        const data = vendors.map(v => ({ x: v.vendor, y: Math.round(Number(v.total) || 0) }));
        if (vendorChart) {
            vendorChart.updateSeries([{ name: 'Purchases', data: data }], true);
        } else {
            vendorChart = new ApexCharts(document.querySelector('#vendorChart'), horizontalBarOptions(data));
            vendorChart.render();
        }
    }

    function renderCustomerTrend() {
        if (!customerTrendData) return;
        const points = customerTrendData.points || [];
        const hasData = points.some(p => Number(p.customers) > 0);
        $('#customerTrendEmpty').prop('hidden', hasData);
        $('#customerTrendSub').text(customerTrendData.granularity === 'monthly'
            ? 'Sign-ups per month over the selected period'
            : 'Sign-ups per day over the selected period');
        if (chartHidden('#customerTrendChart')) return;

        const categories = points.map(p => p.label);
        const data = points.map(p => Number(p.customers) || 0);
        const options = {
            chart: { type: 'bar', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
            series: [{ name: 'New customers', data: data }],
            colors: [CHART_BRAND],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: chartTheme().grid, strokeDashArray: 4 },
            xaxis: { categories: categories, labels: { style: { colors: '#9ca3af', fontSize: '11px' }, rotate: -35, hideOverlappingLabels: true }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#9ca3af', fontSize: '11px' }, formatter: v => Math.round(v) } },
            tooltip: { theme: chartTheme().tooltip, y: { formatter: v => Number(v).toLocaleString() + (Number(v) === 1 ? ' customer' : ' customers') } },
        };

        if (customerTrendChart) {
            customerTrendChart.updateOptions({ xaxis: { categories: categories } }, false, false);
            customerTrendChart.updateSeries([{ name: 'New customers', data: data }], true);
        } else {
            customerTrendChart = new ApexCharts(document.querySelector('#customerTrendChart'), options);
            customerTrendChart.render();
        }
    }

    function fetchCustomerGrowth(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.customerGrowth') }}",
            data: { from_date: $('#fromDate').val(), to_date: $('#toDate').val() },
            success: function (res) { customerTrendData = res.data || {}; renderCustomerTrend(); },
            error: function () { $('#customerTrendEmpty').prop('hidden', false).text('Unable to load customer data'); },
            complete: function () { if (done) done(); }
        });
    }

    function fetchInvCategory(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.inventoryByCategory') }}",
            success: function (res) { invCategoryData = res.data || {}; renderInvCategoryChart(); },
            error: function () { $('#invCategoryEmpty').prop('hidden', false).text('Unable to load stock data'); },
            complete: function () { if (done) done(); }
        });
    }

    function fetchVendors(done) {
        $.ajax({
            url: "{{ route('admin.dashboard.purchasesByVendor') }}",
            data: { from_date: $('#fromDate').val(), to_date: $('#toDate').val() },
            success: function (res) { vendorData = res.data || {}; renderVendorChart(); },
            error: function () { $('#vendorEmpty').prop('hidden', false).text('Unable to load vendor data'); },
            complete: function () { if (done) done(); }
        });
    }

    function updateDashboard(options) {
        options = options || {};
        const withLoader = options.loader === true;
        const fromDate = $('#fromDate').val();
        const toDate = $('#toDate').val();
        let pending = 6;

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
                const rs2 = v => 'Rs ' + parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const num = v => Number(v || 0).toLocaleString();

                $('.js-revenue').text(rs2(response.totalRevenue));
                $('.js-profit').text(rs2(response.grossProfit));
                $('.js-margin').text(Number(response.profitMargin || 0).toFixed(1));
                $('.js-sales').text(num(response.totalSales));
                $('.js-aov').text(rs2(response.avgOrderValue));
                $('.js-invcount').text(num(response.invoiceCount));
                $('.js-alerts').text(num(response.stockAlerts));
                $('.js-invvalue').text(rs2(response.inventoryValue));
                $('.js-invunits').text(num(response.inventoryUnits));
                $('.js-outofstock').text(num(response.outOfStock));
                $('.js-purchases').text(rs2(response.totalPurchases));
                $('.js-purchasecount').text(num(response.purchaseCount));
                $('.js-avgpurchase').text(rs2(response.avgPurchase));
                $('.js-custtotal').text(num(response.totalCustomers));
                $('.js-custregistered').text(num(response.registeredCustomers));
                $('.js-custnew').text(num(response.newCustomers));
                $('.js-repeatrate').text(Number(response.repeatRate || 0).toFixed(1));
                $('.js-repeatcount').text(num(response.repeatCustomers));
                $('.js-buyercount').text(num(response.buyingCustomers));
                $('.js-custvalue').text(rs2(response.avgCustomerValue));
                $('.js-activecust').text(num(response.activeCustomers));
                $('.js-topvendor').text(response.topVendorName || '—');
                $('.js-topvendor-amount').text(parseFloat(response.topVendorAmount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                setStatChange('.js-revenue-change', response.revenueChangePercent);
                setStatChange('.js-profit-change', response.profitChangePercent);
                setStatChange('.js-sales-change', response.salesChangePercent);
                setStatChange('.js-purchases-change', response.purchasesChangePercent);
                setStatChange('.js-custnew-change', response.newCustomersChangePercent);

                renderRecentSales(response.recentSales || []);
                renderLowStockItems(response.lowStockItems || []);
                renderBestSellers(response.bestSellers || []);
                renderTopCustomers(response.topCustomers || []);
                renderRecentPurchases(response.recentPurchases || []);
                renderRecentCustomers(response.recentCustomers || []);
            },
            error: function() {
                $('#recentSalesBody').html('<tr><td colspan="8" class="shopora-panel-empty">Unable to load recent sales</td></tr>');
                $('#lowStockBody').html('<tr><td colspan="3" class="shopora-panel-empty">Unable to load low stock items</td></tr>');
            },
            complete: markDone
        });

        fetchSalesTrend(markDone);
        fetchCategoryBreakdown(markDone);
        fetchInvCategory(markDone);
        fetchVendors(markDone);
        fetchCustomerGrowth(markDone);
    }
</script>

<!-- Payment Method Revenue Modal -->
<div class="modal fade" id="profitModal" tabindex="-1" aria-labelledby="profitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profitModalLabel">Profit Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="profitContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentMethodRevenueModal" tabindex="-1" aria-labelledby="paymentMethodRevenueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodRevenueModalLabel">Revenue Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="paymentMethodRevenueContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="{{ route('admin.invoice.index') }}" class="shopora-panel-link">View all invoices →</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Sales Summary Modal -->
<div class="modal fade" id="salesSummaryModal" tabindex="-1" aria-labelledby="salesSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesSummaryModalLabel">Sales Summary (Quantity)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="salesSummaryContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="{{ route('admin.invoice.index') }}" class="shopora-panel-link">View all invoices →</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showPaymentMethodRevenue() {
        const modalEl = document.getElementById('paymentMethodRevenueModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        showModalLoader('#paymentMethodRevenueContent');
        modal.show();

        $.ajax({
            url: "{{ route('admin.dashboard.paymentMethodRevenue') }}",
            method: 'GET',
            data: {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val()
            },
            success: function(response) {
                renderPaymentRevenueModal(response.data || {});
            },
            error: function() {
                $('#paymentMethodRevenueContent').html(
                    '<p class="text-center text-muted py-4">Unable to load revenue summary</p>'
                );
            }
        });
    }

    function renderPaymentRevenueModal(data) {
        const modes = data.payment_modes || [];
        const gross = parseFloat(data.total_revenue || 0);
        const discount = parseFloat(data.total_discount || 0);
        const net = parseFloat(data.net_revenue || 0);
        const from = data.from_date || $('#fromDate').val();
        const to = data.to_date || $('#toDate').val();

        let html = '<p class="shopora-dash-modal-period">Period: <strong>' + escapeHtml(from) + '</strong> to <strong>' + escapeHtml(to) + '</strong></p>';
        html += '<div class="shopora-dash-modal-summary">';
        html += dashModalSummaryStat('Gross Revenue', formatRs(gross), true);
        html += dashModalSummaryStat('Total Discount', formatRs(discount));
        html += dashModalSummaryStat('Net Revenue', formatRs(net), true);
        html += '</div>';

        html += '<div class="table-responsive"><table class="table shopora-dash-modal-table mb-0">';
        html += '<thead><tr><th>Payment Method</th><th class="col-amount">Amount (Rs)</th><th class="col-amount">Share</th></tr></thead><tbody>';

        if (modes.length) {
            modes.forEach(function(item) {
                const amt = parseFloat(item.total_amount || 0);
                const pct = gross > 0 ? (amt / gross) * 100 : 0;
                html += '<tr>' +
                    '<td>' + escapeHtml(item.payment_title) + '</td>' +
                    '<td class="col-amount">' + amt.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '<td class="col-amount">' + pct.toFixed(1) + '%</td>' +
                '</tr>';
            });
        } else {
            html += '<tr><td colspan="3" class="text-center text-muted py-3">No payment data for this period</td></tr>';
        }

        html += '</tbody></table></div>';
        html += '<p class="mt-3 mb-0 small text-muted">Gross revenue is total collected by payment method. Net revenue subtracts invoice-level discounts from that amount.</p>';

        $('#paymentMethodRevenueContent').html(html);
    }

    function showSalesSummaryModal() {
        const modalEl = document.getElementById('salesSummaryModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        showModalLoader('#salesSummaryContent');
        modal.show();

        $.ajax({
            url: "{{ route('admin.dashboard.salesSummary') }}",
            method: 'GET',
            data: {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val()
            },
            success: function(response) {
                renderSalesSummaryModal(response.data || {});
            },
            error: function() {
                $('#salesSummaryContent').html(
                    '<p class="text-center text-muted py-4">Unable to load sales summary</p>'
                );
            }
        });
    }

    function renderSalesSummaryModal(data) {
        const from = data.from_date || $('#fromDate').val();
        const to = data.to_date || $('#toDate').val();
        const totalQty = Number(data.total_qty || 0);
        const invoiceCount = Number(data.invoice_count || 0);
        const avgQty = parseFloat(data.avg_qty_per_invoice || 0);
        const revenue = parseFloat(data.total_revenue || 0);
        const products = data.top_products || [];

        let html = '<p class="shopora-dash-modal-period">Period: <strong>' + escapeHtml(from) + '</strong> to <strong>' + escapeHtml(to) + '</strong></p>';
        html += '<div class="shopora-dash-modal-summary">';
        html += dashModalSummaryStat('Units Sold', totalQty.toLocaleString(), true);
        html += dashModalSummaryStat('Invoices', invoiceCount.toLocaleString());
        html += dashModalSummaryStat('Avg Units / Invoice', avgQty.toLocaleString(undefined, { maximumFractionDigits: 1 }));
        html += dashModalSummaryStat('Sales Revenue', formatRs(revenue));
        html += '</div>';

        html += '<h6 class="mb-2 fw-semibold">Top Sold Products</h6>';
        html += '<div class="table-responsive"><table class="table shopora-dash-modal-table mb-0">';
        html += '<thead><tr><th>Product</th><th class="col-amount">Qty Sold</th><th class="col-amount">Amount (Rs)</th></tr></thead><tbody>';

        if (products.length) {
            products.forEach(function(row) {
                html += '<tr>' +
                    '<td>' + escapeHtml(row.name) + '</td>' +
                    '<td class="col-amount">' + Number(row.qty || 0).toLocaleString() + '</td>' +
                    '<td class="col-amount">' + parseFloat(row.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                '</tr>';
            });
        } else {
            html += '<tr><td colspan="3" class="text-center text-muted py-3">No sales in this period</td></tr>';
        }

        html += '</tbody></table></div>';
        html += '<p class="mt-3 mb-0 small text-muted">Units sold counts product quantities across all invoices in the selected date range.</p>';

        $('#salesSummaryContent').html(html);
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