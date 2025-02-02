import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps, PaginationProps, Products } from "@/types";
import ProductItem from "@/Components/App/ProductItem";

export default function Dashboard({ products, success, miniCartItems }: PageProps<{ products: PaginationProps<Products> }>) {

    return (
        <AuthenticatedLayout>
            <div className="py-6 px-6 space-y-8">

                {/* Hero Section */}
                <div className="hero bg-base-100 min-h-[18rem] rounded-lg shadow-lg">
                    <div className="hero-content text-center">
                        <div className="max-w-lg">
                            <h1 className="text-5xl font-extrabold text-primary">Welcome to Your Dashboard</h1>
                            <p className="py-6 text-lg text-gray-600">
                                Discover new opportunities, manage your products, and explore our platform with ease.
                            </p>
                            {success && <p className=" text-lg text-gray-600">
                                {success.message}
                                </p>}
                            <button className="btn btn-primary transition-all duration-200">
                                Get Started
                            </button>
                        </div>
                    </div>
                </div>

                {/* Product Grid Section */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    {products.data.map(product => (
                        <ProductItem product={product} key={product.id} />
                    ))}
                </div>

            </div>
        </AuthenticatedLayout>
    );
}
