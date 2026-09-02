@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
    .cat-thumb { width: 56px; height: 42px; border-radius: 8px; object-fit: cover; background: #f3f6fb; }
    .cat-thumb-empty { display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 18px; }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Inventory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.category.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Category
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <div class="alert alert-light border d-flex align-items-start gap-2" role="note">
            <i class="bx bx-info-circle font-22 text-primary"></i>
            <div>
                Categories group your inventory and appear as browsable aisles on the storefront.
                The image and icon here are what shoppers see under <strong>Shop by category</strong>.
                <strong>Inactive</strong> ones stay usable for inventory but are hidden from the storefront.
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categoryTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Items</th>
                                <th>Status</th>
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
        const esc = (v) => $('<div>').text(v == null ? '' : v).html();

        const table = $('#categoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.category') }}",
            order: [],
            columns: [
                { data: 'sort_order', name: 'sort_order', searchable: false,
                  render: (data) => `<span class="text-muted">${Number(data)}</span>` },
                { data: 'image_url', name: 'image', orderable: false, searchable: false,
                  render: (data) => data
                    ? `<img src="${data}" class="cat-thumb" alt="">`
                    : `<div class="cat-thumb cat-thumb-empty"><i class="bx bx-image"></i></div>` },
                { data: 'title', name: 'title', render: (data) => `<span class="fw-semibold">${esc(data)}</span>` },
                { data: 'slug', name: 'slug', render: (data) => `<code class="small">${esc(data)}</code>` },
                { data: 'inventory_items_count', name: 'inventory_items_count', orderable: false, searchable: false,
                  render: (data) => `<span class="badge bg-light text-dark">${Number(data)}</span>` },
                { data: 'status', name: 'status', orderable: false, searchable: false,
                  render: (data) => Number(data)
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>' },
                {
                    data: 'action', name: 'action', orderable: false, searchable: false,
                    render: (data, type, full) => {
                        const editUrl = "{{route('admin.category.edit', ['id' => ':id'])}}".replace(':id', full.id);
                        const del = `<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="${full.id}" data-items="${Number(full.inventory_items_count)}"><i class="bx bx-trash"></i></a>`;
                        const edit = `<a class="btn btn-primary btn-sm" href="${editUrl}"><i class="bx bx-edit"></i></a>`;
                        return `<div class="d-flex gap-sm">${edit} ${del}</div>`;
                    }
                }
            ]
        });

        $('#categoryTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const items = Number($(this).data('items'));

            // Deleting a category with items would cascade the inventory away,
            // so stop the shop before it happens rather than after.
            if (items > 0) {
                Swal.fire('Not allowed', `This category still has ${items} inventory item(s). Move or remove them first.`, 'info');
                return;
            }

            const deleteUrl = "{{ route('admin.category.delete', ['id' => ':id']) }}".replace(':id', id);
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
                        url: deleteUrl, type: 'GET', data: { "_token": "{{ csrf_token() }}" },
                        success: () => { Swal.fire('Deleted!', 'The category has been deleted.', 'success'); table.ajax.reload(null, false); },
                        error: (xhr) => Swal.fire('Error!', (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.', 'error')
                    });
                }
            });
        });
    });
</script>
@endsection
