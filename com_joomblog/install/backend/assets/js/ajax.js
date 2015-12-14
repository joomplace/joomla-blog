// AJAX Admin Interface
delete window.listItemTask;
function listItemTask(id, action) {
	new Event().stop();
	temp = action.split('.', 2);
	var cont = temp[0];
	var task = temp[1];

	var handler = getHandler();

	var form = document.adminForm;
	var target = form[id];
	if (target) {
		for (var c = 0; true; c++) {
			var e = form["cb" + c];
			if (!e) {
				break
			}
			e.checked = false
		}
		target.checked = true;
		form.boxchecked.value = 1;
		form.task.value = action;
		
		var req = new Request({
			url: form.action,
			method: form.method,
			onRequest: function() {
				handler.addClass('loading');
			},
			onSuccess: function(responseText){
				handler.removeClass('loading');
				list.envoke(id, handler, cont, task);
			},
			onFailure: function(){
				alert('Sorry, your request failed :(');
			}
		}).send(form.toQueryString());

		target.checked = false;
	}

	return false
}

function getHandler() {
	e = window.event.target;
    return e;
}

window.handler = '';
window.addEvent('domready', function() {
	actions = $$('a.grid_true');
	actions.combine($$('a.grid_false'));
	actions.each(function(a) {
		a.addEvent('click', function(e) {
			new Event(e).stop();
			window.handler = this;

			var args = JSON.decode(handler.rel);
			listItemTask(args.id, args.task);
		});
	});
});

var list = {
	envoke : function(cid, handler, cont, task) {
		list.controller = cont;
		list.cid = cid;
		list.handler = handler;

		if (typeof window['list'][task] == 'function') {
			window['list'][task]();
		}
	},

	publish : function () {
		list.handler.removeClass('unpublish').addClass('publish');

		var ch = list.handler.getChildren('span');
		ch.set('text', 'Published');

		var pr = list.handler.getParent();
		pr.set('onclick', 'return listItemTask("'+list.cid+'","'+list.controller+'.unpublish")');
		pr.title = 'Unpublish Item';
	},

	unpublish : function () {
		list.handler.removeClass('publish').addClass('unpublish');

		var ch = list.handler.getChildren('span');
		ch.set('text', 'Unpublished');
		
		var pr = list.handler.getParent();
		pr.set('onclick', 'return listItemTask("'+list.cid+'","'+list.controller+'.publish")');
		pr.title = 'Publish Item';
	},

	defaults : function () {
		list.handler.removeClass('grid_false').addClass('grid_true');
		list.handler.rel = '{id:"'+list.cid+'", task:"'+list.controller+'.nodefault"}';
	},

	nodefault : function() {
		list.handler.removeClass('grid_true').addClass('grid_false');
		list.handler.rel = '{id:"'+list.cid+'", task:"'+list.controller+'.defaults"}';
	}
}