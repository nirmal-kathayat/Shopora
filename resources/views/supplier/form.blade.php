@extends("layouts.app")
@php
$url = isset($supplier) ? route('admin.supplier.update',['id' => $supplier->id]) : route('admin.supplier.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Create Supplier</h3>
            </div>
            <hr>
            <form class="row g-3" action="{{$url}}" method="post">
                @csrf
                @if(isset($supplier))
                @method('PUT')
                @endif
                <div class="col-md-6">
                    <label for="name" class="form-label required">Supplier</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{isset($supplier) ? $supplier->name: ''}}" placeholder="Supplier Name" data-validation="required">
                    @error('name')
                    <span class="validation-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="ph_number" class="form-label required">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control" id="ph_number" value="{{isset($supplier) ? $supplier->phone_number: ''}}" placeholder="Phone Number" data-validation="required">
                    @error('phone_number')
                    <span class="validation-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="address" class="form-label required">Address</label>
                    <input type="text" class="form-control" name="address" id="address" value="{{isset($supplier) ? $supplier->address: ''}}" placeholder="Address" data-validation="required">
                    @error('address')
                    <span class="validation-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="pan_num" class="form-label required">Pan Number</label>
                    <input type="text" class="form-control" name="pan_number" id="pan_num" value="{{isset($supplier) ? $supplier->pan_number: ''}}" placeholder="Pan Number" data-validation="required">
                    @error('pan_number')
                    <span class="validation-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-12 justify-item-end">
                    <a href="{{route('admin.supplier')}}" class="btn btn-secondary px-5">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">{{isset($supplier) ? 'Update':'Submit'}}</button>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
@endsection
@push('script')
@include('scripts.validation')