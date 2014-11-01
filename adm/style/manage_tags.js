phpbb.addAjaxCallback('topictags.delete_tag', function(data) {
	$(this).parent().parent().remove();
});

$('.topictags_edit_tag').click(function(e) {
	e.preventDefault();
	$(this).parent().parent().find('.topictags_editable_tag').trigger("click");
});

$('.topictags_editable_tag').editable(function(value, settings) {
	var tag = $(this);
	var tag_count = tag.parent().parent().find('.tag_count');
	var url = window.location.href.split("#")[0] + '&action=edit';
	var phpbb_indicator = $('#loading_indicator');
	phpbb_indicator.show();
	$.post(url, {
		old_tag_name : this.revert,
		new_tag_name : value,
	}).done(function(data) {
		if (data.success) {
			if (undefined !== data.tag_count) {
				// tag might be merged -> update tag_count
				tag_count.text(data.tag_count);
			}
		} else {
			if (undefined == data.error_msg) {
				data.error_msg = 'unknown error';
			}
			alert(data.error_msg);
			if (undefined !== data.old_tag) {
				tag.text(data.old_tag);
			}
		}
	}).always(function() {
		phpbb_indicator.hide();
	});
	return (value);
}, {
	type : 'text',
	submit : 'OK',
	cancel : 'X',
});
