<div class="modal fade" id="purchaseInventoryFormModal" tabindex="-1" aria-labelledby="purchaseInventoryFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="purchaseInventoryModalForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseInventoryFormModalLabel">Create Purchase Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="purchase-inventory-modal-errors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="purchase_inventory_edit_id" value="">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_vendor_name" class="form-label required">Vendor</label>
                            <input type="text" name="vendor_name" id="purchase_vendor_name" class="form-control" placeholder="Vendor name">
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_bill_date" class="form-label required">Bill Date</label>
                            <input type="date" name="bill_date" id="purchase_bill_date" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="purchase_address" class="form-label">Address</label>
                            <input type="text" name="address" id="purchase_address" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_pan_number" class="form-label">Pan Number</label>
                            <input type="text" name="pan_number" id="purchase_pan_number" class="form-control">
                        </div>
                    </div>

                    <div class="purchase-items-section mb-3">
                        <div class="purchase-items-section-header">
                            <span class="purchase-items-section-title">Inventory Items</span>
                            <span class="purchase-items-section-hint">Select item, quantity and rate for each line</span>
                        </div>
                        <div id="purchaseInventoryItemsContainer" class="purchase-items-list"></div>
                    </div>

                    <div class="row mb-3 mt-4">
                        <div class="col-md-4">
                            <label for="purchase_total_taxable_amount" class="form-label">Total Taxable Amount</label>
                            <input type="number" id="purchase_total_taxable_amount" class="form-control" readonly step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label for="purchase_vat_amount" class="form-label">Vat Amount</label>
                            <input type="number" name="vat_amount" id="purchase_vat_amount" class="form-control" step="0.01" placeholder="Enter VAT amount">
                        </div>
                        <div class="col-md-4">
                            <label for="purchase_amount_after_vat" class="form-label">Amount After VAT</label>
                            <input type="number" id="purchase_amount_after_vat" class="form-control" readonly step="0.01">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="purchase-inventory-modal-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="purchaseNestedInventoryItemModal" tabindex="-1" aria-labelledby="purchaseNestedInventoryItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="purchase-nested-inventory-item-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseNestedInventoryItemModalLabel">Add Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="purchase-nested-inventory-item-errors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="purchase_inv_title" class="form-label required">Inventory Name</label>
                            <input type="text" class="form-control" id="purchase_inv_title" placeholder="Inventory Name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_inventory_category_id" class="form-label required">Select Category</label>
                            <div class="cat-input">
                                <select class="form-control" id="purchase_inventory_category_id" required>
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_inv_unit" class="form-label required">Unit</label>
                            <input type="text" class="form-control" id="purchase_inv_unit" placeholder="Unit" required>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_inv_code" class="form-label required">Bar Code</label>
                            <input type="text" class="form-control" id="purchase_inv_code" placeholder="Enter bar code" required>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_inv_price_per_unit" class="form-label required">Selling Price Per Unit</label>
                            <input type="number" class="form-control" id="purchase_inv_price_per_unit" placeholder="Enter price" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="purchase-nested-inventory-item-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('style')
