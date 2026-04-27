<?php
/**
 * Plugin Name: AutoRevista AI Drafts
 * Description: Genera borradores de reviews con flujo de revisión humana, fuentes obligatorias y controles anti-plagio.
 * Version: 0.1.0
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
        $params = $request->get_json_params();

        if (! self::has_minimum_sources($params['sources'] ?? [])) {
            return new WP_REST_Response([
                'error' => 'Debes enviar al menos 2 fuentes verificables (fabricante/organismo/prensa oficial).',
            ], 422);
        }

        $body = self::build_editorial_draft($params);

        $post_id = wp_insert_post([
            'post_type' => 'review',
            'post_status' => 'draft',
            'post_title' => sanitize_text_field(($params['model'] ?? '') . ' - Borrador IA'),
            'post_content' => wp_kses_post($body),
        ]);

        if (is_wp_error($post_id)) {
            return new WP_REST_Response(['error' => $post_id->get_error_message()], 500);
        }

        update_post_meta($post_id, self::META_STATUS, 'pending_human_review');
        update_post_meta($post_id, self::META_SOURCES, wp_json_encode($params['sources']));
        update_post_meta($post_id, self::META_PLAGIARISM, 'pending_manual_check');

        return new WP_REST_Response([
            'post_id' => $post_id,
            'status' => 'draft_created',
            'next_step' => 'editorial_review_required',
            'confidence_notes' => [
                'No publicar sin validación humana.',
                'Revisar datos de autonomía/consumo por ciclo homologación.',
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

    private static function build_editorial_draft(array $params): string
    {
        $model = esc_html($params['model'] ?? 'Modelo no definido');
        $angle = esc_html($params['angle'] ?? 'Review general');

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
<p>Borrador generado para {$model}. Ángulo editorial: {$angle}.</p>
<h2>Datos verificados disponibles</h2>
<ul>" . implode('', $facts) . "</ul>
<h2>Borrador editorial</h2>
<p>[Completar por editor con prueba, contexto y comparativa.]</p>
<h2>Fuentes obligatorias</h2>
<ul>" . implode('', array_filter($sources)) . "</ul>
<p><strong>Nota:</strong> Este texto es un borrador. Requiere revisión humana, anti-plagio y fact-check antes de publicación.</p>
";
    }
}

AutoRevistaAIDrafts::init();
