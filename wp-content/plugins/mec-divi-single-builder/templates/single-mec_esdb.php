<?php
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://webnus.net
 * @since      1.0.0
 *
 * @author    Webnus
 */
get_header();
?>

<section id="main-content" class="mec-container mec-single-event">
    <?php if (strpos($_SERVER['REQUEST_URI'], "preview_id") !== false) : ?>
        <div style="text-align:center; width: 90%; display: block; margin: 0 auto; font-size: 40px; color: #000; margin-bottom: 40px; border: 2px solid #000; padding: 20px 40px;line-height: 40px;"><?php esc_html_e('This is not the right place to check. Please open the single event page for seeing the result.', 'mec-divi-single-builder'); ?></div>
    <?php endif; ?>
    <div id="primary" class="content-area">
        <div id="content" class="site-content mec-wrap" role="main">
            <?php
            // Start the Loop.
            while (have_posts()) : the_post();
                // Include the page content template.
                the_content();

            endwhile;
            ?>
        </div><!-- #content -->
    </div><!-- #primary -->
</section><!-- #main-content -->
<?php
get_footer();
