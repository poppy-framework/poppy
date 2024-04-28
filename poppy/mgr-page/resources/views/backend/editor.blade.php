<script src="/assets/libs/boot/wangeditor@5.1.js"></script>
<div id="$contentId" style="border: 1px solid #ccc;z-index:100;">
    <div id="{{ $contentId }}Toolbar" style="border-bottom:1px solid #ccc;"><!-- 工具栏 --></div>
    <div id="{{ $contentId }}Editor" style="height:500px;"><!-- 编辑器 --></div>
</div>
<input type="hidden" id="{{ $contentId }}Input" name="{{$name}}">

<script>
	if (typeof editHtmlMenu != 'function') {
		const {genModalTextareaElems, genModalButtonElems} = window.wangEditor;

		const HTML_SVG = `<svg viewBox="0 0 1024 1024"><path fill="#333333" d="M60.544 0l82.144 921.632L511.456 1024l369.728-102.528L963.488 0H60.576z m750.208 862.816l-297.216 82.368v0.48l-0.768-0.224-0.768 0.224v-0.48l-297.216-82.368L144.544 75.328h736.48l-70.24 787.488z m-160-332.608l-13.056 146.56-126.208 34.08-125.856-33.92-8.064-90.24H264.064l15.84 177.504 232.064 64.192 231.328-64.192 31.04-347.008H362.368l-10.304-115.744h432.544l10.112-113.024H228.512l30.496 341.792z" /></svg>`

		function randomString(e) {
			e = e || 32;
			var t = "ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678",
				a = t.length,
				n = "";
			for (i = 0; i < e; i++) n += t.charAt(Math.floor(Math.random() * a));
			return n
		}

		class editHtmlMenu {
			$content = null;

			buttonId = randomString(10);

			constructor() {
				this.title = '编辑HTML';
				this.iconSvg = HTML_SVG;
				this.tag = 'button';
				this.showModal = true;
				this.modalWidth = 900;
			};

			getValue(editor) {
				return '';
			};

			isActive(editor) {
				return false;
			};

			exec(editor, value) {

			}

			isDisabled(editor) {
				const {selection} = editor;
				if (selection === null) {
					return true;
				}
				return false;
			};

			getModalPositionNode(editor) {
				return null;
			}

			getModalContentElem(editor) {
				const {textareaId, buttonId} = this;

				console.log(buttonId);

				const [textareaContainerElem, textareaElem] = genModalTextareaElems(
					'HTML 代码',
					textareaId,
					'使用 HTML 语法'
				);

				console.log(textareaContainerElem);

				const $textarea = $(textareaElem);
				const [buttonContainerElem] = genModalButtonElems(buttonId, '确定');


				if (this.$content == null) {
					// 第一次渲染
					const $content = $('<div></div>');

					// 绑定事件（第一次渲染时绑定，不要重复绑定）
					$content.on('click', `#${buttonId}`, e => {
						e.preventDefault();
						const value = $content.find(`#${textareaId}`).val().trim();
						this.editHtml(editor, value);

						editor.hidePanelOrModal() // 隐藏 modal
					});

					// 记录属性，重要
					this.$content = $content
				}

				const $content = this.$content;
				$content.html(''); // 先清空内容

				// append textarea and button
				$content.append(textareaContainerElem);
				$content.append(buttonContainerElem);

				console.log($content.find('textarea').css('height', 300));

				// 设置 input val
				const value = editor.getHtml();
				$textarea.val(value);

				// focus 一个 input（异步，此时 DOM 尚未渲染）
				setTimeout(() => {
					$textarea.focus()
				});

				return $content[0];
				// PS：也可以把 $container 缓存下来，这样不用每次重复创建、重复绑定事件，优化性能
			}

			editHtml(editor, value) {
				if (!value) return;

				// 还原选区
				editor.restoreSelection();

				if (this.isDisabled(editor)) return;

				editor.clear();
				editor.dangerouslyInsertHtml(value)
			}
		}

		const editHtmlConf = {
			key: 'editHtml',
			factory() {
				return new editHtmlMenu();
			}
		};

		wangEditor.Boot.registerMenu(editHtmlConf);
	}

	$(function () {
		const {{ $contentId }}EditorConfig = {
			onChange: function (editor) {
				const html = editor.getHtml();
				$('#{{ $contentId }}Input').val(html)
			},
			MENU_CONF: {
				uploadImage: {
					server: '{{ $uploadUrl }}',
					fieldName: 'image',
					maxFileSize: 50 * 1024 * 1024, // 50M
					maxNumberOfFiles: 10,
					allowedFileTypes: ['image/*'],
					meta: {
						token: '{{ $token }}',
						sign: '{{ $sign }}',
						timestamp: '{{ $timestamp }}'
					},
					customInsert(res, insertFn) {
						if (res.status !== 0) {
							layer.msg(res.message);
							return;
						}
						let url = res.data.url[0];
						insertFn(url, '', '')
					},
				}
			}
		};
		const editor{{ $contentId }} = window.wangEditor.createEditor({
			selector: '#{{ $contentId }}Editor',
			html: '{!! $value !!}',
			config: {{ $contentId }}EditorConfig,
			mode: 'simple', // or 'simple'
		});

		// init value
		$('#{{ $contentId }}Input').val(editor{{ $contentId }}.getHtml());

		const {{ $contentId }}ToolbarConfig = {
			excludeKeys: [
				'headerSelect',
				'emotion',
				'uploadVideo',
				'group-more-style'
			],
			insertKeys: {
				index: 0,
				keys: [
					'editHtml'
				],
			}
		};
		const toolbar{{ $contentId }} = window.wangEditor.createToolbar({
			editor: editor{{ $contentId }},
			selector: '#{{ $contentId }}Toolbar',
			config: {{ $contentId }}ToolbarConfig,
			mode: 'simple', // or 'simple'
		})
	})
</script>