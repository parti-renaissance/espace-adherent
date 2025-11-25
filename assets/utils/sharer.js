window.share = (type, link, title) => {
    const width = 600;
    const height = 450;
    const windowLeft = window.screenLeft ? window.screenLeft : window.screenX;
    const windowTop = window.screenTop ? window.screenTop : window.screenY;
    const left = windowLeft + (window.innerWidth / 2 - width / 2);
    const top = windowTop + (window.innerHeight / 2 - height / 2);

    let socialUrl;

    switch (type) {
        case 'twitter':
            socialUrl = `https://twitter.com/share?url=${link}&text=${title}&via=enmarchefr`;
            break;
        case 'facebook':
            socialUrl = `https://www.facebook.com/dialog/share?app_id=620675918119463&display=popup&href=${link}`;
            break;
    }

    const popup = window.open(socialUrl, title, `width=${width}, height=${height}, top=${top}, left=${left}`);

    if ('object' === typeof popup && 'undefined' !== typeof popup.opener && null !== popup.opener) {
        popup.opener = null;

        if (window.focus) {
            popup.focus();
        }
    }

    return false;
};
