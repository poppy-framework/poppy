<script src="/assets/libs/boot/wangeditor@5.1.js"></script>
<div id="$contentId" style="border: 1px solid #ccc;z-index:100;">
    <div id="{{ $contentId }}Toolbar" style="border-bottom:1px solid #ccc;"><!-- 工具栏 --></div>
    <div id="{{ $contentId }}Editor" style="height:500px;"><!-- 编辑器 --></div>
</div>
<input type="hidden" id="{{ $contentId }}Input" name="{{$name}}">

<script>
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
			html: '{{$value}}',
			config: {{ $contentId }}EditorConfig,
			mode: 'simple', // or 'simple'
		});

		// init value
		const html{{ $contentId }} = editor{{ $contentId }}.getHtml();
		$('#{{ $contentId }}Input').val(html{{ $contentId }});

		const {{ $contentId }}ToolbarConfig = {
			excludeKeys: [
				'headerSelect',
				'emotion',
				'uploadVideo',
				'group-more-style'
			],
			// insertKeys: {
			// 	index: 0,
			// 	keys: [
			// 		'editHtml'
			// 	],
			// }
		};
		const toolbar{{ $contentId }} = window.wangEditor.createToolbar({
			editor: editor{{ $contentId }},
			selector: '#{{ $contentId }}Toolbar',
			config: {{ $contentId }}ToolbarConfig,
			mode: 'simple', // or 'simple'
		})
	})
</script>