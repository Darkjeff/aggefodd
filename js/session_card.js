$(document).ready(function() {
	displayCustomerFields($('#type_session').val());
	$('#type_session').on('change', function (e) {
		displayCustomerFields($(this).val());
	});
});
function displayCustomerFields(type_session) {
	if (type_session == 1) {
		$('.order_customer').hide();
		$('#fk_soc').val(-1).change();
		$('.order_sessionContact').hide();
		$('#contact').val('0').change();
	} else {
		$('.order_customer').show();
		$('.order_sessionContact').show();
	}
}
