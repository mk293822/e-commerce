import {CartItems} from "@/types/index";

export const ArrayAreEqual = (a:any[], b:any[])=>{
    if(a.length !== b.length) return false;

    return a.every((value, index) => value === b[index] );
}
export const productRoute = (item: CartItems)=>{
    const params = new URLSearchParams();
    Object.entries(item.option_ids).forEach(([typeID, optionId])=> {
        params.append(`options[${typeID}]`, optionId + '')
    })

    return route('product.show', item.slug) + '?' + params.toString();
}
