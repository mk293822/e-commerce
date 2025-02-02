import React from 'react';
import {Order, PageProps} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head, Link} from "@inertiajs/react";
import {CheckCircleIcon} from "@heroicons/react/24/outline";
import CurrencyFormatter from "@/Components/App/CurrencyFormatter";

function Success({orders}: PageProps<{orders: Order[]}>) {
    return (
        <AuthenticatedLayout>
            <Head title='Payment was Completed'/>
            <div className="w-[480px] mx-auto py-8 px-4">
                <div className="flex flex-col gap-2 items-center">
                    <div className="text-6xl text-emerald-600">
                        <CheckCircleIcon className={'size-24'}/>
                    </div>
                    <div className="text-3xl">
                        Payment was Completed
                    </div>
                </div>
                <div className="my-6 text-lg text-center">
                    Thanks for your purchase. <br/> Your payment was completed successfully.
                </div>
                {orders.map(order=>(
                    <div key={order.id} className='bg-white dark:bg-gray-800 rounded-lg p-6 mb-4 '>
                        <h3 className="text text-3xl mb-6">Order Summary</h3>
                        <div className="flex justify-between mb-3 font-bold">
                            <div className="text-gray-400">Seller</div>
                            <div className="">
                                <Link href={'#'} className='hover:underline'>
                                    {order.vendorUser.store_name}
                                </Link>
                            </div>
                        </div>
                        <div className="flex justify-between mb-3">
                            <div className="text-gray-400">
                                Order Number
                            </div>
                            <div className="">
                                <Link href={'#'} className='hover:underline'>
                                    #{order.id}
                                </Link>
                            </div>
                        </div>
                        <div className="flex justify-between mb-3">
                            <div className="text-gray-400">
                                Items
                            </div>
                            <div className="">
                                <Link href={'#'} className='hover:underline'>
                                    {order.orderItems.length}
                                </Link>
                            </div>
                        </div>
                        <div className="flex justify-between mb-6">
                            <div className="text-gray-400">
                                Total
                            </div>
                            <div className="">
                                <CurrencyFormatter amount={order.total_price}/>
                            </div>
                        </div>
                        <div className="flex justify-between mb-1">
                            <Link href={'#'} className='btn btn-primary'>
                                View Order Details
                            </Link>
                            <Link href={route('dashboard')} className='btn'>
                                Back To Home
                            </Link>
                        </div>
                    </div>
                ))}
            </div>

        </AuthenticatedLayout>
    );
}

export default Success;
