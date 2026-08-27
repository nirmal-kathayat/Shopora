@extends("layouts.app")

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Purchase Inventory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Purchase Inventory Details</li>
                    </ol>

                </nav>
            </div>
        </div>


        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Purchase Inventory Information</h5>
                    <a href="{{ route('admin.purchaseInventory') }}" class="btn btn-primary btn-sm">
                        Back
                    </a>
                </div>
                <hr />
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Inventory Item:</strong>
                    </div>
                    <div class="col-md-9">
                        {{$purchaseInventory->inventoryItem->title}}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Per Price Rate:</strong>
                    </div>
                    <div class="col-md-9">
                        Rs.{{ $purchaseInventory->per_piece_rate }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Vendor Name:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $purchaseInventory->vendor_name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Purchase Date:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $purchaseInventory->purchase_date }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Purchase Quantity:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $purchaseInventory->qty }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Total Price:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $purchaseInventory->total_price }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection