@extends("layouts.app")
@php
$url = isset($purchaseInventory) ? route('admin.purchaseInventory.update',['id' => $purchaseInventory->id]) : route('admin.purchaseInventory.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Create Purchase Inventory</h3>
            </div>
            <hr>
            <form class="g-3" action="{{$url}}" method="post" id="purchaseForm">
                @csrf

                <!-- First Row: Vendor and Bill Date -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="vendor" class="form-label required">Vendor</label>
                        <input type="text" name="vendor_name" value="{{isset($purchaseInventory) ? $purchaseInventory->vendor:''}}" id="vendor" class="form-control" data-validation="required">
                        @if($errors->has('vendor_name'))
                        <span class="text-danger">{{$errors->first('vendor_name')}}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="bill_date" class="form-label required">Bill Date</label>
                        <input type="date" name="bill_date" id="bill_date"
                            class="form-control"
                            value="{{ isset($purchaseInventory) ? \Carbon\Carbon::parse($purchaseInventory->bill_date)->format('Y-m-d') : '' }}">
                        @if($errors->has('bill_date'))
                        <span class="text-danger">{{$errors->first('bill_date')}}</span>
                        @endif
                    </div>
                </div>

                <!-- Second Row: Address and Pan Number -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" value="{{isset($purchaseInventory) ? $purchaseInventory->address:''}}" id="address" class="form-control">
                        @if($errors->has('address'))
                        <span class="text-danger">{{$errors->first('address')}}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="pan_number" class="form-label">Pan Number</label>
                        <input type="text" name="pan_number" value="{{isset($purchaseInventory) ? $purchaseInventory->pan_number:''}}" id="pan_number" class="form-control">
                        @if($errors->has('pan_number'))
                        <span class="text-danger">{{$errors->first('pan_number')}}</span>
                        @endif
                    </div>
                </div>
                <!-- Dynamic Inventory Items Container -->
                <div id="inventoryItemsContainer">
                    @if(isset($purchaseInventory) && $purchaseInventory->items->count() > 0)
                    @foreach($purchaseInventory->items as $index => $item)
                    <div class="row mb-3 inventory-item-row" data-row-index="{{$index}}">
                        <div class="col-md-4">
                            <label for="inventory_{{$index}}" class="form-label">Inventory Item</label>
                            <div class="cat-input">
                                <select name="inventory_items[{{$index}}][inventory_item_id]" class="form-control select2" id="inventory_{{$index}}">
                                    <option value=""></option>
                                    @foreach($inventories as $inventory)
                                    <option value="{{ $inventory->id }}" {{ $item->inventory_item_id == $inventory->id ? 'selected' : '' }}>
                                        {{ $inventory->title }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="inventory_items[{{$index}}][qty]" value="{{$item->qty}}" class="form-control qty-input" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rate</label>
                            <input type="number" name="inventory_items[{{$index}}][rate]" value="{{$item->rate}}" class="form-control rate-input" min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="inventory_items[{{$index}}][total_amount]" value="{{$item->total_amount}}" class="form-control total-amount-input" min="0" step="0.01" readonly>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                @if($index == 0)
                                <button type="button" class="btn btn-success add-item-btn">
                                    <i class="fas fa-plus"></i> +
                                </button>
                                @else
                                <button type="button" class="btn btn-danger remove-item-btn">
                                    <i class="fas fa-minus"></i> -
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <!-- First inventory item row -->
                    <div class="row mb-3 inventory-item-row" data-row-index="0">
                        <div class="col-md-4">
                            <label for="inventory" class="form-label">Inventory Item</label>
                            <div class="cat-input">
                                <select name="inventory_items[0][inventory_item_id]" class="form-control select2" id="inventory">
                                    <option value=""></option>
                                    @foreach($inventories as $inventory)
                                    <option value="{{ $inventory->id }}">
                                        {{ $inventory->title }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="inventory_items[0][qty]" value="" class="form-control qty-input" min="0" step="0.01">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Rate</label>
                            <input type="number" name="inventory_items[0][rate]" value="" class="form-control rate-input" min="0" step="0.01">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" name="inventory_items[0][total_amount]" value="" class="form-control total-amount-input" min="0" step="0.01" readonly>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-success add-item-btn">
                                    <i class="fas fa-plus"></i> +
                                </button>
                                <button type="button" class="btn btn-danger remove-item-btn" style="display: none;">
                                    <i class="fas fa-minus"></i> -
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Summary Fields Row -->
                <div class="row mb-3 mt-4">
                    <div class="col-md-4">
                        <label for="total_taxable_amount" class="form-label">Total Taxable Amount</label>
                        <input type="number" id="total_taxable_amount" class="form-control" readonly step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label for="vat_amount" class="form-label">Vat Amount</label>
                        <input type="number" name="vat_amount" id="vat_amount" class="form-control" step="0.01" placeholder="Enter VAT amount" value="{{isset($purchaseInventory) ? $purchaseInventory->vat_amount : ''}}">
                    </div>
                    <div class="col-md-4">
                        <label for="amount_after_vat" class="form-label">Amount After VAT</label>
                        <input type="number" id="amount_after_vat" class="form-control" readonly step="0.01">
                    </div>
                </div>

                <div class="col-12 justify-item-end justify-left mt-4">
                    <a href="{{route('admin.purchaseInventory')}}" class="btn btn-secondary px-5">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">{{isset($purchaseInventory) ? 'Update':'Submit'}}</button>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>

@endsection
@include('scripts.validation')

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
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_unit" class="form-label required">Unit</label>
                            <input type="text" class="form-control" id="inv_unit" name="unit" placeholder="Unit" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_code" class="form-label required">Bar Code</label>
                            <input type="text" class="form-control" id="inv_code" name="code" placeholder="Enter bar code" required>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_price_per_unit" class="form-label required">Selling Price Per Unit</label>
                            <input type="number" class="form-control" id="inv_price_per_unit" name="price_per_unit" placeholder="Enter price" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="inventory-item-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
 </div>

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        function initInventorySelect2(element) {
            if (window.jQuery && $.fn.select2) {
                $(element).select2({
                    placeholder: 'Select Inventory',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function () {
                            return `
                                <div class="text-center">
                                    No result found
                                    <button type="button"
                                            class="btn btn-link p-0 ms-2 select2-add-inventory">
                                        Add
                                    </button>
                                </div>
                            `;
                        }
                    },
                    escapeMarkup: function (m) {
                        return m;
                    }
                });
            }
        }

        let rowIndex = {{ isset($purchaseInventory) ? $purchaseInventory->items->count() : 0 }};
        let lastSelectOpened = null;
        /* ================= SELECT2 INIT ================= */
        function initSelect2(element) {
            if (window.jQuery && $.fn.select2) {
                $(element).select2({
                    placeholder: 'Select Inventory',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function() {
                            return '<div class="text-center">No result found <button type="button" class="btn btn-link p-0 ms-2 select2-add-inventory">Add</button></div>';
                        }
                    },
                    escapeMarkup: function(m) { return m; }
                });
            }
        }

        // init select2 for all rows
        document.querySelectorAll('.inventory-item-row .select2').forEach(function(element) {
            initSelect2(element);
        });

        $(document).on('select2:open', '.inventory-item-row .select2', function() {
            lastSelectOpened = this;
        });
        $(document).on('click', '.select2-add-inventory', function(e) {
            e.preventDefault();
            var modal = new bootstrap.Modal(document.getElementById('inventoryItemModal'));
            modal.show();
        });
        $('#inventoryItemModal').on('shown.bs.modal', function() {
            if ($.fn.select2) {
                $('#inventory_category_id').select2({
                    dropdownParent: $('#inventoryItemModal'),
                    width: '100%',
                    placeholder: 'Select Category'
                });
            }
        });
        $('#inventoryItemModal').on('show.bs.modal', function() {
            $('#inventory-item-form-errors').addClass('d-none').empty();
            $('#inventory-item-submit').prop('disabled', false).text('Submit');
            $('#inv_title').val('');
            $('#inv_unit').val('');
            $('#inv_code').val('');
            $('#inv_price_per_unit').val('');
            $('#inventory_category_id').val('').trigger('change');
        });
        $('#inventory-item-form').on('submit', function(e) {
            e.preventDefault();
            $('#inventory-item-submit').prop('disabled', true).text('Saving...');
            $.ajax({
                url: '{{ route("admin.inventoryItem.store") }}',
                method: 'POST',
                data: {
                    title: $('#inv_title').val(),
                    unit: $('#inv_unit').val(),
                    code: $('#inv_code').val(),
                    category_id: $('#inventory_category_id').val(),
                    price_per_unit: $('#inv_price_per_unit').val(),
                    _token: $('input[name="_token"]').val()
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res.type === 'success' && res.inventory && lastSelectOpened) {
                        const opt = new Option(res.inventory.title, res.inventory.id, true, true);
                        $(lastSelectOpened).append(opt).trigger('change');
                        $('#inventoryItemModal').modal('hide');
                    } else {
                        $('#inventory-item-form-errors').removeClass('d-none').text('Unable to add item.');
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                    $('#inventory-item-form-errors').removeClass('d-none').html(msg);
                },
                complete: function() {
                    $('#inventory-item-submit').prop('disabled', false).text('Submit');
                }
            });
        });

        /* ================= TOTAL CALC ================= */
        function calculateRowTotal(row) {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            row.querySelector('.total-amount-input').value = (qty * rate).toFixed(2);
            calculateTotalTaxableAmount();
        }
        /* ================= INIT ROW TOTALS (EDIT MODE FIX) ================= */
        function initializeRowTotals() {
            document.querySelectorAll('.inventory-item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
                row.querySelector('.total-amount-input').value = (qty * rate).toFixed(2);
            });
        }
        /* ================= SUMMARY CALCULATIONS ================= */
        function calculateTotalTaxableAmount() {
            const rows = document.querySelectorAll('.inventory-item-row');
            let total = 0;

            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
                const totalAmount = qty * rate;
                total += totalAmount;
            });

            document.getElementById('total_taxable_amount').value = total.toFixed(2);
            calculateAmountAfterVat();
        }

        function calculateAmountAfterVat() {
            const taxableAmount = parseFloat(document.getElementById('total_taxable_amount').value) || 0;
            const vatAmountInput = document.getElementById('vat_amount');
            const vatAmount = parseFloat(vatAmountInput.value) || 0;

            if (vatAmount > 0) {
                const amountAfterVat = taxableAmount + vatAmount;
                document.getElementById('amount_after_vat').value = amountAfterVat.toFixed(2);
            } else {
                document.getElementById('amount_after_vat').value = '';
            }
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('rate-input')) {
                calculateRowTotal(e.target.closest('.inventory-item-row'));
            }

            if (e.target.id === 'vat_amount') {
                calculateAmountAfterVat();
            }
        });

        /* ================= ADD ROW ================= */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.add-item-btn')) {

                const container = document.getElementById('inventoryItemsContainer');
                const firstRow = container.querySelector('.inventory-item-row');

                /* ✅ DESTROY select2 before cloning */
                const firstSelect = firstRow.querySelector('select');
                if ($(firstSelect).hasClass("select2-hidden-accessible")) {
                    $(firstSelect).select2('destroy');
                }

                /* ✅ CLONE CLEAN ROW */
                const newRow = firstRow.cloneNode(true);
                rowIndex++;
                newRow.setAttribute('data-row-index', rowIndex);

                /* ✅ RESET INPUTS & NAMES */
                newRow.querySelectorAll('input, select').forEach(el => {
                    if (el.name) {
                        el.name = el.name.replace(/\[\d+\]/, `[${rowIndex}]`);
                    }
                    el.value = '';
                });

                /* ✅ REMOVE DUPLICATE IDs */
                newRow.querySelectorAll('[id]').forEach(el => el.removeAttribute('id'));

                /* ✅ BUTTON VISIBILITY */
                const addButton = newRow.querySelector('.add-item-btn');
                if (addButton) {
                    addButton.style.display = 'none';
                }
                let removeButton = newRow.querySelector('.remove-item-btn');
                if (!removeButton) {
                    removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-danger remove-item-btn';
                    removeButton.innerHTML = '<i class="fas fa-minus"></i> -';
                    const btnContainer = newRow.querySelector('.col-md-1 div');
                    if (btnContainer) {
                        btnContainer.appendChild(removeButton);
                    }
                } else {
                    removeButton.style.display = 'inline-block';
                }

                container.appendChild(newRow);

                /* ✅ RE-INIT select2 ON BOTH */
                initInventorySelect2(firstSelect);
                initInventorySelect2(newRow.querySelector('select'));

            }
        });

        /* ================= REMOVE ROW ================= */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('.inventory-item-row').remove();
                calculateTotalTaxableAmount();
            }
        });

        /* ================= INITIALIZATION ================= */
        // Initialize calculations on page load
        initializeRowTotals();
        calculateTotalTaxableAmount();

    });
</script>

@endsection
