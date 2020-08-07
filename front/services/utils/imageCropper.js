import React from 'react';
import { render } from 'react-dom';
import Modal from '../../components/Modal';
import Cropper from 'cropperjs';

let modal, cropper, fileElement, croppedImageElement;

export default (inputFileElement, inputCroppedImageElement) => {
    fileElement = inputFileElement;
    croppedImageElement = inputCroppedImageElement;

    const files = inputFileElement.files;

    if (!files || files.length < 1) {
        return;
    }

    const file = files[0];

    if (URL) {
        displayCropperModal(URL.createObjectURL(file));
    } else if (FileReader) {
        const reader = new FileReader();
        reader.onload = () => displayCropperModal(reader.result);
        reader.readAsDataURL(file);
    }
}

function displayCropperModal(url) {
    modal = render(
        <Modal
            key={url}
            contentCallback={() => getModalContent(url)}
            withClose={false}
        />,
        dom('#modal-wrapper')
    );

    cropper = new Cropper(dom('.image-cropper--container img'), {
        aspectRatio: 1,
        viewMode: 2,
    });
}

function getModalContent(url) {
    return (
        <div>
            <div className={'image-cropper--container'}>
                <img src={url} alt={'img'} />
            </div>

            <div className="b__nudge--top-15 text--center">
                <a className={'btn'} onClick={handleCancelAction}>Annuler</a>
                <a className="btn btn--blue b__nudge--left-small" onClick={handleCropAction}>Valider</a>
            </div>
        </div>
    );
}

function handleCropAction() {
    const canvas = cropper.getCroppedCanvas({
        width: 500,
        height: 500,
    });

    const dataUrl = canvas.toDataURL();

    updatePreviewImages(dataUrl);

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

function updatePreviewImages(src) {
    const previewContainer = dom('.image-uploader--preview');

    find(previewContainer, '.preview-image--container').style.backgroundImage = `url(${src})`;
    show(previewContainer);
    hide(dom('.image-uploader--label'));
}
