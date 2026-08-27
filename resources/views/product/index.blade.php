@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Product List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.product.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Product
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="productTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Code</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal fade" id="addQuantityModal" tabindex="-1" aria-labelledby="addQuantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuantityModalLabel">Inventory Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addQuantityForm">
                    @csrf
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="qty" required>
                    </div>
                    <input type="hidden" class="productId" name="product_id">
                    <!-- <button type="submit" class="btn btn-primary">Submit</button> -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveCategoryBtn">Submit</button>
                    </div>
                </form>
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
        $('#productTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.product') }}",
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: false,
                },
                {
                    data: 'category_title',
                    name: 'categories.title',
                    orderable: false,
                },
                {
                    data: 'supplier_name',
                    name: 'suppliers.name',
                    orderable: false,
                },
                {
                    data: 'qty',
                    name: 'products.qty',
                    orderable: false,
                },
                {
                    data: 'price',
                    name: 'products.price',
                    orderable: false,
                },
                {
                    data: 'code',
                    name: 'products.code',
                    orderable: false,
                },
                {
                    data: 'unit',
                    name: 'products.unit',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var viewUrl = "{{route('admin.product.view',['id'=>':id'])}}".replace(':id', full.id);
                        var editUrl = "{{route('admin.product.edit',['id'=>':id'])}}".replace(':id', full.id);

                        var viewButton = '<a class="btn btn-info btn-sm" href="' + viewUrl + '"><i class="bx bx-show"></i></a>';
                        var editButton = '<a class="btn btn-primary btn-sm" href="' + editUrl + '"><i class="bx bx-edit"></i></a>';
                        var deleteButton = '<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-trash"></i></a>';
                        var addButton = '<a class="btn btn-secondary addQuantityButton btn-sm" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-plus"></i></a>';
                        var actionButtons = '<div class="d-flex gap-sm-2">' + viewButton + editButton + deleteButton + addButton + '</div>';
                        return actionButtons;
                    }
                }
            ],
            initComplete: function(settings, json) {
                console.log(json); // Log the received JSON data
            }
        });

        $(document).on('click', '.addQuantityButton', function() {
            var productId = $(this).data('id');
            $('.productId').val(productId);
            $('#addQuantityModal').modal('show');
        });

        $('#addQuantityForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                type: "POST",
                url: "{{route('admin.product.records')}}",
                data: formData,
                success: function(response) {
                    $('#addQuantityModal').modal('hide');
                    $('#productTable').DataTable().ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Inventory record added successfully!'
                    });
                },
                error: function(error) {
                    // console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something Went wrong!'
                    });
                }
            });
        });
        $('#productTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            var productId = $(this).data('id');
            var deleteUrl = "{{route('admin.product.delete',['id'=>':id'])}}".replace(':id', productId);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'btn btn-success',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'The product has been deleted.',
                                'success'
                            );
                            $('#productTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the product.',
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