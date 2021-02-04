/*
* Youtube Embed Plugin
*
* @author Jonnas Fonini <jonnasfonini@gmail.com>
* @version 2.1.13
*/
(function () {
	CKEDITOR.plugins.add('youtube', {
		lang: ['en', 'bg', 'pt', 'pt-br', 'ja', 'hu', 'it', 'fr', 'tr', 'ru', 'de', 'ar', 'nl', 'pl', 'vi', 'zh', 'el', 'he', 'es', 'nb', 'nn', 'fi', 'et', 'sk', 'cs', 'ko', 'eu', 'uk'],
		init(editor) {
			editor.addCommand('youtube', new CKEDITOR.dialogCommand('youtube', {
				allowedContent: 'div{*}(*); iframe{*}[!width,!height,!src,!frameborder,!allowfullscreen,!allow]; object param[*]; a[*]; img[*]'
			}));

			editor.ui.addButton('Youtube', {
				label: editor.lang.youtube.button,
				toolbar: 'insert',
				command: 'youtube',
				icon: `${this.path}images/icon.png`
			});

			CKEDITOR.dialog.add('youtube', (instance) => {
				let video;
					const disabled = editor.config.youtube_disabled_fields || [];

				return {
					title: editor.lang.youtube.title,
					minWidth: 510,
					minHeight: 200,
					onShow() {
						for (let i = 0; i < disabled.length; i++) {
							this.getContentElement('youtubePlugin', disabled[i]).disable();
						}
					},
					contents:
						[{
							id: 'youtubePlugin',
							expand: true,
							elements:
								[{
									id: 'txtEmbed',
									type: 'textarea',
									label: editor.lang.youtube.txtEmbed,
									onChange(api) {
										handleEmbedChange(this, api);
									},
									onKeyUp(api) {
										handleEmbedChange(this, api);
									},
									validate() {
										if (this.isEnabled()) {
											if (!this.getValue()) {
												alert(editor.lang.youtube.noCode);
												return false;
											} if (0 === this.getValue().length || -1 === this.getValue().indexOf('//')) {
												alert(editor.lang.youtube.invalidEmbed);
												return false;
											}
										}
									}
								},
								{
									type: 'html',
									html: `${editor.lang.youtube.or}<hr>`
								},
								{
									type: 'hbox',
									widths: ['70%', '15%', '15%'],
									children:
									[
										{
											id: 'txtUrl',
											type: 'text',
											label: editor.lang.youtube.txtUrl,
											onChange(api) {
												handleLinkChange(this, api);
											},
											onKeyUp(api) {
												handleLinkChange(this, api);
											},
											validate() {
												if (this.isEnabled()) {
													if (!this.getValue()) {
														alert(editor.lang.youtube.noCode);
														return false;
													}
														video = ytVidId(this.getValue());

														if (0 === this.getValue().length || false === video) {
															alert(editor.lang.youtube.invalidUrl);
															return false;
														}
												}
											}
										},
										{
											type: 'text',
											id: 'txtWidth',
											width: '60px',
											label: editor.lang.youtube.txtWidth,
											'default': null != editor.config.youtube_width ? editor.config.youtube_width : '640',
											validate() {
												if (this.getValue()) {
													const width = parseInt(this.getValue()) || 0;

													if (0 === width) {
														alert(editor.lang.youtube.invalidWidth);
														return false;
													}
												} else {
													alert(editor.lang.youtube.noWidth);
													return false;
												}
											}
										},
										{
											type: 'text',
											id: 'txtHeight',
											width: '60px',
											label: editor.lang.youtube.txtHeight,
											'default': null != editor.config.youtube_height ? editor.config.youtube_height : '360',
											validate() {
												if (this.getValue()) {
													const height = parseInt(this.getValue()) || 0;

													if (0 === height) {
														alert(editor.lang.youtube.invalidHeight);
														return false;
													}
												} else {
													alert(editor.lang.youtube.noHeight);
													return false;
												}
											}
										}
									]
								},
								{
									type: 'hbox',
									widths: ['55%', '45%'],
									children:
										[
											{
												id: 'chkResponsive',
												type: 'checkbox',
												label: editor.lang.youtube.txtResponsive,
												'default': null != editor.config.youtube_responsive ? editor.config.youtube_responsive : false
											},
											{
												id: 'chkNoEmbed',
												type: 'checkbox',
												label: editor.lang.youtube.txtNoEmbed,
												'default': null != editor.config.youtube_noembed ? editor.config.youtube_noembed : false
											}
										]
								},
								{
									type: 'hbox',
									widths: ['55%', '45%'],
									children:
									[
										{
											id: 'chkRelated',
											type: 'checkbox',
											'default': null != editor.config.youtube_related ? editor.config.youtube_related : true,
											label: editor.lang.youtube.chkRelated
										},
										{
											id: 'chkOlderCode',
											type: 'checkbox',
											'default': null != editor.config.youtube_older ? editor.config.youtube_older : false,
											label: editor.lang.youtube.chkOlderCode
										}
									]
								},
								{
									type: 'hbox',
									widths: ['55%', '45%'],
									children:
									[
										{
											id: 'chkPrivacy',
											type: 'checkbox',
											label: editor.lang.youtube.chkPrivacy,
											'default': null != editor.config.youtube_privacy ? editor.config.youtube_privacy : false
										},
										{
											id: 'chkAutoplay',
											type: 'checkbox',
											'default': null != editor.config.youtube_autoplay ? editor.config.youtube_autoplay : false,
											label: editor.lang.youtube.chkAutoplay
										}
									]
								},
								{
									type: 'hbox',
									widths: ['55%', '45%'],
									children:
									[
										{
											id: 'txtStartAt',
											type: 'text',
											label: editor.lang.youtube.txtStartAt,
											validate() {
												if (this.getValue()) {
													const str = this.getValue();

													if (!/^(?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)$/i.test(str)) {
														alert(editor.lang.youtube.invalidTime);
														return false;
													}
												}
											}
										},
										{
											id: 'chkControls',
											type: 'checkbox',
											'default': null != editor.config.youtube_controls ? editor.config.youtube_controls : true,
											label: editor.lang.youtube.chkControls
										}
									]
								}
							]
						}
					],
					onOk() {
						let content = '';
						let responsiveStyle = '';

						if (this.getContentElement('youtubePlugin', 'txtEmbed').isEnabled()) {
							content = this.getValueOf('youtubePlugin', 'txtEmbed');
						} else {
							let url = 'https://'; const params = []; let startSecs; let
paramAutoplay = '';
							const width = this.getValueOf('youtubePlugin', 'txtWidth');
							const height = this.getValueOf('youtubePlugin', 'txtHeight');

							if (true === this.getContentElement('youtubePlugin', 'chkPrivacy').getValue()) {
								url += 'www.youtube-nocookie.com/';
							} else {
								url += 'www.youtube.com/';
							}

							url += `embed/${video}`;

							if (false === this.getContentElement('youtubePlugin', 'chkRelated').getValue()) {
								params.push('rel=0');
							}

							if (true === this.getContentElement('youtubePlugin', 'chkAutoplay').getValue()) {
								params.push('autoplay=1');
								paramAutoplay = 'autoplay';
							}

							if (false === this.getContentElement('youtubePlugin', 'chkControls').getValue()) {
								params.push('controls=0');
							}

							startSecs = this.getValueOf('youtubePlugin', 'txtStartAt');

							if (startSecs) {
								const seconds = hmsToSeconds(startSecs);

								params.push(`start=${seconds}`);
							}

							if (0 < params.length) {
								url = `${url}?${params.join('&')}`;
							}

							if (true === this.getContentElement('youtubePlugin', 'chkResponsive').getValue()) {
								content += '<div class="youtube-embed-wrapper" style="position:relative;padding-bottom:56.25%;padding-top:30px;height:0;overflow:hidden">';
								responsiveStyle = 'style="position:absolute;top:0;left:0;width:100%;height:100%"';
							}

							if (true === this.getContentElement('youtubePlugin', 'chkOlderCode').getValue()) {
								url = url.replace('embed/', 'v/');
								url = url.replace(/&/g, '&amp;');

								if (-1 === url.indexOf('?')) {
									url += '?';
								} else {
									url += '&amp;';
								}
								url += `hl=${this.getParentEditor().config.language ? this.getParentEditor().config.language : 'en'}&amp;version=3`;

								content += `<object width="${width}" height="${height}" ${responsiveStyle}>`;
								content += `<param name="movie" value="${url}"></param>`;
								content += '<param name="allowFullScreen" value="true"></param>';
								content += '<param name="allowscriptaccess" value="always"></param>';
								content += `<embed src="${url}" type="application/x-shockwave-flash" `;
								content += `width="${width}" height="${height}" ${responsiveStyle} allowscriptaccess="always" `;
								content += 'allowfullscreen="true"></embed>';
								content += '</object>';
							} else
							if (true === this.getContentElement('youtubePlugin', 'chkNoEmbed').getValue()) {
								const imgSrc = `//img.youtube.com/vi/${video}/sddefault.jpg`;
								content += `<a href="${url}" ><img width="${width}" height="${height}" src="${imgSrc}" ${responsiveStyle}/></a>`;
							} else {
								content += `<iframe allow="${paramAutoplay};" width="${width}" height="${height}" src="${url}" ${responsiveStyle}`;
								content += 'frameborder="0" allowfullscreen></iframe>';
							}

							if (true === this.getContentElement('youtubePlugin', 'chkResponsive').getValue()) {
								content += '</div>';
							}
						}

						const element = CKEDITOR.dom.element.createFromHtml(content);
						const instance = this.getParentEditor();
						instance.insertElement(element);
					}
				};
			});
		}
	});
}());

