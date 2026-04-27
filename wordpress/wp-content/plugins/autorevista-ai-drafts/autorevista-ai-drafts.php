<?php
/**
 * Plugin Name: AutoRevista AI Drafts
 * Description: Genera borradores de reviews y artículos con flujo de revisión humana, fuentes obligatorias y controles anti-plagio.
 * Version: 0.2.0
 * Author: AutoRevista
 * Requires at least: 6.4
 * Requires PHP: 8.0
 */

if (! defined('ABSPATH')) {
    exit;
}

final class AutoRevistaAIDrafts
{
    private const META_STATUS = '_ar_ai_status';
    private const META_SOURCES = '_ar_ai_sources';
    private const META_PLAGIARISM = '_ar_ai_plagiarism';
    private const ALLOWED_POST_TYPES = ['review', 'post'];

    public static function init(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        register_rest_route('autorevista/v1', '/generate-draft', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle_generate_draft'],
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'args' => [
                'model' => ['required' => true, 'type' => 'string'],
                'angle' => ['required' => true, 'type' => 'string'],
                'verified_facts' => ['required' => true, 'type' => 'array'],
                'sources' => ['required' => true, 'type' => 'array'],
                'target_keywords' => ['required' => false, 'type' => 'array'],
                'disclaimers' => ['required' => false, 'type' => 'array'],
                'post_type' => ['required' => false, 'type' => 'string'],
                'title' => ['required' => false, 'type' => 'string'],
                'categories' => ['required' => false, 'type' => 'array'],
                'tags' => ['required' => false, 'type' => 'array'],
            ],
        ]);

