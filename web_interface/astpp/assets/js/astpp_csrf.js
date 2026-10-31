/*
 * CSRF token in the browser. Counterpart of application/hooks/csrf_guard.php,
 * which puts this script and the meta tag "astpp-csrf" first into <head> of every page.
 *  - every AJAX request to this host (XMLHttpRequest, so also jQuery, facebox, flexigrid, and fetch)
 *    carries the header X-CSRF-Token;
 *  - POST forms without the token field get it on submit (forms built by script, form.submit());
 *  - links and script navigations to state-changing GET actions get the token as query parameter:
 *    clicks on links automatically, script code calls astpp_csrf_url(url);
 *  - a request refused by the server (HTTP 403, header X-CSRF-Failed) shows the message and reloads the page.
 */
(function (window, document) {
	'use strict';
	var meta = document.querySelector('meta[name="astpp-csrf"]');
	if (!meta || window.astpp_csrf_url) {
		return;
	}
	var token = meta.getAttribute('content');
	var conf = {};
	try {
		conf = JSON.parse(meta.getAttribute('data-conf') || '{}');
	} catch (e) {}
	var field = conf.field || 'astpp_csrf';
	var patterns = (conf.get || []).map(function (s) { return new RegExp(s); });
	var harmless = conf.harmless || [];

	function parse(url) {
		try {
			return new URL(url, window.location.href);
		} catch (e) {
			return null;
		}
	}
	function sameOrigin(url) {
		var u = parse(url);
		return !!u && u.origin === window.location.origin;
	}
	function isProtected(url) {
		var u = parse(url);
		if (!u || u.origin !== window.location.origin) {
			return false;
		}
		var seg = u.pathname.replace(/^\/+/, '').toLowerCase().split('/');
		if (!seg[0]) {
			return false;
		}
		var key = seg[0] + '/' + (seg[1] || 'index');
		if (harmless.indexOf(key) >= 0) {
			return false;
		}
		return patterns.some(function (re) { return re.test(key); });
	}

	/** URL with the token if it is a state-changing GET action of this portal. */
	window.astpp_csrf_url = function (url) {
		url = String(url);
		if (!isProtected(url) || url.indexOf(field + '=') >= 0) {
			return url;
		}
		var i = url.indexOf('#');
		var base = i >= 0 ? url.slice(0, i) : url;
		var hash = i >= 0 ? url.slice(i) : '';
		return base + (base.indexOf('?') >= 0 ? '&' : '?') + field + '=' + encodeURIComponent(token) + hash;
	};

	var warned = false;
	function refused(xhr) {
		if (warned || xhr.status !== 403 || !xhr.getResponseHeader('X-CSRF-Failed')) {
			return;
		}
		warned = true;
		var msg = 'This page has expired. Please reload the page and try again.';
		try {
			msg = JSON.parse(xhr.responseText).error || msg;
		} catch (e) {}
		window.alert(msg);
		window.location.reload();
	}

	var open = XMLHttpRequest.prototype.open;
	var send = XMLHttpRequest.prototype.send;
	XMLHttpRequest.prototype.open = function (method, url) {
		this._astppCsrf = sameOrigin(url);
		return open.apply(this, arguments);
	};
	XMLHttpRequest.prototype.send = function () {
		if (this._astppCsrf) {
			try {
				this.setRequestHeader('X-CSRF-Token', token);
			} catch (e) {}
			var xhr = this;
			this.addEventListener('load', function () { refused(xhr); });
		}
		return send.apply(this, arguments);
	};
	if (window.fetch) {
		var fetch0 = window.fetch;
		window.fetch = function (input, init) {
			if ((typeof input === 'string' || input instanceof URL) && sameOrigin(input)) {
				init = init || {};
				var headers = new Headers(init.headers || {});
				headers.set('X-CSRF-Token', token);
				init.headers = headers;
			}
			return fetch0.call(this, input, init);
		};
	}

	function addField(form) {
		if (!form || form.nodeName !== 'FORM' || (form.getAttribute('method') || '').toLowerCase() !== 'post') {
			return;
		}
		if (!sameOrigin(form.getAttribute('action') || window.location.href)) {
			return;
		}
		for (var i = 0; i < form.elements.length; i++) {
			if (form.elements[i].name === field) {
				return;
			}
		}
		var input = document.createElement('input');
		input.type = 'hidden';
		input.name = field;
		input.value = token;
		form.appendChild(input);
	}
	document.addEventListener('submit', function (e) { addField(e.target); }, true);
	var submit0 = HTMLFormElement.prototype.submit;
	HTMLFormElement.prototype.submit = function () {
		addField(this);
		return submit0.apply(this, arguments);
	};

	// links: before the browser follows them (left, middle click, keyboard)
	function link(e) {
		var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
		if (!a) {
			return;
		}
		var href = a.getAttribute('href');
		if (!href || href.charAt(0) === '#' || /^javascript:/i.test(href)) {
			return;
		}
		var next = window.astpp_csrf_url(a.href);
		if (next !== a.href) {
			a.setAttribute('href', next);
		}
	}
	document.addEventListener('click', link, true);
	document.addEventListener('auxclick', link, true);
})(window, document);
