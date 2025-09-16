(function($){
  function buildPannellumConfig(containerId, tour, opts){
    var defaultHFOV = 110;
    var scenes = {};
    (tour.scenes || []).forEach(function(s, idx){
      var sceneId = s.id || ('scene_' + (idx+1));
      var hs = [];
      (s.hotspots || []).forEach(function(h){
        var yaw = parseFloat(h.yaw || 0);
        var pitch = parseFloat(h.pitch || 0);
        if (h.type === 'link' && h.scene) {
          hs.push({ type:'scene', text:(h.title||''), sceneId:h.scene, yaw:yaw, pitch:pitch });
        } else {
          hs.push({
            yaw: yaw, pitch: pitch, cssClass: 'vxlite-hs',
            createTooltipFunc: function(hsDiv) {
              hsDiv.classList.add('vxlite-hs-dot');
              hsDiv.innerHTML = '<div class="vxlite-dot"></div>';
              hsDiv.addEventListener('mouseenter', function(){
                var tip = document.createElement('div');
                tip.className = 'vxlite-tip';
                var html = '';
                if (h.title) html += '<strong>'+ escapeHtml(h.title) +'</strong><br>';
                if (h.type === 'text' && h.text) html += '<span>'+ escapeHtml(h.text) +'</span>';
                if (h.type === 'image' && h.image) html += '<img src="'+ h.image +'" style="max-width:220px;display:block;margin-top:6px;border-radius:4px">';
                tip.innerHTML = html || ' ';
                document.body.appendChild(tip);
                positionTip(tip, hsDiv);
                hsDiv._vxliteTip = tip;
              });
              hsDiv.addEventListener('mouseleave', function(){
                if (hsDiv._vxliteTip) { hsDiv._vxliteTip.remove(); hsDiv._vxliteTip = null; }
              });
              hsDiv.addEventListener('mousemove', function(){ if (hsDiv._vxliteTip) positionTip(hsDiv._vxliteTip, hsDiv); });
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

    var firstId = (tour.scenes && tour.scenes[0] && (tour.scenes[0].id || 'scene_1')) || Object.keys(scenes)[0];

    return { default: { firstScene: firstId, autoLoad: !!opts.autorotate, showControls: !!opts.controls }, scenes: scenes };
  }

  function positionTip(tip, anchor) {
    var r = anchor.getBoundingClientRect();
    tip.style.left = (r.left + r.width/2) + 'px';
    tip.style.top  = (r.top - 8) + 'px';
  }

  function escapeHtml(s){
    return (''+s)
      .replaceAll('&','&amp;').replaceAll('<','&lt;')
      .replaceAll('>','&gt;').replaceAll('"','&quot;')
      .replaceAll("'",'&#039;');
  }

  function buildThumbs(containerId, tour, viewer){
    var $row = $('#'+containerId+'-thumbs');
    $row.empty();
    (tour.scenes || []).forEach(function(s, idx){
      var id = s.id || ('scene_'+(idx+1));
      var $thumb = $('<div class="vxlite-thumb" data-scene="'+id+'"></div>');
      var img = s.thumb ? '<img src="'+ s.thumb +'" alt="">' : '';
      var title = s.title ? '<div class="vxlite-thumb-title">'+ escapeHtml(s.title) +'</div>' : '';
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

  function pingAnalytics(postId){
    if (!postId || !window.vxliteVars || !vxliteVars.ajaxurl) return;
    // Ping only once per page load per postId
    var key = '__vxlite_ping_'+postId;
    if (window[key]) return;
    window[key] = true;
    $.post(vxliteVars.ajaxurl, {
      action: 'vxlite_ping',
      post_id: postId,
      _ajax_nonce: vxliteVars.nonce
    });
  }

  $(function(){
    $('.vxlite-viewer').each(function(){
      var el = this;
      var key = el.id;
      if (!window[key]) return;

      var cfg  = window[key];
      var tour = cfg
