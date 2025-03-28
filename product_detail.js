document.addEventListener('DOMContentLoaded', () => {
    const colorSelect = document.getElementById('color-select');
    const mainProductImage = document.getElementById('main-product-image');
    const addToCartForm = document.getElementById('add-to-cart-form');

    // Update image when color is changed
    colorSelect.addEventListener('change', (e) => {
        const selectedOption = e.target.selectedOptions[0];
        const imageUrl = selectedOption.dataset.image;
        
        if (imageUrl) {
            mainProductImage.src = imageUrl;
        }
    });

    // Handle add to cart submission
    addToCartForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(addToCartForm);
        
        try {
            const response = await fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message || 'Product added to cart!');
                // Optionally update cart icon/count
            } else {
                alert(result.message || 'Failed to add product to cart');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while adding to cart');
        }
    });
});