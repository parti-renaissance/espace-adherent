import ClassicEditor from '@ckeditor/ckeditor5-build-en-marche';
import _ from 'lodash';

function createCKEditor(element, customOptions = {}) {
    element.required = false;

    const defaultOptions = {
        toolbar: {
            items: [
                'removeFormat', '|',
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'bulletedList', 'numberedList', '|',
                'alignment', '|',
                'insertTable', '|',
                'imageUpload', 'link',
            ],
        },
        removePlugins: [
            'ImageCaption',
            'EasyImage',
            'Indent',
        ]
    };

    ClassicEditor.create(element, _.mergeWith(
        defaultOptions,
        customOptions,
        (objValue, srcValue) => { if (_.isArray(objValue)) { return objValue.concat(srcValue);}}
    ));
}

export default function createCKEditorWithUpload(element, uploadUrl, customOptions = {}) {
    createCKEditor(element, _.merge(customOptions, {
        ckfinder: {
            uploadUrl: uploadUrl,
        }
    }));
}
