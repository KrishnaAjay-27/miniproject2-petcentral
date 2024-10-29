(function() {
  //Global app embed
  function loadScript(src, defer, done) {
    var js = document.createElement('script');
    js.src = src;
    js.defer = defer;
    js.onload = function(){done();};
    js.onerror = function(){
      done(new Error('Failed to load script ' + src));
    };
    document.head.appendChild(js);
  }

  function browserSupportsAllFeatures() {
    return window.Promise && window.fetch && window.Symbol;
  }

  if (browserSupportsAllFeatures()) {
    main();
  } else {
    loadScript('https://polyfill.io/v3/polyfill.min.js?features=Promise,fetch', true, main);
  }

  function loadAppScripts(){
    loadScript(window.Pop.global_config.asset_urls.widgets.init_js, true, function(){});
  }

  function main(err) {
    loadScript(window.Pop.global_config.asset_urls.global.helper_js, false, loadAppScripts);
  }
})();
