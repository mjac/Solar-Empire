function TickAll(form_name)
{
	var alpha = document.getElementById(form_name);
	var len = alpha.elements.length;
	for (var index = 0; index < len; ++index) {
		if (alpha.elements[index].name != 'fleet_type' &&
		     alpha.elements[index].name != 'fleet_manip') {
			alpha.elements[index].checked = !alpha.elements[index].checked;
		}
	}
}

function popup(url, width, height)
{
	window.open(url, "Help", "resizable=yes, toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,width=" + width + ",height=" + height);
}