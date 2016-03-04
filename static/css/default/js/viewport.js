/**
 * set viewport
 * Oct. 2013
 * xuximing@snda.com
 */

(function() {
  var head = document.head, ua = navigator.userAgent;
  function addMeta(name, content) {
    var meta = document.createElement('meta');
    meta.setAttribute('name', name);
    meta.setAttribute('content', content);
    head.appendChild(meta);
  }
  function isPhone(ua) {
    var isMobile = /Mobile(\/|\s)/.test(ua);
    // Either:
    // - iOS but not iPad
    // - Android 2
    // - Android with "Mobile" in the UA
    return /(iPhone|iPod)/.test(ua) ||
      (!/(Silk)/.test(ua) && (/(Android)/.test(ua) && (/(Android 2)/.test(ua) || isMobile))) ||
      (/(BlackBerry|BB)/.test(ua) && isMobile) ||
      /(Windows Phone)/.test(ua);
  }
  function isTablet(ua) {
    return !isPhone(ua) && (/iPad/.test(ua) || /Android|Silk/.test(ua) || /(RIM Tablet OS)/.test(ua) ||
      (/MSIE 10/.test(ua) && /; Touch/.test(ua)));
  }
  function isIOS(ua) {
    return /(iPhone|iPod|iPad)/.test(ua);
  }
  function isAdroid(ua) {
    return /(Android)/.test(ua);
  }

    var width=document.documentElement.clientWidth;
    var vwidth=640;
    if (isIOS(ua) && !(/OS 7/.test(ua)) && !(/OS 6/.test(ua))) {

        var scale=Math.min(1, 1-Math.floor((vwidth/width)*10)/10);
        addMeta('viewport', (!window.userNoScale?'':'user-scalable=no, ')+'initial-scale='+scale);
    }else{
        var w=Math.min(vwidth, width);
        addMeta('viewport', (!window.userNoScale?'':'user-scalable=no, ')+'width='+w+', target-densitydpi='+(w/2));
    }
    /*
  if (isIOS(ua)) {
    addMeta('viewport', 'user-scalable=no, initial-scale='+scale);
  } else if (isAdroid(ua)) {
    addMeta('viewport', 'user-scalable=no, width=640, target-densitydpi=320');
  } else {
    addMeta('viewport', 'user-scalable=no, initial-scale=0.5');
  }*/
})();


