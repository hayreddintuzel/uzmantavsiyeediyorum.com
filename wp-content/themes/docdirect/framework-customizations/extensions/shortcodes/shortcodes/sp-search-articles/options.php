<?php

if (!defined('FW'))
    die('Forbidden');



$options = array(
    'news_heading' => array(
        'label' => esc_html__('Heading', 'docdirect'),
        'desc' => esc_html__('Add news section heading. leave it empty to hide.', 'docdirect'),
        'type' => 'text',
    ),
	'sub_heading' => array(
        'type' => 'text',
        'label' => esc_html__('Sub Heading', 'docdirect'),
        'desc' => esc_html__('leave it empty to hide.', 'docdirect'),
    ),
    'news_description' => array(
        'type' => 'wp-editor',
        'label' => esc_html__('Description', 'docdirect'),
        'desc' => esc_html__('Add section description. leave it empty to hide.', 'docdirect'),
        'tinymce' => true,
        'media_buttons' => false,
        'teeny' => true,
        'wpautop' => false,
        'editor_css' => '',
        'reinit' => true,
        'size' => 'small', // small | large
        'editor_type' => 'tinymce',
        'editor_height' => 200
    ),
);
