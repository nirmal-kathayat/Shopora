@extends("layouts.app")

@php
    $isEdit = isset($dealSection);
    $url = $isEdit
        ? route('admin.dealSection.update', ['id' => $dealSection->id])
        : route('admin.dealSection.store');

    // Files are not flashed back on a validation error, so the previews are
    // looked up from what is stored rather than from old input.
    $storedCardImages = $isEdit ? $dealSection->cards->pluck('image', 'id') : collect();

    $cardRows = old('cards')
        ? array_values(old('cards'))
        : ($isEdit ? $dealSection->cards->map(fn ($card) => [
            'id' => $card->id,
            'badge_text' => $card->badge_text,
            'title' => $card->title,
            'description' => $card->description,
            'cta_label' => $card->cta_label,
            'cta_url' => $card->cta_url,
            'icon' => $card->icon,
            'image_alt' => $card->image_alt,
            'featured' => $card->featured,
        ])->all() : []);

    if (empty($cardRows)) {
        $cardRows = [['icon' => 'tag']];
    }

    $trustRows = old('trust_title')
        ? collect(old('trust_title'))->map(fn ($title, $i) => [
            'title' => $title,
            'subtitle' => old('trust_subtitle.' . $i),
            'icon' => old('trust_icon.' . $i),
        ])->all()
        : ($isEdit ? ($dealSection->trust_items ?? []) : []);

    if (empty($trustRows)) {
        $trustRows = [['icon' => 'banknote', 'title' => '', 'subtitle' => '']];
    }

    $currentImage = $isEdit ? inventoryItemImageUrl($dealSection->image) : null;
@endphp

