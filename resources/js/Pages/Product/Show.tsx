import React, {ChangeEvent, useEffect, useMemo, useState} from 'react';
import {PageProps, Products, VariationTypeOption} from "@/types";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {Head, Link, router, useForm, usePage} from "@inertiajs/react";
import Carousel from "@/Components/App/Carousel";
import CurrencyFormatter from "@/Components/App/CurrencyFormatter";
import {ArrayAreEqual} from "@/types/Helper";

function Show({product, variation_type_options, appName}: PageProps<{product: Products, variation_type_options: number[]}>) {

    const form = useForm<{
        option_ids: Record<string, number>;
        quantity: number;
    }>({
        option_ids: {},
        quantity: 1,
    });

    const [selectOption, setSelectOption]= useState<Record<number, VariationTypeOption>>({});
    const {url} = usePage();

    useEffect(() => {
        for (let type of product.variation_types) {
            const selectedOptionId: number = variation_type_options[type.id];
            chooseOption(
                type.id,
                type.variation_type_options.find((op) => op.id == selectedOptionId) || type.variation_type_options[0],
                false
            );
        }
    }, []);

    const images = useMemo(()=>{
        for(let type_id in selectOption){
            const option = selectOption[type_id];
            if(option.type.type === 'image'){
                return option.images;
            }
        }
        return product.images;
    }, [product, selectOption]);


    const computedProduct = useMemo(()=>{
        const selected_option_ids = Object.values(selectOption).map(op=>op.id).sort();

        for(let variation of product.product_variations){
            const optionIds = variation.variation_type_option_ids.sort();
            if(ArrayAreEqual(selected_option_ids, optionIds)){
                return {
                    quantity: variation.quantity,
                    price: variation.price
                }
            }
        }
        return {
            quantity: product.quantity,
            price: product.price,
        }
    }, [product, selectOption]);

    const option_ids_map = (newOption: object)=>{
        return Object.fromEntries(
            Object.entries(newOption).map(([a, b])=> [a, b.id])
        )
    }

    const chooseOption = (
        typeId: number,
        options: VariationTypeOption,
        updateUrl: boolean = true
    )=>{
        setSelectOption((preOptions)=> {
            const newOption = {...preOptions, [typeId]: options,};
            if(updateUrl){
                router.get(
                    url,
                    {
                        options: option_ids_map(newOption)
                    },
                    {
                        preserveScroll: true,
                        preserveState: true
                    }
                )
            }
            return newOption;
        })
    }
    const renderProductVariationTypes = () => {
        return product.variation_types.map((type, i) => (
            <div key={type.id}>
                <b>{type.name}</b>
                {type.type === "image" && (
                    <div className="flex gap-2 mb-4">
                        {type.variation_type_options.map((option) => (
                            <div
                                onClick={() => chooseOption(type.id, option)}
                                key={option.id}
                            >
                                {option.images && (
                                    <img
                                        alt=""
                                        src={option.images[0].thumb}
                                        className={
                                            "w-[50px] h-[50px] object-contain " +
                                            (selectOption[type.id]?.id === option.id
                                                ? "outline outline-4 outline-primary"
                                                : "")
                                        }
                                    />
                                )}
                            </div>
                        ))}
                    </div>
                )}
                {type.type === "radio" && (
                    <div className={type.variation_type_options.length > 1 ? 'flex mb-4 join' : 'flex mb-4'}>
                        {type.variation_type_options.map((option) => (
                            <input
                                onChange={() => chooseOption(type.id, option)}
                                type={type.type}
                                key={option.id}
                                className="join-item btn"
                                value={option.id}
                                checked={selectOption[type.id]?.id === option.id}
                                name={"variation_type_" + type.id}
                                aria-label={option.name}
                            />
                        ))}
                    </div>
                )}
                {type.type === "select" && (
                    <div className="flex mb-4">
                        <select name="" className={'select w-full'}>
                            {type.variation_type_options.map((option) => (
                                <option key={option.id} value={option.id}>{option.name}</option>
                            ))}
                        </select>
                    </div>
                )}

            </div>
        ));
    };

    const onQuantityChange = (ev: ChangeEvent<HTMLSelectElement>)=>{
        form.setData('quantity', parseInt(ev.target.value));
    }


    const addToCart = ()=>{
        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
            onError: (err)=>{
            console.log(err);
        }
    })
    }
    const renderAddToCartButton = () => {
        return (
            <div className="mb-8 flex gap-4">
                <select
                    value={form.data.quantity}
                    onChange={onQuantityChange}
                    className="select select-bordered w-full"
                >
                    {Array.from({
                        length: Math.min(10, computedProduct.quantity),
                    }).map((el, i) => (
                        <option value={i + 1} key={i + 1}>
                            Quantity: {i + 1}
                        </option>
                    ))}
                </select>
                <button  className="btn btn-primary" onClick={addToCart}>
                    Add To Cart
                </button>
            </div>
        );
    };


    useEffect(() => {
        const idMap = Object.fromEntries(
            Object.entries(selectOption).map(
                ([typeId, options]: [string, VariationTypeOption])=> [typeId, options.id]
            )
        );
        form.setData('option_ids', idMap);
    }, [selectOption]);




    return (
        <AuthenticatedLayout>
            <Head>
                <title>{product.name}</title>
                <meta name="title" content={product.meta_title || product.name} />
                <meta name="description" content={product.meta_description} />
                <link rel="canonical" href={route("product.show", product.slug)} />

                <meta property="og:title" content={product.name} />
                <meta property="og:image" content={images[0]?.small} />
                <meta property="og:description" content={product.meta_description} />
                <meta property="og:url" content={route("product.show", product.slug)} />
                <meta property="og:type" content="product" />
                <meta property="og:site_name" content={appName} />
            </Head>

            <div className="container mx-auto p-8">
                <div className="grid gap-8 grid-cols-1 lg:grid-cols-12">
                    <div className="col-span-6">
                        <Carousel images={images} />
                    </div>
                    <div className="col-span-6">
                        <h1 className="text-2xl">{product.name}</h1>

                        <p className={"mb-8"}>
                            by{" "}
                            <Link
                                href={'/'}
                                className="hover:underline"
                            >
                                {product.created_by.name}
                            </Link>
                            &nbsp; in{" "}
                            <Link
                                href={'/'}
                                className="hover:underline"
                            >
                                {product.department.name}
                            </Link>
                        </p>
                        <div>
                            <div className="text-3xl font-semibold mb-4">
                                <CurrencyFormatter amount={computedProduct.price} />
                            </div>
                        </div>
                        {renderProductVariationTypes()}
                        {computedProduct.quantity != undefined &&
                            computedProduct.quantity < 10 && (
                                <div className="text-error my-4">
                                    <span>Only {computedProduct.quantity} left</span>
                                </div>
                            )}
                        {renderAddToCartButton()}
                        <b className="text-xl">About this Item</b>
                        <div
                            className="wysiwyg-output"
                            dangerouslySetInnerHTML={{ __html: product.description }}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Show;
