@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<style>
    .rev-stars { color: #f59e0b; letter-spacing: 1px; }

    /* The grid keeps cells on one line, so a long review would run off and
       stretch the whole table. Cap the column and cut the overflow with an
       ellipsis; the full text stays reachable on hover. */
    .rev-body {
        width: 340px;
        max-width: 340px;
    }

    .rev-body .rev-text {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
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
                        <li class="breadcrumb-item active" aria-current="page">Product Reviews</li>
                    </ol>
                </nav>
            </div>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here. --}}
                <div id="review-grid" class="shopora-grid"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script src="{{asset('assets/js/table-helper.js')}}?v=1"></script>
<script>
    $(document).ready(function() {
        const esc = TableHelper.escape;
        const stars = (n) => '\u2605'.repeat(Number(n)) + '\u2606'.repeat(5 - Number(n));

        new TableHelper({
            containerId: 'review-grid',
            apiUrl: "{{ route('admin.review') }}",
            perPage: 10,
            pagination: true,
            enableCheckbox: false,
            emptyMessage: 'No reviews match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                {
                    name: 'Product',
                    field: 'product_title',
                    render: (row) => `<span class="fw-semibold">${esc(row.product_title)}</span>`
                },
                { name: 'Customer', field: 'customer_name' },
                {
                    name: 'Rating',
                    field: 'rating',
                    render: (row) => `<span class="rev-stars">${stars(row.rating)}</span>`
                        + ` <span class="text-muted">${Number(row.rating)}</span>`
                },
                {
                    name: 'Review',
                    field: 'body',
                    render: function(row) {
                        const heading = row.title
                            ? `<div class="fw-semibold rev-text">${esc(row.title)}</div>`
                            : '';
                        // One line with an ellipsis; the whole thing is in the
                        // title attribute. escape() leaves quotes alone, which
                        // would otherwise break out of the attribute.
                        const tip = esc([row.title, row.body].filter(Boolean).join(' \u2014 ')).replace(/"/g, '&quot;');

                        return `<div class="rev-body" title="${tip}">${heading}`
                            + `<div class="text-muted small rev-text">${esc(row.body) || '\u2014'}</div></div>`;
                    }
                },
                { name: 'Date', field: 'created_at' },
                {
                    name: 'Action',
                    type: 'actions',
                    actions: [
                        {
                            type: 'delete',
                            title: 'Delete',
                            showLabel: false,
                            onClick: (row) => confirmDelete(row.id)
                        }
                    ]
                }
            ],

            enableSortColumns: ['product_title', 'customer_name', 'rating', 'created_at'],

            // No filter bar above this table: the header row and the search
            // box are how you find a review.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'product_title', type: 'text', param: 'product_title', placeholder: 'Product' },
                    { field: 'customer_name', type: 'text', param: 'customer_name', placeholder: 'Customer' }
                ]
            },

            search: { placeholder: 'Search product, customer or review text...' }
        });
    });

    function confirmDelete(id) {
        const deleteUrl = "{{ route('admin.review.delete', ['id' => ':id']) }}".replace(':id', id);

        Swal.fire({
            title: 'Delete this review?',
            text: "This removes the customer's review permanently.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    }
</script>
@endsection
