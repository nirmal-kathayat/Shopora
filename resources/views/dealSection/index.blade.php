@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
    .deal-thumb { width: 76px; height: 48px; border-radius: 8px; object-fit: cover; background: #f3f6fb; }
    .deal-thumb-empty { display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 20px; }
    .deal-heading-cell { max-width: 420px; }
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
                <div class="table-responsive">
                    <table id="dealTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Preview</th>
                                <th>Heading</th>
                                <th>Cards</th>
                                <th>Status</th>
                                <th>Last updated</th>
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

        const table = $('#dealTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.dealSection') }}",
            columns: [
                {
                    data: 'id', name: 'id', searchable: false, orderable: false,
                    render: (data, type, full, meta) => meta.row + 1
                },
                {
                    data: 'image_url', name: 'image', orderable: false, searchable: false,
                    render: (data) => data
                        ? `<img src="${data}" class="deal-thumb" alt="">`
                        : `<div class="deal-thumb deal-thumb-empty"><i class="bx bx-image"></i></div>`
                },
                {
                    data: 'heading', name: 'heading', orderable: false,
                    render: (data, type, full) => {
                        const sub = full.subheading
                            ? `<div class="small text-muted">${esc(full.subheading)}</div>` : '';
                        return `<div class="deal-heading-cell"><div class="fw-semibold">${esc(data)}</div>${sub}</div>`;
                    }
                },
                {
                    data: 'cards_count', name: 'cards_count', orderable: false, searchable: false,
                    render: (data) => `<span class="badge bg-light text-dark">${Number(data)}</span>`
                },
                {
                    data: 'status', name: 'status', orderable: false, searchable: false,
                    render: (data) => Number(data)
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>'
                },
                { data: 'updated_at', name: 'updated_at', orderable: false, searchable: false },
                {
                    data: 'action', name: 'action', orderable: false, searchable: false,
                    render: (data, type, full) => {
                        const editUrl = "{{route('admin.dealSection.edit', ['id' => ':id'])}}".replace(':id', full.id);
                        const del = `<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="${full.id}" data-active="${Number(full.status)}"><i class="bx bx-trash"></i></a>`;
                        const edit = `<a class="btn btn-primary btn-sm" href="${editUrl}"><i class="bx bx-edit"></i></a>`;
                        return `<div class="d-flex gap-sm">${edit} ${del}</div>`;
                    }
                }
            ]
        });

        $('#dealTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const isActive = Number($(this).data('active')) === 1;
            const deleteUrl = "{{ route('admin.dealSection.delete', ['id' => ':id']) }}".replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                // Its cards go with it, and the homepage falls back to the
                // built-in offers - worth saying before it happens.
                text: isActive
                    ? "This one is active. Deleting it removes its cards and leaves the homepage on its default deals."
                    : "This removes the section and all of its cards.",
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
                        data: { "_token": "{{ csrf_token() }}" },
                        success: function() {
                            Swal.fire('Deleted!', 'The deals section has been deleted.', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong while deleting.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
