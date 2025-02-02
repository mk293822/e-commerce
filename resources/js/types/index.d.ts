import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type Vendor = {
    id: number;
    name: string;
    store_name: string;
    store_address: string;
}

export type ProductVariation = {
    id: number;
    product_id: number;
    price: number;
    quantity: number;
    variation_type_option_ids: number[]
}

export type VariationType = {
    id: number;
    name: string;
    type: string;
    product_id: number;
    variation_type_options: VariationTypeOption[]
}

export type VariationTypeOption = {
    id: number;
    name: string;
    type: VariationType
    images: Image[];
}

export type Image = {
    id: number;
    thumb: string;
    small: string;
    large: string;
}

export type Products = {
    id: number;
    name: string;
    slug: string;
    price: number;
    quantity: number;
    discount: number;
    description: string;
    image: string;
    images: Image[];
    created_by: Vendor,
    department: {
        id:number;
        name: string;
        slug: string;
    },
    variation_types: VariationType[],
    product_variations: ProductVariation[],
    meta_title: string;
    meta_description: string;
}

export type PaginationProps<T> = {
    data: Array<T>;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    appName: string;
    miniCartItems: CartItems[];
    totalQuantity: number;
    totalPrice: number;
    csrf_token: string;
    keyword: string;
    error: {
        message: string;
        time: number
    };
    success: {
        message: string;
        time: number;
    }
};

export type CartItems = {
    id: number;
    name: string;
    slug: string;
    image_url: string;
    discount: string;
    quantity: number;
    price: number;
    options: Array<{
        id: number;
        name: string;
        variation_type: {
            id: number;
            name: string;
            type: string;
        }
    }>;
    option_ids: Record<string, number>;
    user: {
        id: number;
        name: string;
        store_name: string;
    }
};

export type GroupCartItems = {
    user: User;
    items: CartItems[];
    total_quantity: number;
    total_price: number;
}


export type OrderItem = {
    id: number;
    quantity: number;
    price: number;
    variation_type_option_ids: number[];
    product: {
        id: number;
        title: string;
        slug: string;
        description: string;
        image: string;
    };
};

export type Order = {
    id: number;
    total_price: number;
    status: string;
    created_at: string;
    vendorUser: {
        id: string;
        name: string;
        email: string;
        store_name: string;
        store_address: string;
    };
    orderItems: OrderItem[];
};
