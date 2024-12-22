import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';

export default function Dashboard({ cart = [], orders = [], products = [] }) {
    const [isCartOpen, setIsCartOpen] = useState(false);
    const [cartItems, setCartItems] = useState(cart);

    const addToCart = async (productId) => {
        try {
            const response = await axios.post('/cart/add', {
                product_id: productId,
                quantity: 1,
            });
            setCartItems(response.data.cart);
            alert('Product added to cart!');
        } catch (error) {
            console.error('Failed to add product to cart:', error);
            alert('Could not add product to cart.');
        }
    };

    // Remove from Cart Function
    const removeFromCart = async (productId) => {
        try {
            const response = await axios.post('/cart/remove', {
                product_id: productId,
            });
            setCartItems(response.data.cart);
            alert('Product removed from cart!');
        } catch (error) {
            console.error('Failed to remove product from cart:', error);
            alert('Could not remove product from cart.');
        }
    };

    const placeOrder = async () => {
        try {
            const response = await axios.post('/orders/place');
            alert('Order placed successfully! Order ID: ' + response.data.order_id);
            setCartItems([]); // Clear cart items in the UI
            setIsCartOpen(false); // Close the cart dialog
        } catch (error) {
            console.error('Failed to place order:', error);
            alert('Could not place order. Please try again.');
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Product Listing */}
                    <div className="mb-8">
                        <h3 className="text-lg font-semibold">Available Products</h3>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {products.map((product) => (
                                <div
                                    key={product.id}
                                    className="p-4 border rounded shadow hover:shadow-lg"
                                >
                                    <h4 className="text-lg font-bold">{product.name}</h4>
                                    <p className="text-gray-600">{product.description}</p>
                                    <p className="text-gray-800">${product.price}</p>
                                    <button
                                        onClick={() => addToCart(product.id)}
                                        className="mt-2 px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                                    >
                                        Add to Cart
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Cart Button */}
                    <button
                        onClick={() => setIsCartOpen(true)}
                        className="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700"
                    >
                        View Cart ({cartItems.length})
                    </button>

                    {/* Orders Section */}
                    <div className="mt-8">
                        <h3 className="mb-4 text-lg font-semibold">Your Orders</h3>
                        <div className="space-y-4">
                            {orders.map((order) => (
                                <div key={order.id} className="p-4 bg-gray-100 rounded shadow">
                                    <h4 className="font-bold">Order #{order.id}</h4>
                                    <ul className="mt-2 space-y-2">
                                        {order.order_items.map((item) => (
                                            <li key={item.id} className="flex justify-between">
                                                <span>{item.product.name}</span>
                                                <span>
                                                    {item.quantity} x ${item.product.price}
                                                </span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Cart Modal */}
            <Dialog open={isCartOpen} onClose={() => setIsCartOpen(false)} className="relative z-50">
                <div className="fixed inset-0 bg-black/30" aria-hidden="true" />
                <div className="fixed inset-0 flex items-center justify-center">
                    <DialogPanel className="w-full max-w-md p-6 mx-auto bg-white rounded">
                        <DialogTitle className="text-lg font-bold">Your Cart</DialogTitle>
                        <ul className="mt-4 space-y-4">
                            {cartItems.map((item) => (
                                <li key={item.product_id} className="flex justify-between items-center">
                                    <div>
                                        <span>{item.name}</span>
                                        <span className="ml-2">
                                            ({item.quantity} x ${item.price})
                                        </span>
                                    </div>
                                    <button
                                        onClick={() => removeFromCart(item.product_id)}
                                        className="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-700"
                                    >
                                        Remove
                                    </button>
                                </li>
                            ))}
                        </ul>
                        <button
                            onClick={placeOrder}
                            className="mt-6 w-full px-4 py-2 text-white bg-green-500 rounded hover:bg-green-700"
                        >
                            Place Order
                        </button>

                    </DialogPanel>
                </div>
            </Dialog>
        </AuthenticatedLayout>
    );
}
