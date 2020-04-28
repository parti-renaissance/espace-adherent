import React from 'react';
import { render } from 'react-dom';
import Modal from '../../components/Modal';
import Cropper from 'cropperjs';

let modal, cropper, fileElement;

export default (inputElement) => {
    fileElement = inputElement;
    const files = inputElement.files;

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
            closeCallback={() => {
                cropper.destroy();
                cropper = null;
            }}
        />,
        dom('#modal-wrapper')
    );

    cropper = new Cropper(dom('.image-cropper--container img'), {
        aspectRatio: 1,
        viewMode: 3,
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
                <a className="btn btn--blue b__nudge--left-small" onClick={handleCropAction}>Appliquer</a>
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

    dom('#candidate_profile_croppedImage').value = dataUrl;
    fileElement.value = '';

    modal.hideModal();
}

function handleCancelAction() {
    updatePreviewImages(dom('.image-cropper--container img').src);
    modal.hideModal();
}

function updatePreviewImages(src) {
    const previewContainer = dom('.preview-cropped-image');

    findAll(previewContainer, 'img').forEach((img) => img.src = src);
    show(previewContainer);
}
