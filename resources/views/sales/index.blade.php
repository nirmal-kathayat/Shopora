@extends("layouts.app")
@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
    /* Sales top bar — project blue #008cff (see bootstrap-extended .btn-primary) */
    .sales-topbar {
        --sales-blue: #008cff;
        --sales-blue-hover: #037de2;
        --sales-blue-focus: rgba(0, 140, 255, 0.25);
    }

    .sales-topbar .sales-search-input {
        height: 40px;
        border: 1px solid var(--sales-blue);
        border-radius: 0.375rem;
        padding-left: 40px;
        font-size: 0.9375rem;
        box-shadow: none;
    }

    .sales-topbar .sales-search-input:focus {
        border-color: var(--sales-blue);
        box-shadow: 0 0 0 0.2rem var(--sales-blue-focus);
    }

    .sales-topbar .search-icon {
        left: 14px;
        font-size: 18px;
        color: #9ca3af;
        pointer-events: none;
    }

    .sales-topbar .sales-topbar-btn {
        height: 40px;
        min-height: 40px;
        padding: 0 1rem;
        font-size: 0.9375rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border-radius: 0.375rem;
        white-space: nowrap;
    }

    .sales-topbar .sales-topbar-btn i {
        font-size: 18px;
        line-height: 1;
    }

    .sales-topbar .sales-topbar-btn.btn-outline-primary {
        border: 1px solid var(--sales-blue) !important;
        background-color: #fff;
        color: var(--sales-blue);
    }

    .sales-topbar .sales-topbar-btn.btn-outline-primary:hover,
    .sales-topbar .sales-topbar-btn.btn-outline-primary:focus {
        background-color: var(--sales-blue);
        border-color: var(--sales-blue) !important;
        color: #fff;
        box-shadow: 0 0 0 0.2rem var(--sales-blue-focus);
    }

    .sales-topbar .sales-topbar-btn.btn-primary {
        border: 1px solid var(--sales-blue) !important;
    }

    .sales-topbar .sales-customer-select-wrap {
        position: relative;
    }

    .sales-topbar .sales-customer-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        font-size: 18px;
        color: #9ca3af;
        pointer-events: none;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container {
        width: 100% !important;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container--default .select2-selection--single {
        height: 40px;
        min-height: 40px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 36px;
        padding-right: 2rem;
        color: #374151;
        font-size: 0.9375rem;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
        right: 8px;
    }

    .sales-topbar .sales-customer-select-wrap .select2-container--default.select2-container--focus .select2-selection--single,
    .sales-topbar .sales-customer-select-wrap .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--sales-blue);
        box-shadow: 0 0 0 0.2rem var(--sales-blue-focus);
    }

    /* Inventory Items panel */
  .sales-inventory-panel {
        --sales-blue: #008cff;
        --sales-blue-hover: #037de2;
        --sales-blue-focus: rgba(0, 140, 255, 0.25);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .sales-inventory-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .sales-inventory-panel-title {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .sales-inventory-category-wrap {
        flex: 0 1 200px;
        min-width: 160px;
    }

    .sales-inventory-category-wrap .select2-container {
        width: 100% !important;
    }

    .sales-inventory-category-wrap .select2-container--default .select2-selection--single {
        height: 38px;
        min-height: 38px;
        border: 1px solid var(--sales-blue);
        border-radius: 0.375rem;
        background: #fff;
    }

    .sales-inventory-category-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 0.75rem;
        font-size: 0.875rem;
        color: #374151;
    }

    .sales-inventory-category-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
    }

    .sales-inventory-table-wrap {
        max-height: calc(100vh - 320px);
        min-height: 280px;
        overflow-y: auto;
    }

    .sales-inventory-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .sales-inventory-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        padding: 0.65rem 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #9ca3af;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .sales-inventory-table thead th.col-price {
        text-align: right;
        width: 110px;
    }

    .sales-inventory-table thead th.col-action {
        text-align: center;
        width: 72px;
    }

    .sales-inventory-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .sales-inventory-item-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .sales-inventory-thumb {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .sales-inventory-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sales-inventory-thumb i {
        font-size: 22px;
        color: #9ca3af;
    }

    .sales-inventory-item-meta {
        min-width: 0;
    }

    .sales-inventory-item-name {
        display: block;
        font-size: 0.9375rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .sales-inventory-item-sub {
        display: block;
        font-size: 0.8125rem;
        color: #9ca3af;
        margin-top: 2px;
    }

    .sales-inventory-col-price {
        text-align: right;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #374151;
        white-space: nowrap;
    }

    .sales-inventory-col-action {
        text-align: center;
    }

    .sales-inventory-add-btn {
        width: 36px;
        height: 36px;
        min-width: 36px;
        min-height: 36px;
        padding: 0;
        margin: 0;
        border: 1px solid var(--sales-blue) !important;
        border-radius: 6px;
        background: #fff;
        color: var(--sales-blue);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .sales-inventory-add-btn i {
        font-size: 22px;
        line-height: 1;
        display: flex;
    }

    .sales-inventory-add-btn:hover {
        background: var(--sales-blue);
        color: #fff;
        border-color: var(--sales-blue) !important;
    }

    .sales-inventory-empty {
        text-align: center;
        padding: 1.25rem 1rem;
        margin: 0.75rem 1rem 1rem;
        background: #f8fafc;
        border: 1px dashed #e5e7eb;
        border-radius: 8px;
    }

    .sales-inventory-empty i {
        font-size: 2rem;
        color: var(--sales-blue);
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .sales-inventory-empty p {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--sales-blue);
        margin: 0 0 0.25rem;
    }

    .sales-inventory-empty span {
        font-size: 0.8125rem;
        color: #9ca3af;
    }

    /* Current Sale cart panel */
    .sales-cart-panel {
        --sales-blue: #008cff;
        --sales-blue-hover: #037de2;
        --sales-blue-focus: rgba(0, 140, 255, 0.25);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        min-height: 520px;
    }

    .sales-cart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .sales-cart-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sales-cart-title-wrap i {
        font-size: 1.25rem;
        color: var(--sales-blue);
    }

    .sales-cart-title-wrap h6 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .sales-cart-clear {
        border: none;
        background: none;
        color: #ef4444;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
    }

    .sales-cart-clear:hover {
        color: #dc2626;
    }

    .sales-cart-table-wrap {
        flex: 1 1 auto;
        max-height: 280px;
        overflow-y: auto;
    }

    .sales-cart-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sales-cart-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #fff;
        padding: 0.65rem 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #9ca3af;
        border-bottom: 1px solid #e5e7eb;
    }

    .sales-cart-table thead th.col-qty {
        text-align: center;
        width: 110px;
    }

    .sales-cart-table thead th.col-price,
    .sales-cart-table thead th.col-total {
        text-align: right;
        width: 90px;
    }

    .sales-cart-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .sales-cart-item-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .sales-cart-thumb {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sales-cart-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sales-cart-thumb i {
        font-size: 20px;
        color: #9ca3af;
    }

    .sales-cart-item-name {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .sales-cart-item-sub {
        display: block;
        font-size: 0.75rem;
        color: #9ca3af;
    }

    .sales-cart-col-qty {
        text-align: center;
    }

    .sales-cart-qty-stepper {
        display: inline-flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }

    .sales-cart-qty-stepper button {
        width: 32px;
        height: 32px;
        border: none;
        background: #f9fafb;
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .sales-cart-qty-stepper button i {
        font-size: 18px;
        line-height: 1;
    }

    .sales-cart-qty-stepper button:hover {
        background: #f3f4f6;
    }

    .sales-cart-qty-value {
        min-width: 28px;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }

    .sales-cart-col-price {
        text-align: right;
        font-size: 0.875rem;
        color: #374151;
        white-space: nowrap;
    }

    .sales-cart-total-cell {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .sales-cart-line-total {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        white-space: nowrap;
    }

    .sales-cart-remove {
        width: 32px;
        height: 32px;
        border: none;
        background: #fef2f2;
        color: #ef4444;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        flex-shrink: 0;
    }

    .sales-cart-remove i {
        font-size: 18px;
    }

    .sales-cart-remove:hover {
        background: #fee2e2;
    }

    .sales-cart-empty {
        text-align: center;
        padding: 2rem 1.25rem;
        margin: 1rem 1.25rem;
        border: 1px dashed #e5e7eb;
        border-radius: 8px;
        background: #fafafa;
    }

    .sales-cart-empty i {
        font-size: 2.5rem;
        color: #d1d5db;
        line-height: 1;
        margin-bottom: 0.75rem;
    }

    .sales-cart-empty p {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #6b7280;
        margin: 0 0 0.25rem;
    }

    .sales-cart-empty span {
        font-size: 0.8125rem;
        color: #9ca3af;
    }

    .sales-cart-payment {
        padding: 1rem 1.25rem;
        border-top: 1px solid #f3f4f6;
    }

    .sales-cart-payment-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.35rem;
    }

    .sales-cart-payment-label i {
        font-size: 1rem;
        color: #6b7280;
    }

    .sales-cart-payment #payment-mode,
    .sales-cart-payment #discount-amount {
        height: 38px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .sales-cart-split-btn {
        height: 38px;
        min-height: 38px;
        border: 1px solid var(--sales-blue) !important;
        color: var(--sales-blue);
        background: #fff;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
        border-radius: 0.375rem;
    }

    .sales-cart-split-btn:hover {
        background: var(--sales-blue);
        color: #fff;
        border-color: var(--sales-blue) !important;
    }

    .sales-cart-summary {
        padding: 1rem 1.25rem 1.25rem;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
        border-radius: 0 0 8px 8px;
    }

    .sales-cart-total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .sales-cart-total-row .total-label {
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .sales-cart-total-row .order-total {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--sales-blue);
    }

    .sales-cart-field-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.35rem;
    }

  .sales-cart-summary #received-amount {
        height: 38px;
        border: 1px solid var(--sales-blue);
        border-radius: 0.375rem;
        font-size: 0.9375rem;
    }

    .sales-cart-summary #received-amount:focus {
        border-color: var(--sales-blue);
        box-shadow: 0 0 0 0.2rem var(--sales-blue-focus);
    }

    .sales-cart-summary #change-amount {
        height: 38px;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background: #f3f4f6;
        font-size: 0.9375rem;
        color: #374151;
    }

    .sales-cart-confirm {
        width: 100%;
        height: 44px;
        margin-top: 1rem;
        border: none !important;
        border-radius: 0.375rem;
        background: var(--sales-blue) !important;
        color: #fff !important;
        font-size: 0.9375rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
    }

    .sales-cart-confirm:hover {
        background: var(--sales-blue-hover) !important;
    }

    .sales-cart-confirm i {
        font-size: 1.125rem;
    }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Top bar: Search + Customer + Actions -->
            <div class="sales-topbar">
                <div class="sales-search">
                    <div class="search-bar-sm position-relative">
                        <input type="text" id="search-input" class="form-control search-input sales-search-input" placeholder="Search inventory by name or code" autocomplete="off">
                        <i class="bx bx-search search-icon position-absolute top-50 translate-middle-y"></i>
                    </div>
                </div>

                <div class="sales-actions">
                    <button type="button" id="btn-inventory" class="btn btn-primary sales-topbar-btn" data-bs-toggle="modal" data-bs-target="#inventoryItemModal">
                        <i class="bx bx-package"></i>
                        <span>Add Inventory Item</span>
                    </button>
                </div>

                <div class="sales-customer">
                    <div class="sales-customer-select-wrap">
                        <i class="bx bx-user sales-customer-icon"></i>
                        <select name="customer_id" class="form-control w-100" id="customer">
                            <option value=""></option>
                        </select>
                    </div>
                </div>

                <div class="sales-actions">
                    <button type="button" id="btn-add-customer" class="btn btn-outline-primary sales-topbar-btn" data-bs-toggle="modal" data-bs-target="#customerModal">
                        <i class="bx bx-user-plus"></i>
                        <span>Add Customer</span>
                    </button>
                </div>
            </div>
            <div class="row row-gap-sm g-3">
                <div class="col-lg-6 sales-filter-sm">
                    <div class="sales-inventory-panel">
                        <div class="sales-inventory-panel-header">
                            <h6 class="sales-inventory-panel-title">Inventory Items</h6>
                            <div class="sales-inventory-category-wrap">
                                <select name="category_filter" class="form-control select2-category" id="category-filter-select">
                                    <option value="all" selected>All Categories</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="sales-inventory-table-wrap">
                            <table class="sales-inventory-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="col-price">Unit Price</th>
                                        <th class="col-action">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="inventory-list">
                                    @foreach ($inventories as $inventory)
                                    <tr class="sales-inventory-row" data-category="{{ $inventory->category_id }}">
                                        <td>
                                            <div class="sales-inventory-item-cell">
                                                <div class="sales-inventory-thumb">
                                                    {!! inventoryItemImageHtml($inventory->image, $inventory->title) !!}
                                                </div>
                                                <div class="sales-inventory-item-meta">
                                                    <span class="sales-inventory-item-name">{{ $inventory->title }}</span>
                                                    <span class="sales-inventory-item-sub">Code: {{ $inventory->code }} • Unit: {{ $inventory->unit }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="sales-inventory-col-price">Rs. {{ number_format($inventory->price_per_unit, 0) }}</td>
                                        <td class="sales-inventory-col-action">
                                            <button type="button" class="sales-inventory-add-btn add-inventory"
                                                data-id="{{ $inventory->id }}"
                                                data-title="{{ $inventory->title }}"
                                                data-price_per_unit="{{ $inventory->price_per_unit }}"
                                                data-unit="{{ $inventory->unit }}"
                                                data-code="{{ $inventory->code }}"
                                                data-image="{{ $inventory->image }}"
                                                data-image-url="{{ inventoryItemImageUrl($inventory->image) }}"
                                                aria-label="Add {{ $inventory->title }}">
                                                <i class="bx bx-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div id="inventory-list-empty" class="sales-inventory-empty {{ $inventories->isEmpty() ? '' : 'd-none' }}">
                            <i class="bx bx-package"></i>
                            <p>No items found</p>
                            <span>Try adjusting your search or category filter</span>
                        </div>
                    </div>
                </div>

                <!-- Current Sale -->
                <div class="col-lg-6">
                    <div class="sales-cart-panel">
                        <div class="sales-cart-header">
                            <div class="sales-cart-title-wrap">
                                <i class="bx bx-cart"></i>
                                <h6>Current Sale</h6>
                            </div>
                            <button type="button" id="clear-cart-btn" class="sales-cart-clear">
                                <i class="bx bx-trash"></i>
                                <span>Clear Cart</span>
                            </button>
                        </div>

                        <div class="sales-cart-table-wrap">
                            <table class="sales-cart-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="col-qty">Qty</th>
                                        <th class="col-price">Price</th>
                                        <th class="col-total">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="selected-items"></tbody>
                            </table>
                        </div>

                        <div id="cart-empty-state" class="sales-cart-empty">
                            <i class="bx bx-cart"></i>
                            <p>No items selected</p>
                            <span>Add items from the inventory list</span>
                        </div>

                        <div id="payment-mode-section" class="sales-cart-payment" style="display: none;">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label for="payment-mode" class="sales-cart-payment-label">
                                        <i class="bx bx-credit-card"></i>
                                        Payment Mode
                                    </label>
                                    <select id="payment-mode" class="form-control">
                                        @foreach($paymentModes as $payment)
                                        <option value="{{ $payment->id }}">{{ $payment->payment_title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn sales-cart-split-btn" data-bs-toggle="modal" data-bs-target="#splitPaymentModal">
                                        <i class="bx bx-user-plus"></i>
                                        Split Payment
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <label for="discount-amount" class="sales-cart-payment-label">Discount</label>
                                    <input type="number" id="discount-amount" class="form-control" placeholder="0.00" min="0" step="0.01" value="">
                                </div>
                            </div>
                        </div>

                        <div class="sales-cart-summary">
                            <div class="sales-cart-total-row">
                                <span class="total-label">Total Amount</span>
                                <span class="order-total">Rs. 0.00</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="received-amount" class="sales-cart-field-label">Received Amount</label>
                                    <input type="number" id="received-amount" class="form-control amount-cal" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-6">
                                    <label for="change-amount" class="sales-cart-field-label">Change Amount</label>
                                    <input type="text" id="change-amount" class="form-control amount-cal" readonly placeholder="Rs. 0.00">
                                </div>
                            </div>
                            <button type="button" class="btn-confirm sales-cart-confirm">
                                <i class="bx bx-check-circle"></i>
                                Confirm Sale
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Item Modal -->
<div class="modal fade" id="inventoryItemModal" tabindex="-1" aria-labelledby="inventoryItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="inventory-item-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryItemModalLabel">Add Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="inventory-item-form-errors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inv_title" class="form-label required">Inventory Name</label>
                            <input type="text" class="form-control" id="inv_title" name="title" placeholder="Inventory Name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inventory_category_id" class="form-label required">Select Category</label>
                            <div class="cat-input">
                                <select class="form-control" id="inventory_category_id" name="category_id" required>
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-modal" id="openInventoryCategoryNested">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_unit" class="form-label required">Unit</label>
                            <input type="text" class="form-control" id="inv_unit" name="unit" placeholder="Unit" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_code" class="form-label">Bar Code</label>
                            <input type="text" class="form-control" id="inv_code" name="code" placeholder="Enter bar code">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_price_per_unit" class="form-label required">Selling Price Per Unit</label>
                            <input type="number" class="form-control" id="inv_price_per_unit" name="price_per_unit" min="0" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="inv_image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="inventory-item-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>

            <!-- Nested overlay (keeps Inventory modal open while adding category) -->
            <div id="inventoryCategoryNested"
                class="d-none position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center bg-dark bg-opacity-50"
                style="z-index: 10;">
                <div class="bg-white rounded shadow p-4" style="width: min(520px, 92vw);">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0">Add New Category</h5>
                        <button type="button" class="btn-close" id="inventoryCategoryNestedClose" aria-label="Close"></button>
                    </div>
                    <div id="inventory-category-errors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label for="inventoryCategoryName" class="form-label required">Category Name</label>
                        <input type="text" class="form-control" id="inventoryCategoryName" placeholder="Category name">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" id="inventoryCategoryNestedCancel">Close</button>
                        <button type="button" class="btn btn-primary" id="saveInventoryCategoryBtn">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <!-- Invoice details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    Print
                </button>

            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="customer-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="customer-form-errors" class="alert alert-danger d-none"></div>

                    <div class="mb-3">
                        <label for="modal-customer-name" class="form-label required">Customer</label>
                        <input type="text" class="form-control" id="modal-customer-name" name="name" placeholder="Customer Name" required>
                    </div>

                    <div class="mb-3">
                        <label for="modal-customer-address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="modal-customer-address" name="address" placeholder="Address">
                    </div>

                    <div class="mb-3">
                        <label for="modal-customer-phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="modal-customer-phone" name="ph_number" placeholder="Phone Number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="customer-form-submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<iframe id="printFrame" style="display:none;"></iframe>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    // Create payment mode mapping globally
    window.paymentModeMap = {};
    @foreach($paymentModes as $payment)
    window.paymentModeMap[{{ $payment->id }}] = '{{ $payment->payment_title }}';
    @endforeach
</script>
@endsection

@section('script')
@include('scripts.sales')
<script>
    let currentInvoice = null;
    let currentDetails = [];

    function loadInvoiceModal(invoiceId) {
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
                    <h2 style="font-size:15px;margin-bottom:5px">Twelve Seven Grocery and Liquor Land Pvt Ltd</h2>
                    <h5 style="font-size:14px;color:#000;margin-bottom:5px;">Kathmandu, Narephant</h5>
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
        const colWidths = {
            sn: 3,
            item: 14,
            qty: 3,
            rate: 6,
            amount: 8
        }; // 32 chars total
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
        lines.push(`Payment: ${(() => {
    const paymentModes = String(details[0]?.payment_mode).split(',').map(id => window.paymentModeMap[id.trim()] || id.trim());
    return paymentModes.join(', ');
})()}`);
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
    // Select2 Initialization
    $(document).ready(function() {
        // Initialize Select2 for Customer
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#customer').select2({
            ajax: {
                url: '{{ route("admin.sales.searchCustomer") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.name + ' - ' + item.ph_number
                            }
                        })
                    };
                }
            },
            minimumInputLength: 0,
            placeholder: 'Select customer (optional)',
            allowClear: true
        });
        // Reset modal form on open
        $('#customerModal').on('show.bs.modal', function() {
            $('#customer-form')[0].reset();
            $('#customer-form-errors').addClass('d-none').empty();
            $('#customer-form-submit').prop('disabled', false).text('Save');
        });
        // Submit customer form via AJAX
        $('#customer-form').on('submit', function(e) {
            e.preventDefault();
            $('#customer-form-submit').prop('disabled', true).text('Saving...');
            const payload = {
                name: $('#modal-customer-name').val(),
                address: $('#modal-customer-address').val(),
                ph_number: $('#modal-customer-phone').val()
            };
            $.ajax({
                url: '{{ route("admin.customer.store") }}',
                method: 'POST',
                data: payload,
                success: function(res) {
                    $('#customerModal').modal('hide');
                    const displayText = (res.name || '') + (res.ph_number ? ' - ' + res.ph_number : '');
                    const newOption = new Option(displayText, res.id, true, true);
                    $('#customer').append(newOption).val(res.id).trigger('change');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message || 'Customer added successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {}
                },
                error: function(xhr) {
                    $('#customer-form-submit').prop('disabled', false).text('Save');
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let html = '<ul class="mb-0">';
                        Object.keys(errors).forEach(function(k) {
                            errors[k].forEach(function(msg) {
                                html += `<li>${msg}</li>`;
                            });
                        });
                        html += '</ul>';
                        $('#customer-form-errors').removeClass('d-none').html(html);
                    } else {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                        $('#customer-form-errors').removeClass('d-none').html(msg);
                    }
                }
            });
        });
        // Initialize Select2 for Category Filter (no AJAX needed)
        $('.select2-category').select2({
            placeholder: 'All Categories',
            allowClear: false,
            width: '100%'
        });

        // Category filter handler
        $('#category-filter-select').on('change', function() {
            const category = $(this).val();

            if (category === 'all') {
                $('#inventory-list tr.sales-inventory-row').show();
            } else {
                $('#inventory-list tr.sales-inventory-row').each(function() {
                    if ($(this).data('category') == category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            if (typeof window.toggleInventoryEmptyState === 'function') {
                window.toggleInventoryEmptyState();
            }
        });
        
        // Update total amount in split payment modal when opened
        $('#splitPaymentModal').on('show.bs.modal', function () {
            const totalAmount = $('.order-total').text();
            $('#split-total-amount').text(totalAmount);
        });
    });
</script>

<!-- Split Payment Modal -->
<div class="modal fade" id="splitPaymentModal" tabindex="-1" aria-labelledby="splitPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="splitPaymentModalLabel">Split Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0">Total Amount:</label>
                        <span id="split-total-amount" class="fw-bold">Rs. 0.00</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Modes:</label>
                    <div class="row">
                        @foreach($paymentModes as $index => $payment)
                        <div class="col-md-6 mb-3">
                            <label for="payment-{{ $payment->id }}" class="form-label">{{ $payment->payment_title }}:</label>
                            <input type="number" id="payment-{{ $payment->id }}" class="form-control" placeholder="0.00" min="0" step="0.01">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-split-payment">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection