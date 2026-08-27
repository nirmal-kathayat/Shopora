@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Store Records</div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.purchaseInventory.storeRecords') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <!-- purchase store record -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="storePurchaseTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Inventory Item</th>
                                <th>Purchase Date</th>
                                <th>Rate Per Piece</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!--  sales store records -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="storeSalesTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Inventory Item</th>
                                <th>Quantity</th>
                                <th>Created_at</th>
                            </tr>
                        </thead>
                    </table>
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
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // store purchase table
        $('#storePurchaseTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.purchaseInventory.viewRecord', ['id' => request()->route('id')]) }}",
                data: function(d) {
                    d.type = 'purchase';
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'inventory_title',
                    name: 'inventory_title',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'purchase_date',
                    name: 'purchase_date',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'rate',
                    name: 'rate',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'qty',
                    name: 'qty',
                    orderable: false,
                    searchable: false
                },

            ],
            initComplete: function(settings, json) {
                console.log(json);
            }
        });
    });

    // store sales table
    $(document).ready(function() {
        $('#storeSalesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.purchaseInventory.viewRecord', ['id' => request()->route('id')]) }}",
                data: function(d) {
                    d.type = 'sales';
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'inventory_title',
                    name: 'inventory_title',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'qty',
                    name: 'qty',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: false,
                    searchable: false
                },

            ],
            initComplete: function(settings, json) {
                console.log(json);
            }
        });
    });
</script>
@endsection