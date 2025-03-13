import React from 'react';
import { createRoot } from 'react-dom/client';
import Cropper from 'cropperjs';
import Modal from '../../components/Modal';

const modalRef = React.createRef();
let cropper;
let fileElement;
let croppedImageElement;
let container;

function updatePreviewImages(src, circle = false) {
    const previewContainer = findOne(container, '.image-uploader--preview');

    const containerElement = findOne(previewContainer, '.preview-image--container');
    containerElement.style.backgroundImage = `url(${src})`;

    if (false === circle) {
        addClass(containerElement, 'preview-image--container-rectangle');
    }

    show(previewContainer);
    hide(findOne(container, '.image-uploader--label'));
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

    modalRef.current.hideModal();
}

function handleCancelAction() {
    cropper.destroy();
    cropper = null;

    fileElement.value = '';

    modalRef.current.hideModal();
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
    createRoot(dom('#modal-wrapper')).render(
        <Modal
            ref={modalRef}
            key={url}
            contentCallback={() => getModalContent(url, options)}
            withClose={false}
        />
    );

    cropper = new Cropper(dom('.image-cropper--container img'), {
        aspectRatio: options.ratio,
        viewMode: 2,
    });
}

export default (inputFileElement, inputCroppedImageElement, inputsContainer, options) => {
    fileElement = inputFileElement;
    croppedImageElement = inputCroppedImageElement;
    container = inputsContainer;

    const {files} = inputFileElement;

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
