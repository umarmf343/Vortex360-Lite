(function($){
  "use strict";

  function getJSONEl(){ return $('textarea.vxlite-json'); }

  function parseJSON(){
    const el = getJSONEl();
    const raw = el.val().trim();
    return raw ? JSON.parse(raw) : { scenes: [] };
  }

  $('#vxlite-pretty').on('click', function(){
    try {
      const obj = parseJSON();
      getJSONEl().val(JSON.stringify(obj, null, 2));
    } catch(e){
      alert('Invalid JSON');
    }
  });

  $('#vxlite-validate').on('click', function(){
    try {
      const obj = parseJSON();
      if (!Array.isArray(obj.scenes)) throw new Error('Missing "scenes" array');
      if (obj.scenes.length > 5) throw new Error('Lite: max 5 scenes per tour');
      obj.scenes.forEach(function(s,i){
        if (!s.id) throw new Error('Scene #'+(i+1)+' missing "id"');
        if (!s.panorama) throw new Error('Scene #'+(i+1)+' missing "panorama" URL');
        if (s.hotspots && s.hotspots.length > 5) throw new Error('Lite: max 5 hotspots per scene (scene '+(s.id||i+1)+')');
      });
      alert('Looks valid ✅');
    } catch(e){
      alert(e && e.message ? e.message : 'Invalid JSON');
    }
  });

  $('#vxlite-add-scene').on('click', function(){
    try {
      const el = getJSONEl();
      const obj = parseJSON();
      obj.scenes = obj.scenes || [];
      if (obj.scenes.length >= 5) { alert('Lite: max 5 scenes'); return; }
      const n = obj.scenes.length + 1;
      obj.scenes.push({
        id: 'scene-' + n,
        title: 'Scene ' + n,
        panorama: '',
        thumb: '',
        hfov: 110,
        pitch: 0,
        yaw: 0,
        hotspots: []
      });
      el.val(JSON.stringify(obj, null, 2));
    } catch(e){
      alert('Invalid JSON');
    }
  });

  $('#vxlite-add-hotspot').on('click', function(){
    try {
      const el = getJSONEl();
      const obj = parseJSON();
      if (!obj.scenes || !obj.scenes.length) { alert('Add a scene first'); return; }
      const s = obj.scenes[obj.scenes.length - 1];
      s.hotspots = s.hotspots || [];
      if (s.hotspots.length >= 5) { alert('Lite: max 5 hotspots per scene'); return; }
      s.hotspots.push({
        type: 'text',
        title: 'Hotspot ' + (s.hotspots.length + 1),
        text: '',
        image: '',
        url: '',
        yaw: 0,
        pitch: 0,
        scene: ''
      });
      el.val(JSON.stringify(obj, null, 2));
    } catch(e){
      alert('Invalid JSON');
    }
  });

})(jQuery);
