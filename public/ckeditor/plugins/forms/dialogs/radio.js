/*
 Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("radio",(b) => ({ title: b.lang.forms.checkboxAndRadio.radioTitle,
minWidth: 350,
minHeight: 140,
onShow() { delete this.radioButton; const a = this.getParentEditor().getSelection().getSelectedElement(); a && "input" == a.getName() && "radio" == a.getAttribute("type") && (this.radioButton = a,this.setupContent(a)); },
onOk() { let a; let c = this.radioButton; const b = !c; b && (a = this.getParentEditor(),c = a.document.createElement("input"),c.setAttribute("type","radio")); b && a.insertElement(c); this.commitContent({ element: c }); },
contents: [{ id: "info",
label: b.lang.forms.checkboxAndRadio.radioTitle,
title: b.lang.forms.checkboxAndRadio.radioTitle,
elements: [{ id: "name",type: "text",label: b.lang.common.name,"default": "",accessKey: "N",setup(a) { this.setValue(a.data("cke-saved-name") || a.getAttribute("name") || ""); },commit(a) { a = a.element; this.getValue() ? a.data("cke-saved-name",this.getValue()) : (a.data("cke-saved-name",!1),a.removeAttribute("name")); } },{ id: "value",
type: "text",
label: b.lang.forms.checkboxAndRadio.value,
"default": "",
accessKey: "V",
setup(a) { this.setValue(a.getAttribute("value") || ""); },
commit(a) { a = a.element; this.getValue() ? a.setAttribute("value",this.getValue()) : a.removeAttribute("value"); } },{ id: "checked",
type: "checkbox",
label: b.lang.forms.checkboxAndRadio.selected,
"default": "",
accessKey: "S",
value: "checked",
setup(a) { this.setValue(a.getAttribute("checked")); },
commit(a) {
 const c = a.element; if (CKEDITOR.env.ie) {
 let d = c.getAttribute("checked"); const e = !!this.getValue(); d != e && (d = CKEDITOR.dom.element.createFromHtml(`\x3cinput type\x3d"radio"${
e ? ' checked\x3d"checked"' : ""}\x3e\x3c/input\x3e`,b.document),c.copyAttributes(d,{ type: 1,checked: 1 }),d.replace(c),b.getSelection().selectElement(d),a.element = d);
} else this.getValue() ? c.setAttribute("checked","checked") : c.removeAttribute("checked");
} },{ id: "required",
type: "checkbox",
label: b.lang.forms.checkboxAndRadio.required,
"default": "",
accessKey: "Q",
value: "required",
setup(a) { this.setValue(a.getAttribute("required")); },
commit(a) {
 a = a.element; this.getValue() ? a.setAttribute("required",
"required") : a.removeAttribute("required");
} }] }] }));
