@extends("layouts.app")
@section("style")
<link rel="stylesheet" type="text/css" href="{{asset('vendor/select2/css/select2.min.css')}}">
@endsection
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Inventory Report</h3>
            </div>
            <hr>
            <form class="row g-3">
                <div class="sales-topbar">
                    <div class="sales-customer" style="min-width: 400px; width: 100%">
                        <label for="category" class="form-label required">Select Category</label>
                        <select name="category_id" id="category" class='form-control select2'>
                            <option value="" disabled selected>Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sales-actions" style="padding-top: 27px;">
                        <button type="button" onclick="downloadReport()" class="btn btn-primary"><span>Download Report</span></button>
                    </div>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
@endsection
@section('script')
<script src="{{asset('vendor/select2/js/select2.full.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // Select2 for searchable Category dropdown
        if ($.fn.select2) {
            $('#category').select2({
                placeholder: 'Select Category',
                allowClear: true,
                width: '100%'
            });
        }

        window.downloadReport = function() {
            var categoryId = $('#category').val();
            if (!categoryId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select a category'
                });
                return;
            }
            var url = "/admin/reports/getInventoryReport?category_id=" + encodeURIComponent(categoryId);
            window.location.href = url;
        };
    });
</script>
@endsection
