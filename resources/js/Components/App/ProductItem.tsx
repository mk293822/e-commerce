import React from 'react';
import {Products} from "@/types";
import {Link, useForm} from "@inertiajs/react";
import CurrencyFormatter from "@/Components/App/CurrencyFormatter";

function ProductItem({product}: {product: Products}) {

    const discounted_price = product.price * ((100 - product.discount) / 100);

    const form = useForm<{
        quantity: number,
        option_ids: Record<string, number>
    }>({
        quantity: 1,
        option_ids: {}
    })
    const addToCart = ()=>{
        form.post(route('cart.store', product.id),{
            preserveScroll: true,
            preserveState: true,
            onError: (err)=>{
                console.log(err);
            }
        })
    }

    return (
        <section className="card mx-auto bg-base-100 shadow-2xl hover:shadow-4xl max-w-[25rem]">
            <Link href={route('product.show', product)}>
                <figure className="relative">
                    <img
                        src={product.image}
                        alt={product.name}
                        className=" pt-4 overflow-scrollx object-contain rounded-t-lg aspect-square"
                    />
                    {product.discount > 0 && (
                        <div
                            className="absolute top-0 right-0 bg-red-500 text-white px-3 py-2 opacity-80 hover:opacity-100 text-xs rounded-bl-lg">
                            {product.discount}% OFF
                        </div>
                    )}
                </figure>
            </Link>
            <div className="card-body">
                <h2 className="card-title text-lg font-extrabold">{product.name}</h2>
                <p className="text-lg text-primary flex gap-2">
                    <span className={'line-through italic'}><CurrencyFormatter amount={product.price}/></span>
                    <CurrencyFormatter amount={discounted_price}/>
                </p>
                {product.discount > 0 && (
                    <p className="text-sm text-red-500">Discount Applied</p>
                )}
                <p className="text-sm text-gray-500 mt-2">
                    Created by
                    <Link href={'/'}><b className="link link-hover text-blue-600"> {product.created_by.name}</b></Link>
                    <br />
                    in department
                    <Link href={'/'}><b className="link link-hover text-blue-600"> {product.department.name}</b></Link>
                </p>
                <div className="card-actions justify-end mt-4">
                    <button onClick={addToCart} className="btn btn-primary hover:bg-primary-focus">Add to Cart</button>
                </div>
            </div>
        </section>
    );
}

export default ProductItem;
