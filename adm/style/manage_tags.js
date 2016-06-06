phpbb.addAjaxCallback('topictags.delete_tag', function(data) {
	$(this).parent().parent().remove();
});

$('.topictags_edit_tag').click(function(e) {
	e.preventDefault();
	$(this).parent().parent().find('.topictags_editable_tag').trigger("click");
});


/**
 * btoa() is not utf8 safe by default
 */
function utf8_to_b64( str ) {
	return window.btoa(encodeURIComponent(str));
}

function b64_to_utf8( str ) {
	return decodeURIComponent(window.atob(str));
}

$('.topictags_editable_tag').editable(function(value, settings) {
	var tag = $(this);
	var tag_count = tag.parent().parent().find('.tag_count');
	var url = window.location.href.split("#")[0] + '&action=edit';
	var phpbb_indicator = $('#loading_indicator');
	var old_tag = this.revert;
	var new_tag = value;
	phpbb_indicator.show();
	$.post(url, {
		old_tag_name : utf8_to_b64(old_tag),
		new_tag_name : utf8_to_b64(new_tag),
	}).done(function(data) {
		if (!(data instanceof Object)) {
			console.log(data);
			data = {success: false};
		}
		if (data.success) {
			if (undefined !== data.tag_count) {
				// tag might be merged -> update tag_count
				tag_count.text(data.tag_count);
			}
			if (undefined !== data.merged && data.merged) {
				// update tag-count of the kept tag if it is on this page
				var new_tag_item = tag.parent().parent().parent().find('.topictags_editable_tag').filter(function() {
				    return $(this).text() === new_tag;
				});
				if (undefined !== new_tag_item) {
					// the tag is on this page -> update its count
					new_tag_item.parent().parent().find('.tag_count').text(data.new_tag_count);
				}
				// remove the old tag row
				tag.parent().parent().remove();
			}
			if (undefined !== data.msg) {
				phpbb.alert('', b64_to_utf8(data.msg));
			}
		} else {
			if (undefined == data.error_msg) {
				data.error_msg = utf8_to_b64(rh_topictags_lang_unknown_error);
			}
			phpbb.alert('', b64_to_utf8(data.error_msg));
			tag.text(old_tag);
		}
	}).fail(function() {
		phpbb.alert(rh_topictags_lang_error, rh_topictags_lang_unknown_error);
		tag.text(old_tag);
	}).always(function() {
		phpbb_indicator.hide();
	});
	return (value);
}, {
	type : 'text',
	submit : rh_topictags_lang_ok,
	cancel : rh_topictags_lang_cancel,
});
