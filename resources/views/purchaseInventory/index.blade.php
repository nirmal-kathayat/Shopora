@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<style>
    /* Scrollable, but without the bar down the side - same as the form modal.
       It still scrolls by wheel, trackpad, touch, arrow keys and Page Up/Down. */
    #purchaseBillModal .modal-body {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    #purchaseBillModal .modal-body::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    /* The item table has four columns and the summary sits beside it, so this
       one wants more room than a stock modal-xl gives on a wide screen. */
    @media (min-width: 1200px) {
        #purchaseBillModal .modal-dialog {
            max-width: 1080px;
        }
    }
</style>

@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Purchase Inventory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Purchase Inventory List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" id="btnNewPurchaseInventory" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseInventoryFormModal">
                        <i class="bx bx-plus me-1"></i> New Purchase Inventory
                    </button>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />

        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here. --}}
                <div id="purchase-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>

@include('purchaseInventory.formModal')

@endsection

<!-- Purchase Bill Modal -->
<div class="modal fade" id="purchaseBillModal" tabindex="-1" aria-labelledby="purchaseBillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseBillModalLabel">Purchase Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="billContent">
                    <!-- Bill content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPurchaseBill()">Print Bill</button>
            </div>
        </div>
    </div>
</div>

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    let table;

    $(document).ready(function() {
        table = new TableHelper({
            containerId: 'purchase-grid',
            apiUrl: "{{ route('admin.purchaseInventory') }}",
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'No purchase bills match these filters',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Vendor Name', field: 'vendor_name' },
                { name: 'Bill Date', field: 'purchase_date' },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        {
                            type: 'view',
                            title: 'View bill',
                            showLabel: false,
                            onClick: (row) => loadPurchaseBill(row.id)
                        },
                        {
                            type: 'edit',
                            title: 'Edit',
                            showLabel: false,
                            onClick: (row) => window.openPurchaseInventoryForEdit(row.id)
                        },
                        {
                            type: 'delete',
                            title: 'Delete',
                            showLabel: false,
                            onClick: (row) => confirmDelete(row.id)
                        }
                    ]
                }
            ],

            enableSortColumns: ['vendor_name', 'purchase_date'],

            // No filter bar above this table, same as Categories: the header
            // row and the search box are how you find a bill.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'vendor_name', type: 'text', param: 'vendor_name', placeholder: 'Vendor' }
                ]
            },

            search: { placeholder: 'Search vendor, PAN or address...' }
        });

        window.purchaseInventoryDataTable = table;
    });

    function confirmDelete(id) {
        const deleteUrl = "{{ route('admin.purchaseInventory.delete', ['id' => ':id']) }}".replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'btn btn-success',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.get(deleteUrl, { "_token": "{{ csrf_token() }}" })
                .done(() => {
                    shoporaToast.success('The purchase inventory has been deleted.', 'Deleted!');
                    table.refresh();
                })
                .fail(() => shoporaToast.error('There was an error deleting the inventory.', 'Error!'));
        });
    }

    /** Rows of the bill shown at once. A long bill is read a page at a time. */
    const BILL_PAGE_SIZE = 10;

    let billData = null;
    let billPage = 1;

    /** Fetch one purchase bill and show it in the bill modal. */
    function loadPurchaseBill(purchaseId) {
        const url = "{{ route('admin.purchaseInventory.view', ['id' => ':id']) }}".replace(':id', purchaseId);

        $.get(url)
            .done(function (response) {
                billData = response;
                billPage = 1;
                renderBill();

                const billModalEl = document.getElementById('purchaseBillModal');
                if (billModalEl && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(billModalEl).show();
                }
            })
            .fail(function () {
                shoporaToast.error('Unable to load bill details.', 'Error!');
            });
    }

    function renderBill() {
        $('#billContent').html(generateBillHtml(billData, billPage));
    }

    // The pager is rebuilt with the bill, so the handler is delegated.
    $(document).on('click', '#billContent [data-bill-page]', function (e) {
        e.preventDefault();
        const page = Number(this.dataset.billPage);
        if (page && page !== billPage) {
            billPage = page;
            renderBill();
        }
    });

    /**
     * Previous / numbers / Next for the bill's items.
     *
     * Windowed to five numbers so a fifty-line bill does not grow a pager
     * wider than the modal.
     */
    function billPagination(page, pages, total, from, to) {
        if (pages <= 1) return '';

        const item = (label, target, opts = {}) => `
            <li class="page-item ${opts.active ? 'active' : ''} ${opts.disabled ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0);" data-bill-page="${target}">${label}</a>
            </li>`;

        let first = Math.max(1, page - 2);
        const last = Math.min(pages, first + 4);
        first = Math.max(1, last - 4);

        let numbers = '';
        for (let i = first; i <= last; i++) numbers += item(i, i, { active: i === page });

        return `
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                <small class="text-muted">Showing ${from} to ${to} of ${total} items</small>
                <nav aria-label="Bill items">
                    <ul class="pagination pagination-sm mb-0">
                        ${item('Previous', page - 1, { disabled: page === 1 })}
                        ${numbers}
                        ${item('Next', page + 1, { disabled: page === pages })}
                    </ul>
                </nav>
            </div>`;
    }

    /**
     * Print the whole bill, not the page on screen.
     *
     * The lines are paged for reading, and only the page shown is in the DOM -
     * so printing straight from it would put ten lines on paper and quietly
     * drop the rest. Everything goes in, it prints, then it goes back.
     */
    function printPurchaseBill() {
        if (!billData) {
            window.print();
            return;
        }

        $('#billContent').html(generateBillHtml(billData, 1, true));
        window.print();
        renderBill();
    }

    /** The bill itself: vendor, one page of its lines, and what it came to. */
    function generateBillHtml(data, page, showAll) {
        const esc = TableHelper.escape;
        const items = data.items || [];

        // The summary is the whole bill, not the page on screen.
        let totalTaxable = 0;
        items.forEach(function (item) {
            totalTaxable += item.qty * item.rate;
        });

        const pages = showAll ? 1 : Math.max(1, Math.ceil(items.length / BILL_PAGE_SIZE));
        const current = Math.min(Math.max(page, 1), pages);
        const start = showAll ? 0 : (current - 1) * BILL_PAGE_SIZE;
        const shown = showAll ? items : items.slice(start, start + BILL_PAGE_SIZE);

        let itemsHtml = '';
        shown.forEach(function (item) {
            const totalAmount = item.qty * item.rate;
            itemsHtml += `
                <tr>
                    <td>${esc(item.inventory_item ? item.inventory_item.title : 'N/A')}</td>
                    <td class="text-end">${Number(item.qty)}</td>
                    <td class="text-end">${parseFloat(item.rate).toFixed(2)}</td>
                    <td class="text-end">${totalAmount.toFixed(2)}</td>
                </tr>
            `;
        });

        if (!shown.length) {
            itemsHtml = '<tr><td colspan="4" class="text-center text-muted py-4">This bill has no items.</td></tr>';
        }

        const vatAmount = parseFloat(data.vat_amount) || 0;
        const amountAfterVat = totalTaxable + vatAmount;

        return `
            <div class="bill-container" style="padding: 20px;">
                <div class="bill-details mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Vendor:</strong> ${esc(data.vendor || 'N/A')}</p>
                            <p><strong>Bill Date:</strong> ${esc(data.bill_date ? String(data.bill_date).slice(0, 10) : 'N/A')}</p>
                            <p><strong>Address:</strong> ${esc(data.address || 'N/A')}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>PAN Number:</strong> ${esc(data.pan_number || 'N/A')}</p>
                            <p><strong>Bill No:</strong> #${Number(data.id)}</p>
                        </div>
                    </div>
                </div>

                <div class="bill-items mb-4">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    ${billPagination(current, pages, items.length, items.length ? start + 1 : 0, start + shown.length)}
                </div>

                <div class="bill-summary">
                    <div class="row">
                        <div class="col-md-7"></div>
                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Total Taxable Amount:</strong></td>
                                    <td class="text-end">${totalTaxable.toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td><strong>VAT Amount:</strong></td>
                                    <td class="text-end">${vatAmount.toFixed(2)}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Amount After VAT:</strong></td>
                                    <td class="text-end"><strong>${amountAfterVat.toFixed(2)}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
</script>
@endsection
