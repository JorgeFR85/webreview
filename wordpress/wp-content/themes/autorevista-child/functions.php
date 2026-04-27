<?php
/**
 * AutoRevista Child theme functions.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'autorevista-child-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );
});

add_action('after_setup_theme', function (): void {
    register_nav_menus([
        'primary' => __('Menú principal', 'autorevista-child'),
        'footer'  => __('Menú footer', 'autorevista-child'),
    ]);
});

add_action('init', function (): void {
    register_post_type('review', [
        'labels' => [
            'name' => __('Reviews', 'autorevista-child'),
            'singular_name' => __('Review', 'autorevista-child'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'reviews'],
        'menu_icon' => 'dashicons-car',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('review_tag', ['review'], [
        'label' => __('Etiquetas de review', 'autorevista-child'),
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
    ]);
});
