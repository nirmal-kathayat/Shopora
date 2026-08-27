@extends("layouts.app")

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Inventory Information</li>
                    </ol>

                </nav>
            </div>
        </div>


        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Inventory Information</h5>
                    <a href="{{ route('admin.product') }}" class="btn btn-primary btn-sm">
                        Back To Product
                    </a>
                </div>
                <hr />
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Product Name:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $product->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Product Category:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $product->category->title }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Product Price:</strong>
                    </div>
                    <div class="col-md-9">
                        Rs.{{ $product->price }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Product Code:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $product->code }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Current Quantity:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $product->qty }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Unit:</strong>
                    </div>
                    <div class="col-md-9">
                        {{ $product->unit }}
                    </div>
                </div>

                <h5 class="mt-4">Inventory Records</h5>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Quantity Change</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryRecords as $record)
                            <tr>
                                <td>{{ $record->date }}</td>
                                <td>{{ $record->qty }}</td>
                                <td>{{ ucfirst($record->type) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection