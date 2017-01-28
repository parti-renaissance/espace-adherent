export default class ShareDialog {
    constructor(url, width, height) {
        this._url = url;
        this._width = width;
        this._height = height;
    }

    getUrl() {
        return this._url;
    }

    getWidth() {
        return this._width;
    }

    getHeight() {
        return this._height;
    }
}
