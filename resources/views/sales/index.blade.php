@extends("layouts.app")
@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Top bar: Search + Customer + Actions -->
            <div class="sales-topbar">
                <div class="sales-search">
                    <div class="search-bar-sm position-relative">
                        <input type="text" id="search-input" class="form-control form-control-sm search-input sales-search-input" placeholder="Search inventory by name or code">
                        <i class="bx bx-search search-icon"></i>
                    </div>
                </div>

                <div class="sales-actions">
                    <button type="button" id="btn-inventory" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryItemModal">
                        Add Inventory Item
                    </button>
                </div>

                <div class="sales-customer">
                    <select name="customer_id" class="form-control select2 w-100" id="customer">
                        <option value=""></option>
                    </select>
                </div>

                <div class="sales-actions">
                    <button type="button" id="btn-add-customer" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                        Add Customer
                    </button>
                </div>
            </div>
            <hr>
            <!-- Category Filter -->
            <div class="col-md-3" style="margin-bottom: 10px;">
                <div class="cat-input">
                    <select name="category_filter" class="form-control select2-category" id="category-filter-select">
                        <option value="all" selected>All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row row-gap-sm">
                <div class="col-md-6 sales-filter-sm">
                    <!-- Inventory List -->
                    <div class="table-responsive table-sm-2">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Inventory Item Details</th>
                                </tr>
                            </thead>
                            <tbody id="inventory-list">
                                @if ($inventories->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No items found!
                                    </td>
                                </tr>
                                @else
                                @foreach ($inventories as $inventory)
                                <tr data-category="{{ $inventory->category_id }}">
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="text-sm">
                                                <span class="fw-bold" style="font-size: 12px;">{{ $inventory->title }}</span>
                                                <br>
                                                <small class="text-muted">- Rs.{{ number_format($inventory->price_per_unit, 0) }}</small>
                                            </div>
                                            <div class="plus-sm add-inventory"
                                                data-id="{{ $inventory->id }}"
                                                data-title="{{ $inventory->title }}"
                                                data-price_per_unit="{{ $inventory->price_per_unit }}"
                                                data-unit="{{ $inventory->unit }}"
                                                data-code="{{ $inventory->code }}">
                                                <i class="bx bx-plus"></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Selected Items Section -->
                <div class="col-md-6 position-relative col-sm-3" style='width: 522px;'>
                    <div id="selected-items" style="max-height: 300px; overflow-y: auto;"></div>
                    <div class="footer-section">
                        <div class="footer-cat">
                            <div id="payment-mode-section" style="display: none;">
                                <div class="form-group mb-2">
                                    <label for="payment-mode">Payment Mode:</label>
                                    <div class="d-flex gap-2">
                                        <select id="payment-mode" class="form-control form-control-sm">
                                            @foreach($paymentModes as $payment)
                                            <option value="{{ $payment->id }}">
                                                {{ $payment->payment_title }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#splitPaymentModal">
                                            Split
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <input type="number" id="discount-amount" class="form-control form-control-sm" placeholder="Discount" min="0" step="0.01">
                                </div>
                            </div>
                            <button type="button" class="btn-confirm">Confirm</button>
                        </div>
                        <div class="price-cat">
                            <div class="price-sm-cat">
                                <span class="total-sm">Total Amount</span>
                                <span class="order-total">Rs. 0.00</span>
                            </div>
                            <div class="label-amt">
                                <label for="received-amount">Received Amount:</label>
                                <input type="number" id="received-amount" class="amount-cal" min="0" step="0.01">
                            </div>
                            <div class="label-amt">
                                <label for="change-amount">Change Amount: </label>
                                <input type="number" id="change-amount" class="amount-cal" readonly>
                            </div>
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
            placeholder: 'Select customer',
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
            placeholder: 'Select category',
            allowClear: false
        });

        // Category filter handler
        $('#category-filter-select').on('change', function() {
            const category = $(this).val();

            if (category === 'all') {
                $('#inventory-list tr').show();
            } else {
                $('#inventory-list tr').each(function() {
                    if ($(this).data('category') == category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
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