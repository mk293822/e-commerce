import React, {FormEventHandler, useState} from 'react';
import {Link, useForm, usePage} from "@inertiajs/react";
import ApplicationLogo from "@/Components/Assets/ApplicationLogo";
import NavLink from "@/Components/Assets/NavLink";
import Dropdown from "@/Components/Assets/Dropdown";
import ResponsiveNavLink from "@/Components/Assets/ResponsiveNavLink";
import MiniCart from "@/Components/App/MiniCart";
import {MagnifyingGlassIcon} from "@heroicons/react/24/outline";

function Navbar() {

    const {keyword} = usePage().props;

    const searchForm = useForm<{keyword: string;}>({
        keyword: keyword || "",
    });

    const user = usePage().props.auth.user;

    const url = usePage().url;
    const onSubmit: FormEventHandler = (e)=>{
        e.preventDefault();

        searchForm.get(url, {
            preserveState: true,
            preserveScroll: true,
        })
    }

    return (
        <nav className="navbar border-b fixed top-0 z-50 shadow-lg border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800 px-6">
            <div className="flex-1">
                <Link href={'/'} className="btn btn-ghost text-2xl">Uni-Cart</Link>
            </div>
            <div className="flex-none gap-4">

                <form onSubmit={onSubmit} className="join flex-1">
                    <div className="flex-1">
                        <input
                            type="text"
                            value={searchForm.data.keyword}
                            onChange={(e) => searchForm.setData("keyword", e.target.value)}
                            onBlur={(e)=>onSubmit(e)}
                            className="input active:border-none active:outline-none join-item w-full"
                            placeholder="Search ....."
                        />
                    </div>
                    <div className="indicator">
                        <button className="btn join-item">
                            <MagnifyingGlassIcon className={"size-4"}/>
                            Search
                        </button>
                    </div>
                </form>

                <MiniCart/>
                {user && (
                    <div className="dropdown dropdown-end">
                        <div tabIndex={0} role="button" className="btn btn-ghost">
                            <div
                                className="capitalize text-lg inline-flex items-center rounded-md border border-transparent px-3 py-2 font-medium leading-4 transition duration-150 ease-in-out focus:outline-none">
                                {user.name}
                                <svg
                                    className="-me-0.5 ms-2 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div
                            tabIndex={0}
                            className="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                )}
                {!user && (
                    <div className="dropdown dropdown-end">
                        <div className="flex gap-4">
                            <Link href={route('login')} className={'btn btn-primary'}>Login</Link>
                            <Link href={route('register')} className={'btn btn-neutral'}>Register</Link>
                        </div>
                    </div>
                )}
            </div>
        </nav>
    );
}

export default Navbar;
