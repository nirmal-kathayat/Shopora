@extends("layouts.app")

@section("style")
<link href="{{asset('assets/css/gridtable.css')}}?v=1" rel="stylesheet" />
<style>
    .hero-thumb {
        width: 76px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        background: #f3f6fb;
    }
    .hero-thumb-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 20px;
    }
    .hero-heading-cell {
        max-width: 420px;
        /* The headline is a sentence, so let it wrap instead of widening
           the column past everything else on the row. */
        white-space: normal;
    }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Storefront</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Hero Section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.heroSection.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Hero Section
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />

        <div class="alert alert-light border d-flex align-items-start gap-2" role="note">
            <i class="bx bx-info-circle font-22 text-primary"></i>
            <div>
                This is the banner at the top of the storefront homepage.
                <strong>Only the Active one is shown</strong> &mdash; making another one active
                turns this one off, so you can prepare a campaign and switch in one click.
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- TableHelper renders the whole table in here: header,
                     filter row, body, pager and the search box above it. --}}
                <div id="hero-grid" class="shopora-grid"></div>
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
        const editUrl = "{{ route('admin.heroSection.edit', ['id' => ':id']) }}";

        table = new TableHelper({
            containerId: 'hero-grid',
            apiUrl: "{{ route('admin.heroSection') }}",
            perPage: 10,
            pagination: true,
            // Banners are edited one at a time - no select column.
            enableCheckbox: false,
            emptyMessage: 'No hero sections match this search',

            columns: [
                { name: 'S.no', isSerialNo: true, width: '64px', align: 'center' },
                {
                    name: 'Preview',
                    field: 'image_url',
                    width: '100px',
                    render: (row) => row.image_url
                        ? `<img src="${esc(row.image_url)}" class="hero-thumb" alt="">`
                        : `<div class="hero-thumb hero-thumb-empty"><i class="bx bx-image"></i></div>`
                },
                {
                    name: 'Heading',
                    field: 'heading',
                    render: (row) => {
                        const badge = row.badge_text
                            ? `<div class="small text-muted">${esc(row.badge_text)}</div>`
                            : '';
                        return `<div class="hero-heading-cell"><div class="fw-semibold">${esc(row.heading)}</div>${badge}</div>`;
                    }
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

            enableSortColumns: ['heading', 'status', 'updated_at'],

            // No filter bar above this table, same as Categories: a shop keeps
            // a handful of banners, and the header row is enough to find one.
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

            search: { placeholder: 'Search heading, badge or button...' }
        });
    });

    function confirmDelete(row) {
        const deleteUrl = "{{ route('admin.heroSection.delete', ['id' => ':id']) }}".replace(':id', row.id);

        Swal.fire({
            title: 'Are you sure?',
            // Deleting the active hero leaves the storefront on its
            // built-in copy, worth saying out loud before it happens.
            text: Number(row.status)
                ? "This one is active. Deleting it leaves the homepage on its default banner."
                : "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.get(deleteUrl, { "_token": "{{ csrf_token() }}" })
                .done(() => {
                    shoporaToast.success('The hero section has been deleted.', 'Deleted!');
                    table.refresh();
                })
                .fail(() => shoporaToast.error('Something went wrong while deleting.', 'Error!'));
        });
    }
</script>
@endsection