<style>
    #purchaseInventoryFormModal {
        --purchase-field-border: #0bb2d3;
        --purchase-field-focus: rgba(11, 178, 211, 0.25);
    }

    #purchaseInventoryFormModal .purchase-items-section {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f8fafc;
        padding: 1rem 1rem 0.75rem;
    }

    #purchaseInventoryFormModal .purchase-items-section-header {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 0.5rem 1rem;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed #d1d5db;
    }

    #purchaseInventoryFormModal .purchase-items-section-title {
        font-weight: 600;
        color: #111827;
        font-size: 0.95rem;
    }

    #purchaseInventoryFormModal .purchase-items-section-hint {
        font-size: 0.8rem;
        color: #6b7280;
    }

    #purchaseInventoryFormModal .purchase-field-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.35rem;
    }

    #purchaseInventoryFormModal .purchase-field-input,
    #purchaseInventoryFormModal #purchase_vendor_name,
    #purchaseInventoryFormModal #purchase_bill_date,
    #purchaseInventoryFormModal #purchase_address,
    #purchaseInventoryFormModal #purchase_pan_number,
    #purchaseInventoryFormModal #purchase_vat_amount,
    #purchaseInventoryFormModal #purchase_total_taxable_amount,
    #purchaseInventoryFormModal #purchase_amount_after_vat {
        border: 1px solid var(--purchase-field-border);
        border-radius: 4px;
        min-height: 38px;
        font-size: 0.95rem;
        box-shadow: none;
    }

    #purchaseInventoryFormModal .purchase-field-input:focus,
    #purchaseInventoryFormModal #purchase_vendor_name:focus,
    #purchaseInventoryFormModal #purchase_bill_date:focus,
    #purchaseInventoryFormModal #purchase_address:focus,
    #purchaseInventoryFormModal #purchase_pan_number:focus,
    #purchaseInventoryFormModal #purchase_vat_amount:focus {
        border-color: var(--purchase-field-border);
        box-shadow: 0 0 0 0.2rem var(--purchase-field-focus);
    }

    #purchaseInventoryFormModal .purchase-field-readonly {
        background-color: #f3f4f6;
        color: #374151;
    }

    #purchaseInventoryFormModal .purchase-field-wrap {
        width: 100%;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container {
        width: 100% !important;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default .select2-selection--single {
        min-height: 38px;
        height: 38px;
        border: 1px solid var(--purchase-field-border);
        border-radius: 4px;
        background: #fff;
        display: flex;
        align-items: center;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0.75rem;
        padding-right: 2rem;
        line-height: 36px;
        color: #374151;
        font-size: 0.95rem;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        top: 1px;
        right: 6px;
        width: 24px;
    }

    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default.select2-container--focus .select2-selection--single,
    #purchaseInventoryFormModal .purchase-select2 + .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--purchase-field-border);
        box-shadow: 0 0 0 0.2rem var(--purchase-field-focus);
    }

  #purchaseInventoryFormModal .select2-dropdown {
        border: 1px solid var(--purchase-field-border);
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    #purchaseInventoryFormModal .select2-results__option {
        padding: 0.55rem 0.75rem;
        font-size: 0.95rem;
    }

    #purchaseInventoryFormModal .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #0bb2d3;
    }

    #purchaseInventoryFormModal .select2-search--dropdown {
        padding: 8px;
    }

    #purchaseInventoryFormModal .select2-search--dropdown .select2-search__field {
        border: 1px solid var(--purchase-field-border) !important;
        border-radius: 4px;
        padding: 0.45rem 0.65rem;
        font-size: 0.95rem;
        outline: none;
        box-shadow: none;
    }

    #purchaseInventoryFormModal .select2-search--dropdown .select2-search__field:focus {
        border-color: var(--purchase-field-border);
        box-shadow: 0 0 0 0.2rem var(--purchase-field-focus);
    }

    #purchaseInventoryFormModal .purchase-row-action {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding-bottom: 0;
    }

    #purchaseInventoryFormModal .purchase-row-action-btn {
        width: 38px;
        height: 38px;
        min-width: 38px;
        min-height: 38px;
        padding: 0;
        margin: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        line-height: 1;
        flex-shrink: 0;
        overflow: visible;
    }

    #purchaseInventoryFormModal .purchase-row-action-btn i {
        font-size: 22px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
    }

    #purchaseNestedInventoryItemModal #purchase_inventory_category_id + .select2-container {
        width: 100% !important;
    }

    #purchaseNestedInventoryItemModal #purchase_inventory_category_id + .select2-container--default .select2-selection--single {
        min-height: 38px;
        border: 1px solid #0bb2d3;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
