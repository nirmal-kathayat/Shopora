<script>
    $(document).ready(function() {
        let selectedInventoryItems = [];

        // Category filtering
        $('#category-filter li').on('click', function() {
            const category = $(this).data('cat');
            $('#category-filter li').removeClass('active');
            $(this).addClass('active');

            if (category === 'all') {
                $('#inventory-list tr.sales-inventory-row').show();
            } else {
                $('#inventory-list tr.sales-inventory-row').each(function() {
                    if ($(this).data('category') === category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            toggleInventoryEmptyState();
        });

        // Search functionality
        $('#search-input').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $.ajax({
                url: window.location.href,
                method: 'GET',
                data: {
                    search: searchTerm
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    updateInventoryList(response.inventories);
                },
                error: function(xhr, status, error) {
                    console.error('Search error:', error);
                }
            });
        });

        // Add inventory item to selected items
        $('#inventory-list').on('click', '.add-inventory', function() {
            const $this = $(this);
            const itemId = $this.data('id');
            let existingItem = selectedInventoryItems.find(item => item.id === itemId);

            if (existingItem) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Item already in cart!'
                });
            } else {
                selectedInventoryItems.push({
                    id: itemId,
                    title: $this.data('title'),
                    price: parseFloat($this.data('price_per_unit')),
                    unit: $this.data('unit'),
                    code: $this.data('code'),
                    image: $this.data('image') || '',
                    image_url: $this.data('imageUrl') || '',
                    quantity: 1,
                    discount: 0
                });
                updateInventoryDisplay();
            }
            $('#search-input').val('');
        });

        // Inventory item modal: initialize select2 + handle submit via AJAX
        function initInventoryModalSelect2() {
            if (!$.fn.select2) return;
            $('#inventory_category_id').select2({
                dropdownParent: $('#inventoryItemModal'),
                width: '100%',
                placeholder: 'Select Category'
            });
        }

        // Bootstrap modal events (works in Bootstrap 5 too)
        $('#inventoryItemModal').on('shown.bs.modal', function() {
            initInventoryModalSelect2();
        });

        $('#inventoryItemModal').on('show.bs.modal', function() {
            $('#inventory-item-form-errors').addClass('d-none').empty();
            $('#inventory-item-submit').prop('disabled', false).text('Submit');
            // reset fields
            $('#inv_title').val('');
            $('#inv_unit').val('');
            $('#inv_code').val('');
            $('#inv_price_per_unit').val('');
            $('#inv_image').val('');
            $('#inventory_category_id').val('').trigger('change');
        });

        // Nested category overlay controls (no second Bootstrap modal)
        function openInventoryCategoryNested() {
            $('#inventory-category-errors').addClass('d-none').empty();
            $('#inventoryCategoryName').val('');
            $('#inventoryCategoryNested').removeClass('d-none').addClass('d-flex');
            setTimeout(() => $('#inventoryCategoryName').trigger('focus'), 0);
        }

        function closeInventoryCategoryNested() {
            $('#inventoryCategoryNested').addClass('d-none').removeClass('d-flex');
        }

        $('#openInventoryCategoryNested').on('click', function() {
            openInventoryCategoryNested();
        });
        $('#inventoryCategoryNestedClose, #inventoryCategoryNestedCancel').on('click', function() {
            closeInventoryCategoryNested();
        });

        function toggleInventoryEmptyState() {
            const totalRows = $('#inventory-list tr.sales-inventory-row').length;
            const visibleRows = $('#inventory-list tr.sales-inventory-row:visible').length;

            if (totalRows === 0 || visibleRows === 0) {
                $('#inventory-list-empty').removeClass('d-none');
            } else {
                $('#inventory-list-empty').addClass('d-none');
            }
        }

        window.toggleInventoryEmptyState = toggleInventoryEmptyState;

        function escapeHtml(text) {
            return String(text || '').replace(/[&<>"']/g, function (m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
            });
        }

        function buildInventoryThumb(item) {
            const imageUrl = item.image_url || (item.image ? '{{ asset('image') }}/' + encodeURIComponent(item.image) : null);
            if (imageUrl) {
                return '<img src="' + imageUrl + '" alt="' + escapeHtml(item.title) + '">';
            }

            return '<i class="bx bx-package"></i>';
        }

        function buildInventoryRow(item) {
            const price = Number(item.price_per_unit).toLocaleString(undefined, { maximumFractionDigits: 0 });
            const code = item.code || '—';
            const unit = item.unit || '—';
            const imageUrl = item.image_url || (item.image ? '{{ asset('image') }}/' + encodeURIComponent(item.image) : '');

            return `
                <tr class="sales-inventory-row" data-category="${item.category_id}">
                    <td>
                        <div class="sales-inventory-item-cell">
                            <div class="sales-inventory-thumb">
                                ${buildInventoryThumb(item)}
                            </div>
                            <div class="sales-inventory-item-meta">
                                <span class="sales-inventory-item-name">${escapeHtml(item.title)}</span>
                                <span class="sales-inventory-item-sub">Code: ${escapeHtml(code)} • Unit: ${escapeHtml(unit)}</span>
                            </div>
                        </div>
                    </td>
                    <td class="sales-inventory-col-price">Rs. ${price}</td>
                    <td class="sales-inventory-col-action">
                        <button type="button" class="sales-inventory-add-btn add-inventory"
                            data-id="${item.id}"
                            data-title="${escapeHtml(item.title)}"
                            data-price_per_unit="${item.price_per_unit}"
                            data-unit="${escapeHtml(item.unit || '')}"
                            data-code="${escapeHtml(item.code || '')}"
                            data-image="${escapeHtml(item.image || '')}"
                            data-image-url="${imageUrl}"
                            aria-label="Add ${escapeHtml(item.title)}">
                            <i class="bx bx-plus"></i>
                        </button>
                    </td>
                </tr>`;
        }

        function prependInventoryToList(item) {
            const $list = $('#inventory-list');
            $list.prepend(buildInventoryRow(item));

            const selectedCategory = $('#category-filter-select').val();
            if (selectedCategory && selectedCategory !== 'all' && String(item.category_id) !== String(selectedCategory)) {
                $list.find('tr').first().hide();
            }

            toggleInventoryEmptyState();
        }

        $('#inventory-item-form').on('submit', function(e) {
            e.preventDefault();
            $('#inventory-item-form-errors').addClass('d-none').empty();
            $('#inventory-item-submit').prop('disabled', true).text('Saving...');

            const payload = {
                title: $('#inv_title').val(),
                category_id: $('#inventory_category_id').val(),
                unit: $('#inv_unit').val(),
                code: $('#inv_code').val(),
                price_per_unit: $('#inv_price_per_unit').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            const formData = new FormData();
            Object.keys(payload).forEach(function (key) {
                formData.append(key, payload[key]);
            });

            const imageFile = $('#inv_image')[0] && $('#inv_image')[0].files[0];
            if (imageFile) {
                formData.append('image', imageFile);
            }

            $.ajax({
                url: '{{ route("admin.inventoryItem.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res.type === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        if (res.inventory) {
                            prependInventoryToList(res.inventory);
                        }

                        // close modal
                        const modalEl = document.getElementById('inventoryItemModal');
                        if (modalEl && window.bootstrap) {
                            const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            instance.hide();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message || 'Something went wrong'
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let html = '<ul class="mb-0">';
                        Object.keys(errors).forEach(function(k) {
                            errors[k].forEach(function(msg) {
                                html += `<li>${msg}</li>`;
                            });
                        });
                        html += '</ul>';
                        $('#inventory-item-form-errors').removeClass('d-none').html(html);
                    } else {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                        $('#inventory-item-form-errors').removeClass('d-none').html(msg);
                    }
                },
                complete: function() {
                    $('#inventory-item-submit').prop('disabled', false).text('Submit');
                }
            });
        });

        // Inventory category add (from inside inventory modal)
        $('#saveInventoryCategoryBtn').on('click', function() {
            const title = ($('#inventoryCategoryName').val() || '').trim();
            $('#inventory-category-errors').addClass('d-none').empty();
            if (!title) {
                $('#inventory-category-errors').removeClass('d-none').text('Please enter a category name.');
                return;
            }

            $('#saveInventoryCategoryBtn').prop('disabled', true).text('Saving...');
            $.ajax({
                url: '{{ route("admin.category.store") }}',
                method: 'POST',
                data: {
                    title,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(res) {
                    if (res.type === 'success' && res.data) {
                        // add to inventory modal select
                        const opt = new Option(res.data.title, res.data.id, true, true);
                        $('#inventory_category_id').append(opt).trigger('change');

                        // also add to sales category filter dropdown if present
                        if ($('#category-filter-select').length) {
                            $('#category-filter-select').append(new Option(res.data.title, res.data.id, false, false)).trigger('change.select2');
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        closeInventoryCategoryNested();
                    } else {
                        $('#inventory-category-errors').removeClass('d-none').text(res.message || 'Something went wrong');
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                    $('#inventory-category-errors').removeClass('d-none').text(msg);
                },
                complete: function() {
                    $('#saveInventoryCategoryBtn').prop('disabled', false).text('Submit');
                }
            });
        });

        function updateInventoryList(items) {
            const $inventoryListContainer = $('#inventory-list');
            $inventoryListContainer.empty();

            if (!items || items.length === 0) {
                toggleInventoryEmptyState();
                return;
            }

            items.forEach(function(item) {
                $inventoryListContainer.append(buildInventoryRow(item));
            });

            const selectedCategory = $('#category-filter-select').val();
            if (selectedCategory && selectedCategory !== 'all') {
                $('#inventory-list tr.sales-inventory-row').each(function() {
                    if ($(this).data('category') == selectedCategory) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            toggleInventoryEmptyState();
        }

        function buildCartRow(item, index) {
            const lineTotal = Math.max((item.price * item.quantity) - (item.discount || 0), 0);
            const unitPrice = Number(item.price).toLocaleString(undefined, { maximumFractionDigits: 0 });

            return `
                <tr class="sales-cart-row" data-index="${index}">
                    <td>
                        <div class="sales-cart-item-cell">
                            <div class="sales-cart-thumb">${buildInventoryThumb(item)}</div>
                            <div>
                                <span class="sales-cart-item-name">${escapeHtml(item.title)}</span>
                                <span class="sales-cart-item-sub">Rs. ${unitPrice}</span>
                            </div>
                        </div>
                    </td>
                    <td class="sales-cart-col-qty">
                        <div class="sales-cart-qty-stepper">
                            <button type="button" class="decrease-quantity" data-index="${index}" aria-label="Decrease quantity">
                                <i class="bx bx-minus"></i>
                            </button>
                            <span class="sales-cart-qty-value">${item.quantity}</span>
                            <button type="button" class="increase-quantity" data-index="${index}" aria-label="Increase quantity">
                                <i class="bx bx-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td class="sales-cart-col-price">Rs. ${unitPrice}</td>
                    <td>
                        <div class="sales-cart-total-cell">
                            <span class="sales-cart-line-total">Rs. ${lineTotal.toFixed(2)}</span>
                            <button type="button" class="sales-cart-remove trash-button" data-index="${index}" aria-label="Remove item">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }

        function updateInventoryDisplay() {
            const $tbody = $('#selected-items');
            $tbody.empty();

            if (selectedInventoryItems.length > 0) {
                for (let i = selectedInventoryItems.length - 1; i >= 0; i--) {
                    $tbody.append(buildCartRow(selectedInventoryItems[i], i));
                }
                $('#payment-mode-section').show();
                $('#cart-empty-state').addClass('d-none');
            } else {
                $('#payment-mode-section').hide();
                $('#cart-empty-state').removeClass('d-none');
            }

            updateTotalAmount();
        }

        $('#clear-cart-btn').on('click', function() {
            if (selectedInventoryItems.length === 0) {
                return;
            }

            selectedInventoryItems = [];
            updateInventoryDisplay();
            $('#received-amount').val('');
            $('#change-amount').val('Rs. 0.00');
            $('#discount-amount').val('');
        });

        // Discount amount input handler
        $('#discount-amount').on('input', function() {
            updateTotalAmount();
        });

        // Quantity controls and other functions remain similar
        function updateTotalAmount() {
            let subtotal = selectedInventoryItems.reduce((total, item) => {
                let itemTotal = (item.price * item.quantity) - (item.discount || 0);
                return total + (itemTotal > 0 ? itemTotal : 0);
            }, 0);
            
            let discountAmount = parseFloat($('#discount-amount').val()) || 0;
            let totalAmount = Math.max(subtotal - discountAmount, 0);
            
        $('.order-total').text(`Rs. ${totalAmount.toFixed(2)}`);
            calculateChange();
        }
        $('#received-amount').on('input', calculateChange);

        function calculateChange() {
            const totalText = $('.order-total').text().replace('Rs.', '').trim();
            let totalAmount = parseFloat(totalText) || 0;
            let receivedAmount = parseFloat($('#received-amount').val()) || 0;
            let changeAmount = Math.max(receivedAmount - totalAmount, 0);
            $('#change-amount').val('Rs. ' + changeAmount.toFixed(2));
        }

        $('#selected-items').on('click', '.increase-quantity, .decrease-quantity', function() {
            const index = $(this).data('index');
            let item = selectedInventoryItems[index];

            if ($(this).hasClass('increase-quantity')) {
                item.quantity++;
            } else if (item.quantity > 1) {
                item.quantity--;
            }

            updateInventoryDisplay();
        });
        $('#selected-items').on('click', '.trash-button', function() {
            const index = $(this).data('index');
            selectedInventoryItems.splice(index, 1);
            updateInventoryDisplay();
        });

        // Split payment functionality
        let isSplitPayment = false;
        
        $('#save-split-payment').on('click', function() {
            const splitPayments = [];
            let totalSplitAmount = 0;
            
            // Collect split payment data
            @foreach($paymentModes as $payment)
            let paymentAmount{{ $payment->id }} = parseFloat($('#payment-{{ $payment->id }}').val()) || 0;
            if (paymentAmount{{ $payment->id }} > 0) {
                splitPayments.push({
                    payment_mode_id: {{ $payment->id }},
                    amount: paymentAmount{{ $payment->id }}
                });
                totalSplitAmount += paymentAmount{{ $payment->id }};
            }
            @endforeach
            
            const totalAmount = parseFloat($('.order-total').text().replace('Rs. ', '')) || 0;
            
            // Validate split payments
            if (Math.abs(totalSplitAmount - totalAmount) > 0.01) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Split payment amounts must equal the total amount!'
                });
                return;
            }
            
            if (splitPayments.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter at least one payment amount!'
                });
                return;
            }
            
            isSplitPayment = true;
            
            // Close modal and process sale
            $('#splitPaymentModal').modal('hide');
            processSale(splitPayments);
        });
        
        function processSale(splitPayments = null) {
            if (selectedInventoryItems.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Select at least one item!'
                });
                return;
            }

            let customerId = $('#customer').val();
            let totalAmount = parseFloat($('.order-total').text().replace('Rs. ', '')) || 0;
            let receivedAmount = parseFloat($('#received-amount').val()) || 0;
            let discountAmount = parseFloat($('#discount-amount').val()) || 0;
            
            // For split payments, skip received amount validation
            if (!splitPayments && receivedAmount < totalAmount) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Received amount must be greater than or equal to the total amount!'
                });
                return;
            }

            let salesData = {
                order_by: '{{ Auth::guard("admin")->user()->id }}',
                customer_id: customerId,
                discount: discountAmount,
                products: selectedInventoryItems.map(item => ({
                    product_id: item.id,
                    qty: item.quantity,
                    payment_mode: splitPayments ? splitPayments.map(p => p.payment_mode_id).join(',') : $('#payment-mode').val(),
                    price_per_unit: item.price,
                    discount: item.discount
                })),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            // Add split payments if present
            if (splitPayments) {
                salesData.split_payments = splitPayments;
            }

            $.ajax({
                url: "/admin/sales/store",
                method: 'POST',
                data: salesData,
                success: function(response) {
                    if (response.type === 'success') {
                        loadInvoiceModal(response.invoice_id);

                        // reset cart
                        selectedInventoryItems = [];
                        updateInventoryDisplay();
                        $('#payment-mode').val('');
                        $('#received-amount').val('');
                        $('#change-amount').val('Rs. 0.00');
                        $('#customer').val('').trigger('change');
                        $('#discount-amount').val('');
                        
                        // Reset split payment modal
                        @foreach($paymentModes as $payment)
                        $('#payment-{{ $payment->id }}').val('');
                        @endforeach
                        isSplitPayment = false;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },

                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while processing the request: ' + error
                    });
                }
            });
        }
        
        // Regular confirm sale button
        $('.btn-confirm').on('click', function() {
            let paymentMode = $('#payment-mode').val();
            if (!paymentMode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a payment mode!'
                });
                return;
            }
            processSale();
        });
    });
</script>