<?php
/**
 * WPForms Mailblast IOStack Integration
 * 
 * @package WPForms_Mailblast_IOStack_Integration
 * @since 1.0.0
 * @copyright Copyright (c) 2023, Eric Mathison
 * @license GPL-2.0+
 */

class WPForms_Mailblast_IOStack_Integration {

    /**
     * Constructor
     *  
     */
    public function __construct() {
        // Add settings section
        add_filter('wpforms_builder_settings_sections', array($this, 'add_settings_section'), 20, 2);
        // Add settings content
        add_filter('wpforms_form_settings_panel_content', array($this, 'add_settings_content'), 20);
        // Send data to bigmailer
        add_action('wpforms_process_complete', array($this, 'send_data_to_mailblast'), 10, 4);
    }

    /**
     * Add settings section
     *  
     */
    public function add_settings_section($sections, $form_data) {
        $sections['em_mailblast_iostack'] = __( 'Mailblast/IOStack', 'wpforms-mailblast-iostack-integration' );
        return $sections;
    }

    /**
     * Add settings content
     *  
     */
    public function add_settings_content($instance) {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-em_mailblast_iostack">';
        echo '<div class="wpforms-panel-content-section-title">' . __( 'Mailblast/IOStack', 'wpforms-mailblast-iostack-integration' ) . '</div>';

        wpforms_panel_field(
            'text',
            'settings',
            'em_mailblast_email',
            $instance->form_data,
            __( 'Mailblast Account Email' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the email address for your Mailblast account.', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_mailblast_api',
            $instance->form_data,
            __( 'Mailblast API Key' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter your Mailblast API Key.', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_iostack_api',
            $instance->form_data,
            __( 'IOStack API Key' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter your IOStack API Key.', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_mailblast_list_id',
            $instance->form_data,
            __( 'Mailblast List ID' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the Mailblast List ID', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_iostack_printable_title',
            $instance->form_data,
            __( 'Printable Name' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the name of the Printable Download.', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_iostack_printable_url',
            $instance->form_data,
            __( 'Printable URL' , 'wpforms-mailblast-iostack-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the URL for the Printable Download.', 'wpforms-mailblast-iostack-integration' ),
            ]
        );

        wpforms_panel_field(
            'select',
            'settings',
            'em_mailblast_field_name',
            $instance->form_data,
            __( 'First Name', 'wpforms-mailblast-iostack-integration' ),
            array(
                'field_map' => array( 'text', 'name' ),
                'placeholder' => __( '-- Select Field --', 'wpforms-mailblast-iostack-integration' ),
            )
        );

        wpforms_panel_field(
            'select',
            'settings',
            'em_mailblast_field_email',
            $instance->form_data,
            __( 'Email', 'wpforms-mailblast-iostack-integration' ),
            array(
                'field_map' => array( 'email' ),
                'placeholder' => __( '-- Select Field --', 'wpforms-mailblast-iostack-integration' ),
            )
        );

        echo '</div>';
    }

    /**
     * Send data to Mailblast
     *  
     */
    public function send_data_to_mailblast($fields, $entry, $form_data, $entry_id) {

        // Get the Mailblast Email
        if (!empty($form_data['settings']['em_mailblast_email']))
            $mailblast_email = esc_html($form_data['settings']['em_mailblast_email']);


        // Get the Mailblast API key
        if (!empty($form_data['settings']['em_mailblast_api']))
            $mailblast_api_key = esc_html($form_data['settings']['em_mailblast_api']);

        // Get the IOStack API key
        if (!empty($form_data['settings']['em_iostack_api']))
            $iostack_api_key = esc_html($form_data['settings']['em_iostack_api']);

        // Get the list ID
        if (!empty($form_data['settings']['em_mailblast_list_id'])) {
            $list_id = esc_html($form_data['settings']['em_mailblast_list_id']);
        }

        // Get the printable title
        if (!empty($form_data['settings']['em_iostack_printable_title']))
            $printable_title = esc_html($form_data['settings']['em_iostack_printable_title']);

        // Get the printable url
        if (!empty($form_data['settings']['em_iostack_printable_url'])) {
            $printable_url = esc_html($form_data['settings']['em_iostack_printable_url']);
        }

        // Get name field id
        $name_field_id = esc_html($form_data['settings']['em_mailblast_field_name']);
        if (!is_numeric($name_field_id) || empty($fields[$name_field_id]['value'])) {
            return;
        }

        // Get email field id
        $email_field_id = esc_html($form_data['settings']['em_mailblast_field_email']);
        if (!is_numeric($email_field_id) || empty($fields[$email_field_id]['value'])) {
            return;
        }

        $create_body = array('data' => array('attributes' => array (
            'email' => $fields[$email_field_id]['value'],
            'first_name' => $fields[$name_field_id]['value'],
        )));

        // Send data to mailblast
        // https://api.mailblast.io/v1/lists/[LIST_ID]/subscribers
        $create_response = wp_remote_post( 'https://api.mailblast.io/v1/lists/' . $list_id . '/subscribers', array(
            'headers' => array(
                'X-USER-TOKEN' => $mailblast_api_key,
                'X-USER-EMAIL' => $mailblast_email,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( $create_body ),
        ));

        // Check if contact is created or already exists
        // Response will be 201 or 422
        if ($create_response['response']['code'] == 201 || $create_response['response']['code'] == 422) {
            $response_message = json_decode($create_response['body']);
            
            if ($create_response['response']['code'] == 422 && $response_message->errors[0]->detail != 'address is already subscribed to this list.') {
                return;
            }

            $printable_body = array(
                'email' => $fields[$email_field_id]['value'],
                'title' => $printable_title,
                'link' => $printable_url,
            );

            // if contact is created or already exists, send printable
            $printable_response = wp_remote_post( 'https://api.iostack.net/fromabcstoacts/freeprintable', array(
                'headers' => array(
                    'X-API-Key' => $iostack_api_key,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode( $printable_body ),
            ));
            
        }

        // Enable Response Logs if debug mode is enabled
        if (function_exists('wpforms_log')) {
            wpforms_log(
                'Mailblast Create Subscriber Response',
                $create_response,
                [
                    'type' => ['provider'],
                    'parent' => $entry_id,
                    'form_id' => $form_data['id'],
                ]
            );

            wpforms_log(
                'IOStack Transaction Response',
                $printable_response,
                [
                    'type' => ['provider'],
                    'parent' => $entry_id,
                    'form_id' => $form_data['id'],
                ]
            );
        }
    }

}
new WPForms_Mailblast_IOStack_Integration();