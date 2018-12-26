import React from 'react';
import PropTypes from 'prop-types';
import { Editor } from 'react-draft-wysiwyg';
import { EditorState } from 'draft-js';

import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

const toolbar = {
    options: ['inline', 'list', 'textAlign'],
    inline: {
        className: 'text-editor__toolbal__group',
        options: ['bold', 'italic', 'underline'],
    },
    list: {
        className: 'text-editor__toolbal__group',
        options: ['unordered', 'ordered'],
    },
    textAlign: {
        className: 'text-editor__toolbal__group',
        options: ['left', 'center', 'right'],
    },
};

class TextEditor extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            editorState: EditorState.createEmpty(),
        };
    }

    render() {
        return (
            <Editor
                editorState={this.state.editorState}
                placeholder={this.props.placeholder}
                toolbar={toolbar}
                editorClassName="text-editor__content"
                toolbarClassName="text-editor__toolbar"
                wrapperClassName="text-editor"
                onEditorStateChange={(editorState) => {
                    this.setState({ editorState });
                }}
            />
        );
    }
}

TextEditor.defaultProp = {
    placeholder: '',
};

TextEditor.propTypes = {
    placeholder: PropTypes.string,
};

export default TextEditor;
