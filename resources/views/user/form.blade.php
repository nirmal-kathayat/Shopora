@extends("layouts.app")
@php
$url = isset($user) ? route('admin.user.update', ['id' => $user->id]) : route('admin.user.store');
$selectedRoles = old('roles', isset($user) ? $user->roles->pluck('id')->toArray() : []);
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
                            <h5 class="mb-0 text-primary">{{ isset($user) ? 'Edit' : 'Create' }} User</h5>
                        </div>
                        <hr>
                        {!! Form::open(['url' => $url, 'class' => 'form-data row g-3']) !!}
                        <div class="col-md-6">
                            {!! Form::label('name', 'Full Name', ['class' => 'form-label required']) !!}
                            {!! Form::text('name', old('name', isset($user) ? $user->name : ''), [
                                'class' => 'form-control',
                                'placeholder' => 'Full name',
                                'data-validation' => 'required',
                            ]) !!}
                            @if($errors->has('name'))
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('username', 'Username', ['class' => 'form-label required']) !!}
                            {!! Form::text('username', old('username', isset($user) ? $user->username : ''), [
                                'class' => 'form-control',
                                'placeholder' => 'Username',
                                'data-validation' => 'required',
                            ]) !!}
                            @if($errors->has('username'))
                            <span class="text-danger">{{ $errors->first('username') }}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('email', 'Email', ['class' => 'form-label required']) !!}
                            {!! Form::email('email', old('email', isset($user) ? $user->email : ''), [
                                'class' => 'form-control',
                                'placeholder' => 'Email',
                                'data-validation' => 'required email',
                            ]) !!}
                            @if($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('roles', 'Select Role', ['class' => 'form-label']) !!}
                            {!! Form::select(
                                'roles[]',
                                $roles->pluck('name', 'id'),
                                $selectedRoles,
                                [
                                    'class' => 'form-control user-role-select2',
                                    'id' => 'role-list',
                                    'multiple' => 'multiple',
                                    'data-placeholder' => 'Select roles',
                                ]
                            ) !!}
                            @if($errors->has('roles'))
                            <span class="text-danger d-block mt-1">{{ $errors->first('roles') }}</span>
                            @endif
                        </div>

                        @if(!isset($user))
                        <div class="col-md-6">
                            {!! Form::label('password', 'Password', ['class' => 'form-label required']) !!}
                            <input type="password" class="form-control" name="password" placeholder="Password" data-validation="required">
                            @if($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('password_confirmation', 'Password Confirmation', ['class' => 'form-label required']) !!}
                            <input type="password" class="form-control" name="password_confirmation" placeholder="Password confirmation" data-validation="required">
                        </div>
                        @endif

                        <div class="col-12 justify-item-end">
                            @if(isset($user))
                            <a href="{{ route('admin.user.create') }}" class="btn btn-secondary px-3 me-2">Back to create</a>
                            @else
                            <a href="{{ route('admin.user') }}" class="btn btn-secondary px-3 me-2">Cancel</a>
                            @endif
                            <button type="submit" class="btn btn-primary px-5">{{ isset($user) ? 'Update User' : 'Create User' }}</button>
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
<style>
    #role-list + .select2-container {
        width: 100% !important;
    }

    #role-list + .select2-container .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: calc(1.5em + 0.75rem + 2px);
        padding: 0.25rem 0.5rem;
        background-color: var(--bs-body-bg, #fff);
        cursor: text;
    }

    #role-list + .select2-container.select2-container--focus .select2-selection--multiple,
    #role-list + .select2-container.select2-container--open .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 4px;
        margin: 0;
        padding: 0;
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__placeholder {
        color: var(--bs-secondary-color, #6c757d);
        opacity: 1;
        margin: 0.25rem 0;
        line-height: 1.5;
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        border: none;
        color: #fff;
        border-radius: 0.25rem;
        padding: 0.15rem 0.45rem 0.15rem 0.25rem;
        margin: 0;
        display: flex;
        align-items: center;
        float: none;
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__choice__display {
        padding-right: 4px;
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255, 255, 255, 0.8);
        border-right: 1px solid rgba(255, 255, 255, 0.35);
        margin-right: 6px;
        padding: 0 6px 0 4px;
        font-weight: 700;
        line-height: 1.2;
        transition: color 0.15s ease, background-color 0.15s ease;
    }

    #role-list + .select2-container .select2-selection--multiple .select2-selection__choice__remove:hover,
    #role-list + .select2-container .select2-selection--multiple .select2-selection__choice__remove:focus {
        color: #fff !important;
        background-color: #dc3545;
        border-radius: 0.15rem;
        outline: none;
    }

    #role-list + .select2-container:not(.select2-container--focus):not(.select2-container--open) .select2-search--inline {
        width: 0;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }

    #role-list + .select2-container:not(.select2-container--focus):not(.select2-container--open) .select2-search--inline .select2-search__field {
        width: 0 !important;
        min-width: 0 !important;
        min-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        caret-color: transparent;
    }

    #role-list + .select2-container.select2-container--focus .select2-search--inline,
    #role-list + .select2-container.select2-container--open .select2-search--inline {
        flex: 1 1 120px;
        min-width: 120px;
    }

    #role-list + .select2-container.select2-container--focus .select2-search--inline .select2-search__field,
    #role-list + .select2-container.select2-container--open .select2-search--inline .select2-search__field {
        width: 100% !important;
        min-width: 120px;
        margin: 0;
        padding: 0.25rem 0;
        min-height: 24px;
        caret-color: auto;
    }
</style>
@endpush

@push('scripts')
@include('scripts.validation')
<script>
    $(function () {
        var $roleList = $('#role-list');

        $roleList.select2({
            placeholder: $roleList.data('placeholder') || 'Select roles',
            width: '100%',
            closeOnSelect: false,
            dropdownAutoWidth: false
        });

        $roleList.on('select2:close', function () {
            $(this).parent().find('.select2-search__field').val('');
        });
    });
</script>
@endpush