        register_rest_route('autorevista/v1', '/generate-article-draft', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle_generate_article_draft'],
            'permission_callback' => function (): bool {
                return current_user_can('edit_posts');
            },
            'args' => [
                'title' => ['required' => true, 'type' => 'string'],
                'angle' => ['required' => true, 'type' => 'string'],
                'verified_facts' => ['required' => true, 'type' => 'array'],
                'sources' => ['required' => true, 'type' => 'array'],
                'target_keywords' => ['required' => false, 'type' => 'array'],
                'disclaimers' => ['required' => false, 'type' => 'array'],
                'categories' => ['required' => false, 'type' => 'array'],
                'tags' => ['required' => false, 'type' => 'array'],
            ],
        ]);

        register_rest_route('autorevista/v1', '/approve-draft/(?P<id>\d+)', [
            'methods'  => 'POST',
            'callback' => [self::class, 'handle_approve_draft'],
            'permission_callback' => function (): bool {
                return current_user_can('publish_posts');
            },
        ]);
    }

    public static function handle_generate_draft(WP_REST_Request $request): WP_REST_Response
    {
        return self::generate_draft_from_params($request->get_json_params());
    }

    public static function handle_generate_article_draft(WP_REST_Request $request): WP_REST_Response
    {
        $params = $request->get_json_params();
        $params['post_type'] = 'post';

        return self::generate_draft_from_params($params);
    }


    private static function generate_draft_from_params(array $params): WP_REST_Response
    {
        $post_type = self::sanitize_post_type($params['post_type'] ?? 'review');
        if (! self::is_allowed_post_type($post_type)) {
            return new WP_REST_Response([
                'error' => 'post_type no permitido. Usa review o post.',
            ], 422);
        }

        if (! self::has_minimum_sources($params['sources'] ?? [])) {
            return new WP_REST_Response([
                'error' => 'Debes enviar al menos 2 fuentes verificables (fabricante/organismo/prensa oficial).',
            ], 422);
        }

        $body = self::build_editorial_draft($params, $post_type);
        $title_seed = self::build_title($params, $post_type);

        $post_id = wp_insert_post([
            'post_type' => $post_type,
            'post_status' => 'draft',
            'post_title' => $title_seed,
            'post_content' => wp_kses_post($body),
        ]);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
        }

        self::apply_post_taxonomies($post_id, $post_type, $params);

        update_post_meta($post_id, self::META_STATUS, 'pending_human_review');
        update_post_meta($post_id, self::META_SOURCES, wp_json_encode($params['sources']));
        update_post_meta($post_id, self::META_PLAGIARISM, 'pending_manual_check');

        return new WP_REST_Response([
            'post_id' => $post_id,
            'post_type' => $post_type,
            'status' => 'draft_created',
            'next_step' => 'editorial_review_required',
            'confidence_notes' => [
                'No publicar sin validación humana.',
                'Verificar fuentes y cifras antes de aprobar.',
            ],
        ], 201);
    }
    public static function handle_approve_draft(WP_REST_Request $request): WP_REST_Response
    {
        $post_id = (int) $request['id'];
        $status = get_post_meta($post_id, self::META_STATUS, true);

        if ($status !== 'pending_human_review') {
            return new WP_REST_Response([
                'error' => 'El borrador no está en estado apto para aprobación.',
            ], 409);
        }

        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'pending',
        ]);

        update_post_meta($post_id, self::META_STATUS, 'pending_editor_approval');

        return new WP_REST_Response([
            'post_id' => $post_id,
            'status' => 'pending_editor_approval',
        ], 200);
    }

    private static function has_minimum_sources(array $sources): bool
    {
        $valid = array_filter($sources, fn ($source) => is_array($source) && ! empty($source['url']) && ! empty($source['label']));
        return count($valid) >= 2;
    }

    private static function sanitize_post_type(string $post_type): string
    {
        return sanitize_key($post_type);
    }

    private static function is_allowed_post_type(string $post_type): bool
    {
        return in_array($post_type, self::ALLOWED_POST_TYPES, true);
    }

    private static function build_title(array $params, string $post_type): string
    {
        if (! empty($params['title'])) {
            return sanitize_text_field((string) $params['title']);
        }

        if ($post_type === 'post') {
            return sanitize_text_field(($params['angle'] ?? 'Artículo') . ' - Borrador IA');
        }

        return sanitize_text_field(($params['model'] ?? 'Review') . ' - Borrador IA');
    }

    private static function apply_post_taxonomies(int $post_id, string $post_type, array $params): void
    {
        if ($post_type !== 'post') {
            if (! empty($params['tags']) && taxonomy_exists('review_tag')) {
                $tags = array_map('sanitize_text_field', (array) $params['tags']);
                wp_set_object_terms($post_id, $tags, 'review_tag');
            }
            return;
        }

        if (! empty($params['categories']) && taxonomy_exists('category')) {
            $categories = [];
            foreach ((array) $params['categories'] as $category_name) {
                $category_name = sanitize_text_field((string) $category_name);
                $term = term_exists($category_name, 'category');
                if ($term === 0 || $term === null) {
                    $term = wp_insert_term($category_name, 'category');
                }

                if (is_array($term) && isset($term['term_id'])) {
                    $categories[] = (int) $term['term_id'];
                }
            }

            if ($categories !== []) {
                wp_set_post_categories($post_id, $categories, false);
            }
        }

        if (! empty($params['tags']) && taxonomy_exists('post_tag')) {
            $tags = array_map('sanitize_text_field', (array) $params['tags']);
            wp_set_post_tags($post_id, $tags, false);
        }
    }

    private static function build_editorial_draft(array $params, string $post_type): string
    {
        $entity = $post_type === 'post'
            ? esc_html($params['title'] ?? 'Artículo no definido')
            : esc_html($params['model'] ?? 'Modelo no definido');

        $angle = esc_html($params['angle'] ?? 'Enfoque general');

        $facts = array_map(
            fn ($fact) => '<li>' . esc_html((string) $fact) . '</li>',
            $params['verified_facts'] ?? []
        );

        $sources = array_map(function ($source): string {
            if (! is_array($source)) {
                return '';
            }

            $label = esc_html((string) ($source['label'] ?? 'Fuente'));
            $url = esc_url_raw((string) ($source['url'] ?? ''));

            return sprintf('<li><a href="%s" rel="nofollow noopener" target="_blank">%s</a></li>', $url, $label);
        }, $params['sources'] ?? []);

        return "
<h2>Introducción</h2>
<p>Borrador generado para {$entity}. Enfoque editorial: {$angle}.</p>
<h2>Datos verificados disponibles</h2>
<ul>" . implode('', $facts) . "</ul>
<h2>Borrador editorial</h2>
<p>[Completar por editor con contexto, comparativa y conclusiones propias.]</p>
<h2>Fuentes obligatorias</h2>
<ul>" . implode('', array_filter($sources)) . "</ul>
<p><strong>Nota:</strong> Este texto es un borrador. Requiere revisión humana, anti-plagio y fact-check antes de publicación.</p>
";
    }
}

AutoRevistaAIDrafts::init();
