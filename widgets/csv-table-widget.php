<?php
class Elementor_CSV_Table_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'csv_table_widget';
    }

    public function get_title() {
        return __( 'CSV Table Widget', 'plugin-name' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function register_controls() {
    $this->start_controls_section(
        'content_section',
        [
            'label' => __( 'Content', 'plugin-name' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]
    );

    $this->add_control(
        'csv_file',
        [
            'label' => __( 'CSV File', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::MEDIA,
            'media_type' => 'text/csv',
        ]
    );

    $this->add_control(
        'delimiter',
        [
            'label' => __( 'Delimiter', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                ',' => __( 'Comma (,)', 'plugin-name' ),
                ';' => __( 'Semicolon (;)', 'plugin-name' ),
            ],
            'default' => ',',
        ]
    );

    $this->add_control(
        'has_header',
        [
            'label' => __( 'Has Header', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'plugin-name' ),
            'label_off' => __( 'No', 'plugin-name' ),
            'return_value' => 'yes',
            'default' => 'yes',
        ]
    );

    $this->add_control(
        'link_emails',
        [
            'label' => __( 'Link Emails', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'plugin-name' ),
            'label_off' => __( 'No', 'plugin-name' ),
            'return_value' => 'yes',
            'default' => '',
        ]
    );

    $this->end_controls_section();

    // Style Section
    $this->start_controls_section(
        'style_section',
        [
            'label' => __( 'Table Style', 'plugin-name' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]
    );

    $this->add_control(
        'table_color',
        [
            'label' => __( 'Table Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table' => 'color: {{VALUE}};',
            ],
        ]
    );

    $this->add_control(
        'table_background_color',
        [
            'label' => __( 'Table Background Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table' => 'background-color: {{VALUE}};',
            ],
        ]
    );

    $this->add_control(
        'header_background_color',
        [
            'label' => __( 'Header Background Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table thead' => 'background-color: {{VALUE}};',
            ],
        ]
    );

    $this->add_control(
        'header_text_color',
        [
            'label' => __( 'Header Text Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table thead' => 'color: {{VALUE}};',
            ],
        ]
    );

    $this->add_control(
        'row_background_color',
        [
            'label' => __( 'Row Background Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table tbody tr' => 'background-color: {{VALUE}};',
            ],
        ]
    );

    $this->add_control(
        'row_text_color',
        [
            'label' => __( 'Row Text Color', 'plugin-name' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} #csv-table tbody tr' => 'color: {{VALUE}};',
            ],
        ]
    );

    $this->end_controls_section();
}

    protected function render() {
    $settings = $this->get_settings_for_display();
    $csv_file = $settings['csv_file']['url'];
    $delimiter = $settings['delimiter'];
    $has_header = $settings['has_header'] === 'yes';
    $link_emails = $settings['link_emails'] === 'yes';

    if ( ! file_exists( ABSPATH . parse_url( $csv_file, PHP_URL_PATH ) ) ) {
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div class="csv-error">Die angegebene CSV-Datei existiert nicht. Bitte überprüfen Sie die Datei und versuchen Sie es erneut.</div>';
        }
        return;
    }

    // Dateiinhalt als UTF-8 einlesen
    $csv_content = file_get_contents($csv_file);
    $csv_content = mb_convert_encoding($csv_content, 'UTF-8', mb_detect_encoding($csv_content, 'UTF-8, ISO-8859-1', true));
    $csv_data = array_map(function($line) use ($delimiter) {
        return str_getcsv($line, $delimiter);
    }, explode(PHP_EOL, $csv_content));

    // Leere Zeilen entfernen
    $csv_data = array_filter($csv_data, function($row) {
        return array_filter($row);
    });

    // Responsive Styles hinzufügen
    echo '<style>
        @media only screen and (max-width: 600px) {
            #csv-table-container {
                overflow-x: auto;
            }
            #csv-table {
                width: 100%;
                min-width: 600px;
            }
        }
    </style>';

    echo '<input type="text" id="csv-filter" placeholder="Filter...">';
    echo '<div id="csv-table-container">';
    echo '<table id="csv-table">';

    if ($has_header) {
        $header = array_shift($csv_data);
        echo '<thead><tr>';
        foreach ($header as $cell) {
            echo '<th>' . esc_html($cell) . '</th>';
        }
        echo '</tr></thead>';
    }

    echo '<tbody>';
    foreach ($csv_data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            if ($link_emails && filter_var($cell, FILTER_VALIDATE_EMAIL)) {
                echo '<td><a href="mailto:' . esc_html($cell) . '">' . esc_html($cell) . '</a></td>';
            } else {
                echo '<td>' . esc_html($cell) . '</td>';
            }
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>';
}

    protected function _content_template() {
    ?>
    <#
    var csv_file = settings.csv_file.url;
    var delimiter = settings.delimiter;
    var has_header = settings.has_header === 'yes';
    var link_emails = settings.link_emails === 'yes';

    if ( csv_file ) {
        var csv_data = []; // Hier sollten die CSV-Daten verarbeitet werden

        #>
        <style>
            @media only screen and (max-width: 600px) {
                #csv-table-container {
                    overflow-x: auto;
                }
                #csv-table {
                    width: 100%;
                    min-width: 600px;
                }
            }
        </style>
        <input type="text" id="csv-filter" placeholder="Filter...">
        <div id="csv-table-container">
            <table id="csv-table">
                <# if ( has_header ) { #>
                    <thead>
                        <tr>
                            <# _.each( csv_data[0], function( cell ) { #>
                                <th>{{{ cell }}}</th>
                            <# }); #>
                        </tr>
                    </thead>
                    <# csv_data.shift(); #>
                <# } #>
                <tbody>
                    <# _.each( csv_data, function( row ) { #>
                        <tr>
                            <# _.each( row, function( cell ) { #>
                                <td>
                                    <# if ( link_emails && cell.match(/^[\w\.\-]+@[\w\-]+\.[a-z]{2,4}$/i) ) { #>
                                        <a href="mailto:{{{ cell }}}">{{{ cell }}}</a>
                                    <# } else { #>
                                        {{{ cell }}}
                                    <# } #>
                                </td>
                            <# }); #>
                        </tr>
                    <# }); #>
                </tbody>
            </table>
        </div>
        <#
    }
    #>
    <?php
}
}
