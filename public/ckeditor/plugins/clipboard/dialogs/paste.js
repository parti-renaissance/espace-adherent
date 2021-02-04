/*
 Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("paste",(c) => {
 function k(a) {
 const b = new CKEDITOR.dom.document(a.document); const g = b.getBody(); const d = b.getById("cke_actscrpt"); d && d.remove(); g.setAttribute("contenteditable",!0); g.on(e.mainPasteEvent,(a) => { a = e.initPasteDataTransfer(a); f ? a != f && (f = e.initPasteDataTransfer()) : f = a; }); if (CKEDITOR.env.ie && 8 > CKEDITOR.env.version)b.getWindow().on("blur",() => { b.$.selection.empty(); }); b.on("keydown",function (a) {
 a = a.data; let b; switch (a.getKeystroke()) {
 case 27: this.hide(); b = 1; break; case 9: case CKEDITOR.SHIFT
+ 9: this.changeFocus(1),b = 1;
}b && a.preventDefault();
},this); c.fire("ariaWidget",new CKEDITOR.dom.element(a.frameElement)); b.getWindow().getFrame().removeCustomData("pendingFocus") && g.focus();
} const h = c.lang.clipboard; var e = CKEDITOR.plugins.clipboard; let f; c.on("pasteDialogCommit",(a) => { a.data && c.fire("paste",{ type: "auto",dataValue: a.data.dataValue,method: "paste",dataTransfer: a.data.dataTransfer || e.initPasteDataTransfer() }); },null,null,1E3); return { title: h.title,
minWidth: CKEDITOR.env.ie && CKEDITOR.env.quirks ? 370
: 350,
minHeight: CKEDITOR.env.quirks ? 250 : 245,
onShow() { this.parts.dialog.$.offsetHeight; this.setupContent(); this.parts.title.setHtml(this.customTitle || h.title); this.customTitle = null; },
onLoad() { (CKEDITOR.env.ie7Compat || CKEDITOR.env.ie6Compat) && "rtl" == c.lang.dir && this.parts.contents.setStyle("overflow","hidden"); },
onOk() { this.commitContent(); },
contents: [{ id: "general",
label: c.lang.common.generalTab,
elements: [{ type: "html",
id: "securityMsg",
html: `\x3cdiv style\x3d"white-space:normal;width:340px"\x3e${
h.securityMsg}\x3c/div\x3e` },{ type: "html",id: "pasteMsg",html: `\x3cdiv style\x3d"white-space:normal;width:340px"\x3e${h.pasteMsg}\x3c/div\x3e` },{ type: "html",
id: "editing_area",
style: "width:100%;height:100%",
html: "",
focus() { const a = this.getInputElement(); const b = a.getFrameDocument().getBody(); !b || b.isReadOnly() ? a.setCustomData("pendingFocus",1) : b.focus(); },
setup() {
 let a = this.getDialog(); const b = `\x3chtml dir\x3d"${c.config.contentsLangDirection}" lang\x3d"${c.config.contentsLanguage || c.langCode
}"\x3e\x3chead\x3e\x3cstyle\x3ebody{margin:3px;height:95%;word-break:break-all;}\x3c/style\x3e\x3c/head\x3e\x3cbody\x3e\x3cscript id\x3d"cke_actscrpt" type\x3d"text/javascript"\x3ewindow.parent.CKEDITOR.tools.callFunction(${CKEDITOR.tools.addFunction(k,a)},this);\x3c/script\x3e\x3c/body\x3e\x3c/html\x3e`; const g = CKEDITOR.env.air ? "javascript:void(0)" : CKEDITOR.env.ie && !CKEDITOR.env.edge ? `javascript:void((function(){${encodeURIComponent(`document.open();(${CKEDITOR.tools.fixDomain})();document.close();`)
}})())"` : ""; const d = CKEDITOR.dom.element.createFromHtml(`\x3ciframe class\x3d"cke_pasteframe" frameborder\x3d"0"  allowTransparency\x3d"true" src\x3d"${g}" aria-label\x3d"${h.pasteArea}" aria-describedby\x3d"${a.getContentElement("general","pasteMsg").domId}"\x3e\x3c/iframe\x3e`); f = null; d.on("load",function (a) { a.removeListener(); a = d.getFrameDocument(); a.write(b); c.focusManager.add(a.getBody()); CKEDITOR.env.air && k.call(this,a.getWindow().$); },a); d.setCustomData("dialog",a); a = this.getElement(); a.setHtml("");
a.append(d); if (CKEDITOR.env.ie && !CKEDITOR.env.edge) { const e = CKEDITOR.dom.element.createFromHtml('\x3cspan tabindex\x3d"-1" style\x3d"position:absolute" role\x3d"presentation"\x3e\x3c/span\x3e'); e.on("focus",() => { setTimeout(() => { d.$.contentWindow.focus(); }); }); a.append(e); this.focus = function () { e.focus(); this.fire("focus"); }; } this.getInputElement = function () { return d; }; CKEDITOR.env.ie && (a.setStyle("display","block"),a.setStyle("height",`${d.$.offsetHeight + 2}px`));
},
commit() {
 const a = this.getDialog().getParentEditor();
const b = this.getInputElement().getFrameDocument().getBody(); const c = b.getBogus(); let d; c && c.remove(); d = b.getHtml(); setTimeout(() => { a.fire("pasteDialogCommit",{ dataValue: d,dataTransfer: f || e.initPasteDataTransfer() }); },0);
} }] }] };
});
