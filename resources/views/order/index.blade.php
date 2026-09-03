@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Orders</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Storefront Orders</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <label for="statusFilter" class="form-label mb-0 text-nowrap">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="orderTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Placed</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- one order: what is in it, where it goes, and where it can go next -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <p class="text-muted mb-0">Loading...</p>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <select id="orderStatusSelect" class="form-select" style="min-width: 200px;"></select>
                    <button type="button" class="btn btn-primary" id="orderStatusSave" disabled>Update status</button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
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

    function money(value) {
        return 'Rs. ' + Number(value || 0).toLocaleString('en-IN');
    }

    function escapeText(value) {
        return $('<div>').text(value === null || value === undefined || value === '' ? '-' : value).html();
    }

    $(document).ready(function() {
        let openOrderId = null;

        const table = $('#orderTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.order') }}",
                data: function(d) {
                    d.status = $('#statusFilter').val();
                }
            },
            pageLength: 25,
            columns: [
                { data: 'id', name: 'sales.id', searchable: false, render: (d, t, full) => full.DT_RowIndex },
                { data: 'code', name: 'sales.id', orderable: false, searchable: false },
                {
                    data: 'customer_name',
                    name: 'customers.name',
                    orderable: false,
                    render: function(data, type, full) {
                        return escapeText(full.customer_name) +
                            '<div class="text-muted small">' + escapeText(full.customer_phone) + '</div>';
                    }
                },
                { data: 'item_count', name: 'item_count', orderable: false, searchable: false },
                {
                    data: 'total',
                    name: 'total',
                    orderable: false,
                    searchable: false,
                    render: (data) => money(data)
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
                { data: 'created_at', name: 'sales.created_at', orderable: false, searchable: false },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full) {
                        return '<a class="btn btn-primary btn-sm viewOrder" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-show"></i></a>';
                    }
                }
            ]
        });

        $('#statusFilter').on('change', () => table.draw());

        $('#orderTable').on('click', '.viewOrder', function() {
            openOrderId = $(this).data('id');
            const url = "{{ route('admin.order.show', ['id' => ':id']) }}".replace(':id', openOrderId);

            $('#orderModalBody').html('<p class="text-muted mb-0">Loading...</p>');
            $('#orderStatusSelect').html('');
            $('#orderStatusSave').prop('disabled', true);
            new bootstrap.Modal(document.getElementById('orderModal')).show();

            $.get(url).done(function(order) {
                $('#orderModalLabel').text('Order ' + order.code);

                const rows = order.items.map(function(item) {
                    return '<tr>' +
                        '<td>' + escapeText(item.name) + '</td>' +
                        '<td class="text-end">' + item.qty + '</td>' +
                        '<td class="text-end">' + money(item.price_per_unit) + '</td>' +
                        '<td class="text-end">' + money(item.line_total) + '</td>' +
                        '</tr>';
                }).join('');

                $('#orderModalBody').html(
                    '<div class="row g-3 mb-3">' +
                        '<div class="col-md-6">' +
                            '<h6 class="mb-1">Customer</h6>' +
                            '<div>' + escapeText(order.customer.name) + '</div>' +
                            '<div class="text-muted small">' + escapeText(order.customer.phone) + '</div>' +
                            '<div class="text-muted small">' + escapeText(order.customer.email) + '</div>' +
                        '</div>' +
                        '<div class="col-md-6">' +
                            '<h6 class="mb-1">Delivering to</h6>' +
                            '<div>' + escapeText(order.delivery.address) + '</div>' +
                            (order.delivery.landmark ? '<div class="text-muted small">Landmark: ' + escapeText(order.delivery.landmark) + '</div>' : '') +
                            '<div class="text-muted small">' + escapeText(order.delivery.recipient) + ' &middot; ' + escapeText(order.delivery.phone) + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="table-responsive"><table class="table table-sm mb-0">' +
                        '<thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                        '<tfoot>' +
                            '<tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">' + money(order.subtotal) + '</td></tr>' +
                            '<tr><td colspan="3" class="text-end">Delivery</td><td class="text-end">' + money(order.delivery_fee) + '</td></tr>' +
                            '<tr><th colspan="3" class="text-end">Total</th><th class="text-end">' + money(order.total) + '</th></tr>' +
                        '</tfoot>' +
                    '</table></div>' +
                    '<p class="text-muted small mt-3 mb-0">Placed ' + escapeText(order.placed_at) + ' &middot; status: ' + escapeText(order.status) + '</p>'
                );

                if (!order.next_statuses.length) {
                    $('#orderStatusSelect').html('<option value="">No further steps</option>').prop('disabled', true);
                    $('#orderStatusSave').prop('disabled', true);
                    return;
                }

                $('#orderStatusSelect')
                    .prop('disabled', false)
                    .html(order.next_statuses.map(function(status) {
                        return '<option value="' + status + '">Mark as ' + status + '</option>';
                    }).join(''));
                $('#orderStatusSave').prop('disabled', false);
            }).fail(function() {
                $('#orderModalBody').html('<p class="text-danger mb-0">Could not load that order.</p>');
            });
        });

        $('#orderStatusSave').on('click', function() {
            if (!openOrderId) return;
            const url = "{{ route('admin.order.status', ['id' => ':id']) }}".replace(':id', openOrderId);
            const button = $(this).prop('disabled', true);

            $.post(url, {
                _token: "{{ csrf_token() }}",
                status: $('#orderStatusSelect').val()
            }).done(function(response) {
                bootstrap.Modal.getInstance(document.getElementById('orderModal')).hide();
                table.draw(false);
                Swal.fire({ icon: 'success', title: response.message, timer: 1800, showConfirmButton: false });
            }).fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: (xhr.responseJSON && xhr.responseJSON.message) || 'Could not update that order.'
                });
            }).always(function() {
                button.prop('disabled', false);
            });
        });
    });
</script>
@endsection
