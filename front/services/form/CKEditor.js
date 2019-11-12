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

    return ClassicEditor.create(element, _.mergeWith(
        defaultOptions,
        customOptions,
        (objValue, srcValue) => { if (_.isArray(objValue)) { return objValue.concat(srcValue);}}
    ));
}

export default function createCKEditorWithUpload(elementSelector, uploadUrl, customOptions = {}) {
    const element = dom(elementSelector);

    return createCKEditor(element, _.merge(customOptions, {
        ckfinder: {
            uploadUrl: uploadUrl,
        },
        wordCount: getWordCountConfig(element, elementSelector),
    }))
}

function getWordCountConfig(element, elementSelector) {
    const counterWrapper = dom(`${elementSelector}_counter`);
    const limit = element.getAttribute('maxlength');

    if (!counterWrapper || !limit) {
        return {};
    }

    return {
        onUpdate: (stats) => {
            counterWrapper.innerHTML = `${stats.characters}/${limit}`;

            removeClass(counterWrapper, 'text--error');
            removeClass(counterWrapper, 'text--blue--soft');

            if (stats.characters > limit) {
                addClass(counterWrapper, 'text--error');
            } else if (stats.characters == limit) {
                addClass(counterWrapper, 'text--blue--soft');
            }
        }
    };
}
