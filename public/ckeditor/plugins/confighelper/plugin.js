/**
 * @file Configuration helper plugin for CKEditor
 * Copyright (C) 2012 Alfonso Mart�nez de Lizarrondo
 *
 */
(function () {
// Check if the browser supports the placeholder attribute on textareas natively.
const supportsPlaceholder = ('placeholder' in document.createElement('textarea'));

// If the data is "empty" (BR, P) or the placeholder then return an empty string.
// Otherwise return the original data
function dataIsEmpty(data) {
	if (!data) return true;

	if (20 < data.length) return false;

	const value = data.replace(/[\n|\t]*/g, '').toLowerCase();
	if (!value || '<br>' == value || '<p>&nbsp;<br></p>' == value || '<p><br></p>' == value || '<p>&nbsp;</p>' == value || '&nbsp;' == value || ' ' == value || '&nbsp;<br>' == value || ' <br>' == value) return true;

	return false;
}

function addPlaceholder(ev) {
	const { editor } = ev;
	const root = (editor.editable ? editor.editable() : ('wysiwyg' == editor.mode ? editor.document && editor.document.getBody() : editor.textarea));
	const placeholder = ev.listenerData;
	if (!root) return;

	if ('wysiwyg' == editor.mode) {
		// If the blur is due to a dialog, don't apply the placeholder
		if (CKEDITOR.dialog._.currentTop) return;

		if (!root) return;

		if (dataIsEmpty(root.getHtml())) {
			root.setHtml(placeholder);
			root.addClass('placeholder');
		}
	}

	if ('source' == editor.mode) {
		if (supportsPlaceholder) {
			if ('mode' == ev.name) {
				root.setAttribute('placeholder', placeholder);
			}
			return;
		}

		if (dataIsEmpty(root.getValue())) {
			root.setValue(placeholder);
			root.addClass('placeholder');
		}
	}
}

function removePlaceholder(ev) {
	const { editor } = ev;
	const root = (editor.editable ? editor.editable() : ('wysiwyg' == editor.mode ? editor.document && editor.document.getBody() : editor.textarea));
	if (!root) return;

	if ('wysiwyg' == editor.mode) {
		if (!root.hasClass('placeholder')) return;

		root.removeClass('placeholder');
		// fill it properly
		if (CKEDITOR.dtd[root.getName()].p) {
			root.setHtml('<p><br/></p>');
			// Set caret in position
			const range = new CKEDITOR.dom.range(editor.document);
			range.moveToElementEditablePosition(root.getFirst(), true);
			editor.getSelection().selectRanges([range]);
		} else {
			root.setHtml(' ');
		}
	}

	if ('source' == editor.mode) {
		if (!root.hasClass('placeholder')) return;

		root.removeClass('placeholder');
		root.setValue('');
	}
}

function getLang(element) {
	if (!element) return null;

	return element.getAttribute('lang') || getLang(element.getParent());
}

CKEDITOR.plugins.add('confighelper',
{
	getPlaceholderCss() {
        return '.placeholder{ color: #999; }';
    },

	onLoad() {
        // v4
        if (CKEDITOR.addCss) CKEDITOR.addCss(this.getPlaceholderCss());
    },

	init(editor) {
		// correct focus status after switch mode
		editor.on('mode', ( ev ) => {
			// Let's update to match reality
			ev.editor.focusManager.hasFocus = false;
			// Now focus it:
		});

		// Placeholder - Start
		// Get the placeholder from the replaced element or from the configuration
		let placeholder = editor.element.getAttribute('placeholder') || editor.config.placeholder;

		if (placeholder) {
			// CSS for WYSIWYG mode
			// v3
			if (editor.addCss) editor.addCss(this.getPlaceholderCss());

			// CSS for textarea mode
			let node = CKEDITOR.document.getHead().append('style');
			node.setAttribute('type', 'text/css');
			let content = 'textarea.placeholder { color: #999; font-style: italic; }';

			if (CKEDITOR.env.ie && 11>CKEDITOR.env.version) node.$.styleSheet.cssText = content;
			else node.$.innerHTML = content;

			// Watch for the calls to getData to remove the placeholder
			editor.on('getData', ( ev ) => {
				let element = (editor.editable ? editor.editable() : ('wysiwyg' == editor.mode ? editor.document && editor.document.getBody() : editor.textarea));

				if (element && element.hasClass('placeholder')) ev.data.dataValue = '';
			});

			// Watch for setData to remove placeholder class
			editor.on('setData', (ev) => {
				if (CKEDITOR.dialog._.currentTop) return;

				if (editor.mode == 'source' && supportsPlaceholder) return;

				let root = (editor.editable ? editor.editable() : ('wysiwyg' == editor.mode ? editor.document && editor.document.getBody() : editor.textarea));

				if (!root) return;

				if (!dataIsEmpty(ev.data.dataValue)) {
					// Remove the class if new data is not empty
					if (root.hasClass('placeholder')) root.removeClass('placeholder');
				} else {
					// if data is empty, set it to the placeholder
					addPlaceholder(ev);
				}
			});

			editor.on('blur', addPlaceholder, null, placeholder);
			editor.on('mode', addPlaceholder, null, placeholder);
			editor.on('contentDom', addPlaceholder, null, placeholder);

			editor.on('focus', removePlaceholder);
			editor.on('beforeModeUnload', removePlaceholder);
		} // Placeholder - End

		// SCAYT lang from element lang:
		let lang = editor.config.contentsLanguage || getLang(editor.element);
		if (lang && !editor.config.scayt_sLang) {
			// Remove the stored language
			if (localStorage) localStorage.removeItem("scayt_0_lang");

			// Convert from HTML5 Lang to spellchecker.net values
			let map = {
				'en': 'en_US',
				'en-us': 'en_US',
				'en-gb': 'en_GB',
				'pt-br': 'pt_BR',
				'da': 'da_DK',
				'da-dk': 'da_DK',
				'nl-nl': 'nl_NL',
				'en-ca': 'en_CA',
				'fi-fi': 'fi_FI',
				'fr': 'fr_FR',
				'fr-fr': 'fr_FR',
				'fr-ca': 'fr_CA',
				'de': 'de_DE',
				'de-de': 'de_DE',
				'el-gr': 'el_GR',
				'it': 'it_IT',
				'it-it': 'it_IT',
				'nb-no': 'nb_NO',
				'pt': 'pt_PT',
				'pt-pt': 'pt_PT',
				'es': 'es_ES',
				'es-es': 'es_ES',
				'sv-se': 'sv_SE'
			};
			editor.config.scayt_sLang = map[lang.toLowerCase()];
		}

		// Parse the config to turn it into a js object
		// format= dialogName:tabName:fieldName
		let parseDefinitionToObject = function (value) {
			// Allow JSON definitions
			if ('object' == typeof value) return value;

			let contents = value.split(';');
				var tabsToProcess = {};
				var i;

			for (i = 0; i < contents.length; i++) {
				let parts = contents[i].split(':');
				if (parts.length == 3) {
					let dialogName = parts[0];
						var tabName = parts[ 1 ];
						var fieldName = parts[2];

					if (!tabsToProcess[dialogName]) tabsToProcess[dialogName] = {};
					if (!tabsToProcess[dialogName][tabName]) tabsToProcess[dialogName][tabName] = [];

					tabsToProcess[dialogName][tabName].push(fieldName);
				}
			}
			return tabsToProcess;
		};

		// Customize dialogs:
		CKEDITOR.on('dialogDefinition', ( ev )
		=> {
			if (editor != ev.editor) return;

			let dialogName = ev.data.name;
				var dialogDefinition = ev.data.definition;
				var tabsToProcess;
				var i; var name; var fields; var 
tab;

			if ('tableProperties'==dialogName) dialogName == 'table';

			// Parse the config to turn it into a js object
			if (!('removeDialogFields' in editor._) && editor.config.removeDialogFields) editor._.removeDialogFields = parseDefinitionToObject(editor.config.removeDialogFields);

			// Remove fields of this dialog.
			if (editor._.removeDialogFields && (tabsToProcess = editor._.removeDialogFields[dialogName])) {
				for (name in tabsToProcess) {
					fields = tabsToProcess[name];
					tab = dialogDefinition.getContents(name);

					for (i = 0; i < fields.length; i++) tab.remove(fields[i]);
				}
			}

			if (!('hideDialogFields' in editor._) && editor.config.hideDialogFields) editor._.hideDialogFields = parseDefinitionToObject(editor.config.hideDialogFields);

			// Remove fields of this dialog.
			if (editor._.hideDialogFields && (tabsToProcess = editor._.hideDialogFields[dialogName])) {
				for (name in tabsToProcess) {
					fields = tabsToProcess[name];
					tab = dialogDefinition.getContents(name);

					for (i = 0; i < fields.length; i++) tab.get(fields[i]).hidden = true;
				}
			}

			// Set default values.
			if (editor.config.dialogFieldsDefaultValues && (tabsToProcess = editor.config.dialogFieldsDefaultValues[dialogName])) {
				for (name in tabsToProcess) {
					fields = tabsToProcess[name];
					tab = dialogDefinition.getContents(name);

					for (var fieldName in fields) {
						let dialogField = tab.get(fieldName);
						if (dialogField) dialogField.default = fields[fieldName];
					}
				}
			}
		});
	}
});
}());

 /**
  * Allows to define which dialog fiels must be removed
  * @name CKEDITOR.config.removeDialogFields
  * @type {String}
  * @example
  *	editor.config.removeDialogFields = "image:info:txtBorder;image:info:txtHSpace";
  */

 /**
  * Allows to define which dialog fiels must be hidden
  * @name CKEDITOR.config.hideDialogFields
  * @type {String}
  * @example
  *	editor.config.hideDialogFields = "image:info:htmlPreview";
  */

 /**
  * Allows to define default values for dialog fields
  * @name CKEDITOR.config.dialogFieldsDefaultValues
  * @type {Object}
  * @example
	config.dialogFieldsDefaultValues =
	{
		image:
			{
				advanced:
					{
						txtGenClass : 'myClass',
						txtGenTitle : 'Image title'
					}
			}
	};
  */

 /**
  * Placeholder text for empty editor
  * @name CKEDITOR.config.placeholder
  * @type {String}
  * @example
  *	editor.config.placeholder = "Please, type here...";
  */
