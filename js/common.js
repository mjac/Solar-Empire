function TickAll(form_name)
{
	alpha = eval("document."+form_name);
	len = alpha.elements.length;
	var index = 0;
	for( index=0; index < len; index++ ) {
		if(alpha.elements[index].name != 'fleet_type' && alpha.elements[index].name != 'fleet_manip'){
			if(alpha.elements[index].checked == false) {
				alpha.elements[index].checked = true;
			} else {
				alpha.elements[index].checked = false;
			}
		}
	}
}
function popup( url, width, height) {
	window.open( url,"Help","resizable=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,width="+width+",height="+height);
}