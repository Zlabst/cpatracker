function modufy_links() {
	var subid = '';
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	var parents = <? 
		if(array_key_exists('cpa_parents', $_COOKIE)) {
			$parents = json_decode($_COOKIE['cpa_parents'], true);
		} else {
			$parents = array();
		}
		echo json_encode($parents);
	?>;
	
	for(var i = 0; i < hashes.length; i++) {
	    hash = hashes[i].split('=');
	    vars.push(hash[0]);
	    vars[hash[0]] = hash[1];
	}
	
	// try get SubID from URL
	if(vars['subid']) {
		subid = vars['subid'];
		
	// try get SubID from tracker cookie
	} else if(parents[window.location.host]) {
		subid = parents[window.location.host];
	
	// try get SubID from our cookie
	} else {
		
		var cookie = " " + document.cookie;
		var search = "cpa_subid=";
		var setStr = null;
		var offset = 0;
		var end = 0;
		if (cookie.length > 0) {
			offset = cookie.indexOf(search);
			if (offset != -1) {
				offset += search.length;
				end = cookie.indexOf(";", offset)
				if (end == -1) {
					end = cookie.length;
				}
				subid = unescape(cookie.substring(offset, end));
			}
		}
	}
	
	if(subid != '') {
		
		var domain_name = domain_name=window.location.hostname;
		if (domain_name.split('.')[0]=='www') {
			domain_name = domain_name.substring(4);
		}
		
		var exp = new Date();
		var cookie_time=exp.getTime() + (365*10*24*60*60*1000);
		document.cookie = "cpa_subid="+subid+";path=/;domain=."+domain_name+";expires="+cookie_time;
		
		var host = '<?=$_SERVER['HTTP_HOST']?>';
		var node = document.getElementsByTagName("body")[0];
		var els = node.getElementsByTagName("a");

		for(var i=0,j=els.length; i<j; i++) {
		  href = els[i].href;
		  if(href.indexOf(host) != -1 && href.indexOf('_subid=') == -1) {
		    divider = href.indexOf('?') == -1 ? '?' : '&';
		    els[i].href = els[i].href + divider + '_subid=' + subid;
		  }
		}
	}
}

(function(){
    var DomReady = window.DomReady = {};
    var userAgent = navigator.userAgent.toLowerCase();

    var browser = {
    	version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
    	safari: /webkit/.test(userAgent),
    	opera: /opera/.test(userAgent),
    	msie: (/msie/.test(userAgent)) && (!/opera/.test( userAgent )),
    	mozilla: (/mozilla/.test(userAgent)) && (!/(compatible|webkit)/.test(userAgent))
    };    

	var readyBound = false;	
	var isReady = false;
	var readyList = [];

	function domReady() {
		if(!isReady) {
			isReady = true;
	        if(readyList) {
	            for(var fn = 0; fn < readyList.length; fn++) {
	                readyList[fn].call(window, []);
	            }
	            readyList = [];
	        }
		}
	};
	
	function addLoadEvent(func) {
	  var oldonload = window.onload;
	  if (typeof window.onload != 'function') {
	    window.onload = func;
	  } else {
	    window.onload = function() {
	      if (oldonload) {
	        oldonload();
	      }
	      func();
	    }
	  }
	};

	function bindReady() {
		if(readyBound) {
		    return;
	    }
		readyBound = true;

		if (document.addEventListener && !browser.opera) {
			document.addEventListener("DOMContentLoaded", domReady, false);
		}

		if (browser.msie && window == top) (function(){
			if (isReady) return;
			try {
				document.documentElement.doScroll("left");
			} catch(error) {
				setTimeout(arguments.callee, 0);
				return;
			}
		    domReady();
		})();

		if(browser.opera) {
			document.addEventListener( "DOMContentLoaded", function () {
				if (isReady) return;
				for (var i = 0; i < document.styleSheets.length; i++)
					if (document.styleSheets[i].disabled) {
						setTimeout( arguments.callee, 0 );
						return;
					}
	            domReady();
			}, false);
		}

		if(browser.safari) {
		    var numStyles;
			(function(){
				if (isReady) return;
				if (document.readyState != "loaded" && document.readyState != "complete") {
					setTimeout( arguments.callee, 0 );
					return;
				}
				if (numStyles === undefined) {
	                var links = document.getElementsByTagName("link");
	                for (var i=0; i < links.length; i++) {
	                	if(links[i].getAttribute('rel') == 'stylesheet') {
	                	    numStyles++;
	                	}
	                }
	                var styles = document.getElementsByTagName("style");
	                numStyles += styles.length;
				}
				if (document.styleSheets.length != numStyles) {
					setTimeout( arguments.callee, 0 );
					return;
				}
			
				domReady();
			})();
		}

	    addLoadEvent(domReady);
	};

	DomReady.ready = function(fn, args) {
		bindReady();
		if (isReady) {
			fn.call(window, []);
	    } else {
	        readyList.push( function() { return fn.call(window, []); } );
	    }
	};
	bindReady();	
})();

DomReady.ready(function() {
	modufy_links();
});