<?php
/**
 * Seed initial editorial content.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_init', function (): void {
    if (! current_user_can('manage_options')) {
        return;
    }

    if (get_option('autorevista_seeded_rav4_review') === 'yes') {
        return;
    }

    update_option('blogname', 'Apex Motor');

    $slug = 'toyota-rav4-2026-gr-sport-plug-in-hybrid-review';

    $existing = get_page_by_path($slug, OBJECT, 'review');
    if ($existing instanceof WP_Post) {
        update_option('autorevista_seeded_rav4_review', 'yes');
        return;
    }

    $content = <<<'HTML'
<h2>Introducción</h2>
<p>El Toyota RAV4 2026 GR Sport Plug-in Hybrid llega con un enfoque claro: ofrecer un SUV enchufable familiar, pero con una puesta a punto más dinámica que la media de su segmento.</p>
<p>Esta review inicial está basada en información pública de Toyota por mercados. Cuando un dato varía según país/ciclo (WLTP o estimación de fabricante en EE. UU.), lo indicamos expresamente para mantener transparencia editorial.</p>

<h2>Diseño exterior</h2>
<p>La variante GR Sport aporta una estética más deportiva con detalles específicos de acabado, una presencia visual más ancha y una imagen más técnica respecto a versiones estándar de la gama RAV4.</p>

<h2>Interior</h2>
<p>En el nuevo RAV4, Toyota prioriza ergonomía y una interfaz más limpia para uso diario. El planteamiento es práctico: buena visibilidad, organización clara de controles y mayor foco en conectividad.</p>

<h2>Tecnología</h2>
<p>El modelo integra la evolución reciente de infotainment y asistentes de seguridad Toyota según mercado. La dotación exacta cambia por versión y país, por lo que conviene confirmar equipamiento local antes de compra.</p>

<h2>Motor</h2>
<p>La base es un sistema Plug-in Hybrid de nueva generación. En algunas comunicaciones oficiales se publican cifras distintas según región/versiones, por lo que evitamos fijar una potencia única global hasta disponer de ficha local cerrada.</p>

<h2>Consumo y autonomía</h2>
<ul>
  <li>Comunicación global Toyota: objetivo de hasta 150 km en modo EV (dato de desarrollo WLTP).</li>
  <li>Toyota EE. UU.: estimación de 52 millas de autonomía eléctrica para la versión Plug-in Hybrid 2026.</li>
  <li>Toyota Reino Unido: hasta 85 millas EV para la especificación local anunciada.</li>
</ul>
<p>Conclusión: no es una contradicción, son marcos de homologación/mercado diferentes. En comparativas serias hay que usar siempre el mismo ciclo y condiciones.</p>

<h2>Conducción</h2>
<p>Por posicionamiento GR Sport, Toyota anuncia una orientación más dinámica en chasis y tacto de conducción. Falta confirmarlo con prueba instrumentada propia para cerrar valoración definitiva.</p>

<h2>Pros y contras</h2>
<h3>Pros</h3>
<ul>
  <li>Planteamiento muy equilibrado entre uso familiar y enfoque dinámico.</li>
  <li>Evolución relevante en autonomía eléctrica según mercado.</li>
  <li>Imagen GR Sport más aspiracional sin perder practicidad.</li>
</ul>
<h3>Contras</h3>
<ul>
  <li>Las cifras clave cambian por ciclo/mercado y pueden generar confusión.</li>
  <li>Precio potencialmente elevado en acabado GR Sport.</li>
  <li>Valoración dinámica aún provisional sin prueba editorial completa.</li>
</ul>

<h2>Veredicto</h2>
<p>El Toyota RAV4 2026 GR Sport Plug-in Hybrid apunta a ser una de las opciones fuertes del segmento SUV PHEV por equilibrio general. Esta valoración es provisional y se actualizará con prueba en condiciones comparables.</p>

<h2>Puntuación (0-10)</h2>
<p><strong>8,4 / 10 (provisional)</strong></p>

<h2>SEO title</h2>
<p>Toyota RAV4 2026 GR Sport Plug-in Hybrid: prueba, autonomía y veredicto</p>

<h2>Meta description</h2>
<p>Review del Toyota RAV4 2026 GR Sport Plug-in Hybrid con análisis de diseño, interior, tecnología, motor, autonomía, pros y contras.</p>

<h2>Slug</h2>
<p>toyota-rav4-2026-gr-sport-plug-in-hybrid-review</p>

<h2>Categorías y tags</h2>
<p>Categorías: Reviews, SUV, Híbridos enchufables.<br>Tags: Toyota RAV4 2026, GR Sport, Plug-in Hybrid, PHEV, SUV mediano.</p>

<h2>Fuentes verificables</h2>
<ul>
  <li>Toyota Global Newsroom (world premiere 2025).</li>
  <li>Toyota USA Pressroom (MY2026 RAV4 Plug-in Hybrid).</li>
  <li>Toyota UK Media Site (gama/pricing 2026).</li>
</ul>
<p><em>Llamada para marcas:</em> Si quieres coordinar una cesión de unidad o presentación técnica, visita nuestra página “Contacto para marcas”.</p>
HTML;

    $post_id = wp_insert_post([
        'post_type' => 'review',
        'post_status' => 'publish',
        'post_title' => 'Toyota RAV4 2026 GR Sport Plug-in Hybrid: análisis completo',
        'post_name' => $slug,
        'post_excerpt' => 'Review inicial del Toyota RAV4 2026 GR Sport Plug-in Hybrid con enfoque técnico y transparencia de datos por mercado.',
        'post_content' => $content,
    ]);

    if (is_wp_error($post_id)) {
        return;
    }

    wp_set_object_terms($post_id, ['Toyota RAV4 2026', 'GR Sport', 'Plug-in Hybrid', 'PHEV', 'SUV mediano'], 'review_tag');

    update_post_meta($post_id, '_ar_review_score', '8.4');
    update_post_meta($post_id, '_ar_seo_title', 'Toyota RAV4 2026 GR Sport Plug-in Hybrid: prueba, autonomía y veredicto');
    update_post_meta($post_id, '_ar_meta_description', 'Review del Toyota RAV4 2026 GR Sport Plug-in Hybrid con análisis de diseño, interior, tecnología, motor, autonomía, pros y contras.');

    update_option('autorevista_seeded_rav4_review', 'yes');
});
