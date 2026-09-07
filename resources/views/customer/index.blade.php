@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
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
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="customer-grid" class="shopora-grid"></div>
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
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    let table;

    // Empty cells read better as a dash than as a blank or "null".
    function dash(value) {
        return (value === null || value === undefined || value === '')
            ? '<span class="text-muted">&mdash;</span>'
            : TableHelper.escape(value);
    }

    $(document).ready(function() {
        const editUrl = "{{ route('admin.customer.edit', ['id' => ':id']) }}";

        table = new TableHelper({
            containerId: 'customer-grid',
            apiUrl: "{{ route('admin.customer') }}",
            perPage: 10,
            pagination: true,
            // Customers are looked up and edited one at a time - no select column.
            enableCheckbox: false,
            emptyMessage: 'No customers match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                {
                    name: 'Name',
                    field: 'name',
                    render: (row) => `<span class="fw-semibold">${TableHelper.escape(row.name)}</span>`
                },
                { name: 'Email', field: 'email', render: (row) => dash(row.email) },
                { name: 'Phone Number', field: 'ph_number', render: (row) => dash(row.ph_number) },
                { name: 'Address', field: 'address', render: (row) => dash(row.address) },
                { name: 'PAN Number', field: 'pan_number', render: (row) => dash(row.pan_number) },
                {
                    // Signed up on the storefront, or typed in at the counter?
                    name: 'Account',
                    field: 'is_registered',
                    align: 'center',
                    render: (row) => Number(row.is_registered)
                        ? '<span class="badge bg-success">Registered</span>'
                        : '<span class="badge bg-secondary">Walk-in</span>'
                },
                {
                    name: 'Saved',
                    field: 'address_count',
                    align: 'center',
                    render: (row) => {
                        const count = Number(row.address_count || 0);
                        return `<span class="badge ${count > 0 ? 'bg-primary' : 'bg-secondary'}">${count}</span>`;
                    }
                },
                { name: 'Added', field: 'created_at', render: (row) => dash(row.created_at) },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        { type: 'edit', title: 'Edit', showLabel: false, url: editUrl.replace(':id', '{id}') },
                        {
                            type: 'view',
                            icon: 'bx bx-map',
                            title: 'Delivery addresses',
                            showLabel: false,
                            onClick: (row) => showAddresses(row)
                        },
                        {
                            type: 'delete',
                            title: 'Delete',
                            showLabel: false,
                            onClick: (row) => confirmDelete(row)
                        }
                    ]
                }
            ],

            enableSortColumns: ['name', 'email', 'ph_number', 'pan_number', 'is_registered', 'address_count', 'created_at'],

            // No filter bar above this table, same as Categories: the header
            // row and the search box are how you find someone.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'name', type: 'text', param: 'name', placeholder: 'Name' },
                    { field: 'email', type: 'text', param: 'email', placeholder: 'Email' },
                    { field: 'ph_number', type: 'text', param: 'ph_number', placeholder: 'Phone' },
                    {
                        field: 'is_registered',
                        type: 'select',
                        param: 'is_registered',
                        options: [
                            { value: '1', text: 'Registered' },
                            { value: '0', text: 'Walk-in' }
                        ]
                    }
                ]
            },

            search: { placeholder: 'Search name, email or phone...' }
        });
    });

    function showAddresses(row) {
        // Nothing saved - the modal would open on an empty table.
        if (Number(row.address_count || 0) === 0) {
            shoporaToast.info('This customer has no saved delivery addresses.', 'Nothing to show');
            return;
        }

        const url = "{{ route('admin.customer.addresses', ['id' => ':id']) }}".replace(':id', row.id);
        const rows = $('#addressModalRows');

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
                    const text = (value) => TableHelper.escape(value || '-');
                    const label = text(address.label || 'Address') +
                        (address.is_default ? ' <span class="badge bg-success">Default</span>' : '');
                    const line = text(address.single_line) +
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
    }

    function confirmDelete(row) {
        const deleteUrl = "{{ route('admin.customer.delete', ['id' => ':id']) }}".replace(':id', row.id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.get(deleteUrl, { "_token": "{{ csrf_token() }}" })
                .done(() => {
                    shoporaToast.success('The customer has been deleted successfully.', 'Deleted!');
                    table.refresh();
                })
                .fail(() => shoporaToast.error('Something went wrong while deleting the customer.', 'Error!'));
        });
    }
</script>

@endsection
