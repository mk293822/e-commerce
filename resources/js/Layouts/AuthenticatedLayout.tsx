import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useEffect, useRef, useState } from 'react';
import Navbar from '@/Components/App/Navbar';

interface Message {
    id: number;
    message: string;
    type: 'success' | 'error';
}

export default function AuthenticatedLayout({children, header}: PropsWithChildren<{ header?: ReactNode }>) {
    const [messages, setMessages] = useState<Message[]>([]);
    const timeOutRefs = useRef<{ [key: number]: ReturnType<typeof setTimeout> }>({});
    const { props } = usePage();

    useEffect(() => {
        // Handle success messages
        if (props.success?.message) {
            const newMessage: Message = {
                ...props.success,
                id: props.success.time,
                type: 'success', // Set message type
            };

            // Avoid duplicate messages
            if (!messages.some((msg) => msg.id === newMessage.id)) {
                setMessages((prevMessages) => [newMessage, ...prevMessages]);

                const timeOutId = setTimeout(() => {
                    setMessages((prevMessages) =>
                        prevMessages.filter((msg) => msg.id !== newMessage.id)
                    );
                    delete timeOutRefs.current[newMessage.id];
                }, 4000);

                timeOutRefs.current[newMessage.id] = timeOutId;
            }
        }

        // Handle error messages
        if (props.error?.message) {
            const newErrorMessage: Message = {
                ...props.error,
                id: props.error.time,
                type: 'error', // Set message type
            };

            // Avoid duplicate messages
            if (!messages.some((msg) => msg.id === newErrorMessage.id)) {
                setMessages((prevMessages) => [newErrorMessage, ...prevMessages]);

                const timeOutId = setTimeout(() => {
                    setMessages((prevMessages) =>
                        prevMessages.filter((msg) => msg.id !== newErrorMessage.id)
                    );
                    delete timeOutRefs.current[newErrorMessage.id];
                }, 4000);

                timeOutRefs.current[newErrorMessage.id] = timeOutId;
            }
        }

        // Cleanup function to clear timeouts on unmount
        return () => {
            Object.values(timeOutRefs.current).forEach(clearTimeout);
        };
    }, [props.success, props.error]);

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Navbar />

            {/* Messages (Success and Error) */}
            {messages.length > 0 && (
                <div
                    className="toast toast-top toast-end z-[1000] mt-14 max-h-[13rem] overflow-y-auto"
                    aria-live="polite"
                >
                    {messages.map((msg) => (
                        <div
                            key={msg.id}
                            className={`alert text-center shadow-md ${
                                msg.type === 'success'
                                    ? 'alert-success shadow-emerald-700'
                                    : 'alert-error shadow-red-700'
                            }`}
                        >
                            <span>{msg.message}</span>
                        </div>
                    ))}
                </div>
            )}

            {/* Main Content */}
            <main className="mt-16">{children}</main>
        </div>
    );
}
