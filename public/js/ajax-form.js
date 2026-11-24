/**
 * BIS Admin: ajax forms.
 *
 * Sends a <form data-ajax-form> with fetch and shows Laravel's answer without
 * reloading the page. Without JavaScript the form still posts the normal way.
 *
 * Form attributes:
 *   data-ajax-form            turns it on
 *   data-success="#selector"  element for the success message (optional)
 *   data-error="#selector"    element for errors that belong to no field (optional)
 *   data-error-class="show"   class added to an element to show it (default "show")
 *   data-hidden-class="d-none" class removed from an element to show it (optional)
 *   data-reset                clear the form after a success (default for forms with data-success)
 *   data-confirm="Question?"  ask before sending (e.g. delete buttons)
 *
 * Field errors go in an element with data-error-for="<field name>"; the field
 * itself gets the class "is-invalid" (Bootstrap) while it has an error.
 *
 * The server answers JSON: {message, redirect?} on success, Laravel's
 * {message, errors: {field: [..]}} on a 422, and {message} on other errors.
 */
(function () {
	'use strict';

	var GENERIC_ERROR = 'Something went wrong. Please try again.';
	var EXPIRED = 'Your session has expired. Please reload the page and try again.';

	function find(form, selector) {
		return selector ? document.querySelector(selector) : null;
	}

	// cls is {show: class to add, hidden: class to remove} when an element is shown.
	function show(el, text, cls) {
		if (!el) { return; }
		el.textContent = text;
		if (cls.show) { el.classList.add(cls.show); }
		if (cls.hidden) { el.classList.remove(cls.hidden); }
	}

	function hide(el, cls) {
		if (!el) { return; }
		el.textContent = '';
		if (cls.show) { el.classList.remove(cls.show); }
		if (cls.hidden) { el.classList.add(cls.hidden); }
	}

	function fieldsNamed(form, name) {
		// "tags.0" from Laravel matches the field "tags[]" or "tags[0]"
		var base = name.split('.')[0];
		return form.querySelectorAll('[name="' + name + '"], [name="' + base + '"], [name="' + base + '[]"]');
	}

	function clearField(form, name, cls) {
		hide(form.querySelector('[data-error-for="' + name + '"]'), cls);
		fieldsNamed(form, name).forEach(function (field) { field.classList.remove('is-invalid'); });
	}

	function clearAll(form, cls) {
		form.querySelectorAll('[data-error-for]').forEach(function (el) { hide(el, cls); });
		form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
	}

	function init(form) {
		var cls = {
			show: form.getAttribute('data-error-class') || 'show',
			hidden: form.getAttribute('data-hidden-class') || ''
		};
		var success = find(form, form.getAttribute('data-success'));
		var error = find(form, form.getAttribute('data-error'));
		var reset = form.hasAttribute('data-reset') || !!success;
		var busy = false;

		// An edited field loses its old error at once.
		form.addEventListener('input', function (event) {
			if (event.target.name) { clearField(form, event.target.name.replace(/\[\]$/, ''), cls); }
		});

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			if (busy) { return; }
			if (form.hasAttribute('data-confirm') && !window.confirm(form.getAttribute('data-confirm'))) { return; }
			busy = true;

			var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
			buttons.forEach(function (button) { button.disabled = true; });
			clearAll(form, cls);
			hide(success, cls);
			hide(error, cls);

			fetch(form.action, {
				method: 'POST', // Laravel reads PUT/PATCH/DELETE from the _method field
				body: new FormData(form),
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
			}).then(function (response) {
				return response.json().catch(function () { return {}; }).then(function (data) {
					return { status: response.status, ok: response.ok, data: data };
				});
			}).then(function (result) {
				var data = result.data || {};

				if (result.ok) {
					if (data.redirect) { window.location.assign(data.redirect); return; }
					if (reset) { form.reset(); }
					show(success, data.message || 'Saved.', cls);
					return;
				}

				if (result.status === 422 && data.errors) {
					var first = null;
					Object.keys(data.errors).forEach(function (name) {
						var message = data.errors[name][0];
						var target = form.querySelector('[data-error-for="' + name + '"]') ||
							form.querySelector('[data-error-for="' + name.split('.')[0] + '"]');
						var fields = fieldsNamed(form, name);

						fields.forEach(function (field) { field.classList.add('is-invalid'); });
						if (!first && fields.length) { first = fields[0]; }

						if (target) {
							show(target, message, cls);
						} else {
							show(error, message, cls);
						}
					});
					if (first) { first.focus(); }
					return;
				}

				show(error, result.status === 419 ? EXPIRED : (data.message || GENERIC_ERROR), cls);
			}).catch(function () {
				show(error, GENERIC_ERROR, cls);
			}).then(function () {
				busy = false;
				buttons.forEach(function (button) { button.disabled = false; });
			});
		});
	}

	function start() {
		document.querySelectorAll('form[data-ajax-form]').forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
