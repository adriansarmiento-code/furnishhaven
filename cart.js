document.addEventListener('DOMContentLoaded', () => {
    // Remove the readonly restriction from quantity inputs
    const quantityInputs = document.querySelectorAll('.qty-input');
    quantityInputs.forEach(input => {
        input.removeAttribute('readonly');
        input.style.backgroundColor = '';
        input.style.cursor = '';
    });

    // Quantity increment/decrement functionality
    document.querySelectorAll('.quantity-control').forEach(control => {
        const input = control.querySelector('.qty-input');
        const decrement = control.querySelector('.qty-decrement');
        const increment = control.querySelector('.qty-increment');
        const cartId = input.dataset.cartId;
        
        decrement.addEventListener('click', () => {
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateCartQuantity(cartId, input.value);
            }
        });
        
        increment.addEventListener('click', () => {
            input.value = parseInt(input.value) + 1;
            updateCartQuantity(cartId, input.value);
        });
        
        input.addEventListener('change', () => {
            if (parseInt(input.value) < 1) input.value = 1;
            updateCartQuantity(cartId, input.value);
        });
    });
    
    // Remove item functionality
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            const cartId = cartItem.dataset.cartId;
            
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItem(cartId, cartItem);
            }
        });
    });
    
    // Prevent checkout with empty cart
    document.querySelector('a.checkout-btn')?.addEventListener('click', (e) => {
        if (document.querySelectorAll('.cart-item').length === 0) {
            e.preventDefault();
            alert('Your cart is empty!');
        }
    });
    
    // Function to update cart quantity via AJAX
    async function updateCartQuantity(cartId, quantity) {
        try {
            const response = await fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `cart_id=${cartId}&quantity=${quantity}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Reload page to update all totals
                location.reload();
            } else {
                // Show error message
                alert(result.message || 'Failed to update quantity');
                
                // If insufficient stock, set to max available
                if (result.currentQuantity) {
                    const input = document.querySelector(`.qty-input[data-cart-id="${cartId}"]`);
                    if (input) input.value = result.currentQuantity;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update cart');
        }
    }
    
    // Function to remove cart item via AJAX
    async function removeCartItem(cartId, cartElement) {
        try {
            const response = await fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `cart_id=${cartId}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Reload page to update all totals
                location.reload();
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to remove item from cart');
        }
    }
});