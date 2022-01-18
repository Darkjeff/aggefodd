// transforms the input of a form into a key value array,
function objectifyForm(formArray) {
	var returnArray = {};
	for (var i = 0; i < formArray.length; i++) {
		if (formArray[i]['name'].substring(formArray[i]['name'].length - 2) == "[]") {
			var name = formArray[i]['name'].substring(0, formArray[i]['name'].length - 2);
			if (!returnArray.hasOwnProperty(name)) returnArray[name] = [];
			returnArray[name].push(formArray[i]['value']);
		} else returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}


