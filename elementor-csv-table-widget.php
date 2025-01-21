<?php
/*
Plugin Name: Elementor CSV Table Widget
Description: Ein Elementor-Widget, das eine CSV-Datei aus der Medienbibliothek auswählt und als Tabelle darstellt.
Version: 1.0
Author: Dominik Scharrer
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Elementor Widgets laden
function register_csv_table_widget( $widgets_manager ) {
    require_once( __DIR__ . '/widgets/csv-table-widget.php' );
    $widgets_manager->register( new \Elementor_CSV_Table_Widget() );
}
add_action( 'elementor/widgets/register', 'register_csv_table_widget' );

// JavaScript-Datei einbinden
function enqueue_csv_table_widget_scripts() {
    wp_enqueue_script( 'csv-table-widget', plugins_url( '/csv-table-widget.js', __FILE__ ), [ 'jquery' ], false, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_csv_table_widget_scripts' );
