@extends("layouts.app")
@php
$url = isset($customer) ? route('admin.customer.update',['id' => $customer->id]) : route('admin.customer.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Create Customer</h3>
            </div>
            <hr>
            <form class="row g-3" action="{{$url}}" method="post">
                @csrf
                <div class="col-md-6">
                    <label for="name" class="form-label required">Customer</label>
                    <input type="text" class="form-control" id="name" value="{{isset($customer) ? $customer->name : ''}}" name="name" placeholder="Customer Name" data-validation="required">
                    @error('name')
                    <span class="validation-error">{{$message}}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" value="{{isset($customer) ? $customer->address : ''}}" name="address" id="address" placeholder="Address" >
                </div>
                <div class="col-md-6">
                    <label for="ph_number" class="form-label">Phone Number</label>
                    <input type="text" name="ph_number" value="{{isset($customer) ? $customer->ph_number: ''}}" class="form-control" id="ph_number" placeholder="Phone Number">
                </div>
                <div class="col-12 justify-item-end">
                    <a href="{{route('admin.customer')}}" class="btn btn-secondary px-5">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">{{isset($customer) ? 'Update':'Submit'}}</button>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
@endsection
@push('script')
@include('scripts.validation')