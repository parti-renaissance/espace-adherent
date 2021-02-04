/*
 Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("hiddenfield",(d) => ({ title: d.lang.forms.hidden.title,
hiddenField: null,
minWidth: 350,
minHeight: 110,
onShow() { delete this.hiddenField; const a = this.getParentEditor(); const b = a.getSelection(); let c = b.getSelectedElement(); c && c.data("cke-real-element-type") && "hiddenfield" == c.data("cke-real-element-type") && (this.hiddenField = c,c = a.restoreRealElement(this.hiddenField),this.setupContent(c),b.selectElement(this.hiddenField)); },
onOk() {
 var a = this.getValueOf("info","_cke_saved_name");
const b = this.getParentEditor(); var a = CKEDITOR.env.ie && 8 > CKEDITOR.document.$.documentMode ? b.document.createElement(`\x3cinput name\x3d"${CKEDITOR.tools.htmlEncode(a)}"\x3e`) : b.document.createElement("input"); a.setAttribute("type","hidden"); this.commitContent(a); a = b.createFakeElement(a,"cke_hidden","hiddenfield"); this.hiddenField ? (a.replace(this.hiddenField),b.getSelection().selectElement(a)) : b.insertElement(a); return !0;
},
contents: [{ id: "info",
label: d.lang.forms.hidden.title,
title: d.lang.forms.hidden.title,
elements: [{ id: "_cke_saved_name",
type: "text",
label: d.lang.forms.hidden.name,
"default": "",
accessKey: "N",
setup(a) { this.setValue(a.data("cke-saved-name") || a.getAttribute("name") || ""); },
commit(a) { this.getValue() ? a.setAttribute("name",this.getValue()) : a.removeAttribute("name"); } },{ id: "value",type: "text",label: d.lang.forms.hidden.value,"default": "",accessKey: "V",setup(a) { this.setValue(a.getAttribute("value") || ""); },commit(a) { this.getValue() ? a.setAttribute("value",this.getValue()) : a.removeAttribute("value"); } }] }] }));
