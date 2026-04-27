<?php
/**
 * Cards for home sections.
 *
 * @var array $args
 */

if (! defined('ABSPATH')) {
    exit;
}

$type = $args['type'] ?? 'post';
$query = new WP_Query([
    'post_type' => $type,
    'posts_per_page' => 6,
]);
?>
<div class="ar-card-grid">
    <?php if ($query->have_posts()) : ?>
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <article class="ar-card">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="ar-meta"><?php echo esc_html(get_the_date()); ?></p>
                <?php the_excerpt(); ?>
            </article>
        <?php endwhile; wp_reset_postdata(); ?>
    <?php else : ?>
        <p><?php esc_html_e('No hay contenido todavía.', 'autorevista-child'); ?></p>
    <?php endif; ?>
</div>
