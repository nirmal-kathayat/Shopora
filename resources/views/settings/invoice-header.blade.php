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
                        </p>

                        <form action="{{ route('admin.settings.invoiceHeader.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Shop name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $header['name']) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <header class="bill-head" style="border:0;padding-bottom:0;">
                                <h2 class="bill-shop-name">{{ $header['name'] }}</h2>
                                @if($header['address'])
                                    <p class="bill-shop-line">{{ $header['address'] }}</p>
                                @endif
                                @if($header['pan'] || $header['phone'])
                                    <p class="bill-shop-line">
                                        {{ collect([
                                            $header['pan'] ? 'PAN: ' . $header['pan'] : null,
                                            $header['phone'] ? 'Tel: ' . $header['phone'] : null,
                                        ])->filter()->implode(' · ') }}
                                    </p>
                                @endif
                                <span class="bill-kind">Abbreviated Tax Invoice</span>
                            </header>
                            @if($header['footer_note'])
                                <p class="bill-note">{{ $header['footer_note'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("style")
<link href="{{ asset('assets/css/invoice-bill.css') }}?v=1" rel="stylesheet" />
@endsection
