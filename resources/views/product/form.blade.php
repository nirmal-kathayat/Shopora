@extends("layouts.app")
@section("style")
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@php
$url = isset($product) ? route('admin.product.update',['id' => $product->id]) : route('admin.product.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Create Product</h3>
            </div>
            <hr>
            <form class="row g-3" action="{{$url}}" method="post" enctype="multipart/form-data">
                @csrf
                @if(isset($product))
                @method('PUT')
                @endif
                <div class="form-group row g-3">
                    <div class="col-md-6">
                        <label for="products" class="form-label required">Product</label>
                        <input type="text" name="name" class="form-control" data-validation="required" id="products" value="{{isset($product) ? $product->name : ''}}" placeholder="Product Name">
                        @if($errors->has('name'))
                        <span class="text-danger">{{$errors->first('name')}}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="category_form" class="form-label required">Select Category</label>
                        <div class="cat-input">
                            <select name="category_id" class="form-control" id="category_form" data-validation="required">
                                <option value="" disabled selected>Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ isset($product) && $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-modal" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6 input-file">
                        <label for="image" class="form-label required">Upload Image</label>
                        <input type="file" class="form-control" name="image" id="image" data-validation="required image"
                            data-error-required="Please select an image"
                            data-error-image="Please select a valid image file">
                        @if(isset($product) && $product->image)
                        <p style="color: black;">{{ $product->image }}</p>
                        @endif

                        @if($errors->has('image'))
                        <span class="text-danger">{{$errors->first('unit')}}</span>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="units" class="form-label required">Select Unit</label>
                        <select name="unit" id="units" class="form-control" data-validation="required">
                            <option value="" disabled selected>Select unit</option>
                            @foreach(get_units() as $unit)
                            <option value="{{$unit}}" <?php echo isset($product) && strtolower($product->unit) == strtolower($unit) ? 'selected' : ''; ?>>{{$unit}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('unit'))
                        <span class="text-danger">{{$errors->first('unit')}}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="supplier_form" class="form-label">Select Supplier</label>
                        <select name="supplier_id" class="form-control" id="supplier_form">
                            <option value="" disabled selected>Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{$supplier->id}}" {{isset($product) && $product->supplier_id == $supplier->id ? 'selected' : ''}}>{{$supplier->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="qty" class="form-label required">Quantity</label>
                        <input type="number" name="qty" id="qty" value="{{isset($product) ? $product->qty : ''}}" class="form-control" data-validation="required">
                        @if($errors->has('qty'))
                        <span class="text-danger">{{$errors->first('qty')}}</span>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label required">Price</label>
                        <input type="number" name="price" id="price" value="{{isset($product) ? $product->price : ''}}" class="form-control" data-validation="required">
                        @if($errors->has('price'))
                        <span class="text-danger">{{$errors->first('price')}}</span>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" name="code" id="code" value="{{isset($product) ? $product->code : ''}}" class="form-control">
                    </div>

                </div>

                <div class="col-md-6 col-top">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" cols="106" rows="5" id="description" placeholder="Enter Here Description"></textarea>
                </div>

                <div class="col-12 justify-item-end justify-left">
                    <a href="{{route('admin.product')}}" class="btn btn-secondary px-5">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">{{isset($product) ? 'Update':'Submit'}}</button>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" name="title" class="form-control" id="categoryName" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('script')
@include('scripts.validation')

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        $('#saveCategoryBtn').click(function() {
            var categoryName = $('#categoryName').val();
            if (categoryName) {
                $.ajax({
                    url: "{{ route('admin.category.store') }}",
                    method: 'POST',
                    data: {
                        title: categoryName,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.type === 'success') {
                            $('#category_form').append($('<option>', {
                                value: response.data.id,
                                text: response.data.title
                            }));

                            $('#category_form').val(response.data.id);
                            $('#categoryModal').modal('hide');
                            $('#categoryName').val('');

                            // Swal.fire(response.message);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Category added successfully!'
                            });
                        } else {
                            Swal('Error: error ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error: error ' + xhr.responseJSON.message || 'An unknown error occurred.');
                    }
                });
            } else {
                alert('Please enter a category name.');
            }
        });
    });
</script>

@endsection