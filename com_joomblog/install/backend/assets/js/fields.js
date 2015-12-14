function addOption() {
	var pr = $$('label.option-label')[0].getParent();
	var cl = pr.clone();
	var im = new Element('img', {src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAABl0RVh0U29mdHdhcmUAUGFpbnQuTkVUIHYzLjUuODc7gF0AAAJuSURBVDhPjVNLSxthFE1AqI9QXDTbQiEiKIL9MXad31DI1m3dlG6qpmCdyXyTydOYmETznGiiec4kk0T3HReuGij0B5ze+xWEkAr94C7mcc8959zzeT0vnJ2dHb/X6/UB8Mxms59PT0+/X/r3n+81TUuPRiP0+n0Eg8GPLzZvbGy8iUQiQuVSVaEoivh+eipKpbLb7fZwe3cH+m6Fw9/E8XFYHB0dia+Hh2J/f/+zz+db8mxubr57eHjA/f09JpMpxuMxRo6D4XAIy7LRHwzQ7fXQ7nQJrI1m6xbXN00oasQlgBWP3+9/HQqFvpimiXq9jlqtjmq1ikqlinK5AmKCq6sSLi8vUSwWkS8UENG0X3t7H0Jra2tLUtru7vsAs+BiBsxkOp2CPXDomcuyLGI1kswymczj+vr68rMv29vbAZbAVaAJjUaDQCbI5S5gmg0CcqjpHDViaNk20mdnj6urq/MAPHFCdXGRl1IcZ4zz8yxJqsGmqan0mZTFnqRS6UUAnjgeT2QTe8D009RUrlQk/XgiKf3o9fpIJJOLAKzdIfe5qVQuw7aHSFATG9jvDxA1DBTJyA5tIx5PzANsbW0FWCeblEwmyfErDGiqYcSk87xGTRPI5wu4o1UasdgiAOu0yaBYLCaN5AQKIaQn7U4HiqIim8uhRTnQo8YiAIdmMLCgC126zymkZCKbzco0npycyE3cUIiE0BcBWCcbZNIKm60W2u0OKmRm4/pGPrMXtbpJ3685SPMAgUDgbTwe/0H0XdLtRqOGq+tRV+i6qwnhUoNLd8VVVNU9VRT308FBl3Lwau6CLf89K/9ZzyH6A+inGXSQm6+fAAAAAElFTkSuQmCC', onclick: 'removeOption(this);'});
	im.inject(cl);
	cl.getElement('input').value = '';
	cl.inject($$('label.option-label').getLast().getParent(), 'after');
}

function removeOption(el) {
	el.getParent().destroy();
}

window.addEvent('domready', function() {
	var type = $('jform_field_type').addEvent('change', function(event) {
		$$('fieldset.adminform:not(#fieldset-details)').hide();
		$('fieldset-'+this.value).show();
	});
	$('fieldset-'+type.value).show();
});