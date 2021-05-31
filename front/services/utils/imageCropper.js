import React from 'react';
import { render } from 'react-dom';
import Cropper from 'cropperjs';

import Modal from '../../components/Modal';

let modal;
let cropper;
let fileElement;
let croppedImageElement;

function updatePreviewImages(src, inputContainerElement, circle = false) {
    const previewContainer = find(inputContainerElement, '.image-uploader--preview');

    const containerElement = find(previewContainer, '.preview-image--container');
    containerElement.style.backgroundImage = `url(${src})`;

    if (false === circle) {
        addClass(containerElement, 'preview-image--container-rectangle');
    }

    show(previewContainer);
    hide(find(inputContainerElement, '.image-uploader--label'));
}

async function handleCropAction(inputContainerElement, options) {
    const canvas = cropper.getCroppedCanvas({
        width: options.width,
        height: options.height,
    });

    const dataUrl = await canvas.toDataURL();

    updatePreviewImages(dataUrl, inputContainerElement, options.width === options.height);

    croppedImageElement.value = dataUrl;
    fileElement.value = '';

    modal.hideModal();
}

function handleCancelAction() {
    cropper.destroy();
    cropper = null;

    fileElement.value = '';

    modal.hideModal();
}

function getModalContent(url, inputContainerElement, options) {
    return (
        <div>
            <div className={'image-cropper--container'}>
                <img src={url} alt={'img'} />
            </div>

            <div className="b__nudge--top-15 text--center">
                <a className={'btn'} onClick={handleCancelAction}>Annuler</a>
                <a className="btn btn--blue b__nudge--left-small" onClick={ () => handleCropAction(inputContainerElement, options) }>Valider</a>
            </div>
        </div>
    );
}

function displayCropperModal(url, inputContainerElement, options) {
    modal = render(
        <Modal
            key={url}
            contentCallback={() => getModalContent(url, inputContainerElement, options)}
            withClose={false}
        />,
        dom('#modal-wrapper')
    );

    cropper = new Cropper(dom('.image-cropper--container img'), {
        aspectRatio: options.ratio,
        viewMode: 2,
    });
}


export default (inputContainerElement, options) => {
    fileElement = find(inputContainerElement, '.em-form__file--area');
    croppedImageElement = find(inputContainerElement, '.em-form__cropped--area');

    const files = find(inputContainerElement, '.em-form__file--area').files;

    if (!files || 1 > files.length) {
        return;
    }

    const file = files[0];

    if (URL) {
        displayCropperModal(URL.createObjectURL(file), inputContainerElement, options);
    } else if (FileReader) {
        const reader = new FileReader();
        reader.onload = () => displayCropperModal(reader.result, inputContainerElement, options);
        reader.readAsDataURL(file);
    }
};
