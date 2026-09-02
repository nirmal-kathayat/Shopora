@extends("layouts.app")

@php
    $isEdit = isset($item);
    $url = $isEdit
        ? route('admin.inventoryItem.update', ['id' => $item->id])
        : route('admin.inventoryItem.store');
    $currentImage = $isEdit ? inventoryItemImageUrl($item->image) : null;
    $highlightRows = old('highlight_title')
        ? collect(old('highlight_title'))->map(fn ($t, $i) => ['title' => $t, 'subtitle' => old('highlight_subtitle.' . $i), 'icon' => old('highlight_icon.' . $i)])->all()
        : ($isEdit ? ($item->highlights ?? []) : []);
    if (empty($highlightRows)) { $highlightRows = [['icon' => 'sparkles', 'title' => '', 'subtitle' => '']]; }
@endphp

@section("style")
<style>
    .inv-form-card { border: 1px solid #eef0f3; border-radius: 12px; }
    .inv-form-card .card-header { background: #fbfcfe; border-bottom: 1px solid #eef0f3; font-weight: 600; }
    .inv-image-preview { max-width: 160px; border-radius: 10px; border: 1px solid #eef0f3; background: #f8fafc; }
    .field-hint { font-size: 12.5px; color: #6b7280; }
    .cat-input { display: flex; gap: 8px; }
    .badge-row + .badge-row { margin-top: 10px; }
    .cat-input .btn-modal { flex-shrink: 0; border: 1px solid #d5dbe3; background: #fff; border-radius: 8px; width: 42px; display: flex; align-items: center; justify-content: center; color: #056659; }
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.inventoryItem') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'New' }} Inventory Item</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.inventoryItem') }}" class="btn btn-primary">
                        <i class="bx bx-list-ul me-1"></i> Inventory List
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <form action="{{ $url }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card inv-form-card mb-3">
                <div class="card-header"><i class="bx bx-package me-1"></i> Basic information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label required">Name</label>
                            <input type="text" name="title" id="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $isEdit ? $item->title : '') }}" placeholder="Product name" required>
                            @error('title')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label required">Category</label>
                            <div class="cat-input">
                                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('category_id', $isEdit ? $item->category_id : '') ? '' : 'selected' }}>Select category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('category_id', $isEdit ? $item->category_id : '') === (string) $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-modal" id="btnAddCategory" title="Add category"><i class="bx bx-plus"></i></button>
                            </div>
                            @error('category_id')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label required">Unit</label>
                            <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror"
                                   value="{{ old('unit', $isEdit ? $item->unit : '') }}" placeholder="e.g. pcs, btl, kg" required>
                            @error('unit')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code" class="form-label required">Bar code</label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                   value="{{ old('code', $isEdit ? $item->code : '') }}" placeholder="e.g. EL-001" required>
                            @error('code')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card inv-form-card mb-3">
                <div class="card-header"><i class="bx bx-money me-1"></i> Pricing</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="price_per_unit" class="form-label required">Selling price per unit</label>
                            <input type="number" step="0.01" name="price_per_unit" id="price_per_unit"
                                   class="form-control @error('price_per_unit') is-invalid @enderror"
                                   value="{{ old('price_per_unit', $isEdit ? $item->price_per_unit : '') }}" placeholder="Enter price" required>
                            @error('price_per_unit')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="compare_at_price" class="form-label">Compare-at price</label>
                            <input type="number" step="0.01" name="compare_at_price" id="compare_at_price" class="form-control"
                                   value="{{ old('compare_at_price', $isEdit ? $item->compare_at_price : '') }}" placeholder="Original price (optional)">
                            <div class="field-hint">The "was" price. Shown struck-through with a discount badge when above the selling price.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card inv-form-card mb-3">
                <div class="card-header"><i class="bx bx-image me-1"></i> Image</div>
                <div class="card-body">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-6">
                            <label for="image" class="form-label">Product image</label>
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp,image/gif">
                            <div class="field-hint">JPG, PNG, WEBP or GIF, up to 2MB.</div>
                            @error('image')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            @if($currentImage)
                            <label class="form-label d-block">Current main image</label>
                            <img src="{{ $currentImage }}" alt="" class="inv-image-preview mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label small" for="remove_image">Remove this image</label>
                            </div>
                            @endif
                            <img id="imagePreview" alt="" class="inv-image-preview d-none">
                        </div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12">
                            <label for="gallery" class="form-label">Gallery images</label>
                            <input type="file" name="gallery[]" id="gallery" class="form-control @error('gallery.*') is-invalid @enderror"
                                   accept="image/jpeg,image/png,image/webp,image/gif" multiple>
                            <div class="field-hint">Extra photos shown as a thumbnail strip on the storefront. Select several at once. Up to 8.</div>
                            @error('gallery.*')<span class="validation-error">{{ $message }}</span>@enderror
                            <div id="galleryPreview" class="d-flex flex-wrap gap-2 mt-2"></div>

                            @if($isEdit && $item->productImages->count())
                            <label class="form-label d-block mt-3">Current gallery</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($item->productImages as $gimg)
                                <div class="text-center">
                                    <img src="{{ inventoryItemImageUrl($gimg->image) }}" alt="" class="inv-image-preview" style="max-width:110px;">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="{{ $gimg->id }}" id="rmg{{ $gimg->id }}">
                                        <label class="form-check-label small" for="rmg{{ $gimg->id }}">Remove</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card inv-form-card mb-3">
                <div class="card-header"><i class="bx bx-detail me-1"></i> Storefront details <span class="text-muted fw-normal">(optional)</span></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control"
                                      placeholder="Shown under &quot;About this product&quot; on the storefront.">{{ old('description', $isEdit ? $item->description : '') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="features_text" class="form-label">Key features</label>
                            <textarea name="features_text" id="features_text" rows="4" class="form-control"
                                      placeholder="One per line, e.g.&#10;Effectively removes dirt and germs&#10;Enriched with skin moisturizers">{{ old('features_text', $isEdit ? implode("\n", (array) ($item->features ?? [])) : '') }}</textarea>
                            <div class="field-hint">The bullet list under "About this product". One feature per line.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="brand" class="form-label">Brand</label>
                            <input type="text" name="brand" id="brand" class="form-control"
                                   value="{{ old('brand', $isEdit ? $item->brand : '') }}" placeholder="e.g. CarePlus">
                        </div>
                        <div class="col-md-4">
                            <label for="net_volume" class="form-label">Net volume</label>
                            <input type="text" name="net_volume" id="net_volume" class="form-control"
                                   value="{{ old('net_volume', $isEdit ? $item->net_volume : '') }}" placeholder="e.g. 500ml">
                        </div>
                        <div class="col-md-4">
                            <label for="country_of_origin" class="form-label">Country of origin</label>
                            <input type="text" name="country_of_origin" id="country_of_origin" class="form-control"
                                   value="{{ old('country_of_origin', $isEdit ? $item->country_of_origin : '') }}" placeholder="e.g. Nepal">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card inv-form-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bx bx-bulb me-1"></i> Product highlights <span class="text-muted fw-normal">(optional)</span></span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addHighlight"><i class="bx bx-plus"></i> Add</button>
                </div>
                <div class="card-body">
                    <div class="field-hint mb-2">The little icon band on the product page, specific to this product. Four fit the row, up to 8.</div>
                    @error('highlights.*.title')<span class="validation-error d-block mb-2">{{ $message }}</span>@enderror
                    <div id="highlightRows">
                        @foreach($highlightRows as $hl)
                        <div class="row g-2 badge-row">
                            <div class="col-md-3">
                                <select name="highlight_icon[]" class="form-control">
                                    @foreach($highlightIcons as $value => $label)
                                    <option value="{{ $value }}" {{ ($hl['icon'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="highlight_title[]" class="form-control" value="{{ $hl['title'] ?? '' }}" placeholder="pH Balanced">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="highlight_subtitle[]" class="form-control" value="{{ $hl['subtitle'] ?? '' }}" placeholder="Gentle on hands">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 removeHighlight"><i class="bx bx-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card inv-form-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bx bx-check-shield me-1"></i> Trust badges <span class="text-muted fw-normal">— shown on every product page</span></span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addBadge"><i class="bx bx-plus"></i> Add</button>
                </div>
                <div class="card-body">
                    <div class="field-hint mb-2">These are store-wide: editing them here changes every product page. Four fit the 2&times;2 grid, up to 6.</div>
                    <div id="badgeRows">
                        @foreach($trustBadges as $badge)
                        <div class="row g-2 badge-row">
                            <div class="col-md-3">
                                <select name="badge_icon[]" class="form-control">
                                    @foreach($trustIcons as $value => $label)
                                    <option value="{{ $value }}" {{ ($badge['icon'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="badge_title[]" class="form-control" value="{{ $badge['title'] ?? '' }}" placeholder="Fast delivery">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="badge_subtitle[]" class="form-control" value="{{ $badge['subtitle'] ?? '' }}" placeholder="Across Kathmandu Valley">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 removeBadge"><i class="bx bx-trash"></i></button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <a href="{{ route('admin.inventoryItem') }}" class="btn btn-secondary px-5">Cancel</a>
                <button type="submit" class="btn btn-primary px-5">{{ $isEdit ? 'Update' : 'Submit' }}</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="quickCategoryName" class="form-label">Category name</label>
                <input type="text" class="form-control" id="quickCategoryName" placeholder="Category name">
                <div id="quickCategoryError" class="validation-error mt-1"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="quickCategorySave">Save</button>
            </div>
        </div>
    </div>
</div>
<template id="highlightTemplate">
    <div class="row g-2 badge-row">
        <div class="col-md-3">
            <select name="highlight_icon[]" class="form-control">
                @foreach($highlightIcons as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><input type="text" name="highlight_title[]" class="form-control" placeholder="pH Balanced"></div>
        <div class="col-md-4"><input type="text" name="highlight_subtitle[]" class="form-control" placeholder="Gentle on hands"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 removeHighlight"><i class="bx bx-trash"></i></button></div>
    </div>
</template>

<template id="badgeTemplate">
    <div class="row g-2 badge-row">
        <div class="col-md-3">
            <select name="badge_icon[]" class="form-control">
                @foreach($trustIcons as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><input type="text" name="badge_title[]" class="form-control" placeholder="Fast delivery"></div>
        <div class="col-md-4"><input type="text" name="badge_subtitle[]" class="form-control" placeholder="Across Kathmandu Valley"></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 removeBadge"><i class="bx bx-trash"></i></button></div>
    </div>
</template>
@endsection

@section("script")
<script>
    (function () {
        const categoryStoreUrl = "{{ route('admin.category.store') }}";
        const csrf = "{{ csrf_token() }}";

        // live image preview
        const fileInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0];
            if (!file) { preview.classList.add('d-none'); return; }
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });

        // quick add category
        const modalEl = document.getElementById('quickCategoryModal');
        const modal = new bootstrap.Modal(modalEl);
        document.getElementById('btnAddCategory').addEventListener('click', () => {
            document.getElementById('quickCategoryName').value = '';
            document.getElementById('quickCategoryError').textContent = '';
            modal.show();
        });

        document.getElementById('quickCategorySave').addEventListener('click', function () {
            const name = document.getElementById('quickCategoryName').value.trim();
            if (!name) { document.getElementById('quickCategoryError').textContent = 'Enter a category name.'; return; }

            fetch(categoryStoreUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: new URLSearchParams({ title: name }),
            })
                .then((r) => r.json())
                .then((res) => {
                    if (res.type === 'success' && res.data) {
                        const select = document.getElementById('category_id');
                        const option = new Option(res.data.title, res.data.id, true, true);
                        select.add(option);
                        if (window.jQuery && jQuery.fn.select2) { jQuery(select).trigger('change'); }
                        modal.hide();
                    } else {
                        document.getElementById('quickCategoryError').textContent = res.message || 'Could not add category.';
                    }
                })
                .catch(() => {
                    document.getElementById('quickCategoryError').textContent = 'Something went wrong.';
                });
        });
        // searchable category dropdown
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#category_id').select2({ width: '100%', placeholder: 'Select category' });
        }

        // product highlight repeatable rows
        (function () {
            const rows = document.getElementById('highlightRows');
            const tpl = document.getElementById('highlightTemplate');
            document.getElementById('addHighlight').addEventListener('click', function () {
                if (rows.querySelectorAll('.badge-row').length >= 8) return;
                rows.insertAdjacentHTML('beforeend', tpl.innerHTML);
            });
            rows.addEventListener('click', function (e) {
                const btn = e.target.closest('.removeHighlight');
                if (!btn) return;
                if (rows.querySelectorAll('.badge-row').length === 1) {
                    rows.querySelectorAll('input').forEach((i) => (i.value = ''));
                    return;
                }
                btn.closest('.badge-row').remove();
            });
        })();

        // trust badge repeatable rows
        (function () {
            const rows = document.getElementById('badgeRows');
            const tpl = document.getElementById('badgeTemplate');
            document.getElementById('addBadge').addEventListener('click', function () {
                if (rows.querySelectorAll('.badge-row').length >= 6) return;
                rows.insertAdjacentHTML('beforeend', tpl.innerHTML);
            });
            rows.addEventListener('click', function (e) {
                const btn = e.target.closest('.removeBadge');
                if (!btn) return;
                if (rows.querySelectorAll('.badge-row').length === 1) {
                    rows.querySelectorAll('input').forEach((i) => (i.value = ''));
                    return;
                }
                btn.closest('.badge-row').remove();
            });
        })();

        // gallery previews
        const gallery = document.getElementById('gallery');
        const galleryPreview = document.getElementById('galleryPreview');
        gallery.addEventListener('change', function () {
            galleryPreview.innerHTML = '';
            Array.from(gallery.files).forEach((file) => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'inv-image-preview';
                img.style.maxWidth = '90px';
                galleryPreview.appendChild(img);
            });
        });
    })();
</script>
@endsection