@include('scripts.validation')
<script>
    (function () {
        const storeUrl = "{{ route('admin.purchaseInventory.store') }}";
        const updateUrlTemplate = "{{ route('admin.purchaseInventory.update', ['id' => ':id']) }}";
        const viewUrlTemplate = "{{ route('admin.purchaseInventory.view', ['id' => ':id']) }}";
        const inventoryStoreUrl = "{{ route('admin.inventoryItem.store') }}";

        let purchaseInventoryOptions = @json(($inventories ?? collect())->values());
        let purchaseRowIndex = 0;
        let purchaseLastSelectOpened = null;
        let purchaseModalMode = 'create';

        function getPurchaseFormModalEl() {
            return document.getElementById('purchaseInventoryFormModal');
        }

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
            });
        }

        function buildInventoryOptions(selectedId) {
            return purchaseInventoryOptions.map(function (inv) {
                const selected = String(selectedId) === String(inv.id) ? ' selected' : '';
                return '<option value="' + inv.id + '"' + selected + '>' + escapeHtml(inv.title) + '</option>';
            }).join('');
        }

        function buildPurchaseItemRow(index, item, isFirst) {
            const qty = item && item.qty != null ? item.qty : '';
            const rate = item && item.rate != null ? item.rate : '';
            const total = (qty && rate) ? (parseFloat(qty) * parseFloat(rate)).toFixed(2) : '';
            const selectedId = item ? item.inventory_item_id : '';

            const actionBtn = isFirst
                ? '<button type="button" class="btn btn-success purchase-row-action-btn purchase-add-item-btn" aria-label="Add row"><i class="bx bx-plus"></i></button>'
                : '<button type="button" class="btn btn-danger purchase-row-action-btn purchase-remove-item-btn" aria-label="Remove row"><i class="bx bx-minus"></i></button>';

            return `
                <div class="row g-2 mb-2 purchase-inventory-item-row align-items-end" data-row-index="${index}">
                    <div class="col-md-4 col-lg-4">
                        <label class="form-label purchase-field-label">Inventory Item</label>
                        <div class="purchase-field-wrap">
                            <select name="inventory_items[${index}][inventory_item_id]" class="form-control purchase-select2">
                                <option value=""></option>
                                ${buildInventoryOptions(selectedId)}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <label class="form-label purchase-field-label">Quantity</label>
                        <input type="number" name="inventory_items[${index}][qty]" value="${qty}" class="form-control purchase-qty-input purchase-field-input" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="col-md-2 col-lg-2">
                        <label class="form-label purchase-field-label">Rate</label>
                        <input type="number" name="inventory_items[${index}][rate]" value="${rate}" class="form-control purchase-rate-input purchase-field-input" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <label class="form-label purchase-field-label">Total Amount</label>
                        <input type="number" name="inventory_items[${index}][total_amount]" value="${total}" class="form-control purchase-total-amount-input purchase-field-input purchase-field-readonly" min="0" step="0.01" readonly>
                    </div>
                    <div class="col-md-1 col-lg-1">
                        <div class="purchase-row-action">${actionBtn}</div>
                    </div>
                </div>
            `;
        }

        function initPurchaseSelect2(element) {
            if (!window.jQuery || !$.fn.select2) {
                return;
            }

            const $el = $(element);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            $el.select2({
                placeholder: 'Select Inventory',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#purchaseInventoryFormModal'),
                language: {
                    noResults: function () {
                        return '<div class="text-center">No result found <button type="button" class="btn btn-link p-0 ms-2 purchase-select2-add-inventory">Add</button></div>';
                    }
                },
                escapeMarkup: function (m) {
                    return m;
                }
            });
        }

        function initAllPurchaseSelect2() {
            document.querySelectorAll('#purchaseInventoryItemsContainer .purchase-select2').forEach(function (el) {
                initPurchaseSelect2(el);
            });
        }

        function destroyAllPurchaseSelect2() {
            document.querySelectorAll('#purchaseInventoryItemsContainer .purchase-select2').forEach(function (el) {
                if ($(el).hasClass('select2-hidden-accessible')) {
                    $(el).select2('destroy');
                }
            });
        }

        function calculatePurchaseRowTotal(row) {
            const qty = parseFloat(row.querySelector('.purchase-qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.purchase-rate-input').value) || 0;
            row.querySelector('.purchase-total-amount-input').value = (qty * rate).toFixed(2);
            calculatePurchaseTotalTaxableAmount();
        }

        function calculatePurchaseTotalTaxableAmount() {
            let total = 0;
            document.querySelectorAll('#purchaseInventoryItemsContainer .purchase-inventory-item-row').forEach(function (row) {
                const qty = parseFloat(row.querySelector('.purchase-qty-input').value) || 0;
                const rate = parseFloat(row.querySelector('.purchase-rate-input').value) || 0;
                total += qty * rate;
            });
            document.getElementById('purchase_total_taxable_amount').value = total.toFixed(2);
            calculatePurchaseAmountAfterVat();
        }

        function calculatePurchaseAmountAfterVat() {
            const taxableAmount = parseFloat(document.getElementById('purchase_total_taxable_amount').value) || 0;
            const vatAmount = parseFloat(document.getElementById('purchase_vat_amount').value) || 0;
            const amountAfterVatEl = document.getElementById('purchase_amount_after_vat');

            if (vatAmount > 0) {
                amountAfterVatEl.value = (taxableAmount + vatAmount).toFixed(2);
            } else {
                amountAfterVatEl.value = '';
            }
        }

        function resetPurchaseInventoryModal() {
            purchaseModalMode = 'create';
            purchaseRowIndex = 0;
            purchaseLastSelectOpened = null;
            $('#purchase_inventory_edit_id').val('');
            $('#purchaseInventoryFormModalLabel').text('Create Purchase Inventory');
            $('#purchase-inventory-modal-submit').text('Submit');
            $('#purchase-inventory-modal-errors').addClass('d-none').empty();
            $('#purchase_vendor_name').val('');
            $('#purchase_bill_date').val('');
            $('#purchase_address').val('');
            $('#purchase_pan_number').val('');
            $('#purchase_vat_amount').val('');
            $('#purchase_total_taxable_amount').val('');
            $('#purchase_amount_after_vat').val('');

            destroyAllPurchaseSelect2();
            $('#purchaseInventoryItemsContainer').html(buildPurchaseItemRow(0, null, true));
            initAllPurchaseSelect2();
        }

        function populatePurchaseInventoryModal(data) {
            purchaseModalMode = 'edit';
            $('#purchase_inventory_edit_id').val(data.id);
            $('#purchaseInventoryFormModalLabel').text('Edit Purchase Inventory');
            $('#purchase-inventory-modal-submit').text('Update');
            $('#purchase-inventory-modal-errors').addClass('d-none').empty();

            $('#purchase_vendor_name').val(data.vendor || '');
            $('#purchase_address').val(data.address || '');
            $('#purchase_pan_number').val(data.pan_number || '');
            $('#purchase_vat_amount').val(data.vat_amount || '');

            if (data.bill_date) {
                const billDate = new Date(data.bill_date);
                if (!isNaN(billDate.getTime())) {
                    $('#purchase_bill_date').val(billDate.toISOString().split('T')[0]);
                }
            }

            destroyAllPurchaseSelect2();
            const items = data.items || [];
            let rowsHtml = '';

            if (items.length === 0) {
                rowsHtml = buildPurchaseItemRow(0, null, true);
                purchaseRowIndex = 0;
            } else {
                purchaseRowIndex = items.length - 1;
                items.forEach(function (item, index) {
                    rowsHtml += buildPurchaseItemRow(index, {
                        inventory_item_id: item.inventory_item_id,
                        qty: item.qty,
                        rate: item.rate
                    }, index === 0);
                });
            }

            $('#purchaseInventoryItemsContainer').html(rowsHtml);
            initAllPurchaseSelect2();
            calculatePurchaseTotalTaxableAmount();
        }

        function showPurchaseModalErrors(message) {
            $('#purchase-inventory-modal-errors').removeClass('d-none').html(message);
        }

        $(document).on('click', '#btnNewPurchaseInventory', function () {
            resetPurchaseInventoryModal();
        });

        $(document).on('click', '.editPurchaseInventory', function () {
            const id = $(this).data('id');
            if (!id) {
                return;
            }

            $.ajax({
                url: viewUrlTemplate.replace(':id', id),
                type: 'GET',
                success: function (data) {
                    populatePurchaseInventoryModal(data);
                    const modalEl = getPurchaseFormModalEl();
                    if (modalEl && window.bootstrap) {
                        bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    }
                },
                error: function () {
                    Swal.fire('Error!', 'Unable to load purchase inventory details.', 'error');
                }
            });
        });

        $(document).on('select2:open', '#purchaseInventoryItemsContainer .purchase-select2', function () {
            purchaseLastSelectOpened = this;
        });

        $(document).on('click', '.purchase-select2-add-inventory', function (e) {
            e.preventDefault();
            const nestedModalEl = document.getElementById('purchaseNestedInventoryItemModal');
            if (nestedModalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(nestedModalEl).show();
            }
        });

        $('#purchaseNestedInventoryItemModal').on('shown.bs.modal', function () {
            if ($.fn.select2) {
                const $cat = $('#purchase_inventory_category_id');
                if ($cat.hasClass('select2-hidden-accessible')) {
                    $cat.select2('destroy');
                }
                $cat.select2({
                    dropdownParent: $('#purchaseNestedInventoryItemModal'),
                    width: '100%',
                    placeholder: 'Select Category'
                });
            }
        });

        $('#purchaseNestedInventoryItemModal').on('show.bs.modal', function () {
            $('#purchase-nested-inventory-item-errors').addClass('d-none').empty();
            $('#purchase-nested-inventory-item-submit').prop('disabled', false).text('Submit');
            $('#purchase_inv_title').val('');
            $('#purchase_inv_unit').val('');
            $('#purchase_inv_code').val('');
            $('#purchase_inv_price_per_unit').val('');
            $('#purchase_inventory_category_id').val('').trigger('change');
        });

        $('#purchase-nested-inventory-item-form').on('submit', function (e) {
            e.preventDefault();
            $('#purchase-nested-inventory-item-submit').prop('disabled', true).text('Saving...');

            $.ajax({
                url: inventoryStoreUrl,
                method: 'POST',
                data: {
                    title: $('#purchase_inv_title').val(),
                    unit: $('#purchase_inv_unit').val(),
                    code: $('#purchase_inv_code').val(),
                    category_id: $('#purchase_inventory_category_id').val(),
                    price_per_unit: $('#purchase_inv_price_per_unit').val(),
                    _token: $('input[name="_token"]').val()
                },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (res) {
                    if (res.type === 'success' && res.inventory) {
                        purchaseInventoryOptions.push({ id: res.inventory.id, title: res.inventory.title });

                        if (purchaseLastSelectOpened) {
                            const opt = new Option(res.inventory.title, res.inventory.id, true, true);
                            $(purchaseLastSelectOpened).append(opt).trigger('change');
                        }

                        const nestedModalEl = document.getElementById('purchaseNestedInventoryItemModal');
                        if (nestedModalEl && window.bootstrap) {
                            bootstrap.Modal.getInstance(nestedModalEl)?.hide();
                        }
                    } else {
                        $('#purchase-nested-inventory-item-errors').removeClass('d-none').text('Unable to add item.');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Something went wrong';
                    $('#purchase-nested-inventory-item-errors').removeClass('d-none').html(msg);
                },
                complete: function () {
                    $('#purchase-nested-inventory-item-submit').prop('disabled', false).text('Submit');
                }
            });
        });

        $(document).on('input', '#purchaseInventoryFormModal .purchase-qty-input, #purchaseInventoryFormModal .purchase-rate-input', function () {
            calculatePurchaseRowTotal(this.closest('.purchase-inventory-item-row'));
        });

        $(document).on('input', '#purchase_vat_amount', function () {
            calculatePurchaseAmountAfterVat();
        });

        $(document).on('click', '.purchase-add-item-btn', function () {
            const container = document.getElementById('purchaseInventoryItemsContainer');
            const firstRow = container.querySelector('.purchase-inventory-item-row');
            const firstSelect = firstRow.querySelector('.purchase-select2');

            if ($(firstSelect).hasClass('select2-hidden-accessible')) {
                $(firstSelect).select2('destroy');
            }

            purchaseRowIndex++;
            const newRowHtml = buildPurchaseItemRow(purchaseRowIndex, null, false);
            container.insertAdjacentHTML('beforeend', newRowHtml);

            initPurchaseSelect2(firstSelect);
            initPurchaseSelect2(container.querySelector('.purchase-inventory-item-row:last-child .purchase-select2'));
        });

        $(document).on('click', '.purchase-remove-item-btn', function () {
            const row = this.closest('.purchase-inventory-item-row');
            const select = row.querySelector('.purchase-select2');
            if ($(select).hasClass('select2-hidden-accessible')) {
                $(select).select2('destroy');
            }
            row.remove();
            calculatePurchaseTotalTaxableAmount();
        });

        $('#purchaseInventoryFormModal').on('hidden.bs.modal', function () {
            destroyAllPurchaseSelect2();
            resetPurchaseInventoryModal();
        });

        $('#purchaseInventoryModalForm').on('submit', function (e) {
            e.preventDefault();

            const editId = $('#purchase_inventory_edit_id').val();
            const isEdit = purchaseModalMode === 'edit' && editId;
            const url = isEdit ? updateUrlTemplate.replace(':id', editId) : storeUrl;

            $('#purchase-inventory-modal-submit').prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
            $('#purchase-inventory-modal-errors').addClass('d-none').empty();

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (res) {
                    if (res.type === 'success') {
                        const modalEl = getPurchaseFormModalEl();
                        if (modalEl && window.bootstrap) {
                            bootstrap.Modal.getInstance(modalEl)?.hide();
                        }

                        if (window.purchaseInventoryDataTable) {
                            window.purchaseInventoryDataTable.ajax.reload(null, false);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message || (isEdit ? 'Purchase inventory updated.' : 'Purchase inventory created.')
                        });
                    } else {
                        showPurchaseModalErrors(res.message || 'Unable to save purchase inventory.');
                    }
                },
                error: function (xhr) {
                    let message = 'Something went wrong.';
                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    showPurchaseModalErrors(message);
                },
                complete: function () {
                    const isEditMode = purchaseModalMode === 'edit';
                    $('#purchase-inventory-modal-submit').prop('disabled', false).text(isEditMode ? 'Update' : 'Submit');
                }
            });
        });

        resetPurchaseInventoryModal();
    })();
</script>
@endpush
