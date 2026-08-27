<script>
    $(document).ready(function() {
        let selectedInventoryItems = [];

        // Category filtering
        $('#category-filter li').on('click', function() {
            const category = $(this).data('cat');
            $('#category-filter li').removeClass('active');
            $(this).addClass('active');

            if (category === 'all') {
                $('#inventory-list tr').show();
            } else {
                $('#inventory-list tr').each(function() {
                    if ($(this).data('category') === category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
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

        function buildInventoryRow(item) {
            return `
                <tr data-category="${item.category_id}">
                    <td>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-sm">
                                <span class="fw-bold">${item.title}</span>
                                <br>
                                <small class="text-muted">- Rs. ${Number(item.price_per_unit).toLocaleString()}</small>
                            </div>
                            <div class="plus-sm add-inventory"
                                data-id="${item.id}"
                                data-title="${item.title}"
                                data-price_per_unit="${item.price_per_unit}"
                                data-unit="${item.unit}"
                                data-code="${item.code}">
                                <i class="bx bx-plus"></i>
                            </div>
                        </div>
                    </td>
                </tr>`;
        }

        function prependInventoryToList(item) {
            const $list = $('#inventory-list');
            // remove "No items found" placeholder if present
            if ($list.find('tr').length === 1 && $list.find('td').length && $list.text().toLowerCase().includes('no items')) {
                $list.empty();
            }
            $list.prepend(buildInventoryRow(item));

            // Respect category filter (select based)
            const selectedCategory = $('#category-filter-select').val();
            if (selectedCategory && selectedCategory !== 'all' && String(item.category_id) !== String(selectedCategory)) {
                $list.find('tr').first().hide();
            }
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

            $.ajax({
                url: '{{ route("admin.inventoryItem.store") }}',
                method: 'POST',
                data: payload,
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
            let $inventoryListContainer = $('#inventory-list');
            $inventoryListContainer.empty();

            if (!items || items.length === 0) {
                $inventoryListContainer.append('<tr><td colspan="4" class="text-center">No items found</td></tr>');
            } else {
                items.forEach(function(item) {
                    let itemRow = `
                    <tr data-category="${item.category_id}">
                        <td>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-sm">
                                    <span class="fw-bold">${item.title}</span>
                                    <br>
                                    <small class="text-muted">- Rs. ${Number(item.price_per_unit).toLocaleString()}</small>
                                </div>
                                <div class="plus-sm add-inventory"
                                    data-id="${item.id}"
                                    data-title="${item.title}"
                                    data-price_per_unit="${item.price_per_unit}"
                                    data-unit="${item.unit}"
                                    data-code="${item.code}">
                                    <i class="bx bx-plus"></i>
                                </div>
                            </div>
                        </td>
                    </tr>`;
                    $inventoryListContainer.append(itemRow);
                });
            }
        }

        function updateInventoryDisplay() {
            let $selectedItemsContainer = $('#selected-items');
            $selectedItemsContainer.empty();

            if (selectedInventoryItems.length > 0) {
                // Iterate in reverse order to show latest items first
                for (let i = selectedInventoryItems.length - 1; i >= 0; i--) {
                    let item = selectedInventoryItems[i];
                    let index = i; // Keep original index for data-index
                    let itemHtml = `
                    <div class="card-sm-wrapper" data-index="${index}">
                        <div class="product-drawer">  
                            <div class="product-item-sm">
                                <h5 class="card-title sm-name">${item.title}</h5>
                                <h6>Quantity</h6>
                               <div class="qty-discount">
                                    <div class="btn-qty">
                                        <button class="decrease-quantity" data-index="${index}">-</button>
                                        <span class="count">${item.quantity}</span>
                                        <button class="increase-quantity" data-index="${index}">+</button>
                                    </div>
                                </div>

                            </div>
                            <div class="price-sm">
                                <button class="btn-sm-trash trash-button" data-index="${index}">
                                    <i class="bx bx-trash"></i>
                                </button>
                                <span class="product-total">Rs.${(item.price * item.quantity).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>`;
                    $selectedItemsContainer.append(itemHtml);
                }

                $('#payment-mode-section').show();
            } else {
                $selectedItemsContainer.html('<div class="text-center">No items selected</div>');
                $('#payment-mode-section').hide();
            }

            updateTotalAmount();
        }

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
        $('.amount-cal').first().on('input', calculateChange);

        function calculateChange() {
            let totalAmount = parseFloat($('.order-total').text().replace('Rs. ', '')) || 0;
            let receivedAmount = parseFloat($('.amount-cal').first().val()) || 0;
            let changeAmount = Math.max(receivedAmount - totalAmount, 0);
            $('.amount-cal').last().val(changeAmount.toFixed(2));
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
        $('#selected-items').on('input', '.discount-input', function() {
            const index = $(this).data('index');
            let discount = parseFloat(this.value);
            if (isNaN(discount) || discount < 0) discount = 0;
            selectedInventoryItems[index].discount = discount;
            const item = selectedInventoryItems[index];
            const itemTotal = Math.max(
                (item.price * item.quantity) - discount,
                0
            );
            $(this)
                .closest('.card-sm-wrapper')
                .find('.product-total')
                .text(`Rs.${itemTotal.toFixed(2)}`);

            // update grand total
            updateTotalAmount();
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
            let receivedAmount = parseFloat($('.amount-cal').first().val()) || 0;
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
                        $('.amount-cal').val('');
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