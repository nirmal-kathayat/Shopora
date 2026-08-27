@extends("layouts.app")
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Permissions</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{isset($permission) ? 'Edit' : 'Create'}} Permission</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{route('admin.permission')}}" class="btn btn-primary">
                        <i class="bx bx-list-ul me-1"></i> Permission List
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <!-- <h6 class="mb-0 text-uppercase">{{isset($permission) ? 'Edit' : 'Create'}} Permission</h6> -->
                <hr />
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bxs-key me-1 font-22 text-primary"></i>
                            </div>
                            <h5 class="mb-0 text-primary">Permission Details</h5>
                        </div>
                        <hr>
                        <form action="{{ isset($permission) ? route('admin.permission.update', $permission->id) : route('admin.permission.store') }}" method="post" class="row g-3">
                            @csrf
                            <div class="col-md-6">
                                <label for="inputPermissionName" class="form-label">Permission Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="inputPermissionName" value="{{old('name',$permission->name ?? '')}}" required>
                                @error('name')
                                <div class="validation-error">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Access URI</label>
                                <div class="row">
                                    @foreach($routeLists as $key => $items)
                                    <div class="col-md-4 mb-3">
                                        <div class="card card-wrap">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">{{preg_replace('/([a-z])([A-Z])/', '$1 $2',ucfirst($key))}}</h5>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($items as $itemKey => $route)
                                                    @if(is_array($route))
                                                    @foreach($route as $otherRoute)
                                                    @if($otherRoute !== 'admin/dashboard')
                                                    @php
                                                    $arr = explode('/',$otherRoute);
                                                    @endphp
                                                    <li class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="access_uri[]" value="{{$otherRoute}}" id="{{$otherRoute}}" {{ isset($permission) && is_array($permission->access_uri) && in_array($otherRoute, $permission->access_uri) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{$otherRoute}}">
                                                                {{ucfirst($arr[2])}} {{ucfirst($key)}}
                                                            </label>
                                                        </div>
                                                    </li>
                                                    @endif
                                                    @endforeach
                                                    @else
                                                    <li class="mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="access_uri[]" value="{{$route}}" id="{{$route}}" {{ isset($permission) && is_array($permission->access_uri) && in_array($route, $permission->access_uri) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="{{$route}}">
                                                                {{str_replace('-',' ',ucfirst($itemKey))}} {{$key == 'admin' ? '' :preg_replace('/([a-z])([A-Z])/', '$1 $2',ucfirst($key))}}
                                                            </label>
                                                        </div>
                                                    </li>
                                                    @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @error('access_uri')
                                            <p class="validation-error">{{$message}}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('access_uri')
                                <div class="text-danger mt-2">{{$message}}</div>
                                @enderror
                            </div>
                            <div class="col-12 justify-item-end">
                                @if(isset($permission))
                                <a href="{{route('admin.permission.create')}}" class="btn btn-secondary px-3 me-2">Back to create</a>
                                @else
                                <a href="{{route('admin.permission')}}" class="btn btn-secondary px-3 me-2">Cancel</a>
                                @endif
                                <button type="submit" class="btn btn-primary px-5">{{(isset($role)) ? 'Update Permission' : 'Create Permission'}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
@endsection

@push('scripts')
@include('scripts.validation')
@endpush