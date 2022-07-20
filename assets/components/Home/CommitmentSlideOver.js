import React, { useEffect, useState } from 'react';
import SlideOver from '../SlideOver';
import Commitment from "./Commitment";

const CommitmentSlideOver = () => {
    const [open, setOpen] = useState(false);
    const [currentValue, setCurrentValue] = useState(null);

    const onClose = (status) => {
        setOpen(!status);
        setCurrentValue(null);
    };

    const prepareContent = (element) => {
        if (!element) {
            return null;
        }
        const attributes = {...element.dataset};

        return <Commitment
            title={attributes.commitmentTitle}
            slug={attributes.commitmentSlug}
            description={attributes.commitmentDescription}
            nextPreviousCallback={(needToMoveToPreviousElement) => {
                if (needToMoveToPreviousElement) {
                    if (currentValue.previousElementSibling) {
                        setCurrentValue(currentValue.previousElementSibling)
                    } else {
                        setCurrentValue(currentValue.parentNode.lastElementChild)
                    }
                } else {
                    if (currentValue.nextElementSibling) {
                        setCurrentValue(currentValue.nextElementSibling)
                    } else {
                        setCurrentValue(currentValue.parentNode.firstElementChild)
                    }
                }
            }}
        />;
    };

    useEffect(() => {
        findAll(document, '.slide-over--trigger').forEach((element) => on(element, 'click', () => {
            setOpen(true);
            setCurrentValue(element);
        }));
    }, []);

    return <SlideOver open={open} onClose={onClose} contentCallback={() => prepareContent(currentValue)}/>;
};

export default CommitmentSlideOver;
