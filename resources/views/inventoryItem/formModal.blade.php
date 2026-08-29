<div class="modal fade" id="inventoryItemFormModal" tabindex="-1" aria-labelledby="inventoryItemFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="inventory-item-modal-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryItemFormModalLabel">Create Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="inventory-item-modal-errors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="inventory_item_edit_id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="inv_modal_title" class="form-label required">Inventory Name</label>
                            <input type="text" name="title" class="form-control" data-validation="required" id="inv_modal_title" placeholder="Inventory Name">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_modal_category_id" class="form-label required">Select Category</label>
                            <div class="cat-input">
                                <select name="category_id" class="form-control inv-modal-category-select" id="inv_modal_category_id" data-validation="required">
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-modal" id="btnOpenInventoryCategoryModal">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="inv_modal_unit" class="form-label required">Unit</label>
                            <input type="text" name="unit" id="inv_modal_unit" class="form-control" data-validation="required" placeholder="Unit">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_modal_code" class="form-label required">Bar Code</label>
                            <input type="text" name="code" id="inv_modal_code" class="form-control" placeholder="Enter bar code" data-validation="required">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_modal_price_per_unit" class="form-label required">Selling Price Per Unit</label>
                            <input type="number" name="price_per_unit" id="inv_modal_price_per_unit" class="form-control" data-validation="required" step="0.01" placeholder="Enter price">
                        </div>
                        <div class="col-md-6">
                            <label for="inv_modal_image" class="form-label">Product Image</label>
                            <input type="file" name="image" id="inv_modal_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted">Optional. JPG, PNG, WEBP or GIF (max 2MB)</small>
                            <div id="inv_modal_image_preview" class="inv-modal-image-preview d-none mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="inventory-item-modal-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="inventoryCategoryModal" tabindex="-1" aria-labelledby="inventoryCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inventoryCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="inventoryCategoryName" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="inventoryCategoryName" placeholder="Category name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveInventoryCategoryBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    #inventoryItemFormModal .inv-modal-image-preview img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }
</style>
@endpush

