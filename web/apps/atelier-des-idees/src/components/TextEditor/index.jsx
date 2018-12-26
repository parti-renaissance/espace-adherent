import React from 'react';
import PropTypes from 'prop-types';
import { Editor } from 'react-draft-wysiwyg';
import { EditorState } from 'draft-js';
import { stateToHTML } from 'draft-js-export-html';
import { stateFromHTML } from 'draft-js-import-html';

import 'react-draft-wysiwyg/dist/react-draft-wysiwyg.css';

// see https://jpuri.github.io/react-draft-wysiwyg/#/docs for more about this
const initialToolbar = {
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

/**
 * Custom WYSIWYG editor based on react-draft-wysiwyg and draft-js
 * See more https://jpuri.github.io/react-draft-wysiwyg
 */
class TextEditor extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            editorState: props.initialContent
                ? EditorState.createWithContent(stateFromHTML(props.initialContent)) // create initial state from html string
                : EditorState.createEmpty(),
            textContent: '',
        };
        this.onEditorStateChange = this.onEditorStateChange.bind(this);
        this.handleBeforeInput = this.handleBeforeInput.bind(this);
        // this.handlePastedText = this.handlePastedText.bind(this);
    }

    handleBeforeInput() {
        const { maxLength } = this.props;
        const contentState = this.state.editorState.getCurrentContent();
        const contentText = contentState.getPlainText();
        // if text longer than maxLength, prevent Editor from adding new character
        if (maxLength && contentText.length >= maxLength) {
            this.onEditorStateChange(this.state.editorState);
            return 'handled';
        }
        // otherwise, defer to draft-js
        return 'not-handled';
    }

    // Uncomment to handle pasted text if longer than maxLength
    // handlePastedText(pastedText) {
    //     const { maxLength } = this.props;
    //     const contentState = this.state.editorState.getCurrentContent();
    //     const contentText = contentState.getPlainText();

    //     if (contentText.length + pastedText.length > maxLength) {
    //         return 'handled';
    //     }
    //     return 'not-handled';
    // }

    onEditorStateChange(editorState) {
        // convert content state to html and text
        const contentState = editorState.getCurrentContent();
        const htmlContent = stateToHTML(contentState);
        const textContent = contentState.getPlainText();
        // update state
        this.setState({ editorState, textContent }, () => this.props.onChange(htmlContent));
    }

    render() {
        return (
            <div className="text-editor">
                <Editor
                    editorState={this.state.editorState}
                    placeholder={this.props.placeholder}
                    toolbar={this.props.toolbar}
                    editorClassName="text-editor__content"
                    toolbarClassName="text-editor__toolbar"
                    handleBeforeInput={this.handleBeforeInput}
                    onEditorStateChange={this.onEditorStateChange}
                />
                {this.props.maxLength && (
                    <div className="text-editor__count">{`${this.state.textContent.length}/${
                        this.props.maxLength
                    }`}</div>
                )}
            </div>
        );
    }
}

TextEditor.defaultProps = {
    initialContent: '',
    maxLength: undefined,
    placeholder: '',
    toolbar: initialToolbar,
};

TextEditor.propTypes = {
    initialContent: PropTypes.string, // html string
    maxLength: PropTypes.number,
    onChange: PropTypes.func.isRequired,
    placeholder: PropTypes.string,
    toolbar: PropTypes.object,
};

export default TextEditor;
