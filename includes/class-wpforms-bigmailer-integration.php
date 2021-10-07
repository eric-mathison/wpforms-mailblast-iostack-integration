<?php
/**
 * WPForms BigMailer Integration
 * 
 * @package WPForms_BigMailer_Integration
 * @since 1.0.0
 * @copyright Copyright (c) 2021, Eric Mathison
 * @license GPL-2.0+
 */

class WPForms_BigMailer_Integration {

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
        add_action('wpforms_process_complete', array($this, 'send_data_to_bigmailer'), 10, 4);
    }

    /**
     * Add settings section
     *  
     */
    public function add_settings_section($sections, $form_data) {
        $sections['em_bigmailer'] = __( 'BigMailer', 'wpforms-bigmailer-integration' );
        return $sections;
    }

    /**
     * Add settings content
     *  
     */
    public function add_settings_content($instance) {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-em_bigmailer">';
        echo '<div class="wpforms-panel-content-section-title">' . __( 'BigMailer', 'wpforms-bigmailer-integration' ) . '</div>';

        wpforms_panel_field(
            'text',
            'settings',
            'em_bigmailer_api',
            $instance->form_data,
            __( 'Bigmailer API Key' , 'wpforms-bigmailer-integration' ),
            [
                'tooltip' => esc_html__( 'Enter your Account API Key.', 'wpforms-bigmailer-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_bigmailer_brand_id',
            $instance->form_data,
            __( 'BigMailer Brand ID' , 'wpforms-bigmailer-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the Brand ID', 'wpforms-bigmailer-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_bigmailer_list_id',
            $instance->form_data,
            __( 'BigMailer List ID' , 'wpforms-bigmailer-integration' ),
            [
                'tooltip' => esc_html__( 'Enter the IDs for the lists the subscriber will be added to. (Comma Seperated)', 'wpforms-bigmailer-integration' ),
            ]
        );

        wpforms_panel_field(
            'text',
            'settings',
            'em_bigmailer_message_type_id',
            $instance->form_data,
            __( 'Bigmailer Message Type ID' , 'wpforms-bigmailer-integration' ),
            [
                'tooltip' => esc_html__( 'Optional. Enter the IDs of Message Types the subscriber will be resubscribed to.', 'wpforms-bigmailer-integration' ),
            ]
        );

        wpforms_panel_field(
            'select',
            'settings',
            'em_bigmailer_field_name',
            $instance->form_data,
            __( 'Name', 'wpforms-bigmailer-integration' ),
            array(
                'field_map' => array( 'text', 'name' ),
                'placeholder' => __( '-- Select Field --', 'wpforms-bigmailer-integration' ),
            )
        );

        wpforms_panel_field(
            'select',
            'settings',
            'em_bigmailer_field_email',
            $instance->form_data,
            __( 'Email', 'wpforms-bigmailer-integration' ),
            array(
                'field_map' => array( 'email' ),
                'placeholder' => __( '-- Select Field --', 'wpforms-bigmailer-integration' ),
            )
        );

        echo '</div>';
    }

    /**
     * Send data to bigmailer
     *  
     */
    public function send_data_to_bigmailer($fields, $entry, $form_data, $entry_id) {

        // Get the API key
        if (!empty($form_data['settings']['em_bigmailer_api']))
            $api_key = esc_html($form_data['settings']['em_bigmailer_api']);

        // Get the brand ID
        if (!empty($form_data['settings']['em_bigmailer_brand_id']))
            $brand_id = esc_html($form_data['settings']['em_bigmailer_brand_id']);

        // Get the list ID and create array of ids
        if (!empty($form_data['settings']['em_bigmailer_list_id'])) {
            $list_id = esc_html($form_data['settings']['em_bigmailer_list_id']);
            $list_id_array = preg_split('/[\s,]+/', $list_id);
        }

        // Get the unsubscribe IDs and create array of ids
        if (!empty($form_data['settings']['em_bigmailer_message_type_id'])) {
            $unsubscribe_id = esc_html($form_data['settings']['em_bigmailer_message_type_id']);
            $unsubscribe_id_array = preg_split('/[\s,]+/', $unsubscribe_id);
        }

        // Get name field id
        $name_field_id = esc_html($form_data['settings']['em_bigmailer_field_name']);
        if (!is_numeric($name_field_id) || empty($fields[$name_field_id]['value'])) {
            return;
        }
        // Get email field id
        $email_field_id = esc_html($form_data['settings']['em_bigmailer_field_email']);
        if (!is_numeric($email_field_id) || empty($fields[$email_field_id]['value'])) {
            return;
        }

        $create_body = array(
            'email' => $fields[$email_field_id]['value'],
            'list_ids' => $list_id_array,
            'field_values' => [array(
                'name' => 'name',
                'string' => $fields[$name_field_id]['value'],
            )]
        );

        // Send data to bigmailer
        // https://api.bigmailer.io/v1/brands/{brand_id}/contacts
        $create_response = wp_remote_post( 'https://api.bigmailer.io/v1/brands/' . $brand_id . '/contacts', array(
            'headers' => array(
                'X-API-Key' => $api_key,
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode( $create_body ),
        ));

        // Check if contact doesn't already exist
        // Response will be something other than 200
        if ($create_response['response']['code'] != 200) {
            $response_message = json_decode($create_response['body']);

            if ($response_message->code == 'resource_already_exists') {
                // If contact already exists, update it
                $update_args = array (
                    'list_ids_op' => 'add',
                    'unsubscribe_ids_op' => 'remove',
                );
                
                $update_body = array(
                    'email' => $fields[$email_field_id]['value'],
                    'list_ids' => $list_id_array,
                    'unsubscribe_all' => false,
                );

                if (!empty($unsubscribe_id_array)) {
                    $update_body['unsubscribe_ids'] = $unsubscribe_id_array;
                }

                $update_response = wp_remote_post( add_query_arg($update_args, 'https://api.bigmailer.io/v1/brands/' . $brand_id . '/contacts/' . $fields[$email_field_id]['value']), array(
                    'headers' => array(
                        'X-API-Key' => $api_key,
                        'Content-Type' => 'application/json',
                    ),
                    'body' => wp_json_encode( $update_body )
                ));
            }
        }

        // Enable Response Logs if debug mode is enabled
        if (function_exists('wpforms_log')) {
            wpforms_log(
                'Bigmailer Create Subscriber Response',
                $create_response,
                [
                    'type' => ['provider'],
                    'parent' => $entry_id,
                    'form_id' => $form_data['id'],
                ]
            );

            if (!empty($update_response)) {
                wpforms_log(
                    'Bigmailer Update Subscriber Response',
                    $update_response,
                    [
                        'type' => ['provider'],
                        'parent' => $entry_id,
                        'form_id' => $form_data['id'],
                    ]
                );
            }
        }
    }

}
new WPForms_BigMailer_Integration();