@push('scripts')
@include('scripts.validation')
<script>
    (function () {
        const storeUrl = "{{ route('admin.inventoryItem.store') }}";
        const updateUrlTemplate = "{{ route('admin.inventoryItem.update', ['id' => ':id']) }}";
        const categoryStoreUrl = "{{ route('admin.category.store') }}";
        const inventoryImageBaseUrl = "{{ asset('image') }}/";
        let inventoryItemModalMode = 'create';

        function getInventoryItemModalEl() {
            return document.getElementById('inventoryItemFormModal');
        }

        function resetInventoryImagePreview() {
            $('#inv_modal_image').val('');
            $('#inv_modal_image_preview').addClass('d-none').empty();
        }

        function showInventoryImagePreview(imageUrl) {
            if (!imageUrl) {
                resetInventoryImagePreview();
                return;
            }

            $('#inv_modal_image_preview')
                .removeClass('d-none')
                .html('<img src="' + imageUrl + '" alt="Product image preview">');
        }

        function resetInventoryItemModal() {
            inventoryItemModalMode = 'create';
            $('#inventory_item_edit_id').val('');
            $('#inventoryItemFormModalLabel').text('Create Inventory Item');
            $('#inventory-item-modal-submit').text('Submit');
            $('#inventory-item-modal-errors').addClass('d-none').empty();
            $('#inventory-item-modal-form')[0].reset();
            $('#inv_modal_category_id').val('').trigger('change');
            resetInventoryImagePreview();
        }

        function populateInventoryItemModal(row) {
            inventoryItemModalMode = 'edit';
            $('#inventory_item_edit_id').val(row.id);
            $('#inventoryItemFormModalLabel').text('Edit Inventory Item');
            $('#inventory-item-modal-submit').text('Update');
            $('#inventory-item-modal-errors').addClass('d-none').empty();
            $('#inv_modal_title').val(row.title);
            $('#inv_modal_unit').val(row.unit);
            $('#inv_modal_code').val(row.code);
            $('#inv_modal_price_per_unit').val(row.price_per_unit);
            $('#inv_modal_category_id').val(row.category_id).trigger('change');
            resetInventoryImagePreview();

            const imageUrl = row.image_url || (row.image ? inventoryImageBaseUrl + row.image : '');
            showInventoryImagePreview(imageUrl);
        }

        function showInventoryItemModalErrors(message) {
            $('#inventory-item-modal-errors').removeClass('d-none').html(message);
        }

        function initInventoryCategorySelect2() {
            if (!$.fn.select2) {
                return;
            }

            const $category = $('#inv_modal_category_id');
            if ($category.hasClass('select2-hidden-accessible')) {
                $category.select2('destroy');
            }

            $category.select2({
                placeholder: 'Select Category',
                width: '100%',
                dropdownParent: $('#inventoryItemFormModal')
            });
        }

        $(document).on('click', '#btnNewInventoryItem', function () {
            resetInventoryItemModal();
        });

        $(document).on('click', '.editInventoryItem', function () {
            const table = window.inventoryItemDataTable;
            if (!table) {
                return;
            }

            const $btn = $(this);
            let row = table.row($btn.closest('tr')).data();

            if (!row || !row.id) {
                row = {
                    id: $btn.data('id'),
                    title: $btn.data('title'),
                    unit: $btn.data('unit'),
                    code: $btn.data('code'),
                    price_per_unit: $btn.data('price'),
                    category_id: $btn.data('categoryId'),
                    image: $btn.data('image'),
                    image_url: $btn.data('imageUrl'),
                };
            }

            populateInventoryItemModal(row);
            const modalEl = getInventoryItemModalEl();
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });

        $('#inventoryItemFormModal').on('shown.bs.modal', function () {
            initInventoryCategorySelect2();
        });

        $('#inventoryItemFormModal').on('hidden.bs.modal', function () {
            const $category = $('#inv_modal_category_id');
            if ($category.hasClass('select2-hidden-accessible')) {
                $category.select2('destroy');
            }
            resetInventoryItemModal();
        });

        $('#btnOpenInventoryCategoryModal').on('click', function () {
            const categoryModalEl = document.getElementById('inventoryCategoryModal');
            if (categoryModalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(categoryModalEl).show();
            }
        });

        $('#saveInventoryCategoryBtn').on('click', function () {
            const categoryName = $('#inventoryCategoryName').val().trim();
            if (!categoryName) {
                Swal.fire('Please enter a category name.');
                return;
            }

            $.ajax({
                url: categoryStoreUrl,
                method: 'POST',
                data: {
                    title: categoryName,
                    _token: $('input[name="_token"]').val()
                },
                success: function (response) {
                    if (response.type === 'success') {
                        const $category = $('#inv_modal_category_id');
                        $category.append($('<option>', {
                            value: response.data.id,
                            text: response.data.title
                        }));
                        $category.val(response.data.id).trigger('change');

                        const categoryModalEl = document.getElementById('inventoryCategoryModal');
                        if (categoryModalEl && window.bootstrap) {
                            bootstrap.Modal.getInstance(categoryModalEl)?.hide();
                        }

                        $('#inventoryCategoryName').val('');
                        $('#categoryFilter').append($('<option>', {
                            value: response.data.id,
                            text: response.data.title
                        }));

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Category added successfully!'
                        });
                    } else {
                        Swal.fire('Error: ' + (response.message || 'Unable to add category.'));
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'An unknown error occurred.';
                    Swal.fire('Error: ' + message);
                }
            });
        });

        $('#inv_modal_image').on('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                $('#inv_modal_image_preview')
                    .removeClass('d-none')
                    .html('<img src="' + e.target.result + '" alt="Product image preview">');
            };
            reader.readAsDataURL(file);
        });

        $('#inventory-item-modal-form').on('submit', function (e) {
            e.preventDefault();

            const editId = $('#inventory_item_edit_id').val();
            const isEdit = inventoryItemModalMode === 'edit' && editId;
            const url = isEdit ? updateUrlTemplate.replace(':id', editId) : storeUrl;

            $('#inventory-item-modal-submit').prop('disabled', true).text(isEdit ? 'Updating...' : 'Saving...');
            $('#inventory-item-modal-errors').addClass('d-none').empty();

            const formData = new FormData();
            formData.append('title', $('#inv_modal_title').val());
            formData.append('unit', $('#inv_modal_unit').val());
            formData.append('code', $('#inv_modal_code').val());
            formData.append('category_id', $('#inv_modal_category_id').val());
            formData.append('price_per_unit', $('#inv_modal_price_per_unit').val());
            formData.append('_token', $('input[name="_token"]').val());

            const imageFile = $('#inv_modal_image')[0].files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function (res) {
                    if (res.type === 'success') {
                        const modalEl = getInventoryItemModalEl();
                        if (modalEl && window.bootstrap) {
                            bootstrap.Modal.getInstance(modalEl)?.hide();
                        }

                        if (window.inventoryItemDataTable) {
                            window.inventoryItemDataTable.draw(false);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message || (isEdit ? 'Inventory item updated.' : 'Inventory item created.')
                        });
                    } else {
                        showInventoryItemModalErrors(res.message || 'Unable to save inventory item.');
                    }
                },
                error: function (xhr) {
                    let message = 'Something went wrong.';
                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    showInventoryItemModalErrors(message);
                },
                complete: function () {
                    const isEditMode = inventoryItemModalMode === 'edit';
                    $('#inventory-item-modal-submit').prop('disabled', false).text(isEditMode ? 'Update' : 'Submit');
                }
            });
        });
    })();
</script>
@endpush
