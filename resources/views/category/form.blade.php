@extends("layouts.app")

@php
    $isEdit = isset($category);
    $url = $isEdit
        ? route('admin.category.update', ['id' => $category->id])
        : route('admin.category.store');

    $currentImage = $isEdit ? inventoryItemImageUrl($category->image) : null;
@endphp

@section("style")
<style>
    .cat-form-card { border: 1px solid #eef0f3; border-radius: 12px; }
    .cat-form-card .card-header { background: #fbfcfe; border-bottom: 1px solid #eef0f3; font-weight: 600; }
    .cat-image-preview { max-width: 200px; border-radius: 10px; border: 1px solid #eef0f3; background: #f8fafc; }
    .field-hint { font-size: 12.5px; color: #6b7280; }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Inventory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.category') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'New' }} Category</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.category') }}" class="btn btn-primary">
                        <i class="bx bx-list-ul me-1"></i> Category List
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <form action="{{ $url }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card cat-form-card mb-3">
                <div class="card-header"><i class="bx bx-category me-1"></i> Category</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label required">Name</label>
                            <input type="text" name="title" id="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $isEdit ? $category->title : '') }}"
                                   placeholder="Grocery" required>
                            <div class="field-hint">The storefront URL is made from this, e.g. <code>/products/grocery</code>.</div>
                            @error('title')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-3">
                            <label for="icon" class="form-label">Icon</label>
                            <select name="icon" id="icon" class="form-control">
                                @foreach($icons as $value => $label)
                                <option value="{{ $value }}" {{ old('icon', $isEdit ? $category->icon : 'grid') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="field-hint">Shown when no image is set.</div>
                        </div>

                        <div class="col-md-3">
                            @php $currentStatus = (int) old('status', $isEdit ? (int) $category->status : 1); @endphp
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ $currentStatus === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $currentStatus === 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="field-hint">Inactive hides it from the storefront.</div>
                        </div>

                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">Order</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" min="0"
                                   value="{{ old('sort_order', $isEdit ? $category->sort_order : '') }}"
                                   placeholder="Auto">
                            <div class="field-hint">Lower shows first. Leave blank to add at the end.</div>
                        </div>

                        <div class="col-md-5">
                            <label for="image" class="form-label">Shelf image</label>
                            <input type="file" name="image" id="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp">
                            <div class="field-hint">The photo on the category card. A wide (4:3) shelf photo works best.</div>
                            @error('image')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="image_alt" class="form-label">Image description</label>
                            <input type="text" name="image_alt" id="image_alt" class="form-control"
                                   value="{{ old('image_alt', $isEdit ? $category->image_alt : '') }}"
                                   placeholder="Fresh vegetables on a shelf">
                        </div>

                        <div class="col-md-3">
                            @if($currentImage)
                            <label class="form-label d-block">Current</label>
                            <img src="{{ $currentImage }}" alt="" class="cat-image-preview mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label small" for="remove_image">Remove this image</label>
                            </div>
                            @endif
                            <img id="imagePreview" alt="" class="cat-image-preview d-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <a href="{{ route('admin.category') }}" class="btn btn-secondary px-5">Cancel</a>
                <button type="submit" class="btn btn-primary px-5">{{ $isEdit ? 'Update' : 'Submit' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section("script")
<script>
    (function () {
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0];
            if (!file) { preview.classList.add('d-none'); return; }
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    })();
</script>
@endsection
