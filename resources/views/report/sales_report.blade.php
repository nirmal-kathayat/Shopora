@extends("layouts.app")
<?php
$activeCat = $_GET['cat'] ?? ''
?>
@section("style")
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <div class="card-body body-wrapper p-5">
            <div class="card-title d-flex align-items-center">
                <h3 class="mb-0 text-primary text-font">Sales Reports</h3>
            </div>
            <hr>
            <div class="form-group row g-3">
                <div class="col-md-6">
                    <label for="year" class="input-label">Select Year</label>
                    <select name="year" id="year" class='form-control'>
                        @foreach($data['years'] as $key=>$value)
                        <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="month" class="input-label">Select Month</label>
                    <select name="month" id="month" class='form-control'>
                        @foreach($data['months'] as $key=>$value)
                        <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="col-12 justify-item-end">
                <button onclick="redirectToReport()" class="btn btn-primary px-5"><span>Download Report</span></button>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function() {
        // alert('test')
        window.redirectToReport = function() {
            var month = $('#month').val();
            var year = $('#year').val();
            var url = "/admin/reports/getSalesReport?month=" + encodeURIComponent(month) + "&year=" + encodeURIComponent(year);
            window.location.href = url;
        };
    });
</script>
@endsection