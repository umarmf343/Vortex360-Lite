<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Loader {
    private $actions = [];
    private $filters = [];

    public function add_action( $hook, $component, $callback, $priority = 10, $args = 1 ) {
        $this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'args' );
    }

    public function add_filter( $hook, $component, $callback, $priority = 10, $args = 1 ) {
        $this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'args' );
    }

    public function run() {
        foreach ( $this->actions as $a ) {
            add_action( $a['hook'], [ $a['component'], $a['callback'] ], $a['priority'], $a['args'] );
        }
        foreach ( $this->filters as $f ) {
            add_filter( $f['hook'], [ $f['component'], $f['callback'] ], $f['priority'], $f['args'] );
        }
    }
}
