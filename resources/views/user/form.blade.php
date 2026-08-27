@extends("layouts.app")
@php
$url=(isset($user))? route('admin.user.update',['id'=>$user['id']]) :route('admin.user.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Users</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">User Form</li>
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
                            <h5 class="mb-0 text-primary">{{isset($user) ? 'Edit' : "Create"}} User</h5>
                        </div>
                        <hr>
                        {!! Form::open(['url' => $url, 'class'=>'form-data row g-3']) !!}
                        <div class="col-md-6">
                            {!! Form::label('name', 'Full Name',['class' => 'form-label required']) !!}
                            {!! Form::text('name',old('name',$user['name'] ??
                            ''),['class'=>'form-control','placeholder'=>'Name',
                            'data-validation'=>'required']) !!}
                            @if($errors->has('name'))
                            <span class="text-danger">{{$errors->first('name')}}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('Username', '',['class' => 'form-label required']) !!}
                            {!! Form::text('username',old('username',$user->username ??
                            ''),['class'=>'form-control','placeholder'=>'Username',
                            'data-validation'=>'required']) !!}

                            @if($errors->has('username'))
                            <span class="text-danger">{{$errors->first('username')}}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('Email', '',['class' => 'form-label required']) !!}
                            {!! Form::email('email',old('email',$user->email ??
                            ''),['class'=>'form-control','placeholder'=>'Email',
                            'data-validation'=>'required']) !!}

                            @if($errors->has('email'))
                            <span class="text-danger">{{$errors->first('email')}}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('roles','Select Role',['class' => 'form-label']) !!}
                            {!! Form::select('roles[]',$roles->pluck('name','id'),isset($user) ? $user->roles->pluck('id') :old('roles'),['class'=>'form-control multiple-select-sm','id'=>'role-list','multiple'=>'multiple']) !!}
                        </div>
                        @if(!isset($user))
                        <div class="col-md-6 margin-top">
                            {!! Form::label('Password', '',['class' => 'form-label required']) !!}
                            <input type="password" class="form-control" name="password" placeholder="Password" data-validation="required}}">

                            @if($errors->has('password'))
                            <span class="text-danger">{{$errors->first('password')}}</span>
                            @endif
                        </div>

                        <div class="col-md-6 margin-top">
                            {!! Form::label('Password', '',['class' => 'form-label required']) !!}
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Password Confirmation" data-validation="required}}">

                            @if($errors->has('password'))
                            <span class="text-danger">{{$errors->first('password')}}</span>
                            @endif
                        </div>
                        @endif
                        <div class="col-12 justify-item-end">
                            @if(isset($user))
                            <a href="{{route('admin.user.create')}}" class="btn btn-secondary px-3 me-2">Back to create</a>
                            @else
                            <a href="{{route('admin.user')}}" class="btn btn-secondary px-3 me-2">Cancel</a>
                            @endif
                            <button type="submit" class="btn btn-primary px-5">{{(isset($user)) ? 'Update User' : 'Create User'}}</button>
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
    $('.multiple-select-sm').each(function() {
        let id = '#' + $(this).attr('id')
        $(id).select2({
            placeholder: 'Select'
        })
    })
</script>
@endpush