import ClassicEditor from '@ckeditor/ckeditor5-build-en-marche';

function createCKEditor(element, customOptions) {
    element.required = false;

    const defaultOptions = {
        removePlugins: [
            'ImageCaption',
            'EasyImage',
            'Indent',
        ]
    };

    ClassicEditor.create(element, Object.assign({}, defaultOptions, customOptions));
}

export default function createCKEditorWithUpload(element, uploadUrl) {
    createCKEditor(element, {
        ckfinder: {
            uploadUrl: uploadUrl,
        }
    })
}

