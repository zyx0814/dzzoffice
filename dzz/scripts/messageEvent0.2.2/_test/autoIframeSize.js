(function (messageEvent, document) {

var getStyle = function (elem, name) {
	return elem.currentStyle ?
	elem.currentStyle[name] :
	elem.ownerDocument.defaultView.getComputedStyle(elem, null)[name];
};

var getDocumentSize = function (type) {
	var margin = type === 'width' ? 'Right' : 'Top';
	type = type === 'width' ? 'Width' : 'Height';
	return Math.max(
		document.documentElement['client' + type],
		document.body['scroll' + type], document.documentElement['scroll' + type],
		document.body['offset' + type], document.documentElement['offset' + type]
	) + 2 * parseFloat(getStyle(document.body, 'margin' + margin));
};

messageEvent.autoIframeSize = function () {
	//var width = getDocumentSize('width');
	var height = getDocumentSize('height');
	
	messageEvent.postMessage(parent, {
		autoIframeSize: {
			name: window.name,
			//width: width,
			height: height
		}
	}, '*');
};

messageEvent.add(function (event) {
	var data = event.data;
	if (typeof data !== 'object' || !data.autoIframeSize) {
		return;
	};
	
	var autoIframeSize = data.autoIframeSize;
	var iframe;
	var iframes = document.getElementsByTagName('iframe');
	var ileng = iframes.length;
	
	for (var i = 0; i < ileng; i ++) {
		if (iframes[i].contentWindow == event.source) {
			iframe = iframes[i];
		};
	};

	if (iframe) {
		//iframe.style.width = autoIframeSize.width + 'px';
		iframe.style.height = autoIframeSize.height + 'px';
	};
});

})(this.messageEvent, document);