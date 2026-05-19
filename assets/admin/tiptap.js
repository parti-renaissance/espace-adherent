import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Images from '../editor-icons';

const setupTipTap = () => {
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
            onPress: (editor) => () => editor.chain().focus().toggleUnderline().run(),
            disabled: (editor) => !editor.can().toggleUnderline(),
            image: () => Images.underline,
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
        {
            onPress: (editor) => () => {
                const previousUrl = editor.getAttributes('link').href;
                const url = window.prompt('URL', previousUrl);

                // cancelled
                if (null === url) {
                    return;
                }

                // empty
                if ('' === url) {
                    editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    return;
                }

                // update link
                editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
            },
            disabled: () => false,
            image: () => Images.link,
        },
    ];

    document.querySelectorAll('.tiptap-container').forEach((container) => {
        const editorElement = container.querySelector('.tiptap-editor');
        const menuContainer = container.querySelector('.tiptap-menu .btn-group');

        if (!editorElement || !menuContainer) {
            return;
        }

        const isHtmlMode = 'html' === container.dataset.tiptapMode;

        let initialContent;
        let onUpdate;

        if (isHtmlMode) {
            // HTML-only mode: the direct-child hidden input is the single source/destination.
            const inputHtml = container.querySelector(':scope > input[type="hidden"]');
            if (!inputHtml) {
                return;
            }
            initialContent = inputHtml.value;
            onUpdate = ({ editor }) => {
                inputHtml.value = editor.getHTML();
            };
        } else {
            // Legacy JSON+HTML mode: the JSON input is in the container, the HTML sibling lives elsewhere in the form.
            const inputJson = container.querySelector('input[name*="json"]');
            const inputHtml = document.querySelector('.tiptap-html-content');
            initialContent = inputJson.value ? JSON.parse(inputJson.value) : inputHtml.value;
            onUpdate = ({ editor }) => {
                inputJson.value = JSON.stringify(editor.getJSON());
                inputHtml.value = editor.getHTML();
            };
        }

        const editor = new Editor({
            element: editorElement.parentElement,
            content: initialContent,
            extensions: [
                StarterKit.configure({
                    link: {
                        openOnClick: false,
                        defaultProtocol: 'https',
                        protocols: ['http', 'https', 'mailto', 'tel'],
                        isAllowedUri: (url, ctx) => {
                            try {
                                const parsedUrl = url.includes(':') ? new URL(url) : new URL(`${ctx.defaultProtocol}://${url}`);

                                if (!ctx.defaultValidate(parsedUrl.href)) {
                                    return false;
                                }

                                const protocol = parsedUrl.protocol.replace(':', '');
                                const allowedProtocols = ctx.protocols.map((p) => ('string' === typeof p ? p : p.scheme));

                                return allowedProtocols.includes(protocol);
                            } catch {
                                return false;
                            }
                        },
                    },
                }),
            ],
            onUpdate,
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
};

export default setupTipTap;
