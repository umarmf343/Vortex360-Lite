(function($){
  // Very small placeholder viewer:
  // Renders first scene's panorama as a background image and shows simple hotspots (absolute % via yaw/pitch ~ crude).
  // You can later replace this with Pannellum; this keeps the shortcode functional immediately.

  $(function(){
    $('.vxlite-viewer').each(function(){
      var el = $(this)[0];
      var key = el.id; // localized object name
      if (!window[key]) return;

      var cfg = window[key];
      var $wrap = $(el);
      var tour = cfg.tour || {};
      var scenes = Array.isArray(tour.scenes) ? tour.scenes : [];
      if (!scenes.length) return;

      var scene = scenes[0];
      if (scene.panorama) {
        $wrap.css({
          backgroundImage: 'url(' + scene.panorama + ')',
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        });
      }

      // Fake projection: yaw(-180..180), pitch(-90..90) -> percentage box
      function toXY(yaw, pitch){
        var x = (yaw + 180) / 360 * 100;
        var y = (pitch + 90) / 180 * 100;
        return {x:x, y:y};
      }

      (scene.hotspots || []).forEach(function(h){
        var pos = toXY(parseFloat(h.yaw||0), parseFloat(h.pitch||0));
        var $hs = $('<div class="vxlite-hotspot" />').text(h.title || '•');
        $hs.css({ left: pos.x+'%', top: pos.y+'%' });
        $wrap.append($hs);
      });

      if (cfg.options && cfg.options.controls) {
        var $ctrl = $('<div class="vxlite-controls" />');
        var $fs = $('<button class="vxlite-btn" type="button">⛶</button>');
        $fs.on('click', function(){
          if (!$wrap[0].requestFullscreen) return;
          $wrap[0].requestFullscreen();
        });
        $ctrl.append($fs);
        $wrap.append($ctrl);
      }
    });
  });
})(jQuery);
