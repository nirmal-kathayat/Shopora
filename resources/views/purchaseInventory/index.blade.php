@extends("layouts.app")

@section("style")
<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
<style>
    /* Action button wrapper */
    .action-btn-group {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: center;
        white-space: nowrap;
    }

    /* Individual buttons */
    .action-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }

    /* Icon size */
    .action-btn i {
        font-size: 16px;
    }
</style>

@endsection

@section("wrapper")
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Purchase Inventory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Purchase Inventory List</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('admin.purchaseInventory.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> New Purchase Inventory
                    </a>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->
        <hr />
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="purchaseInventoryTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Vendor Name</th>
                                <th>Bill Date</th>
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

<!-- Purchase Bill Modal -->
<div class="modal fade" id="purchaseBillModal" tabindex="-1" aria-labelledby="purchaseBillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="purchaseBillModalLabel">Purchase Bill</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="billContent">
                    <!-- Bill content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">Print Bill</button>
            </div>
        </div>
    </div>
</div>

@section("script")

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#purchaseInventoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.purchaseInventory') }}",
            pageLength: 25,
            columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    render: function(data, type, full, meta) {
                        return full?.DT_RowIndex
                    }
                },
                {
                    data: 'vendor_name',
                    name: 'vendor',
                    orderable: false,
                },
                {
                    data: 'purchase_date',
                    name: 'bill_date',
                    orderable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, full, meta) {
                        var editUrl = "{{route('admin.purchaseInventory.edit',['id'=>':id'])}}".replace(':id', full.id);
                        var viewButton = '<a class="btn-primary btn-sm action-btn view-bill-btn" title="View" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-show"></i></a>';
                        var editButton = '<a class="btn-primary btn-sm action-btn" title="Edit" href="' + editUrl + '"><i class="bx bx-edit"></i></a>';
                        var deleteButton = '<a class="btn-danger btn-sm action-btn deleteAction" title="Delete" href="javascript:void(0)" data-id="' + full.id + '"><i class="bx bx-trash"></i></a>';
                        return `<div class="action-btn-group">${viewButton}${editButton}${deleteButton}</div>`;
                    }
                }
            ],
            initComplete: function(settings, json) {
                console.log(json); // Log the received JSON data
            }
        });

        // delete action
        $('#purchaseInventoryTable').on('click', '.deleteAction', function(e) {
            e.preventDefault();
            var inventoryId = $(this).data('id');
            var deleteUrl = "{{route('admin.purchaseInventory.delete',['id'=>':id'])}}".replace(':id', inventoryId);

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'btn btn-success',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'GET',
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'The purchase inventory has been deleted.',
                                'success'
                            );
                            $('#purchaseInventoryTable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the inventory.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        // View bill button click handler
        $('#purchaseInventoryTable').on('click', '.view-bill-btn', function(e) {
            e.preventDefault();
            var purchaseId = $(this).data('id');
            loadPurchaseBill(purchaseId);
        });

        // Function to load purchase bill
        function loadPurchaseBill(purchaseId) {
            $.ajax({
                url: "{{route('admin.purchaseInventory.view', ['id' => ':id'])}}".replace(':id', purchaseId),
                type: 'GET',
                success: function(response) {
                    var billHtml = generateBillHtml(response);
                    $('#billContent').html(billHtml);
                    $('#purchaseBillModal').modal('show');
                },
                error: function(xhr) {
                    Swal.fire(
                        'Error!',
                        'Unable to load bill details.',
                        'error'
                    );
                }
            });
        }

        // Function to generate bill HTML
        function generateBillHtml(data) {
            var itemsHtml = '';
            var totalTaxable = 0;

            if (data.items && data.items.length > 0) {
                data.items.forEach(function(item) {
                    var totalAmount = item.qty * item.rate;
                    totalTaxable += totalAmount;
                    itemsHtml += `
                        <tr>
                            <td>${item.inventory_item ? item.inventory_item.title : 'N/A'}</td>
                            <td class="text-end">${item.qty}</td>
                            <td class="text-end">${parseFloat(item.rate).toFixed(2)}</td>
                            <td class="text-end">${totalAmount.toFixed(2)}</td>
                        </tr>
                    `;
                });
            }

            var vatAmount = parseFloat(data.vat_amount) || 0;
            var amountAfterVat = totalTaxable + vatAmount;

            return `
                <div class="bill-container" style="padding: 20px;">
                    <div class="bill-details mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Vendor:</strong> ${data.vendor || 'N/A'}</p>
                                <p><strong>Bill Date:</strong> ${data.bill_date ? new Date(data.bill_date).toISOString().split('T')[0] : 'N/A'}</p>
                                <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>PAN Number:</strong> ${data.pan_number || 'N/A'}</p>
                                <p><strong>Bill No:</strong> #${data.id}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bill-items mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHtml}
                            </tbody>
                        </table>
                    </div>

                    <div class="bill-summary">
                        <div class="row">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Total Taxable Amount:</strong></td>
                                        <td class="text-end">${totalTaxable.toFixed(2)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>VAT Amount:</strong></td>
                                        <td class="text-end">${vatAmount.toFixed(2)}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Amount After VAT:</strong></td>
                                        <td class="text-end"><strong>${amountAfterVat.toFixed(2)}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    });
</script>
@endsection