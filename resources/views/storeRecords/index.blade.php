@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Record Details</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory Records</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="records-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    $(document).ready(function() {
        const viewUrl = "{{ route('admin.purchaseInventory.viewRecord', ['id' => ':id']) }}";

        new TableHelper({
            containerId: 'records-grid',
            apiUrl: "{{ route('admin.purchaseInventory.storeRecords') }}",
            perPage: 10,
            pagination: true,
            // Read-only: this is what is on the shelf, not a list to act on.
            enableCheckbox: false,
            emptyMessage: 'No stock records match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Inventory Items', field: 'inventory_title' },
                {
                    name: 'Total Quantity',
                    field: 'net_qty',
                    align: 'right',
                    // Net stock can go negative if a sale was recorded against
                    // units the purchase side never had; showing it plainly is
                    // how anyone notices.
                    render: (row) => Number(row.net_qty)
                },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        {
                            type: 'view',
                            title: 'View records',
                            showLabel: false,
                            url: viewUrl.replace(':id', '{inventory_item_id}')
                        }
                    ]
                }
            ],

            enableSortColumns: ['inventory_title', 'net_qty'],

            // No filter bar above this table, same as Categories: the header
            // row and the search box are how you find an item.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'inventory_title', type: 'text', param: 'inventory_title', placeholder: 'Item' }
                ]
            },

            search: { placeholder: 'Search inventory item...' }
        });
    });
</script>
@endsection
