@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="{{ asset('assets/css/date-filter.css') }}?v=1" rel="stylesheet" />
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

        {{-- One filter bar. The dashboard keeps its quick-range tabs; here the
             range opens on the current month and is simply picked. --}}
        <div class="shopora-date-filter">
            <div class="date-field">
                <label for="fromDate">From Date</label>
                <div class="date-input-wrap">
                    <input type="text" id="fromDate" class="date-picker" placeholder="Select From Date" readonly />
                    <i class='bx bx-calendar cal-icon'></i>
                </div>
            </div>
            <div class="date-field">
                <label for="toDate">To Date</label>
                <div class="date-input-wrap">
                    <input type="text" id="toDate" class="date-picker" placeholder="Select To Date" readonly />
                    <i class='bx bx-calendar cal-icon'></i>
                </div>
            </div>
            <div class="date-field is-wide">
                <label for="categoryFilter">Filter by Category</label>
                <select id="categoryFilter" class="form-select form-control">
                    <option value="">All Category</option>
                </select>
            </div>
            {{-- id is the component's own, so it binds and clears every filter
                 here in one go rather than this page binding a second handler --}}
            <button type="button" class="btn-clear-filter" id="clearFilters">Clear</button>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="inventory-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>

<!-- who has this product saved -->
<div class="modal fade" id="wishlistModal" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wishlistModalLabel">Wishlisted by</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="wishlistModalProduct"></p>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Saved on</th>
                            </tr>
                        </thead>
                        <tbody id="wishlistModalRows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section("script")
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<style>
    /* The category sits in the filter bar beside the two date boxes, so it
       takes their border, radius and height rather than its own blue one. */
    .shopora-date-filter .select2-container--default .select2-selection--single {
        height: 42px;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .shopora-date-filter .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #008cff;
        box-shadow: 0 0 0 3px rgba(0, 140, 255, 0.12);
    }

    /* dark theme - OFF for now, same as the grid's own block in gridtable.css
    html.dark-theme .shopora-date-filter .select2-container--default .select2-selection--single {
        background: #0d1315;
        border-color: #2a3236;
    }

    html.dark-theme .shopora-date-filter .select2-selection__rendered {
        color: #eef1f3 !important;
    }
    */
    
    .shopora-date-filter .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0;
        line-height: 24px;
        font-size: 14px;
    }

    .shopora-date-filter .select2-container--default .select2-selection--single .select2-selection__arrow {
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
    let table;

    $(document).ready(function() {
        $('#categoryFilter').select2({ allowClear: false, width: '100%' });
        $('#categoryFilter').val('').trigger('change');
        loadCategories();

        const editUrl = "{{ route('admin.inventoryItem.edit', ['id' => ':id']) }}";

        table = new TableHelper({
            containerId: 'inventory-grid',
            apiUrl: "{{ route('admin.inventoryItem') }}",
            perPage: 10,
            pagination: true,
            // A catalogue is read, not acted on in bulk - no select column.
            enableCheckbox: false,
            emptyMessage: 'No inventory items match these filters',

            // Opens on the month so far, which is the range someone asking
            // about stock almost always wants, and Clear comes back to it.
            dateRangeDefaults: {
                type: 'thisMonth',
                fromSelector: '#fromDate',
                toSelector: '#toDate',
            },

            // Select2 draws its own box, so resetting the underlying <select>
            // is not enough - it has to be told, and told with the namespaced
            // event so the table does not reload a second time.
            onClear: function() {
                $('#categoryFilter').val('').trigger('change.select2');
                table.applyDefaultDateRange();
            },

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                { name: 'Inventory', field: 'title' },
                { name: 'Category', field: 'category_title' },
                { name: 'Unit', field: 'unit' },
                { name: 'Price Per Unit', field: 'price_per_unit', align: 'right' },
                { name: 'Code', field: 'code' },
                {
                    name: 'Wishlist',
                    field: 'wishlist_count',
                    align: 'center',
                    render: function(row) {
                        const count = Number(row.wishlist_count || 0);
                        return '<span class="badge ' + (count > 0 ? 'bg-danger' : 'bg-secondary') + '">' + count + '</span>';
                    }
                },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        // Icon-only, as this table has always been - the
                        // three buttons are recognisable and the column is
                        // narrow enough already.
                        { type: 'edit', title: 'Edit', showLabel: false, url: editUrl.replace(':id', '{id}') },
                        {
                            type: 'custom',
                            icon: 'bx bx-heart',
                            class: 'btn btn-sm btn-outline-danger',
                            title: 'Wishlisted by',
                            showLabel: false,
                            // Nobody has saved it, so there is no list to open.
                            disabled: function(row) { return Number(row.wishlist_count || 0) === 0; },
                            onClick: function(row) { openWishlist(row.id); }
                        },
                        {
                            type: 'delete',
                            title: 'Delete',
                            showLabel: false,
                            onClick: function(row) { confirmDelete(row.id); }
                        }
                    ]
                }
            ],

            enableSortColumns: ['title', 'category_title', 'unit', 'price_per_unit', 'code', 'wishlist_count'],

            filters: {
                // emptyMeans is left at its default 'all': a blank box is
                // every item, not today.
                dateRange: { fromId: 'fromDate', toId: 'toDate' },
                additional: [{ id: 'categoryFilter', param: 'category_id' }],
                autoReload: ['#categoryFilter', '#fromDate', '#toDate'],
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'title', type: 'text', param: 'title', placeholder: 'Item' },
                    { field: 'code', type: 'text', param: 'code', placeholder: 'Code' }
                ]
            },

            search: { placeholder: 'Search item, code or brand...' }
        });

        window.inventoryItemDataTable = table;

        // The pickers themselves are the component's - it finds .date-picker
        // and binds flatpickr, then fills them with the default range.
    });

    function openWishlist(id) {
        const url = "{{ route('admin.inventoryItem.wishlist', ['id' => ':id']) }}".replace(':id', id);
        const rows = $('#wishlistModalRows');

        rows.html('<tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>');
        $('#wishlistModalProduct').text('');
        new bootstrap.Modal(document.getElementById('wishlistModal')).show();

        $.get(url)
            .done(function(response) {
                $('#wishlistModalProduct').text(response.product || '');

                if (!response.customers || !response.customers.length) {
                    rows.html('<tr><td colspan="5" class="text-center text-muted py-4">No one has saved this product yet.</td></tr>');
                    return;
                }

                rows.html(response.customers.map(function(customer, index) {
                    return '<tr>' +
                        '<td>' + (index + 1) + '</td>' +
                        '<td>' + $('<div>').text(customer.name || '-').html() + '</td>' +
                        '<td>' + $('<div>').text(customer.ph_number || '-').html() + '</td>' +
                        '<td>' + $('<div>').text(customer.email || '-').html() + '</td>' +
                        '<td>' + $('<div>').text(customer.saved_at || '-').html() + '</td>' +
                        '</tr>';
                }).join(''));
            })
            .fail(function() {
                rows.html('<tr><td colspan="5" class="text-center text-danger py-4">Could not load that list.</td></tr>');
            });
    }

    // Our own dialog rather than the action's `confirm`, which is the
    // browser's bare confirm() box.
    function confirmDelete(id) {
        const deleteUrl = "{{route('admin.inventoryItem.delete',['id'=>':id'])}}".replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'btn btn-success',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.get(deleteUrl, { "_token": "{{ csrf_token() }}" })
                .done(function() {
                    shoporaToast.success('The inventory item has been deleted.', 'Deleted!');
                    table.refresh();
                })
                .fail(function() {
                    shoporaToast.error('There was an error deleting the inventory item.', 'Error!');
                });
        });
    }

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