document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkout-form');
    const newAddressCheckbox = document.getElementById('new-address-checkbox');
    const newAddressForm = document.getElementById('new-address-form');

    // Toggle new address form visibility
    newAddressCheckbox.addEventListener('change', () => {
        newAddressForm.style.display = newAddressCheckbox.checked ? 'block' : 'none';
        
        // Add/remove required attributes based on checkbox
        const inputFields = newAddressForm.querySelectorAll('input');
        inputFields.forEach(input => {
            input.required = newAddressCheckbox.checked;
        });
    });

    // Form validation function
    function validateForm() {
        // Check if an existing address is selected or new address is being added
        const existingAddressSelected = document.querySelector('input[name="shipping_address_id"]:checked');
        const isNewAddressChecked = newAddressCheckbox.checked;

        if (!existingAddressSelected && !isNewAddressChecked) {
            alert('Please select a shipping address or add a new address.');
            return false;
        }

        // Check if a payment method is selected
        const paymentMethodSelected = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethodSelected) {
            alert('Please select a payment method.');
            return false;
        }

        // If new address is checked, validate all new address fields
        if (isNewAddressChecked) {
            const requiredFields = newAddressForm.querySelectorAll('input[required]');
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    alert('Please fill in all new address fields.');
                    field.focus();
                    return false;
                }
            }
        }

        return true;
    }

    // Form submission handler
    checkoutForm.addEventListener('submit', (e) => {
        e.preventDefault();

        // Validate the form
        if (!validateForm()) {
            return;
        }

        // If validation passes, submit the form
        checkoutForm.submit();
    });
});