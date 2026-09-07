@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<style>
    .records-panel-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 600;
        color: #1a3a6b;
    }

    .records-panel-title i {
        font-size: 1.25rem;
        line-height: 1;
    }

    .records-panel-title .in {
        color: #16a34a;
    }

    .records-panel-title .out {
        color: #dc2626;
    }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Store Records</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.purchaseInventory.storeRecords') }}"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $item ?? 'Item' }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.purchaseInventory.storeRecords') }}" class="btn btn-primary">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />

        <!-- what came in -->
        <div class="card">
            <div class="card-body">
                <h6 class="records-panel-title"><i class="bx bx-down-arrow-alt in"></i> Purchases</h6>
                <div id="purchase-records-grid" class="shopora-grid"></div>
            </div>
        </div>

        <!-- what went out -->
        <div class="card">
            <div class="card-body">
                <h6 class="records-panel-title"><i class="bx bx-up-arrow-alt out"></i> Sales</h6>
                <div id="sales-records-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    $(document).ready(function() {
        const recordsUrl = "{{ route('admin.purchaseInventory.viewRecord', ['id' => request()->route('id')]) }}";

        // Two tables on one page. The component namespaces its sort handler,
        // select-all and totals row by containerId, so the two do not reach
        // into each other - but they share #applyFilters / #clearFilters, so
        // neither declares a filter bar.

        new TableHelper({
            containerId: 'purchase-records-grid',
            apiUrl: recordsUrl,
            extraParams: () => ({ type: 'purchase' }),
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'No purchases recorded for this item',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Vendor', field: 'vendor' },
                { name: 'Purchase Date', field: 'purchase_date' },
                { name: 'Rate Per Piece', field: 'rate', align: 'right' },
                { name: 'Quantity', field: 'qty', align: 'right' }
            ],

            enableSortColumns: ['vendor', 'purchase_date', 'rate', 'qty'],
            filters: { autoGenerateColumnFilters: false },
            search: { placeholder: 'Search vendor...' }
        });

        new TableHelper({
            containerId: 'sales-records-grid',
            apiUrl: recordsUrl,
            extraParams: () => ({ type: 'sales' }),
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'This item has not been sold yet',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                {
                    name: 'Sale',
                    field: 'sales_id',
                    render: (row) => row.sales_id ? 'T' + Number(row.sales_id) : '-'
                },
                { name: 'Quantity', field: 'qty', align: 'right' },
                { name: 'Sold On', field: 'created_at' }
            ],

            enableSortColumns: ['qty', 'created_at'],
            filters: { autoGenerateColumnFilters: false },
            // Nothing here is text to search - the sale number and the date are
            // what identify a line, and both are sortable.
            search: false
        });
    });
</script>
@endsection
