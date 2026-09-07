@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<style>
    .deal-thumb { width: 76px; height: 48px; border-radius: 8px; object-fit: cover; background: #f3f6fb; }
    .deal-thumb-empty { display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 20px; }
        /* The heading is a sentence, so let it wrap instead of widening the
       column past everything else on the row. */
    .deal-heading-cell { max-width: 420px; white-space: normal; }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Storefront</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Deals Section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.dealSection.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Deals Section
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <div class="alert alert-light border d-flex align-items-start gap-2" role="note">
            <i class="bx bx-info-circle font-22 text-primary"></i>
            <div>
                The "Deals this week" band under the homepage banner.
                <strong>Only the Active one is shown</strong> &mdash; making another one active
                turns this one off, so next week's offers can be written ahead of time.
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="deal-grid" class="shopora-grid"></div>
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
        const editUrl = "{{ route('admin.dealSection.edit', ['id' => ':id']) }}";

        table = new TableHelper({
            containerId: 'deal-grid',
            apiUrl: "{{ route('admin.dealSection') }}",
            perPage: 10,
            pagination: true,
            // Bands are edited one at a time - no select column.
            enableCheckbox: false,
            emptyMessage: 'No deals sections match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                {
                    name: 'Preview',
                    field: 'image_url',
                    width: '100px',
                    render: (row) => row.image_url
                        ? `<img src="${esc(row.image_url)}" class="deal-thumb" alt="">`
                        : `<div class="deal-thumb deal-thumb-empty"><i class="bx bx-image"></i></div>`
                },
                {
                    name: 'Heading',
                    field: 'heading',
                    render: (row) => {
                        const sub = row.subheading
                            ? `<div class="small text-muted">${esc(row.subheading)}</div>` : '';
                        return `<div class="deal-heading-cell"><div class="fw-semibold">${esc(row.heading)}</div>${sub}</div>`;
                    }
                },
                {
                    name: 'Cards',
                    field: 'cards_count',
                    align: 'center',
                    render: (row) => `<span class="badge bg-light text-dark">${Number(row.cards_count)}</span>`
                },
                {
                    // Active is the one the storefront is serving right now.
                    name: 'Status',
                    field: 'status',
                    align: 'center',
                    render: (row) => Number(row.status)
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>'
                },
                { name: 'Last updated', field: 'updated_at' },
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

            enableSortColumns: ['heading', 'cards_count', 'status', 'updated_at'],

            // No filter bar above this table, same as Categories: a shop keeps
            // a handful of these, and the header row is enough to find one.
            filters: {
                autoGenerateColumnFilters: false,
                columnFilters: [
                    { field: 'heading', type: 'text', param: 'heading', placeholder: 'Heading' },
                    {
                        field: 'status',
                        type: 'select',
                        param: 'status',
                        options: [
                            { value: '1', text: 'Active' },
                            { value: '0', text: 'Inactive' }
                        ]
                    }
                ]
            },

            search: { placeholder: 'Search heading or subheading...' }
        });
    });

    function confirmDelete(row) {
        const deleteUrl = "{{ route('admin.dealSection.delete', ['id' => ':id']) }}".replace(':id', row.id);

        Swal.fire({
            title: 'Are you sure?',
            // Its cards go with it, and the homepage falls back to the
            // built-in offers - worth saying before it happens.
            text: Number(row.status)
                ? "This one is active. Deleting it removes its cards and leaves the homepage on its default deals."
                : "This removes the section and all of its cards.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.get(deleteUrl, { "_token": "{{ csrf_token() }}" })
                .done(() => {
                    shoporaToast.success('The deals section has been deleted.', 'Deleted!');
                    table.refresh();
                })
                .fail(() => shoporaToast.error('Something went wrong while deleting.', 'Error!'));
        });
    }
</script>
@endsection
