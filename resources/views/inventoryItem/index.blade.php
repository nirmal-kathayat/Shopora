@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Inventory Items</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory Item List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.inventoryItem.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Inventory Item
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <label for="categoryFilter" class="form-label mb-0 text-nowrap">Filter by Category</label>
                            <select id="categoryFilter" class="form-select form-control select2-container" style="width: 50%; flex: 1;">
                                <option value="">All Category</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="inventoryItemTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Inventory</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Price Per Unit</th>
                                <th>Code</th>
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
<style>
    .select2-container--default .select2-selection--single {
        border-radius: 0.375rem;
        border: 1px solid #0d6efd;
        padding: 0.75rem;
        min-height: 42px;
        font-size: 1rem;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 18px;
        font-size: 0.95rem;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    
    .select2-dropdown {
        border-radius: 0.375rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .select2-results__option {
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    
    .select2-results__option--highlighted {
        background-color: #0d6efd;
    }
    
    .select2-search__field {
        padding: 0.5rem;
        font-size: 0.95rem;
    }
</style>
<script>
    let table; // Global variable to store DataTable instance

    $(document).ready(function() {
        // Initialize Select2
        $('#categoryFilter').select2({
            allowClear: false,
            width: '50%'
        });

        // Set default value to "All Categories"
        $('#categoryFilter').val('').trigger('change');

        // Load categories
        loadCategories();

        // Initialize DataTable
        table = $('#inventoryItemTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.inventoryItem') }}",
                type: 'GET',
                data: function(d) {
                    d.category_id = $('#categoryFilter').val();
                },
                error: function(xhr) {
                    console.error('Inventory table load failed:', xhr.responseText || xhr.statusText);
                }
            },
            pageLength: 25,
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'title',
                    name: 'title',
                    orderable: false,
                },
                {
                    data: 'category_title',
                    name: 'categories.title',
                    orderable: false,
                },
                {
                    data: 'unit',
                    name: 'unit',
                    orderable: false,
                },
                {
                    data: 'price_per_unit',
                    name: 'price_per_unit',
                    orderable: false,
                },
                {
                    data: 'code',
                    name: 'code',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var imageUrl = full.image ? "{{ asset('image') }}/" + encodeURIComponent(full.image) : '';
                        var editUrl = "{{ route('admin.inventoryItem.edit', ['id' => ':id']) }}".replace(':id', full.id);
                        var editButton = '<a href="' + editUrl + '" class="btn btn-primary btn-sm"><i class="bx bx-edit"></i></a>';
                        var deleteButton = '<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-trash"></i></a>';
                        var actionButtons = '<div class="d-flex gap-sm-2">' + editButton + deleteButton + '</div>';
                        return actionButtons;
                    }
                }
            ],
            initComplete: function(settings, json) {
                console.log(json); // Log the received JSON data
            }
        });

        window.inventoryItemDataTable = table;

        // Category filter change event
        $('#categoryFilter').on('change', function() {
            table.draw();
        });

        // delete action
        $('#inventoryItemTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            var inventoryId = $(this).data('id');
            var deleteUrl = "{{route('admin.inventoryItem.delete',['id'=>':id'])}}".replace(':id', inventoryId);

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
                                'The inventory item has been deleted.',
                                'success'
                            );
                            table.draw();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the inventory item.',
                                'error'
                            );
                        }
                    });
                }
            });
        });
    });

    // Function to load categories
    function loadCategories() {
        $.ajax({
            url: "{{ route('admin.inventoryItem.categories') }}",
            type: 'GET',
            success: function(response) {
                var categorySelect = $('#categoryFilter');
                // Clear existing options except the first one
                categorySelect.find('option:not(:first)').remove();
                
                // Add categories
                $.each(response.categories, function(index, category) {
                    categorySelect.append('<option value="' + category.id + '">' + category.title + '</option>');
                });
                
                // Refresh Select2
                categorySelect.trigger('change');
            },
            error: function(xhr) {
                console.error('Error loading categories:', xhr);
            }
        });
    }
</script>
@endsection