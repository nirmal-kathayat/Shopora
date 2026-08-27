@extends("layouts.app")
@php
$url=(isset($role))? route('admin.role.update',['id'=>$role['id']]) :route('admin.role.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <!-- <div class="breadcrumb-title pe-3">Forms</div> -->
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Role Form</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-7 mx-auto">
                <hr />
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bxs-user me-1 font-22 text-primary"></i>
                            </div>
                            <h5 class="mb-0 text-primary">{{isset($role) ? 'Edit' : "Create"}} Role</h5>
                        </div>
                        <hr>
                        {!! Form::open(['url' => $url, 'class'=>'form-data row g-3']) !!}
                        <div class="col-md-6">
                            {!! Form::label('name', 'Role Name', ['class' => 'form-label required']) !!}
                            {!! Form::text('name', old('name', $role->name ?? ''), ['class' => 'form-control', 'placeholder' => 'Role Name', 'data-validation' => 'required']) !!}
                            @if($errors->has('name'))
                            <span class="text-danger">{{$errors->first('name')}}</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            {!! Form::label('permissions', 'Select Permissions', ['class' => 'form-label']) !!}
                            {!! Form::select('permissions[]', $permissions->pluck('name', 'id'), isset($role) ? $role->permissions->pluck('id') : old('permissions'), ['class' => 'form-control multiple-select-sm', 'id' => 'permission-list', 'multiple' => 'multiple']) !!}
                        </div>
                        <div class="col-12 justify-item-end">
                            @if(isset($role))
                            <a href="{{route('admin.role.create')}}" class="btn btn-secondary px-3 me-2">Back to create</a>
                            @else
                            <a href="{{route('admin.role')}}" class="btn btn-secondary px-3 me-2">Cancel</a>
                            @endif
                            <button type="submit" class="btn btn-primary px-5">{{(isset($role)) ? 'Update Role' : 'Create Role'}}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" type="text/css" href="{{asset('vendor/select2/css/select2.min.css')}}">
@endpush

@push('scripts')
@include('scripts.validation')
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<script>
    $('.multiple-select').each(function() {
        let id = '#' + $(this).attr('id')
        $(id).select2({
            placeholder: 'Select'
        })
    })
</script>
@endpush