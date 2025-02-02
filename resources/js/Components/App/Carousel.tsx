import React, { useEffect, useState } from "react";
import { Image } from "@/types";

function Carousel({ images }: { images: Image[] }) {
    const [selectedImage, setSelectedImage] = useState<Image>(images[0]);
    useEffect(() => {
        setSelectedImage(images[0]);
    }, [images]);
    // console.log(images);
    return (
        <div className="flex gap-6">
            <div className="flex items-start gap-8">
                <div className="flex flex-col items-center gap-2 py-2">
                    {images.map((image, i) => (

                        <button
                            onClick={(en) => setSelectedImage(image)}
                            className={
                                "border-2 " +
                                (selectedImage.id === image.id
                                    ? "border-blue-500"
                                    : "hover:border-blue-500")
                            }
                            key={image.id}
                        >
                            {/*<pre>{JSON.stringify(image.thumb)}</pre>*/}
                            <img
                                src={image.thumb ? image.thumb : "/"}
                                alt=""
                                className="w-[50px]"
                            />
                        </button>
                    ))}
                </div>
            </div>
            <div className="carousel w-full">
                <div className="carousel-item w-full">
                    {/*<pre>{JSON.stringify(selectedImage.large)}</pre>*/}
                    <img
                        alt=""
                        src={selectedImage.large}
                        className="w-full max-h-[40rem]"
                    />
                </div>
            </div>
        </div>
    );
}

export default Carousel;
