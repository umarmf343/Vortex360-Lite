(function($){
  "use strict";

  /**
   * Build a pannellum config object from our tour JSON + options.
   */
  function buildPannellumConfig(tour, opts){
    var defaultHFOV = 110;
    var scenes = {};

    (tour.scenes || []).forEach(function(s, idx){
      var sceneId = s.id || ('scene_' + (idx + 1));
      var hs = [];

      (s.hotspots || []).forEach(function(h){
        var yaw   = parseFloat(h.yaw || 0);
        var pitch = parseFloat(h.pitch || 0);

        if (h.type === 'link' && h.scene) {
          // Scene link hotspot
          hs.push({
            type: 'scene',
            text: (h.title || ''),
            sceneId: h.scene,
            yaw: yaw,
            pitch: pitch
          });
        } else {
          // Info hotspot with custom tooltip
          hs.push({
            yaw: yaw,
            pitch: pitch,
            cssClass: 'vxlite-hs',
            createTooltipFunc: function(hsDiv) {
              hsDiv.classList.add('vxlite-hs-dot');
              hsDiv.innerHTML = '<div class="vxlite-dot"></div>';

              hsDiv.addEventListener('mouseenter', function(){
                var tip = document.createElement('div');
                tip.className = 'vxlite-tip';
                var html = '';
                if (h.title) html += '<strong>'+ escapeHtml(h.title) +'</strong><br>';
                if (h.type === 'text' && h.text) {
                  html += '<span>'+ escapeHtml(h.text) +'</span>';
                }
                if (h.type === 'image' && h.image) {
                  html += '<img src="'+ h.image +'" style="max-width:220px;display:block;margin-top:6px;border-radius:4px">';
                }
                tip.innerHTML = html || ' ';
                document.body.appendChild(tip);
                positionTip(tip, hsDiv);
                hsDiv._vxliteTip = tip;
              });

              hsDiv.addEventListener('mouseleave', function(){
                if (hsDiv._vxliteTip) {
                  hsDiv._vxliteTip.remove();
                  hsDiv._vxliteTip = null;
                }
              });

              hsDiv.addEventListener('mousemove', function(){
                if (hsDiv._vxliteTip) positionTip(hsDiv._vxliteTip, hsDiv);
              });
            }
          });
        }
      });

      scenes[sceneId] = {
        type: 'equirectangular',
        panorama: s.panorama || '',
        hfov: parseFloat(s.hfov || defaultHFOV),
        pitch: parseFloat(s.pitch || 0),
        yaw: parseFloat(s.yaw || 0),
        autoLoad: !!opts.autorotate,
        hotSpots: hs,
        compass: !!opts.compass
      };
    });

    var firstId =
      (tour.scenes && tour.scenes[0] && (tour.scenes[0].id || 'scene_1')) ||
      Object.keys(scenes)[0];

    return {
      "default": {
        firstScene: firstId,
        autoLoad: !!opts.autorotate,
        showControls: !!opts.controls
      },
      scenes: scenes
    };
  }

  /**
   * Tooltip positioning helpers
   */
  function positionTip(tip, anchor) {
    var r = anchor.getBoundingClientRect();
    tip.style.left = (r.left + r.width / 2) + 'px';
    tip.style.top  = (r.top  - 8) + 'px';
  }

  function escapeHtml(s){
    return ('' + s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  /**
   * Build one-row thumbnail strip
   */
  function buildThumbs(containerId, tour, viewer){
    var $row = $('#' + containerId + '-thumbs');
    $row.empty();

    (tour.scenes || []).forEach(function(s, idx){
      var id = s.id || ('scene_' + (idx + 1));
      var $thumb = $('<div class="vxlite-thumb" data-scene="' + id + '"></div>');
      var img = s.thumb ? '<img src="' + s.thumb + '" alt="">' : '';
      var title = s.title ? '<div class="vxlite-thumb-title">' + escapeHtml(s.title) + '</div>' : '';
      $thumb.html(img + title);

      if (idx === 0) $thumb.addClass('active');

      $thumb.on('click', function(){
        $('.vxlite-thumb').removeClass('active');
        $(this).addClass('active');
        viewer.loadScene(id, null, 'fade', 1000);
      });

      $row.append($thumb);
    });
  }

  /**
   * Optional analytics ping (supports both global vxliteVars and per-instance cfg.ajax)
   */
  function pingAnalytics(perInstanceAjax, postId){
    // Prefer per-instance config from shortcode localize
    if (perInstanceAjax && perInstanceAjax.url && perInstanceAjax.nonce && perInstanceAjax.action && postId) {
      var key1 = '__vxlite_ping_instance_'+postId;
      if (!window[key1]) {
        window[key1] = true;
        $.post(perInstanceAjax.url, {
          action: perInstanceAjax.action,
          post_id: postId,
          _ajax_nonce: perInstanceAjax.nonce
        });
      }
      return;
    }

    // Fallback to global vxliteVars
    if (!window.vxliteVars || !window.vxliteVars.ajaxurl || !window.vxliteVars.nonce || !postId) return;
    var key2 = '__vxlite_ping_'+postId;
    if (window[key2]) return;
    window[key2] = true;
    $.post(window.vxliteVars.ajaxurl, {
      action: 'vxlite_ping',
      post_id: postId,
      _ajax_nonce: window.vxliteVars.nonce
    });
  }

  /**
   * Init for every shortcode container on page
   */
  $(function(){
    $('.vxlite-viewer').each(function(){
      var el  = this;
      var id  = el.id;
      var cfg = window[id];      // localized per-instance payload from PHP

      if (!cfg || !cfg.tour) return;

      var tour = cfg.tour || {};
      var opts = cfg.options || {};

      // Build Pannellum instance
      var pann = buildPannellumConfig(tour, opts);
      var viewer = pannellum.viewer(el, {
        "default": pann["default"],
        "scenes": pann.scenes,
        autoRotate: opts.autorotate ? -2 : 0, // gentle rotate
        showControls: !!opts.controls,
        compass: !!opts.compass
      });

      // Thumbnails row
      if (opts.thumbnails) {
        buildThumbs(id, tour, viewer);
      }

      // Optional analytics ping
      var postId = (cfg.ajax && cfg.ajax.pid) ? parseInt(cfg.ajax.pid, 10) : 0;
      pingAnalytics(cfg.ajax || null, postId);
    });
  });

})(jQuery);
