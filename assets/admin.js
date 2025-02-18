import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Images } from './editor-icons';

const TOOLS = [
    {
        onPress: (editor) => () => editor.chain().focus().toggleBold().run(),
        disabled: (editor) => !editor.can().toggleBold(),
        image: () => Images.bold,
    },
    {
        onPress: (editor) => () => editor.chain().focus().toggleItalic().run(),
        disabled: (editor) => !editor.can().toggleItalic(),
        image: () => Images.italic,
    },
    {
        onPress: (editor) => () => editor.chain().focus().toggleOrderedList().run(),
        disabled: (editor) => !editor.can().toggleOrderedList(),
        image: () => Images.orderedList,
    },
    {
        onPress: (editor) => () => editor.chain().focus().toggleBulletList().run(),
        disabled: (editor) => !editor.can().toggleBulletList(),
        image: () => Images.bulletList,
    },
];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tiptap-editor').forEach((element) => {
        const container = element.parentElement;
        const inputJson = container.querySelector('input[name*="json"]');
        const inputHtml = document.querySelector('.tiptap-html-content');
        const menuContainer = container.querySelector('.tiptap-menu .btn-group');

        const editor = new Editor({
            element: element.parentElement,
            content: inputJson.value ? JSON.parse(inputJson.value) : inputHtml.value,
            extensions: [StarterKit],
            onUpdate: (props) => {
                inputJson.value = JSON.stringify(props.editor.getJSON());
                inputHtml.value = props.editor.getHTML();
            },
        });

        TOOLS.forEach((tool) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.classList.add('btn', 'btn-default', 'btn-sm');
            const img = document.createElement('img');
            img.src = tool.image().default;
            button.appendChild(img);
            button.disabled = tool.disabled(editor);
            button.addEventListener('click', tool.onPress(editor));
            menuContainer.appendChild(button);
        });
    });
});
