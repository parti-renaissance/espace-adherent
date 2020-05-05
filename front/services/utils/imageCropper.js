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
            closeCallback={(event) => handleCloseAction(url, event)}
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
                <a className={'btn'} onClick={() => handleCancelAction(url)}>Annuler</a>
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

function handleCancelAction(imageOriginalUrl) {
    updatePreviewImages(imageOriginalUrl);

    modal.hideModal();
}

function handleCloseAction(imageOriginalUrl, event) {
    cropper.destroy();
    cropper = null;

    if (event.closed) {
        updatePreviewImages(imageOriginalUrl);
    }
}

function updatePreviewImages(src) {
    const previewContainer = dom('.image-uploader--preview');

    find(previewContainer, '.preview-image--container').style.backgroundImage = `url(${src})`;
    show(previewContainer);
    hide(dom('.image-uploader--label'));
}
