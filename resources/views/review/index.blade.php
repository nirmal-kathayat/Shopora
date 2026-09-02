@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
    .rev-stars { color: #f59e0b; letter-spacing: 1px; }
    .rev-body { max-width: 360px; }
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
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Product Reviews</li>
                    </ol>
                </nav>
            </div>
        </div>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="reviewTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        const esc = (v) => $('<div>').text(v == null ? '' : v).html();
        const stars = (n) => '★'.repeat(Number(n)) + '☆'.repeat(5 - Number(n));

        const table = $('#reviewTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.review') }}",
            columns: [
                { data: 'id', name: 'product_reviews.id', searchable: false, orderable: false,
                  render: (d, t, full, meta) => meta.row + 1 },
                { data: 'product_title', name: 'inventory_items.title',
                  render: (d) => `<span class="fw-semibold">${esc(d)}</span>` },
                { data: 'customer_name', name: 'customers.name', render: esc },
                { data: 'rating', name: 'product_reviews.rating', searchable: false,
                  render: (d) => `<span class="rev-stars">${stars(d)}</span> <span class="text-muted">${Number(d)}</span>` },
                { data: 'body', name: 'product_reviews.body', orderable: false,
                  render: (d, t, full) => {
                    const title = full.title ? `<div class="fw-semibold">${esc(full.title)}</div>` : '';
                    return `<div class="rev-body">${title}<div class="text-muted small">${esc(d) || '—'}</div></div>`;
                  }},
                { data: 'created_at', name: 'product_reviews.created_at', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false,
                  render: (d, t, full) => `<a class="btn btn-danger deleteAction btn-sm" href="javascript:void(0)" data-id="${full.id}"><i class="bx bx-trash"></i></a>` }
            ]
        });

        $('#reviewTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const deleteUrl = "{{ route('admin.review.delete', ['id' => ':id']) }}".replace(':id', id);
            Swal.fire({
                title: 'Delete this review?',
                text: "This removes the customer's review permanently.",
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
</script>
@endsection
