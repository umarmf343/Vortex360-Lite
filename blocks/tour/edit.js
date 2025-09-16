( function( wp ) {
  const { __ } = wp.i18n;
  const { useSelect } = wp.data;
  const { PanelBody, ToggleControl, TextControl, SelectControl } = wp.components;
  const { InspectorControls, useBlockProps } = wp.blockEditor;

  wp.blocks.registerBlockType( 'vortex360/tour', {
    edit: function( props ) {
      const { attributes, setAttributes } = props;
      const blockProps = useBlockProps();

      const tours = useSelect( ( select ) => {
        const query = { per_page: 100, status: 'any' };
        try {
          const recs = select( 'core' ).getEntityRecords( 'postType', 'vortex_tour', query ) || [];
          return recs.map( p => ({ label: p.title.rendered, value: p.id }) );
        } catch(e) { return []; }
      }, [] );

      return (
        wp.element.createElement( 'div', blockProps,
          wp.element.createElement( InspectorControls, {},
            wp.element.createElement( PanelBody, { title: __('Tour Settings','vortex360-lite'), initialOpen: true },
              wp.element.createElement( SelectControl, {
                label: __('Select Tour','vortex360-lite'),
                value: attributes.postId || 0,
                options: [ {label: __('— Select —','vortex360-lite'), value: 0 } ].concat( tours ),
                onChange: (val) => setAttributes({ postId: parseInt(val, 10) || 0 })
              } ),
              wp.element.createElement( TextControl, {
                label: __('Width','vortex360-lite'),
                value: attributes.width,
                onChange: (val) => setAttributes({ width: val })
              } ),
              wp.element.createElement( TextControl, {
                label: __('Height','vortex360-lite'),
                value: attributes.height,
                onChange: (val) => setAttributes({ height: val })
              } ),
              wp.element.createElement( ToggleControl, {
                label: __('Thumbnails','vortex360-lite'),
                checked: !!attributes.thumbnails,
                onChange: (val) => setAttributes({ thumbnails: !!val })
              } ),
              wp.element.createElement( ToggleControl, {
                label: __('Autorotate','vortex360-lite'),
                checked: !!attributes.autorotate,
                onChange: (val) => setAttributes({ autorotate: !!val })
              } ),
              wp.element.createElement( ToggleControl, {
                label: __('Compass','vortex360-lite'),
                checked: !!attributes.compass,
                onChange: (val) => setAttributes({ compass: !!val })
              } ),
              wp.element.createElement( ToggleControl, {
                label: __('Controls','vortex360-lite'),
                checked: !!attributes.controls,
                onChange: (val) => setAttributes({ controls: !!val })
              } ),
              wp.element.createElement( ToggleControl, {
                label: __('Fullscreen','vortex360-lite'),
                checked: !!attributes.fullscreen,
                onChange: (val) => setAttributes({ fullscreen: !!val })
              } )
            )
          ),
          wp.element.createElement( 'div',
            { style: { border:'1px dashed #ccd', padding:'12px', background:'#f9fbff' } },
            attributes.postId
              ? __('Vortex360 Tour selected. It will render on the front end.', 'vortex360-lite')
              : __('Select a Vortex360 Tour in the sidebar.', 'vortex360-lite')
          )
        )
      );
    },
    save: function() {
      // Save nothing — let PHP render via shortcode on the front end (handled by dynamic replacement in enqueue).
      return null;
    }
  } );
} )( window.wp );