function handleLinkChange(el, api) {
	const video = ytVidId(el.getValue());
	const time = ytVidTime(el.getValue());

	if (0 < el.getValue().length) {
		el.getDialog().getContentElement('youtubePlugin', 'txtEmbed').disable();
	} else {
		el.getDialog().getContentElement('youtubePlugin', 'txtEmbed').enable();
	}

	if (video && time) {
		const seconds = timeParamToSeconds(time);
		const hms = secondsToHms(seconds);
		el.getDialog().getContentElement('youtubePlugin', 'txtStartAt').setValue(hms);
	}
}

function handleEmbedChange(el, api) {
	if (0 < el.getValue().length) {
		el.getDialog().getContentElement('youtubePlugin', 'txtUrl').disable();
	} else {
		el.getDialog().getContentElement('youtubePlugin', 'txtUrl').enable();
	}
}

/**
 * JavaScript function to match (and return) the video Id
 * of any valid Youtube Url, given as input string.
 * @author: Stephan Schmitz <eyecatchup@gmail.com>
 * @url: http://stackoverflow.com/a/10315969/624466
 */
function ytVidId(url) {
	const p = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
	return (url.match(p)) ? RegExp.$1 : false;
}

/**
 * Matches and returns time param in YouTube Urls.
 */
function ytVidTime(url) {
	const p = /t=([0-9hms]+)/;
	return (url.match(p)) ? RegExp.$1 : false;
}

