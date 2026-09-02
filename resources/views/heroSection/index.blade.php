@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
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
                <div class="table-responsive">
                    <table id="heroTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Preview</th>
                                <th>Heading</th>
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
        const table = $('#heroTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.heroSection') }}",
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'image_url',
                    name: 'image',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data
                            ? `<img src="${data}" class="hero-thumb" alt="">`
                            : `<div class="hero-thumb hero-thumb-empty"><i class="bx bx-image"></i></div>`;
                    }
                },
                {
                    data: 'heading',
                    name: 'heading',
                    orderable: false,
                    render: function(data, type, full) {
                        const badge = full.badge_text
                            ? `<div class="small text-muted">${$('<div>').text(full.badge_text).html()}</div>`
                            : '';
                        return `<div class="hero-heading-cell"><div class="fw-semibold">${$('<div>').text(data || '').html()}</div>${badge}</div>`;
                    }
                },
                {
                    // Active is the one the storefront is serving right now.
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return Number(data)
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var editUrl = "{{route('admin.heroSection.edit', ['id' => ':id'])}}".replace(':id', full.id);
                        var deleteButton = `<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="${full.id}" data-active="${Number(full.status)}"><i class="bx bx-trash"></i></a>`;
                        var editButton = `<a class="btn btn-primary btn-sm" href="${editUrl}"><i class="bx bx-edit"></i></a>`;
                        return `<div class="d-flex gap-sm">${editButton} ${deleteButton}</div>`;
                    }
                }
            ]
        });

        $('#heroTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            var heroId = $(this).data('id');
            var isActive = Number($(this).data('active')) === 1;
            var deleteUrl = "{{ route('admin.heroSection.delete', ['id' => ':id']) }}".replace(':id', heroId);

            Swal.fire({
                title: 'Are you sure?',
                // Deleting the active hero leaves the storefront on its
                // built-in copy, worth saying out loud before it happens.
                text: isActive
                    ? "This one is active. Deleting it leaves the homepage on its default banner."
                    : "You won't be able to revert this!",
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
                            Swal.fire('Deleted!', 'The hero section has been deleted.', 'success');
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
