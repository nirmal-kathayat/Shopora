@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
@media print {

    @page {
        size: 80mm auto;
        margin: 5mm;
    }

    body * {
        visibility: hidden;
    }

    .modal.show .print-area,
    .modal.show .print-area * {
        visibility: visible;
    }

    .modal.show .print-area {
        position: absolute;
        top: 0;
        left: 0;
        width: 80mm;
    }

    .no-print {
        display: none !important;
    }
}
</style>

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
                <div class="table-responsive">
                    <table id="invoiceTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Order By</th>
                                <th>Bill No</th>
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
    <div class="modal fade" id="invoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- PRINT AREA -->
            <div class="modal-body print-area">
                <!-- Invoice details will be loaded here -->
            </div>

            <div class="modal-footer no-print">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    Print
                </button>
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

<script>
    // Create payment mode mapping globally
    window.paymentModeMap = {};
    @foreach($paymentModes as $payment)
    window.paymentModeMap[{{ $payment->id }}] = '{{ $payment->payment_title }}';
    @endforeach

    let currentInvoice = null;
    let currentDetails = [];
    $(document).ready(function() {
        $('#invoiceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.invoice.index') }}",
            pageLength: 10,
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
                    orderable: false,

                },
                {
                    data: 'id',
                    name: 'sales.id',
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return 'T' + data + '-80/81';
                    }
                },
                {
                    data: 'customer_title',
                    name: 'customers.name',
                    orderable: false,

                },
                {
                    data: 'created_at',
                    name: 'sales.created_at',
                    orderable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var viewButton = '<a class="btn btn-info btn-sm view-invoice" data-id="' + full.id + '"><i class="bx bx-show"></i></a>';
                        var actionButton = '<div class="d-flex gap-sm-2">' + viewButton +'</div>';
                        return actionButton;
                    }
                }
            ]
        });

        // Handle view button for kot
        
        
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
                    </table>
                    
                    <h5 style="border-bottom:1px dashed #111 !important;font-size:14px;padding-bottom:10px;">Thank you for visiting.</h5>
                </div>
            `);
                $('#invoiceModal').modal('show');
            });
        });
    });
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