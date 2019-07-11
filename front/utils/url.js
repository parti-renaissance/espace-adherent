window.getUrlParameter = (url, name) => {
    const results = new RegExp(`[\\?&]${name}=([^&#]*)`).exec(url);

    return null === results ? null : decodeURIComponent(results[1].replace(/\+/g, ' '));
};