@section("style")
<style>
    .deal-form-card { border: 1px solid #eef0f3; border-radius: 12px; }
    .deal-form-card .card-header { background: #fbfcfe; border-bottom: 1px solid #eef0f3; font-weight: 600; }
    .deal-image-preview { max-width: 200px; border-radius: 10px; border: 1px solid #eef0f3; background: #f8fafc; }
    .field-hint { font-size: 12.5px; color: #6b7280; }
    .card-row { border: 1px solid #e9edf3; border-radius: 10px; padding: 16px; background: #fdfdff; }
    .card-row + .card-row { margin-top: 14px; }
    .card-row-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .trust-row + .trust-row { margin-top: 10px; }
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.dealSection') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'New' }} Deals Section</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.dealSection') }}" class="btn btn-primary">
                        <i class="bx bx-list-ul me-1"></i> Deals Section List
                    </a>
                </div>
            </div>
        </div>
        <hr />

        <form action="{{ $url }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="card deal-form-card mb-3">
                <div class="card-header"><i class="bx bx-purchase-tag me-1"></i> Section</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            @php $currentStatus = (int) old('status', $isEdit ? (int) $dealSection->status : 1); @endphp
                            <label for="status" class="form-label required">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" {{ $currentStatus === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $currentStatus === 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <div class="field-hint">Only one deals section can be active at a time.</div>
                        </div>

                        <div class="col-md-8">
                            <label for="heading" class="form-label required">Heading</label>
                            <input type="text" name="heading" id="heading"
                                   class="form-control @error('heading') is-invalid @enderror"
                                   value="{{ old('heading', $isEdit ? $dealSection->heading : '') }}"
                                   placeholder="Deals *this week*">
                            <div class="field-hint">Wrap a word in <code>*asterisks*</code> to colour it green.</div>
                            @error('heading')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="subheading" class="form-label">Sub heading</label>
                            <input type="text" name="subheading" id="subheading" class="form-control"
                                   value="{{ old('subheading', $isEdit ? $dealSection->subheading : '') }}"
                                   placeholder="Offers running right now">
                        </div>

                        <div class="col-md-4">
                            <label for="image" class="form-label">Decoration image</label>
                            <input type="file" name="image" id="image"
                                   class="form-control @error('image') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp">
                            <div class="field-hint">The products sitting behind the heading. Optional.</div>
                            @error('image')<span class="validation-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="image_alt" class="form-label">Image description</label>
                            <input type="text" name="image_alt" id="image_alt" class="form-control"
                                   value="{{ old('image_alt', $isEdit ? $dealSection->image_alt : '') }}"
                                   placeholder="A bundle of grocery products">
                        </div>

                        @if($currentImage)
                        <div class="col-12">
                            <img src="{{ $currentImage }}" alt="" class="deal-image-preview mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label small" for="remove_image">Remove this image</label>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card deal-form-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bx bx-collection me-1"></i> Cards</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addCard">
                        <i class="bx bx-plus"></i> Add card
                    </button>
                </div>
                <div class="card-body">
                    <div class="field-hint mb-3">
                        Three fit the row on a wide screen. Mark one as featured to give it the dark
                        treatment that leads the row. Order here is the order on the homepage.
                    </div>
                    @error('cards')<span class="validation-error d-block mb-2">{{ $message }}</span>@enderror

                    <div id="cardRows">
                        @foreach($cardRows as $i => $card)
                        <div class="card-row">
                            <div class="card-row-head">
                                <strong class="text-muted small text-uppercase">Card</strong>
                                <button type="button" class="btn btn-sm btn-outline-danger removeCard">
                                    <i class="bx bx-trash"></i> Remove
                                </button>
                            </div>

                            <input type="hidden" name="cards[{{ $i }}][id]" value="{{ $card['id'] ?? '' }}">

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label required">Icon</label>
                                    <select name="cards[{{ $i }}][icon]" class="form-control">
                                        @foreach($cardIcons as $value => $label)
                                        <option value="{{ $value }}" {{ ($card['icon'] ?? 'tag') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="field-hint">Also picks the artwork when no image is set.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Badge</label>
                                    <input type="text" name="cards[{{ $i }}][badge_text]" class="form-control"
                                           value="{{ $card['badge_text'] ?? '' }}" placeholder="Weekend offer">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Title</label>
                                    <input type="text" name="cards[{{ $i }}][title]"
                                           class="form-control @error('cards.'.$i.'.title') is-invalid @enderror"
                                           value="{{ $card['title'] ?? '' }}" placeholder="Save up to *15%*">
                                    @error('cards.'.$i.'.title')<span class="validation-error">{{ $message }}</span>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="cards[{{ $i }}][description]" class="form-control"
                                           value="{{ $card['description'] ?? '' }}" placeholder="On selected grocery essentials">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Button label</label>
                                    <input type="text" name="cards[{{ $i }}][cta_label]" class="form-control"
                                           value="{{ $card['cta_label'] ?? '' }}" placeholder="Shop grocery">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Button link</label>
                                    <input type="text" name="cards[{{ $i }}][cta_url]" class="form-control"
                                           value="{{ $card['cta_url'] ?? '' }}" placeholder="/products/grocery">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Card image</label>
                                    <input type="file" name="cards[{{ $i }}][image]" class="form-control"
                                           accept="image/png,image/jpeg,image/webp">
                                    <div class="field-hint">Replaces the built-in artwork. Optional.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Image description</label>
                                    <input type="text" name="cards[{{ $i }}][image_alt]" class="form-control"
                                           value="{{ $card['image_alt'] ?? '' }}" placeholder="A basket of groceries">
                                </div>
                                <div class="col-md-4 d-flex flex-column justify-content-end">
                                    @php $cardImage = isset($card['id']) ? inventoryItemImageUrl($storedCardImages[$card['id']] ?? null) : null; @endphp
                                    @if($cardImage)
                                    <img src="{{ $cardImage }}" alt="" class="deal-image-preview mb-2" style="max-width:140px;">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="cards[{{ $i }}][remove_image]" value="1">
                                        <label class="form-check-label small">Remove this image</label>
                                    </div>
                                    @endif
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="cards[{{ $i }}][featured]" value="1"
                                               {{ !empty($card['featured']) ? 'checked' : '' }}>
                                        <label class="form-check-label">Featured (dark card)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card deal-form-card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bx bx-check-shield me-1"></i> Promises bar</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addTrust">
                        <i class="bx bx-plus"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <div class="field-hint mb-2">The strip along the bottom of the section. Four fit a wide screen.</div>

                    <div id="trustRows">
                        @foreach($trustRows as $i => $item)
                        <div class="row g-2 trust-row">
                            <div class="col-md-3">
                                <select name="trust_icon[]" class="form-control">
                                    @foreach($trustIcons as $value => $label)
                                    <option value="{{ $value }}" {{ ($item['icon'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="trust_title[]" class="form-control"
                                       value="{{ $item['title'] ?? '' }}" placeholder="Best prices">
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="trust_subtitle[]" class="form-control"
                                       value="{{ $item['subtitle'] ?? '' }}" placeholder="Everyday low prices">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 removeTrust">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <a href="{{ route('admin.dealSection') }}" class="btn btn-secondary px-5">Cancel</a>
                <button type="submit" class="btn btn-primary px-5">{{ $isEdit ? 'Update' : 'Submit' }}</button>
            </div>
        </form>
    </div>
</div>

<template id="cardTemplate">
    <div class="card-row">
        <div class="card-row-head">
            <strong class="text-muted small text-uppercase">Card</strong>
            <button type="button" class="btn btn-sm btn-outline-danger removeCard"><i class="bx bx-trash"></i> Remove</button>
        </div>
        <input type="hidden" name="cards[__INDEX__][id]" value="">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label required">Icon</label>
                <select name="cards[__INDEX__][icon]" class="form-control">
                    @foreach($cardIcons as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="field-hint">Also picks the artwork when no image is set.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Badge</label>
                <input type="text" name="cards[__INDEX__][badge_text]" class="form-control" placeholder="Weekend offer">
            </div>
            <div class="col-md-6">
                <label class="form-label required">Title</label>
                <input type="text" name="cards[__INDEX__][title]" class="form-control" placeholder="Save up to *15%*">
            </div>
            <div class="col-md-6">
                <label class="form-label">Description</label>
                <input type="text" name="cards[__INDEX__][description]" class="form-control" placeholder="On selected grocery essentials">
            </div>
            <div class="col-md-3">
                <label class="form-label">Button label</label>
                <input type="text" name="cards[__INDEX__][cta_label]" class="form-control" placeholder="Shop grocery">
            </div>
            <div class="col-md-3">
                <label class="form-label">Button link</label>
                <input type="text" name="cards[__INDEX__][cta_url]" class="form-control" placeholder="/products/grocery">
            </div>
            <div class="col-md-4">
                <label class="form-label">Card image</label>
                <input type="file" name="cards[__INDEX__][image]" class="form-control" accept="image/png,image/jpeg,image/webp">
                <div class="field-hint">Replaces the built-in artwork. Optional.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Image description</label>
                <input type="text" name="cards[__INDEX__][image_alt]" class="form-control" placeholder="A basket of groceries">
            </div>
            <div class="col-md-4 d-flex flex-column justify-content-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="cards[__INDEX__][featured]" value="1">
                    <label class="form-check-label">Featured (dark card)</label>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="trustTemplate">
    <div class="row g-2 trust-row">
        <div class="col-md-3">
            <select name="trust_icon[]" class="form-control">
                @foreach($trustIcons as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="text" name="trust_title[]" class="form-control" placeholder="Best prices"></div>
        <div class="col-md-4"><input type="text" name="trust_subtitle[]" class="form-control" placeholder="Everyday low prices"></div>
        <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 removeTrust"><i class="bx bx-trash"></i></button></div>
    </div>
</template>
@endsection

@section("script")
<script>
    (function () {
        const cardRows = document.getElementById('cardRows');
        const trustRows = document.getElementById('trustRows');
        const cardTemplate = document.getElementById('cardTemplate');
        const trustTemplate = document.getElementById('trustTemplate');
        const MAX_CARDS = 6;
        const MAX_TRUST = 6;

        // Keeps growing rather than reusing indexes, so removing a row cannot
        // make a new one collide with a file input that is still queued.
        let nextCardIndex = cardRows.querySelectorAll('.card-row').length;

        document.getElementById('addCard').addEventListener('click', function () {
            if (cardRows.querySelectorAll('.card-row').length >= MAX_CARDS) return;
            const html = cardTemplate.innerHTML.replace(/__INDEX__/g, nextCardIndex++);
            cardRows.insertAdjacentHTML('beforeend', html);
        });

        cardRows.addEventListener('click', function (event) {
            const button = event.target.closest('.removeCard');
            if (!button) return;
            if (cardRows.querySelectorAll('.card-row').length === 1) {
                Swal.fire('Not allowed', 'A deals section needs at least one card.', 'info');
                return;
            }
            button.closest('.card-row').remove();
        });

        document.getElementById('addTrust').addEventListener('click', function () {
            if (trustRows.querySelectorAll('.trust-row').length >= MAX_TRUST) return;
            trustRows.insertAdjacentHTML('beforeend', trustTemplate.innerHTML);
        });

        trustRows.addEventListener('click', function (event) {
            const button = event.target.closest('.removeTrust');
            if (!button) return;
            // Always leave one row, so the bar can be refilled without a reload.
            if (trustRows.querySelectorAll('.trust-row').length === 1) {
                trustRows.querySelectorAll('input').forEach((input) => { input.value = ''; });
                return;
            }
            button.closest('.trust-row').remove();
        });
    })();
</script>
@endsection
