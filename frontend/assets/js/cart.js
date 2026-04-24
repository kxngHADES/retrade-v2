const CartAPI = {
    add: async (shopId, productId, price, amount) => {
        try {
            const response = await fetch('/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', shop_id: shopId, product_id: productId, price: parseFloat(price), amount: parseInt(amount) })
            });
            const data = await response.json();
            if (data.success) {
                alert('Added to cart successfully!');
            } else {
                alert('Failed to add to cart: ' + data.error);
            }
        } catch (error) {
            console.error('Cart add error:', error);
            alert('An error occurred.');
        }
    },
    update: async (itemId, quantity) => {
        try {
            const response = await fetch('/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', item_id: itemId, quantity: parseInt(quantity) })
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update: ' + data.error);
            }
        } catch (error) {
            console.error('Cart update error:', error);
            alert('An error occurred.');
        }
    },
    remove: async (itemId) => {
        if (!confirm('Are you sure you want to remove this item?')) return;
        try {
            const response = await fetch('/api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'remove', item_id: itemId })
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to remove: ' + data.error);
            }
        } catch (error) {
            console.error('Cart remove error:', error);
            alert('An error occurred.');
        }
    }
};
