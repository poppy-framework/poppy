<script>
	let $body = $('body');
	let fp = '';

	let fpUrl = '{!! (new \Poppy\System\Action\FpTrack())->getUrl(); !!}'
	if (fpUrl) {
		const fpPromise = import(fpUrl)
			.then(FingerprintJS => FingerprintJS.load());

		fpPromise
			.then(fp => fp.get())
			.then(result => {
				fp = result.visitorId;
				if (fp.length > 0) {
					$.ajax({
						type: 'post',
						url: '{!! route_url('py-mgr-page:backend.home.track') !!}',
						headers: {
							'X-FP': fp
						}
					})
				}
			});
	}

	$body.on('click', '.login_submit', function(e) {
		let request_url = $(this).attr('data-url');
		let $form       = $(this).parents('form');
		if (!$form.length) {
			Util.splash({
				status : 'error',
				msg : '您不在表单范围内， 请添加到表单范围内'
			});
			return false;
		}

		let old_url = $form.attr('action');
		if (!request_url) {
			request_url = old_url;
		}
		// confirm
		let str_confirm = $(this).attr('data-confirm');
		if (str_confirm === 'true') {
			str_confirm = '您确定删除此条目 ?';
		}
		if (str_confirm && !confirm(str_confirm)) return false;

		let data_ajax   = $(this).attr('data-ajax');
		let data_method = $(this).attr('data-method') ? $(this).attr('data-method') : 'post';

		$form.attr('action', request_url);
		$form.attr('method', data_method);

		// 显示 layer 层
		let index = layer.load(0, {shade : false});
		let conf;
		if (( data_ajax === 'false' )) {
			conf = Util.validateConfig({}, false);
			$form.validate(conf);
			$form.submit();
		} else {
			conf = Util.validateConfig({}, true);
			console.log($form.validate(conf));
			let $btn = $(this);
			Util.buttonInteraction($btn, 5);
			$form.ajaxSubmit({
				headers: {'X-FP': fp},
				success : function(data) {
					layer.close(index);
					Util.splash(data);
					Util.buttonInteraction($btn, data)
				}
			});
		}
		// 还原
		$form.attr('action', old_url);
		e.preventDefault();
	});
</script>