jQuery(document).ready(function($) {
	if($('.edd-store-hours').length ) {
		var dateFormat = 'mm/dd/yy';
		$('.edd-store-hours').datetimepicker({
			timeOnly: true,
			showTime: false,
			timeFormat: 'h:mm tt'
		});
	}
    $('.edd-store-hours').clearable();

    /*jQuery("select[name='edd_settings[edd_store_hours_hide_buttons]']").change(function () {
        var selectedItem = jQuery("select[name='edd_settings[edd_store_hours_hide_buttons]'] option:selected");

        if (selectedItem.val() === 'true') {
            jQuery("input[name='edd_settings[edd_store_hours_closed_label]']").closest('tr').css('display', 'none');
        } else {
            jQuery("input[name='edd_settings[edd_store_hours_closed_label]']").closest('tr').css('display', 'table-row');
        }
    }).change();*/
});
