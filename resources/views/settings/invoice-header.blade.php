@extends("layouts.app")

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Settings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bill Header</li>
                    </ol>
                </nav>
            </div>
        </div>
        <hr />

        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-1">Bill header</h5>
                        <p class="text-muted mb-4">
                            Printed at the top of every bill — counter sales and online orders alike.
                            The shop name and logo also appear on the admin sidebar and sign-in page.
                        </p>

                        <form action="{{ route('admin.settings.invoiceHeader.update') }}" method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Shop name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $header['name']) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row mb-3 align-items-start">
                                <div class="col-md-8">
                                    <label for="logo" class="form-label">Logo</label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                           id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
                                    <div class="form-text">
                                        Shown on the sidebar and sign-in page. A wide PNG with a transparent
                                        background works best. Up to 1 MB. Leave empty to keep the current logo;
                                        with none uploaded, a "LOGO" placeholder is shown.
                                    </div>
                                    @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label d-block">Current</label>
                                    <img src="{{ $brand['logo_url'] }}" alt="{{ $brand['name'] }}" id="logoPreview"
                                         class="{{ $brand['logo_url'] ? '' : 'd-none' }}"
                                         style="max-height:48px;max-width:100%;object-fit:contain;">
                                    @unless($brand['logo_url'])
                                        <span id="logoPlaceholder" class="d-inline-block fw-bold"
                                              style="line-height:40px;font-size:22px;color:#2563eb;">LOGO</span>
                                    @endunless
                                    @if($brand['logo_url'])
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_logo"
                                                   value="1" id="remove_logo">
                                            <label class="form-check-label small" for="remove_logo">
                                                Remove the logo
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror"
                                       id="address" name="address" value="{{ old('address', $header['address']) }}"
                                       placeholder="Kathmandu, Nepal">
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pan" class="form-label">PAN / VAT No.</label>
                                    <input type="text" class="form-control @error('pan') is-invalid @enderror"
                                           id="pan" name="pan" value="{{ old('pan', $header['pan']) }}"
                                           placeholder="600112233">
                                    <div class="form-text">Left blank, the line is not printed.</div>
                                    @error('pan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $header['phone']) }}"
                                           placeholder="01-4000000">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="footer_note" class="form-label">Closing line</label>
                                <input type="text" class="form-control @error('footer_note') is-invalid @enderror"
                                       id="footer_note" name="footer_note"
                                       value="{{ old('footer_note', $header['footer_note']) }}"
                                       placeholder="Thank you for shopping with us.">
                                @error('footer_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary px-4">Save</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:.08em;">
                            How it prints
                        </h6>
                        {{-- The same classes the bill itself uses, so this preview cannot drift. --}}
                        <div class="shopora-bill" style="padding:0;">
                            <div class="bill-head">
                                <h2>{{ $header['name'] }}</h2>
                                @if($header['address'])<p>{{ $header['address'] }}</p>@endif
                                @if($header['pan'])<p>Vat No : {{ $header['pan'] }}</p>@endif
                                @if($header['phone'])<p>Contact : {{ $header['phone'] }}</p>@endif
                            </div>
                            @if($header['footer_note'])
                                <h5 class="bill-note">{{ $header['footer_note'] }}</h5>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script>
    // Show the chosen file straight away, before it is saved.
    document.getElementById('logo').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var preview = document.getElementById('logoPreview');
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('d-none');
            var placeholder = document.getElementById('logoPlaceholder');
            if (placeholder) placeholder.classList.add('d-none');
        }
    });
</script>
@endsection

@section("style")
<link href="{{ asset('assets/css/invoice-bill.css') }}?v=4" rel="stylesheet" />
@endsection
