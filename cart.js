document.addEventListener('DOMContentLoaded', () => {
    const cartItems = document.querySelector('.cart-items');
    
    // Disable quantity input and prevent manual editing
    const quantityInputs = document.querySelectorAll('.qty-input');
    quantityInputs.forEach(input => {
        input.setAttribute('readonly', true);
        input.style.backgroundColor = '#f4f4f4';
        input.style.cursor = 'not-allowed';
    });

    // Update quantity
    cartItems.addEventListener('click', async (e) => {
        const decreaseBtn = e.target.closest('.decrease');
        const increaseBtn = e.target.closest('.increase');
        const removeBtn = e.target.closest('.remove-item');
        
        if (decreaseBtn || increaseBtn) {
            const quantityControl = e.target.closest('.quantity-control');
            const input = quantityControl.querySelector('.qty-input');
            const cartId = input.dataset.cartId;
            let currentQuantity = parseInt(input.value);
            let newQuantity = currentQuantity;
            
            if (decreaseBtn) {
                // Only decrease if current quantity is greater than 1
                newQuantity = Math.max(1, currentQuantity - 1);
            } else if (increaseBtn) {
                newQuantity = currentQuantity + 1;
            }
            
            // Only make API call if quantity actually changed
            if (newQuantity !== currentQuantity) {
                input.value = newQuantity;
                
                try {
                    const response = await fetch('update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `cart_id=${cartId}&quantity=${newQuantity}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Reload page to update all totals
                        location.reload();
                    } else {
                        // Revert quantity if update failed
                        input.value = currentQuantity;
                        
                        // Show error message
                        alert(result.message || 'Failed to update quantity');
                        
                        // If insufficient stock, set to max available
                        if (result.currentQuantity) {
                            input.value = result.currentQuantity;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    input.value = currentQuantity;
                    alert('Failed to update cart');
                }
            }
        }
        
        // Remove item from cart
        if (removeBtn) {
            const cartItem = removeBtn.closest('.cart-item');
            const cartId = cartItem.dataset.cartId;
            
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
});