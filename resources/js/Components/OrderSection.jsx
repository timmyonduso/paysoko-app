export default function OrderSection({ orders }) {
    return (
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
    );
}
