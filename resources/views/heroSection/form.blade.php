@extends("layouts.app")

@php
    $isEdit = isset($heroSection);
    $url = $isEdit
        ? route('admin.heroSection.update', ['id' => $heroSection->id])
        : route('admin.heroSection.store');

    // On a validation error the old input wins, otherwise the stored row, and
    // finally the copy the storefront ships with.
    $chips = old('popular_label')
        ? collect(old('popular_label'))->map(fn ($label, $i) => [
            'label' => $label,
            'url' => old('popular_url.' . $i),
        ])->all()
        : ($isEdit ? ($heroSection->popular_searches ?? []) : []);

    if (empty($chips)) {
        $chips = [['label' => '', 'url' => '']];
    }

    $currentImage = $isEdit ? inventoryItemImageUrl($heroSection->image) : null;
@endphp

@section("style")
<style>
    .hero-form-card { border: 1px solid #eef0f3; border-radius: 12px; }
    .hero-form-card .card-header {
        background: #fbfcfe;
        border-bottom: 1px solid #eef0f3;
        font-weight: 600;
    }
    .hero-image-preview {
        max-width: 240px;
        border-radius: 10px;
        border: 1px solid #eef0f3;
        background: #f8fafc;
    }
    .popular-row + .popular-row { margin-top: 10px; }
    .field-hint { font-size: 12.5px; color: #6b7280; }
</style>
@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Storefront</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.heroSection') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'New' }} Hero Section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.heroSection') }}" class="btn btn-primary">
                        <i class="bx bx-list-ul me-1"></i> Hero Section List
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <form action="{{ $url }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card hero-form-card mb-3">
                <div class="card-header"><i class="bx bx-text me-1"></i> Headline</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            @php $currentStatus = (int) old('status', $isEdit ? (int) $heroSection->status : 1); @endphp
                            <label for="status" class="form-label required">Status</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                <option value="1" {{ $currentStatus === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $currentStatus === 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="field-hint">
                                Only one hero can be active at a time &mdash; making this one active turns the others off.
                            </div>
                            @error('status')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="badge_text" class="form-label">Badge</label>
                            <input type="text" name="badge_text" id="badge_text"
                                   class="form-control @error('badge_text') is-invalid @enderror"
                                   value="{{ old('badge_text', $isEdit ? $heroSection->badge_text : '') }}"
                                   placeholder="Your everyday, delivered.">
                            <div class="field-hint">The small green pill above the heading. Leave empty to hide it.</div>
                            @error('badge_text')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-12">
                            <label for="heading" class="form-label required">Heading</label>
                            <textarea name="heading" id="heading" rows="2"
                                      class="form-control @error('heading') is-invalid @enderror"
                                      placeholder="Everything your *home* needs,&#10;in *one place.*">{{ old('heading', $isEdit ? $heroSection->heading : '') }}</textarea>
                            <div class="field-hint">
                                Wrap a word in <code>*asterisks*</code> to colour it green. Press Enter where you want the
                                line to break on wide screens.
                            </div>
                            @error('heading')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-12">
                            <label for="subheading" class="form-label">Supporting text</label>
                            <textarea name="subheading" id="subheading" rows="2"
                                      class="form-control @error('subheading') is-invalid @enderror"
                                      placeholder="From your morning coffee to the cable you need today...">{{ old('subheading', $isEdit ? $heroSection->subheading : '') }}</textarea>
                            @error('subheading')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card hero-form-card mb-3">
                <div class="card-header"><i class="bx bx-mouse me-1"></i> Buttons</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="primary_label" class="form-label">Primary button</label>
                            <input type="text" name="primary_label" id="primary_label" class="form-control"
                                   value="{{ old('primary_label', $isEdit ? $heroSection->primary_label : '') }}"
                                   placeholder="Shop all products">
                        </div>
                        <div class="col-md-3">
                            <label for="primary_url" class="form-label">Primary link</label>
                            <input type="text" name="primary_url" id="primary_url" class="form-control"
                                   value="{{ old('primary_url', $isEdit ? $heroSection->primary_url : '') }}"
                                   placeholder="/products">
                        </div>
                        <div class="col-md-3">
                            <label for="secondary_label" class="form-label">Secondary button</label>
                            <input type="text" name="secondary_label" id="secondary_label" class="form-control"
                                   value="{{ old('secondary_label', $isEdit ? $heroSection->secondary_label : '') }}"
                                   placeholder="Browse categories">
                        </div>
                        <div class="col-md-3">
                            <label for="secondary_url" class="form-label">Secondary link</label>
                            <input type="text" name="secondary_url" id="secondary_url" class="form-control"
                                   value="{{ old('secondary_url', $isEdit ? $heroSection->secondary_url : '') }}"
                                   placeholder="#categories">
                        </div>
                        <div class="col-12">
                            <div class="field-hint">
                                Links starting with <code>/</code> go to a storefront page, and <code>#categories</code>
                                scrolls down to the category row. Leave a label empty to hide that button.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card hero-form-card mb-3">
                <div class="card-header"><i class="bx bx-image me-1"></i> Image</div>
                <div class="card-body">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-5">
                            <label for="image" class="form-label">Hero image</label>
                            <input type="file" name="image" id="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp">
                            <div class="field-hint">
                                PNG with a transparent background works best. Up to 3 MB.
                                Leave empty to keep the storefront's built-in basket image.
                            </div>
                            @error('image')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="image_alt" class="form-label">Image description</label>
                            <input type="text" name="image_alt" id="image_alt" class="form-control"
                                   value="{{ old('image_alt', $isEdit ? $heroSection->image_alt : '') }}"
                                   placeholder="A Shopora basket filled with everyday groceries">
                            <div class="field-hint">Read aloud by screen readers, and shown if the image fails to load.</div>
                        </div>

                        <div class="col-md-3">
                            @if($currentImage)
                                <label class="form-label d-block">Current</label>
                                <img src="{{ $currentImage }}" alt="" class="hero-image-preview mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                    <label class="form-check-label small" for="remove_image">Remove this image</label>
                                </div>
                            @endif
                            <img id="imagePreview" alt="" class="hero-image-preview d-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card hero-form-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bx bx-search me-1"></i> Popular searches</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addPopularRow">
                        <i class="bx bx-plus"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <div class="field-hint mb-2">
                        The chips under the buttons. Leave the link empty to search the catalogue for the label instead.
                        Up to 8 &mdash; more will not fit on a phone.
                    </div>
                    @error('popular_searches')<span class="validation-error d-block mb-2">{{ $message }}</span>@enderror

                    <div id="popularRows">
                        @foreach($chips as $chip)
                        <div class="row g-2 popular-row">
                            <div class="col-md-4">
                                <input type="text" name="popular_label[]" class="form-control"
                                       value="{{ $chip['label'] ?? '' }}" placeholder="Rice">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="popular_url[]" class="form-control"
                                       value="{{ $chip['url'] ?? '' }}" placeholder="/products/grocery (optional)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 removePopularRow">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card hero-form-card mb-3">
                <div class="card-header"><i class="bx bx-badge-check me-1"></i> Highlight cards</div>
                <div class="card-body">
                    <div class="field-hint mb-3">The two small cards floating over the bottom of the image.</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="delivery_title" class="form-label">Left card title</label>
                            <input type="text" name="delivery_title" id="delivery_title" class="form-control"
                                   value="{{ old('delivery_title', $isEdit ? $heroSection->delivery_title : '') }}"
                                   placeholder="Fast & reliable delivery">
                        </div>
                        <div class="col-md-6">
                            <label for="delivery_subtitle" class="form-label">Left card subtitle</label>
                            <input type="text" name="delivery_subtitle" id="delivery_subtitle" class="form-control"
                                   value="{{ old('delivery_subtitle', $isEdit ? $heroSection->delivery_subtitle : '') }}"
                                   placeholder="Across Kathmandu Valley">
                        </div>
                        <div class="col-md-4">
                            <label for="trust_label" class="form-label">Right card label</label>
                            <input type="text" name="trust_label" id="trust_label" class="form-control"
                                   value="{{ old('trust_label', $isEdit ? $heroSection->trust_label : '') }}"
                                   placeholder="Trusted by">
                        </div>
                        <div class="col-md-4">
                            <label for="trust_value" class="form-label">Right card number</label>
                            <input type="text" name="trust_value" id="trust_value" class="form-control"
                                   value="{{ old('trust_value', $isEdit ? $heroSection->trust_value : '') }}"
                                   placeholder="10,000+">
                        </div>
                        <div class="col-md-4">
                            <label for="trust_subtitle" class="form-label">Right card subtitle</label>
                            <input type="text" name="trust_subtitle" id="trust_subtitle" class="form-control"
                                   value="{{ old('trust_subtitle', $isEdit ? $heroSection->trust_subtitle : '') }}"
                                   placeholder="happy customers">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <a href="{{ route('admin.heroSection') }}" class="btn btn-secondary px-5">Cancel</a>
                <button type="submit" class="btn btn-primary px-5">{{ $isEdit ? 'Update' : 'Submit' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section("script")
<script>
    (function () {
        const rows = document.getElementById('popularRows');
        const MAX_ROWS = 8;

        function rowMarkup() {
            return '<div class="row g-2 popular-row">'
                + '<div class="col-md-4"><input type="text" name="popular_label[]" class="form-control" placeholder="Rice"></div>'
                + '<div class="col-md-6"><input type="text" name="popular_url[]" class="form-control" placeholder="/products/grocery (optional)"></div>'
                + '<div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 removePopularRow"><i class="bx bx-trash"></i></button></div>'
                + '</div>';
        }

        document.getElementById('addPopularRow').addEventListener('click', function () {
            if (rows.querySelectorAll('.popular-row').length >= MAX_ROWS) return;
            rows.insertAdjacentHTML('beforeend', rowMarkup());
        });

        rows.addEventListener('click', function (event) {
            const button = event.target.closest('.removePopularRow');
            if (!button) return;

            // Always leave one row standing, so the section can be re-filled
            // without reloading the page.
            if (rows.querySelectorAll('.popular-row').length === 1) {
                rows.querySelectorAll('input').forEach((input) => { input.value = ''; });
                return;
            }
            button.closest('.popular-row').remove();
        });

        const fileInput = document.getElementById('image');
        const preview = document.getElementById('imagePreview');
        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0];
            if (!file) {
                preview.classList.add('d-none');
                return;
            }
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
        });
    })();
</script>
@endsection
