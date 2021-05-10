import React from 'react';
import { render } from 'react-dom';
import Cropper from 'cropperjs';

import Modal from '../../components/Modal';

let modal;
let cropper;
let fileElement;
let croppedImageElement;

function updatePreviewImages(src, circle = false) {
    const previewContainer = dom('.image-uploader--preview');

    const containerElement = find(previewContainer, '.preview-image--container');
    containerElement.style.backgroundImage = `url(${src})`;

    if (false === circle) {
        addClass(containerElement, 'preview-image--container-rectangle');
    }

    show(previewContainer);
    hide(dom('.image-uploader--label'));
}

async function handleCropAction(options) {
    const canvas = cropper.getCroppedCanvas({
        width: options.width,
        height: options.height,
    });

    const dataUrl = await canvas.toDataURL();

    updatePreviewImages(dataUrl, options.width === options.height);

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

function getModalContent(url, options) {
    return (
        <div>
            <div className={'image-cropper--container'}>
                <img src={url} alt={'img'} />
            </div>

            <div className="b__nudge--top-15 text--center">
                <a className={'btn'} onClick={handleCancelAction}>Annuler</a>
                <a className="btn btn--blue b__nudge--left-small" onClick={ () => handleCropAction(options) }>Valider</a>
            </div>
        </div>
    );
}

function displayCropperModal(url, options) {
    modal = render(
        <Modal
            key={url}
            contentCallback={() => getModalContent(url, options)}
            withClose={false}
        />,
        dom('#modal-wrapper')
    );

    cropper = new Cropper(dom('.image-cropper--container img'), {
        aspectRatio: options.ratio,
        viewMode: 2,
    });
}


export default (inputFileElement, inputCroppedImageElement, options) => {
    fileElement = inputFileElement;
    croppedImageElement = inputCroppedImageElement;

    const files = inputFileElement.files;

    if (!files || 1 > files.length) {
        return;
    }

    const file = files[0];

    if (URL) {
        displayCropperModal(URL.createObjectURL(file), options);
    } else if (FileReader) {
        const reader = new FileReader();
        reader.onload = () => displayCropperModal(reader.result, options);
        reader.readAsDataURL(file);
    }
};
