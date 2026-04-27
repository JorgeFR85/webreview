<?php
/**
 * Home editorial template.
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="primary" class="site-main">
    <section class="ar-section ar-hero">
        <div>
            <p class="ar-meta"><?php esc_html_e('Review destacada', 'autorevista-child'); ?></p>
            <h1><?php esc_html_e('Toyota RAV4 2026 GR Sport Plug-in Hybrid: análisis completo', 'autorevista-child'); ?></h1>
            <p><?php esc_html_e('Prueba con enfoque técnico, consumo realista y valoración editorial sin clickbait.', 'autorevista-child'); ?></p>
            <a class="ar-btn" href="<?php echo esc_url(home_url('/reviews/toyota-rav4-2026-gr-sport-plug-in-hybrid-review/')); ?>">
                <?php esc_html_e('Leer review', 'autorevista-child'); ?>
            </a>
        </div>
        <div class="ar-card">
            <h2><?php esc_html_e('Nuestra metodología', 'autorevista-child'); ?></h2>
            <p><?php esc_html_e('Contrastamos ficha técnica oficial, condiciones de prueba y comparación de mercado para ofrecer contenido fiable a lectores y marcas.', 'autorevista-child'); ?></p>
        </div>
    </section>

    <section class="ar-section">
        <h2><?php esc_html_e('Últimas reviews', 'autorevista-child'); ?></h2>
        <?php get_template_part('template-parts/home/cards', null, ['type' => 'review']); ?>
    </section>

    <section class="ar-section">
        <h2><?php esc_html_e('Últimas noticias', 'autorevista-child'); ?></h2>
        <?php get_template_part('template-parts/home/cards', null, ['type' => 'post']); ?>
    </section>

    <section class="ar-section ar-cta-brands">
        <h2><?php esc_html_e('Para marcas', 'autorevista-child'); ?></h2>
        <p><?php esc_html_e('¿Buscas cobertura editorial seria y transparente para lanzamientos y pruebas? Publicamos metodología, política editorial y métricas de rendimiento de cada contenido.', 'autorevista-child'); ?></p>
        <a class="ar-btn" href="<?php echo esc_url(home_url('/contacto-para-marcas/')); ?>"><?php esc_html_e('Contactar con el equipo', 'autorevista-child'); ?></a>
    </section>

    <section class="ar-section ar-newsletter">
        <h2><?php esc_html_e('Newsletter', 'autorevista-child'); ?></h2>
        <p><?php esc_html_e('Una selección semanal de reviews, comparativas y noticias clave del sector.', 'autorevista-child'); ?></p>
        <p><strong><?php esc_html_e('Formulario placeholder: conecta aquí tu plugin de email marketing.', 'autorevista-child'); ?></strong></p>
    </section>
</main>
<?php
get_footer();
