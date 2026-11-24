/**
 * BIS Admin: property maps (Leaflet 1.9.4 in public/vendor/leaflet, OpenStreetMap tiles).
 *
 * <div data-map
 *      data-center='{"lat":41.38,"lng":2.16,"zoom":13}'   where to open when there are no markers
 *      data-markers='[{"lat":..,"lng":..,"title":"..","url":".."}]'
 *      data-picker='{"lat":"#latitude","lng":"#longitude"}'>   optional: click to set the inputs
 * </div>
 */
(function () {
	'use strict';

	function parse(el, name, fallback) {
		try {
			return JSON.parse(el.getAttribute(name)) || fallback;
		} catch (e) {
			return fallback;
		}
	}

	function popup(marker) {
		var box = document.createElement('div');
		var title = document.createElement(marker.url ? 'a' : 'strong');

		title.textContent = marker.title || '';
		if (marker.url) { title.href = marker.url; title.className = 'fw-semibold'; }
		box.appendChild(title);

		if (marker.subtitle) {
			var sub = document.createElement('div');
			sub.className = 'small text-muted';
			sub.textContent = marker.subtitle;
			box.appendChild(sub);
		}

		return box;
	}

	function init(el) {
		var center = parse(el, 'data-center', { lat: 41.3874, lng: 2.1686, zoom: 13 });
		var markers = parse(el, 'data-markers', []);
		var picker = parse(el, 'data-picker', null);

		var map = L.map(el, { scrollWheelZoom: false }).setView([center.lat, center.lng], center.zoom || 13);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);

		var layer = L.featureGroup().addTo(map);

		markers.forEach(function (marker) {
			if (marker.lat === null || marker.lng === null) { return; }
			L.marker([marker.lat, marker.lng], { title: marker.title || '' }).bindPopup(popup(marker)).addTo(layer);
		});

		if (markers.length === 1) {
			map.setView([markers[0].lat, markers[0].lng], 16);
		} else if (markers.length > 1) {
			map.fitBounds(layer.getBounds(), { padding: [30, 30], maxZoom: 16 });
		}

		if (picker) {
			var latInput = document.querySelector(picker.lat);
			var lngInput = document.querySelector(picker.lng);
			var pin = null;

			var place = function (lat, lng, pan) {
				layer.clearLayers();
				pin = L.marker([lat, lng], { draggable: true }).addTo(layer);
				pin.on('dragend', function () {
					var p = pin.getLatLng();
					set(p.lat, p.lng);
				});
				if (pan) { map.setView([lat, lng], Math.max(map.getZoom(), 15)); }
			};

			var set = function (lat, lng) {
				latInput.value = lat.toFixed(7);
				lngInput.value = lng.toFixed(7);
				latInput.dispatchEvent(new Event('input', { bubbles: true }));
				lngInput.dispatchEvent(new Event('input', { bubbles: true }));
			};

			map.on('click', function (event) {
				place(event.latlng.lat, event.latlng.lng, false);
				set(event.latlng.lat, event.latlng.lng);
			});

			// Typing coordinates moves the pin.
			[latInput, lngInput].forEach(function (input) {
				input.addEventListener('change', function () {
					var lat = parseFloat(latInput.value);
					var lng = parseFloat(lngInput.value);
					if (!isNaN(lat) && !isNaN(lng)) { place(lat, lng, true); }
				});
			});

			if (latInput.value !== '' && lngInput.value !== '') {
				place(parseFloat(latInput.value), parseFloat(lngInput.value), false);
			}
		}

		// The map may start in a hidden or resized container (sidebar toggle, tabs).
		setTimeout(function () { map.invalidateSize(); }, 200);
	}

	function start() {
		if (typeof L === 'undefined') { return; }
		document.querySelectorAll('[data-map]').forEach(init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