/**
 * Converts time in hms format to seconds only
 */
function hmsToSeconds(time) {
	const arr = time.split(':'); let s = 0; let
m = 1;

	while (0 < arr.length) {
		s += m * parseInt(arr.pop(), 10);
		m *= 60;
	}

	return s;
}

/**
 * Converts seconds to hms format
 */
function secondsToHms(seconds) {
	const h = Math.floor(seconds / 3600);
	const m = Math.floor((seconds / 60) % 60);
	const s = seconds % 60;

	const pad = function (n) {
		n = String(n);
		return 2 <= n.length ? n : `0${n}`;
	};

	if (0 < h) {
		return `${pad(h)}:${pad(m)}:${pad(s)}`;
	}

		return `${pad(m)}:${pad(s)}`;
}

/**
 * Converts time in youtube t-param format to seconds
 */
function timeParamToSeconds(param) {
	const componentValue = function (si) {
		const regex = new RegExp(`(\\d+)${si}`);
		return param.match(regex) ? parseInt(RegExp.$1, 10) : 0;
	};

	return componentValue('h') * 3600
		+ componentValue('m') * 60
		+ componentValue('s');
}

/**
 * Converts seconds into youtube t-param value, e.g. 1h4m30s
 */
function secondsToTimeParam(seconds) {
	const h = Math.floor(seconds / 3600);
	const m = Math.floor((seconds / 60) % 60);
	const s = seconds % 60;
	let param = '';

	if (0 < h) {
		param += `${h}h`;
	}

	if (0 < m) {
		param += `${m}m`;
	}

	if (0 < s) {
		param += `${s}s`;
	}

	return param;
}
