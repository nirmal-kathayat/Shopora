@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Customers</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customer List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.customer.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Customer
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customerTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Saved</th>
                                <th>PAN Number</th>
                                <th>Account</th>
                                <th>Added</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- every delivery address this customer keeps -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addressModalLabel">Delivery addresses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="addressModalCustomer"></p>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Label</th>
                                <th>Address</th>
                                <th>Receiver</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody id="addressModalRows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section("script")
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
    // Empty cells read better as a dash than as a blank or "null".
    function dash(data) {
        return (data === null || data === '') ? '<span class="text-muted">&mdash;</span>' : data;
    }

    $(document).ready(function() {
        const table = $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.customer') }}",
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return meta.row + 1; // For numbering rows
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: false,
                },
                {
                    data: 'email',
                    name: 'email',
                    orderable: false,
                    render: dash
                },
                {
                    data: 'ph_number',
                    name: 'ph_number',
                    orderable: false,
                },
                {
                    data: 'address',
                    name: 'address',
                    orderable: false,
                    render: dash
                },
                {
                    data: 'pan_number',
                    name: 'pan_number',
                    orderable: false,
                    render: dash
                },
                {
                    // Signed up on the storefront, or typed in at the counter?
                    data: 'is_registered',
                    name: 'is_registered',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return Number(data)
                            ? '<span class="badge bg-success">Registered</span>'
                            : '<span class="badge bg-secondary">Walk-in</span>';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    orderable: false,
                    searchable: false,
                    render: dash
                },
                {
                    data: 'address_count',
                    name: 'address_count',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var count = Number(full.address_count || 0);
                        var tone = count > 0 ? 'bg-primary' : 'bg-secondary';
                        return `<span class="badge ${tone}">${count}</span>`;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var editUrl = "{{route('admin.customer.edit', ['id' => ':id'])}}".replace(':id', full.id);
                        var deleteButton = `<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="${full.id}"><i class="bx bx-trash"></i></a>`;
                        var editButton = `<a class="btn btn-primary btn-sm" href="${editUrl}"><i class="bx bx-edit"></i></a>`;
                        var disabled = Number(full.address_count || 0) > 0 ? '' : ' disabled';
                        var addressButton = `<a class="btn btn-outline-primary addressAction btn-sm${disabled}" href="javascript:void(0)" title="Delivery addresses" data-id="${full.id}"><i class="bx bx-map"></i></a>`;
                        return `<div class="d-flex gap-sm">${editButton} ${addressButton} ${deleteButton}</div>`;
                    }
                }
            ]
        });

        // Delete action using AJAX
        // the customer's saved delivery addresses
        $('#customerTable').on('click', '.addressAction', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) return;

            var url = "{{ route('admin.customer.addresses', ['id' => ':id']) }}".replace(':id', $(this).data('id'));
            var rows = $('#addressModalRows');

            rows.html('<tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>');
            $('#addressModalCustomer').text('');
            new bootstrap.Modal(document.getElementById('addressModal')).show();

            $.get(url)
                .done(function(response) {
                    $('#addressModalCustomer').text(response.customer || '');

                    if (!response.addresses || !response.addresses.length) {
                        rows.html('<tr><td colspan="5" class="text-center text-muted py-4">No saved addresses.</td></tr>');
                        return;
                    }

                    rows.html(response.addresses.map(function(address, index) {
                        var text = function(value) {
                            return $('<div>').text(value || '-').html();
                        };
                        var label = text(address.label || 'Address') +
                            (address.is_default ? ' <span class="badge bg-success">Default</span>' : '');
                        var line = text(address.single_line) +
                            (address.landmark ? '<div class="text-muted small">Landmark: ' + text(address.landmark) + '</div>' : '');

                        return '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + label + '</td>' +
                            '<td>' + line + '</td>' +
                            '<td>' + text(address.recipient_name) + '</td>' +
                            '<td>' + text(address.ph_number) + '</td>' +
                            '</tr>';
                    }).join(''));
                })
                .fail(function() {
                    rows.html('<tr><td colspan="5" class="text-center text-danger py-4">Could not load those addresses.</td></tr>');
                });
        });

        $('#customerTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            var customerId = $(this).data('id');
            var deleteUrl = "{{ route('admin.customer.delete', ['id' => ':id']) }}".replace(':id', customerId);

            // SweetAlert confirmation
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'The customer has been deleted successfully.',
                                'success'
                            );
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'Something went wrong while deleting the customer.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });
</script>

@endsection