import React from 'react';
import ReactMarkdown from 'react-markdown';

const Commitment = ({
    title, slug, description, imageUrl, nextPreviousCallback,
}) => {
    const currentHref = window.location.href.split('#')[0];

    return (
        <div className="relative h-full">
            {imageUrl ? <div><img src={imageUrl} alt="valeur image" /></div> : null}
            <div className="p-8">
                <h3 className="text-blue-600 font-bold">{title}</h3>

                <div className="mt-4">
                    <ReactMarkdown>{description}</ReactMarkdown>
                </div>
            </div>

            <div className="fixed w-full bottom-0 bg-yellow-400 flex flex-row items-stretch">
                <button type="button" className="p-8 basis-1/4 hover:bg-yellow-500"
                    onClick={() => nextPreviousCallback(true)}>
                    <i className="fa fa-caret-left"></i>
                </button>

                <ul className="p-8 flex justify-evenly basis-1/2">
                    <li className='cursor-pointer' onClick={() => {
                        share('twitter', `${currentHref}%23valeur-${slug}`, title);
                    }}>
                        <i className="fa fa-twitter"></i>
                    </li>
                    <li className='cursor-pointer' onClick={() => {
                        share('facebook', `${currentHref}%23valeur-${slug}`, title);
                    }}>
                        <i className="fa fa-facebook"></i>
                    </li>
                </ul>

                <button type="button" className="p-8 basis-1/4 hover:bg-yellow-500"
                    onClick={() => nextPreviousCallback(false)}>
                    <i className="fa fa-caret-right"></i>
                </button>
            </div>
        </div>
    );
};

export default Commitment;
