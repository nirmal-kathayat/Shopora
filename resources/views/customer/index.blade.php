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
                                <th>Address</th>
                                <th>Phone Number</th>
                                <th>Action</th>
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
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
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
                    data: 'address',
                    name: 'address',
                    orderable: false,
                },
                {
                    data: 'ph_number',
                    name: 'ph_number',
                    orderable: false,
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
                        return `<div class="d-flex gap-sm">${editButton} ${deleteButton}</div>`;
                    }
                }
            ]
        });

        // Delete action using AJAX
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