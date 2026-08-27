@extends("layouts.app")
@section("style")
@endsection
@php
$url = isset($inventoryItem) ? route('admin.inventoryItem.update',['id' => $inventoryItem->id]) : route('admin.inventoryItem.store');
@endphp
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Create Inventory Items</h3>
            </div>
            <hr>
            <form class="row g-3" action="{{$url}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group row g-3">
                    <div class="col-md-6">
                        <label for="inventory" class="form-label required">Inventory Name</label>
                        <input type="text" name="title" class="form-control" data-validation="required" id="inventory" value="{{isset($inventoryItem) ? $inventoryItem->title: ''}}" placeholder="Inventory Name">
                        @if($errors->has('title'))
                        <span class="text-danger">{{$errors->first('title')}}</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="category_form" class="form-label required">Select Category</label>
                        <div class="cat-input">
                            <select name="category_id" class="form-control select2" id="category_form" data-validation="required">
                                <option value="" disabled selected>Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ isset($inventoryItem) && $inventoryItem->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-modal" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="unit" class="form-label required">Unit</label>
                        <input type="text" name="unit" id="unit" value="{{isset($inventoryItem) ? $inventoryItem->unit: ''}}" class="form-control" data-validation="required">
                        @if($errors->has('unit'))
                        <span class="text-danger">{{$errors->first('unit')}}</span>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="code" class="form-label">Bar Code</label>
                        <input type="text" name="code" id="code"
                            value="{{ isset($inventoryItem) ? $inventoryItem->code : '' }}"
                            class="form-control"
                            placeholder="Enter bar code">
                    </div>
                    <div class="col-md-6">
                        <label for="price_per_unit" class="form-label required">Selling Price Per Unit</label>
                        <input type="number" name="price_per_unit" id="price_per_unit" value="{{isset($inventoryItem) ? $inventoryItem->price_per_unit: ''}}" class="form-control" data-validation="required">
                        @if($errors->has('price_per_unit'))
                        <span class="text-danger">{{$errors->first('price_per_unit')}}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12 justify-item-end justify-left">
                    <a href="{{route('admin.inventoryItem')}}" class="btn btn-secondary px-5">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5">{{isset($inventoryItem) ? 'Update':'Submit'}}</button>
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
@section('script')
@include('scripts.validation')

<script>
    $(document).ready(function() {
        // Select2 for searchable Category dropdown
        if ($.fn.select2) {
            $('#category_form').select2({
                placeholder: 'Select Category',
                width: '100%'
            });
        }

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
                            if ($.fn.select2) {
                                $('#category_form').trigger('change.select2');
                            }

                            // Proper Bootstrap 5 hide (prevents stuck backdrop)
                            const modalEl = document.getElementById('categoryModal');
                            if (modalEl && window.bootstrap) {
                                modalEl.addEventListener('hidden.bs.modal', function() {
                                    // safety cleanup in case any backdrop is left behind
                                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                                    document.body.classList.remove('modal-open');
                                    document.body.style.removeProperty('padding-right');
                                }, { once: true });

                                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                                modal.hide();
                            }
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