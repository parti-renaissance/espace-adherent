CKEDITOR.dialog.add("scaytDialog",(d) => {
 const f = d.scayt; var p = `\x3cp\x3e\x3cimg src\x3d"${f.getLogo()}" /\x3e\x3c/p\x3e\x3cp\x3e${f.getLocal("version")}${f.getVersion()}\x3c/p\x3e\x3cp\x3e${f.getLocal("text_copyrights")}\x3c/p\x3e`; const q = CKEDITOR.document; const l = { isChanged() { return null === this.newLang || this.currentLang === this.newLang ? !1 : !0; },currentLang: f.getLang(),newLang: null,reset() { this.currentLang = f.getLang(); this.newLang = null; },id: "lang" }; var p = [{ id: "options",
label: f.getLocal("tab_options"),
onShow() {},
elements: [{ type: "vbox",id: "scaytOptions",children: (function () { let a = f.getApplicationConfig(); const b = []; const e = { "ignore-all-caps-words": "label_allCaps","ignore-domain-names": "label_ignoreDomainNames","ignore-words-with-mixed-cases": "label_mixedCase","ignore-words-with-numbers": "label_mixedWithDigits" }; let h; for (h in a)a = { type: "checkbox" },a.id = h,a.label = f.getLocal(e[h]),b.push(a); return b; }()),onShow() { this.getChild(); for (let a = d.scayt,b = 0; b < this.getChild().length; b++) this.getChild()[b].setValue(a.getApplicationConfig()[this.getChild()[b].id]); } }] },
{ id: "langs",
label: f.getLocal("tab_languages"),
elements: [{ id: "leftLangColumn",
type: "vbox",
align: "left",
widths: ["100"],
children: [{ type: "html",
id: "langBox",
style: "overflow: hidden; white-space: normal;margin-bottom:15px;",
html: `\x3cdiv\x3e\x3cdiv style\x3d"float:left;width:45%;margin-left:5px;" id\x3d"left-col-${d.name}" class\x3d"scayt-lang-list"\x3e\x3c/div\x3e\x3cdiv style\x3d"float:left;width:45%;margin-left:15px;" id\x3d"right-col-${d.name}" class\x3d"scayt-lang-list"\x3e\x3c/div\x3e\x3c/div\x3e`,
onShow() { const a = d.scayt.getLang(); q.getById(`scaytLang_${d.name}_${a}`).$.checked = !0; } },{ type: "html",
id: "graytLanguagesHint",
html: `\x3cdiv style\x3d"margin:5px auto; width:95%;white-space:normal;" id\x3d"${d.name}graytLanguagesHint"\x3e\x3cspan style\x3d"width:10px;height:10px;display: inline-block; background:#02b620;vertical-align:top;margin-top:2px;"\x3e\x3c/span\x3e - This languages are supported by Grammar As You Type(GRAYT).\x3c/div\x3e`,
onShow() {
 const a = q.getById(`${d.name}graytLanguagesHint`);
d.config.grayt_autoStartup || (a.$.style.display = "none");
} }] }] },{ id: "dictionaries",
label: f.getLocal("tab_dictionaries"),
elements: [{ type: "vbox",
id: "rightCol_col__left",
children: [{ type: "html",id: "dictionaryNote",html: "" },{ type: "text",
id: "dictionaryName",
label: f.getLocal("label_fieldNameDic") || "Dictionary name",
onShow(a) {
 const b = a.sender; const e = d.scayt; setTimeout(() => {
 b.getContentElement("dictionaries","dictionaryNote").getElement().setText(""); null != e.getUserDictionaryName() && "" != e.getUserDictionaryName()
&& b.getContentElement("dictionaries","dictionaryName").setValue(e.getUserDictionaryName());
},0);
} },{ type: "hbox",
id: "notExistDic",
align: "left",
style: "width:auto;",
widths: ["50%","50%"],
children: [{ type: "button",
id: "createDic",
label: f.getLocal("btn_createDic"),
title: f.getLocal("btn_createDic"),
onClick() {
 const a = this.getDialog(); const b = n; const e = d.scayt; const h = a.getContentElement("dictionaries","dictionaryName").getValue(); e.createUserDictionary(h,(c) => {
 c.error || b.toggleDictionaryButtons.call(a,!0); c.dialog = a; c.command = "create"; c.name = h; d.fire("scaytUserDictionaryAction",c);
},(c) => { c.dialog = a; c.command = "create"; c.name = h; d.fire("scaytUserDictionaryActionError",c); });
} },{ type: "button",
id: "restoreDic",
label: f.getLocal("btn_restoreDic"),
title: f.getLocal("btn_restoreDic"),
onClick() {
 const a = this.getDialog(); const b = d.scayt; const e = n; const h = a.getContentElement("dictionaries","dictionaryName").getValue(); b.restoreUserDictionary(h,(c) => {
 c.dialog = a; c.error || e.toggleDictionaryButtons.call(a,!0); c.command = "restore";
c.name = h; d.fire("scaytUserDictionaryAction",c);
},(c) => { c.dialog = a; c.command = "restore"; c.name = h; d.fire("scaytUserDictionaryActionError",c); });
} }] },{ type: "hbox",
id: "existDic",
align: "left",
style: "width:auto;",
widths: ["50%","50%"],
children: [{ type: "button",
id: "removeDic",
label: f.getLocal("btn_deleteDic"),
title: f.getLocal("btn_deleteDic"),
onClick() {
 const a = this.getDialog(); const b = d.scayt; const e = n; const h = a.getContentElement("dictionaries","dictionaryName"); const c = h.getValue(); b.removeUserDictionary(c,(b) => {
 h.setValue("");
b.error || e.toggleDictionaryButtons.call(a,!1); b.dialog = a; b.command = "remove"; b.name = c; d.fire("scaytUserDictionaryAction",b);
},(b) => { b.dialog = a; b.command = "remove"; b.name = c; d.fire("scaytUserDictionaryActionError",b); });
} },{ type: "button",
id: "renameDic",
label: f.getLocal("btn_renameDic"),
title: f.getLocal("btn_renameDic"),
onClick() {
 const a = this.getDialog(); const b = d.scayt; const e = a.getContentElement("dictionaries","dictionaryName").getValue(); b.renameUserDictionary(e,(b) => {
 b.dialog = a; b.command = "rename";
b.name = e; d.fire("scaytUserDictionaryAction",b);
},(b) => { b.dialog = a; b.command = "rename"; b.name = e; d.fire("scaytUserDictionaryActionError",b); });
} }] },{ type: "html",id: "dicInfo",html: `\x3cdiv id\x3d"dic_info_editor1" style\x3d"margin:5px auto; width:95%;white-space:normal;"\x3e${f.getLocal("text_descriptionDic")}\x3c/div\x3e` }] }] },{ id: "about",label: f.getLocal("tab_about"),elements: [{ type: "html",id: "about",style: "margin: 5px 5px;",html: `\x3cdiv\x3e\x3cdiv id\x3d"scayt_about_"\x3e${p}\x3c/div\x3e\x3c/div\x3e` }] }];
d.on("scaytUserDictionaryAction",(a) => {
 const b = SCAYT.prototype.UILib; const e = a.data.dialog; const d = e.getContentElement("dictionaries","dictionaryNote").getElement(); const c = a.editor.scayt; let g; void 0 === a.data.error ? (g = c.getLocal(`message_success_${a.data.command}Dic`),g = g.replace("%s",a.data.name),d.setText(g),b.css(d.$,{ color: "blue" })) : ("" === a.data.name ? d.setText(c.getLocal("message_info_emptyDic")) : (g = c.getLocal(`message_error_${a.data.command}Dic`),g = g.replace("%s",a.data.name),d.setText(g)),b.css(d.$,{ color: "red" }),
null != c.getUserDictionaryName() && "" != c.getUserDictionaryName() ? e.getContentElement("dictionaries","dictionaryName").setValue(c.getUserDictionaryName()) : e.getContentElement("dictionaries","dictionaryName").setValue(""));
}); d.on("scaytUserDictionaryActionError",(a) => {
 const b = SCAYT.prototype.UILib; const e = a.data.dialog; const d = e.getContentElement("dictionaries","dictionaryNote").getElement(); const c = a.editor.scayt; let g; "" === a.data.name ? d.setText(c.getLocal("message_info_emptyDic")) : (g = c.getLocal(`message_error_${a.data.command
}Dic`),g = g.replace("%s",a.data.name),d.setText(g)); b.css(d.$,{ color: "red" }); null != c.getUserDictionaryName() && "" != c.getUserDictionaryName() ? e.getContentElement("dictionaries","dictionaryName").setValue(c.getUserDictionaryName()) : e.getContentElement("dictionaries","dictionaryName").setValue("");
}); var n = { title: f.getLocal("text_title"),
resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
minWidth: "moono-lisa" == (CKEDITOR.skinName || d.config.skin) ? 450 : 340,
minHeight: 260,
onLoad() {
 if (0 != d.config.scayt_uiTabs[1]) {
 const a = n; const b = a.getLangBoxes.call(this); b.getParent().setStyle("white-space","normal"); a.renderLangList(b); this.definition.minWidth = this.getSize().width; this.resize(this.definition.minWidth,this.definition.minHeight);
}
},
onCancel() { l.reset(); },
onHide() { d.unlockSelection(); },
onShow() {
 d.fire("scaytDialogShown",this); if (0 != d.config.scayt_uiTabs[2]) {
 const a = d.scayt; const b = this.getContentElement("dictionaries","dictionaryName"); const e = this.getContentElement("dictionaries","existDic").getElement().getParent();
const h = this.getContentElement("dictionaries","notExistDic").getElement().getParent(); e.hide(); h.hide(); null != a.getUserDictionaryName() && "" != a.getUserDictionaryName() ? (this.getContentElement("dictionaries","dictionaryName").setValue(a.getUserDictionaryName()),e.show()) : (b.setValue(""),h.show());
}
},
onOk() { let a = n; const b = d.scayt; this.getContentElement("options","scaytOptions"); a = a.getChangedOption.call(this); b.commitOption({ changedOptions: a }); },
toggleDictionaryButtons(a) {
 const b = this.getContentElement("dictionaries",
"existDic").getElement().getParent(); const d = this.getContentElement("dictionaries","notExistDic").getElement().getParent(); a ? (b.show(),d.hide()) : (b.hide(),d.show());
},
getChangedOption() { const a = {}; if (1 == d.config.scayt_uiTabs[0]) for (let b = this.getContentElement("options","scaytOptions").getChild(),e = 0; e < b.length; e++)b[e].isChanged() && (a[b[e].id] = b[e].getValue()); l.isChanged() && (a[l.id] = d.config.scayt_sLang = l.currentLang = l.newLang); return a; },
buildRadioInputs(a,b,e) {
 const h = new CKEDITOR.dom.element("div");
const c = `scaytLang_${d.name}_${b}`; const g = CKEDITOR.dom.element.createFromHtml(`\x3cinput id\x3d"${c}" type\x3d"radio"  value\x3d"${b}" name\x3d"scayt_lang" /\x3e`); const f = new CKEDITOR.dom.element("label"); const m = d.scayt; h.setStyles({ "white-space": "normal",position: "relative","padding-bottom": "2px" }); g.on("click",(a) => { l.newLang = a.sender.getValue(); }); f.appendText(a); f.setAttribute("for",c); e && d.config.grayt_autoStartup && f.setStyles({ color: "#02b620" }); h.append(g); h.append(f); b === m.getLang() && (g.setAttribute("checked",
!0),g.setAttribute("defaultChecked","defaultChecked")); return h;
},
renderLangList(a) {
 const b = a.find(`#left-col-${d.name}`).getItem(0); a = a.find(`#right-col-${d.name}`).getItem(0); const e = f.getScaytLangList(); const h = f.getGraytLangList(); let c = {}; let g = []; let l = 0; let m = !1; let k; for (k in e.ltr)c[k] = e.ltr[k]; for (k in e.rtl)c[k] = e.rtl[k]; for (k in c)g.push([k,c[k]]); g.sort((a,b) => { let c = 0; a[1] > b[1] ? c = 1 : a[1] < b[1] && (c = -1); return c; }); c = {}; for (m = 0; m < g.length; m++)c[g[m][0]] = g[m][1]; g = Math.round(g.length / 2); for (k in c) {
 l++,m = k
in h.ltr || k in h.rtl,this.buildRadioInputs(c[k],k,m).appendTo(l <= g ? b : a);
}
},
getLangBoxes() { return this.getContentElement("langs","langBox").getElement(); },
contents: (function (a,b) { const d = []; const f = b.config.scayt_uiTabs; if (f) { for (const c in f)1 == f[c] && d.push(a[c]); d.push(a[a.length - 1]); } else return a; return d; }(p,d)) }; return n;
});
