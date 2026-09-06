@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />

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
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Created_at</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('partials.invoice-modal')
</div>

@endsection

@section("script")

<!-- DataTables -->
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>

<script>
    const STATUS_TONES = {
        placed: 'bg-secondary',
        confirmed: 'bg-info',
        shipped: 'bg-warning',
        delivered: 'bg-success',
        cancelled: 'bg-danger',
    };

    function escapeText(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

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
                    name: 'order_by_name',
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
                    data: 'payment_method',
                    name: 'sales.payment_method',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full) {
                        // A counter sale leaves payment_method/payment_status at their
                        // defaults, so the bill was settled at the till and the
                        // storefront method/status pair says nothing useful here.
                        if (full.channel !== 'storefront') {
                            return '<span class="fw-medium">Counter</span>'
                                + '<div class="mt-1"><span class="badge bg-success">Paid</span></div>';
                        }
                        const label = data === 'esewa' ? 'eSewa' : 'Cash on Delivery';
                        let badge;
                        if (full.payment_status === 'paid') {
                            badge = '<span class="badge bg-success">Paid</span>';
                        } else if (full.payment_status === 'failed') {
                            badge = '<span class="badge bg-danger">Failed</span>';
                        } else if (data === 'cod') {
                            // COD is collected by the rider, so "unpaid" here just
                            // means the cash is due on delivery, not that anything failed.
                            badge = '<span class="badge bg-secondary">On delivery</span>';
                        } else {
                            badge = '<span class="badge bg-danger">Unpaid</span>';
                        }
                        return '<span class="fw-medium">' + escapeText(label) + '</span>'
                            + '<div class="mt-1">' + badge + '</div>';
                    }
                },
                {
                    data: 'status',
                    name: 'sales.status',
                    orderable: false,
                    render: function(data) {
                        const tone = STATUS_TONES[data] || 'bg-secondary';
                        return '<span class="badge ' + tone + '">' + escapeText(data) + '</span>';
                    }
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

    });
</script>



@endsection