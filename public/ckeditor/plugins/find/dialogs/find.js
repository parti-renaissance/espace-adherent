/*
 Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
(function () {
 function C(c) { return c.type == CKEDITOR.NODE_TEXT && 0 < c.getLength() && (!r || !c.isReadOnly()); } function w(c) { return !(c.type == CKEDITOR.NODE_ELEMENT && c.isBlockBoundary(CKEDITOR.tools.extend({},CKEDITOR.dtd.$empty,CKEDITOR.dtd.$nonEditable))); } function q(c,g) {
 function n(a,b) {
 const d = this; const c = new CKEDITOR.dom.walker(a); c.guard = b ? w : function (a) { !w(a) && (d._.matchBoundary = !0); }; c.evaluator = C; c.breakOnFalse = 1; a.startContainer.type == CKEDITOR.NODE_TEXT && (this.textNode = a.startContainer,this.offset = a.startOffset
- 1); this._ = { matchWord: b,walker: c,matchBoundary: !1 };
} function q(a,b) { const d = c.createRange(); d.setStart(a.textNode,b ? a.offset : a.offset + 1); d.setEndAt(c.editable(),CKEDITOR.POSITION_BEFORE_END); return d; } function t(a) { const b = c.getSelection().getRanges()[0]; const d = c.editable(); b && !a ? (a = b.clone(),a.collapse(!0)) : (a = c.createRange(),a.setStartAt(d,CKEDITOR.POSITION_AFTER_START)); a.setEndAt(d,CKEDITOR.POSITION_BEFORE_END); return a; } const x = new CKEDITOR.style(CKEDITOR.tools.extend({ attributes: { "data-cke-highlight": 1 },
fullMatch: 1,
ignoreReadonly: 1,
childRule() { return 0; } },c.config.find_highlight,!0)); n.prototype = { next() { return this.move(); },
back() { return this.move(!0); },
move(a) {
 let b = this.textNode; if (null === b) return y.call(this); this._.matchBoundary = !1; if (b && a && 0 < this.offset) this.offset--; else if (b && this.offset < b.getLength() - 1) this.offset++; else {
 for (b = null; !b && !(b = this._.walker[a ? "previous" : "next"].call(this._.walker),this._.matchWord && !b || this._.walker._.end););this.offset = (this.textNode = b)
? a ? b.getLength() - 1 : 0 : 0;
} return y.call(this);
} }; const u = function (a,b) { this._ = { walker: a,cursors: [],rangeLength: b,highlightRange: null,isMatched: 0 }; }; u.prototype = { toDomRange() { const a = c.createRange(); let b = this._.cursors; if (1 > b.length) { var d = this._.walker.textNode; if (d)a.setStartAfter(d); else return null; } else d = b[0],b = b[b.length - 1],a.setStart(d.textNode,d.offset),a.setEnd(b.textNode,b.offset + 1); return a; },
updateFromDomRange(a) {
 const b = new n(a); this._.cursors = []; do a = b.next(),a.character && this._.cursors.push(a);
while (a.character); this._.rangeLength = this._.cursors.length;
},
setMatched() { this._.isMatched = !0; },
clearMatched() { this._.isMatched = !1; },
isMatched() { return this._.isMatched; },
highlight() { if (!(1 > this._.cursors.length)) { this._.highlightRange && this.removeHighlight(); const a = this.toDomRange(); let b = a.createBookmark(); x.applyToRange(a,c); a.moveToBookmark(b); this._.highlightRange = a; b = a.startContainer; b.type != CKEDITOR.NODE_ELEMENT && (b = b.getParent()); b.scrollIntoView(); this.updateFromDomRange(a); } },
removeHighlight() { if (this._.highlightRange) { const a = this._.highlightRange.createBookmark(); x.removeFromRange(this._.highlightRange,c); this._.highlightRange.moveToBookmark(a); this.updateFromDomRange(this._.highlightRange); this._.highlightRange = null; } },
isReadOnly() { return this._.highlightRange ? this._.highlightRange.startContainer.isReadOnly() : 0; },
moveBack() {
 const a = this._.walker.back(); let b = this._.cursors; a.hitMatchBoundary && (this._.cursors = b = []); b.unshift(a); b.length > this._.rangeLength
&& b.pop(); return a;
},
moveNext() { const a = this._.walker.next(); let b = this._.cursors; a.hitMatchBoundary && (this._.cursors = b = []); b.push(a); b.length > this._.rangeLength && b.shift(); return a; },
getEndCharacter() { const a = this._.cursors; return 1 > a.length ? null : a[a.length - 1].character; },
getNextCharacterRange(a) { let b; let d; d = this._.cursors; d = (b = d[d.length - 1]) && b.textNode ? new n(q(b)) : this._.walker; return new u(d,a); },
getCursors() { return this._.cursors; } }; const z = function (a,b) {
 const d = [-1]; b && (a = a.toLowerCase());
for (let c = 0; c < a.length; c++) for (d.push(d[c] + 1); 0 < d[c + 1] && a.charAt(c) != a.charAt(d[c + 1] - 1);)d[c + 1] = d[d[c + 1] - 1] + 1; this._ = { overlap: d,state: 0,ignoreCase: !!b,pattern: a };
}; z.prototype = { feedCharacter(a) { for (this._.ignoreCase && (a = a.toLowerCase()); ;) { if (a == this._.pattern.charAt(this._.state)) return this._.state++,this._.state == this._.pattern.length ? (this._.state = 0,2) : 1; if (this._.state) this._.state = this._.overlap[this._.state]; else return 0; } },reset() { this._.state = 0; } }; const D = /[.,"'?!;: \u0085\u00a0\u1680\u280e\u2028\u2029\u202f\u205f\u3000]/;
const A = function (a) { if (!a) return !0; const b = a.charCodeAt(0); return 9 <= b && 13 >= b || 8192 <= b && 8202 >= b || D.test(a); }; const e = { searchRange: null,
matchRange: null,
find(a,b,d,f,e,E) {
 this.matchRange ? (this.matchRange.removeHighlight(),this.matchRange = this.matchRange.getNextCharacterRange(a.length)) : this.matchRange = new u(new n(this.searchRange),a.length); for (let k = new z(a,!b),l = 0,m = "%"; null !== m;) {
 for (this.matchRange.moveNext(); m = this.matchRange.getEndCharacter();) {
 l = k.feedCharacter(m); if (2 == l) break; this.matchRange.moveNext().hitMatchBoundary
&& k.reset();
} if (2 == l) { if (d) { var h = this.matchRange.getCursors(); let p = h[h.length - 1]; var h = h[0]; const g = c.createRange(); g.setStartAt(c.editable(),CKEDITOR.POSITION_AFTER_START); g.setEnd(h.textNode,h.offset); h = g; p = q(p); h.trim(); p.trim(); h = new n(h,!0); p = new n(p,!0); if (!A(h.back().character) || !A(p.next().character)) continue; } this.matchRange.setMatched(); !1 !== e && this.matchRange.highlight(); return !0; }
} this.matchRange.clearMatched(); this.matchRange.removeHighlight(); return f && !E ? (this.searchRange = t(1),this.matchRange = null,
arguments.callee.apply(this,Array.prototype.slice.call(arguments).concat([!0]))) : !1;
},
replaceCounter: 0,
replace(a,b,d,f,e,g,k) {
 r = 1; a = 0; a = this.hasMatchOptionsChanged(b,f,e); if (!this.matchRange || !this.matchRange.isMatched() || this.matchRange._.isReplaced || this.matchRange.isReadOnly() || a)a && this.matchRange && (this.matchRange.clearMatched(),this.matchRange.removeHighlight(),this.matchRange = null),a = this.find(b,f,e,g,!k); else {
 this.matchRange.removeHighlight(); b = this.matchRange.toDomRange(); d = c.document.createText(d);
if (!k) { var l = c.getSelection(); l.selectRanges([b]); c.fire("saveSnapshot"); }b.deleteContents(); b.insertNode(d); k || (l.selectRanges([b]),c.fire("saveSnapshot")); this.matchRange.updateFromDomRange(b); k || this.matchRange.highlight(); this.matchRange._.isReplaced = !0; this.replaceCounter++; a = 1;
}r = 0; return a;
},
matchOptions: null,
hasMatchOptionsChanged(a,b,c) { a = [a,b,c].join("."); b = this.matchOptions && this.matchOptions != a; this.matchOptions = a; return b; } }; const f = c.lang.find; return { title: f.title,
resizable: CKEDITOR.DIALOG_RESIZE_NONE,
minWidth: 350,
minHeight: 170,
buttons: [CKEDITOR.dialog.cancelButton(c,{ label: c.lang.common.close })],
contents: [{ id: "find",
label: f.find,
title: f.find,
accessKey: "",
elements: [{ type: "hbox",
widths: ["230px","90px"],
children: [{ type: "text",id: "txtFindFind",label: f.findWhat,isChanged: !1,labelLayout: "horizontal",accessKey: "F" },{ type: "button",
id: "btnFind",
align: "left",
style: "width:100%",
label: f.find,
onClick() {
 const a = this.getDialog(); e.find(a.getValueOf("find","txtFindFind"),a.getValueOf("find","txtFindCaseChk"),
a.getValueOf("find","txtFindWordChk"),a.getValueOf("find","txtFindCyclic")) || alert(f.notFoundMsg);
} }] },{ type: "fieldset",className: "cke_dialog_find_fieldset",label: CKEDITOR.tools.htmlEncode(f.findOptions),style: "margin-top:29px",children: [{ type: "vbox",padding: 0,children: [{ type: "checkbox",id: "txtFindCaseChk",isChanged: !1,label: f.matchCase },{ type: "checkbox",id: "txtFindWordChk",isChanged: !1,label: f.matchWord },{ type: "checkbox",id: "txtFindCyclic",isChanged: !1,"default": !0,label: f.matchCyclic }] }] }] },{ id: "replace",
label: f.replace,
accessKey: "M",
elements: [{ type: "hbox",
widths: ["230px","90px"],
children: [{ type: "text",id: "txtFindReplace",label: f.findWhat,isChanged: !1,labelLayout: "horizontal",accessKey: "F" },{ type: "button",
id: "btnFindReplace",
align: "left",
style: "width:100%",
label: f.replace,
onClick() {
 const a = this.getDialog(); e.replace(a,a.getValueOf("replace","txtFindReplace"),a.getValueOf("replace","txtReplace"),a.getValueOf("replace","txtReplaceCaseChk"),a.getValueOf("replace","txtReplaceWordChk"),a.getValueOf("replace",
"txtReplaceCyclic")) || alert(f.notFoundMsg);
} }] },{ type: "hbox",
widths: ["230px","90px"],
children: [{ type: "text",id: "txtReplace",label: f.replaceWith,isChanged: !1,labelLayout: "horizontal",accessKey: "R" },{ type: "button",
id: "btnReplaceAll",
align: "left",
style: "width:100%",
label: f.replaceAll,
isChanged: !1,
onClick() {
 const a = this.getDialog(); e.replaceCounter = 0; e.searchRange = t(1); e.matchRange && (e.matchRange.removeHighlight(),e.matchRange = null); for (c.fire("saveSnapshot"); e.replace(a,a.getValueOf("replace",
"txtFindReplace"),a.getValueOf("replace","txtReplace"),a.getValueOf("replace","txtReplaceCaseChk"),a.getValueOf("replace","txtReplaceWordChk"),!1,!0););e.replaceCounter ? (alert(f.replaceSuccessMsg.replace(/%1/,e.replaceCounter)),c.fire("saveSnapshot")) : alert(f.notFoundMsg);
} }] },{ type: "fieldset",
label: CKEDITOR.tools.htmlEncode(f.findOptions),
children: [{ type: "vbox",
padding: 0,
children: [{ type: "checkbox",id: "txtReplaceCaseChk",isChanged: !1,label: f.matchCase },{ type: "checkbox",
id: "txtReplaceWordChk",
isChanged: !1,
label: f.matchWord },{ type: "checkbox",id: "txtReplaceCyclic",isChanged: !1,"default": !0,label: f.matchCyclic }] }] }] }],
onLoad() {
 const a = this; let b; let c = 0; this.on("hide",() => { c = 0; }); this.on("show",() => { c = 1; }); this.selectPage = CKEDITOR.tools.override(this.selectPage,(f) => function (e) {
 f.call(a,e); var g = a._.tabs[e]; let k; k = "find" === e ? "txtFindWordChk" : "txtReplaceWordChk"; b = a.getContentElement(e,"find" === e ? "txtFindFind" : "txtFindReplace"); a.getContentElement(e,k); g.initialized || (CKEDITOR.document.getById(b._.inputId),
g.initialized = !0); if (c) { let l; e = "find" === e ? 1 : 0; var g = 1 - e; let m; const h = v.length; for (m = 0; m < h; m++)k = this.getContentElement(B[e],v[m][e]),l = this.getContentElement(B[g],v[m][g]),l.setValue(k.getValue()); }
});
},
onShow() { e.searchRange = t(); const a = this.getParentEditor().getSelection().getSelectedText(); const b = this.getContentElement(g,"find" == g ? "txtFindFind" : "txtFindReplace"); b.setValue(a); b.select(); this.selectPage(g); this[`${"find" == g && this._.editor.readOnly ? "hide" : "show"}Page`]("replace"); },
onHide() {
 let a;
e.matchRange && e.matchRange.isMatched() && (e.matchRange.removeHighlight(),(a = e.matchRange.toDomRange()) && c.getSelection().selectRanges([a]),c.focus()); delete e.matchRange;
},
onFocus() { return "replace" == g ? this.getContentElement("replace","txtFindReplace") : this.getContentElement("find","txtFindFind"); } };
} let r; var y = function () { return { textNode: this.textNode,offset: this.offset,character: this.textNode ? this.textNode.getText().charAt(this.offset) : null,hitMatchBoundary: this._.matchBoundary }; }; var B = ["find","replace"];
var v = [["txtFindFind","txtFindReplace"],["txtFindCaseChk","txtReplaceCaseChk"],["txtFindWordChk","txtReplaceWordChk"],["txtFindCyclic","txtReplaceCyclic"]]; CKEDITOR.dialog.add("find",(c) => q(c,"find")); CKEDITOR.dialog.add("replace",(c) => q(c,"replace"));
}());
