@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Roles</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Role List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.role.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Role
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="roleTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Name</th>
                                <th>Role Type</th>
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
        $('#roleTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.role') }}", // Replace with the correct route
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
                    data: 'permissions',
                    name: 'permissions',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var editUrl = "{{route('admin.role.edit',['id'=>':id'])}}".replace(':id', full.id);
                        var deleteUrl = "{{route('admin.role.delete',['id'=>':id'])}}".replace(':id', full.id);
                        var editButton = '<a class="btn btn-primary btn-sm" href="' + editUrl + '"><i class="bx bx-edit"></i></a>';
                        var deleteButton = `<a class="btn btn-danger deleteAction btn-sm" href=${deleteUrl}><i class="bx bx-trash"></i></a>`;
                        var actionButtons = `<div class="d-flex gap-sm">${editButton} ${deleteButton}</div>`;
                        return actionButtons;
                    }
                }
            ],
            initComplete: function(settings, json) {
                console.log(json); // Log the received JSON data
            }
        });
    });
</script>
@endsection