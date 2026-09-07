@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
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
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="category-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    let table;

    $(document).ready(function() {
        const esc = TableHelper.escape;
        const editUrl = "{{ route('admin.category.edit', ['id' => ':id']) }}";

        table = new TableHelper({
            containerId: 'category-grid',
            apiUrl: "{{ route('admin.category') }}",
            perPage: 10,
            pagination: true,
            // Aisles are read and edited one at a time - no select column.
            enableCheckbox: false,
            emptyMessage: 'No categories match this search',

            columns: [
                {
                    name: 'Order',
                    field: 'sort_order',
                    width: '80px',
                    align: 'center',
                    render: (row) => `<span class="text-muted">${Number(row.sort_order)}</span>`
                },
                {
                    name: 'Image',
                    field: 'image_url',
                    width: '80px',
                    render: (row) => row.image_url
                        ? `<img src="${esc(row.image_url)}" class="cat-thumb" alt="">`
                        : `<div class="cat-thumb cat-thumb-empty"><i class="bx bx-image"></i></div>`
                },
                {
                    name: 'Name',
                    field: 'title',
                    render: (row) => `<span class="fw-semibold">${esc(row.title)}</span>`
                },
                {
                    name: 'Slug',
                    field: 'slug',
                    render: (row) => `<code class="small">${esc(row.slug)}</code>`
                },
                {
                    name: 'Items',
                    field: 'inventory_items_count',
                    align: 'center',
                    render: (row) => `<span class="badge bg-light text-dark">${Number(row.inventory_items_count)}</span>`
                },
                {
                    name: 'Status',
                    field: 'status',
                    align: 'center',
                    render: (row) => Number(row.status)
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>'
                },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        { type: 'edit', title: 'Edit', showLabel: false, url: editUrl.replace(':id', '{id}') },
                        {
                            type: 'delete',
                            title: 'Delete',
                            showLabel: false,
                            onClick: (row) => confirmDelete(row)
                        }
                    ]
                }
            ],

            enableSortColumns: ['sort_order', 'title', 'slug', 'inventory_items_count', 'status'],

            // No filter bar above this table - a shop has a handful of aisles.
            // The header row and the search box are how you find one.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'title', type: 'text', param: 'title', placeholder: 'Name' },
                    { field: 'slug', type: 'text', param: 'slug', placeholder: 'Slug' }
                ]
            },

            search: { placeholder: 'Search category name or slug...' }
        });
    });

    function confirmDelete(row) {
        const items = Number(row.inventory_items_count || 0);

        // Deleting a category with items would cascade the inventory away,
        // so stop the shop before it happens rather than after.
        if (items > 0) {
            shoporaToast.info(`This category still has ${items} inventory item(s). Move or remove them first.`, 'Not allowed');
            return;
        }

        const deleteUrl = "{{ route('admin.category.delete', ['id' => ':id']) }}".replace(':id', row.id);

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
                    shoporaToast.success('The category has been deleted.', 'Deleted!');
                    table.refresh();
                })
                .fail((xhr) => shoporaToast.error(
                    (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.', 'Error!'));
        });
    }
</script>
@endsection
