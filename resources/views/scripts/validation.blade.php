<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            const inputs = form.querySelectorAll('.form-control');

            function validateInput(input) {
                const value = input.value.trim();
                const validations = input.dataset.validation ? input.dataset.validation.split(' ') : [];
                let isValid = true;
                let errorMessage = '';

                for (let validation of validations) {
                    switch (validation) {
                        case 'required':
                            if (value === '') {
                                isValid = false;
                                errorMessage = input.dataset.errorRequired || 'This field is required';
                            }
                            break;
                        case 'email':
                            if (value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                                isValid = false;
                                errorMessage = input.dataset.errorEmail || 'Please enter a valid email address';
                            }
                            break;
                        case 'phone':
                            if (value !== '' && !/^\d{10}$/.test(value)) {
                                isValid = false;
                                errorMessage = input.dataset.errorPhone || 'Please enter a valid 10-digit phone number';
                            }
                            break;
                        case 'image':
                            if (input.type === 'file') {
                                const file = input.files[0];
                                if (file) {
                                    if (!file.type.startsWith('image/')) {
                                        isValid = false;
                                        errorMessage = input.dataset.errorImage || 'Please select a valid image file';
                                    }
                                    // else if (file.size > 5 * 1024 * 1024) { // 5MB limit
                                    //     isValid = false;
                                    //     errorMessage = input.dataset.errorImageSize || 'Image size should be less than 5MB';
                                    // }
                                } else if (input.hasAttribute('required')) {
                                    isValid = false;
                                    errorMessage = input.dataset.errorRequired || 'Please select an image';
                                }
                            }
                            break;

                    }

                    if (!isValid) break;
                }

                showError(input, errorMessage);
                return isValid;
            }

            function showError(input, message) {
                const errorElement = input.nextElementSibling;
                if (errorElement && errorElement.classList.contains('validation-error')) {
                    errorElement.textContent = message;
                } else if (message) {
                    const errorSpan = document.createElement('span');
                    errorSpan.classList.add('validation-error');
                    errorSpan.textContent = message;
                    input.parentNode.insertBefore(errorSpan, input.nextSibling);
                }
            }

            function validateForm() {
                let isFormValid = true;
                inputs.forEach(input => {
                    if (!validateInput(input)) {
                        isFormValid = false;
                    }
                });
                return isFormValid;
            }

            inputs.forEach(input => {
                ['blur', 'input'].forEach(eventType => {
                    input.addEventListener(eventType, () => validateInput(input));
                });
            });

            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        });
    });
</script>