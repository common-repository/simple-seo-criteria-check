<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_init', 'sscc_image_tabs_preinit');
function sscc_image_tabs_preinit()
{
    if (apply_filters('simple-seo-criteria-check_filter_show_image_tabs', true)) {
        add_filter('simple-seo-criteria-check_filter_settingsscreen_tabs', 'sscc_add_image_tabs');
    }
}

function sscc_add_image_tabs($in)
{
    $in = array_merge($in, array('sscc_images' => __('Images', 'simple-seo-criteria-check')));
    return $in;
}

add_action('admin_menu', 'sscc_images_init');
function sscc_images_init()
{
    if (apply_filters('simple-seo-criteria-check_filter_show_image_tabs', true)) {
        $hook = add_submenu_page(NULL, 'sscc image', 'sscc image', 'manage_options', 'sscc_images', 'sscc_images');
        // register_settings here as well if needed
    }
}


function sscc_count_image_excerpts()
{ // dt. Beschriftung
    global $wpdb;

    $posts = $wpdb->get_results("SELECT id FROM $wpdb->posts WHERE 
    post_excerpt <> '' AND 
    post_type <> 'revision' AND 
    post_type IN ('attachment') AND 
    post_parent <> '0' AND
    post_status IN ('inherit')");

    $count = 0;
    if (!empty($posts))
        $count = count($posts);
    return $count;
}


// replaced by alt tag
function sscc_count_image_alttags()
{ // dt. Beschriftung
    global $wpdb;
    $posts = $wpdb->get_results("SELECT pm.meta_id FROM $wpdb->postmeta pm inner join $wpdb->posts p WHERE pm.post_id = p.ID AND pm.meta_key IN ('_wp_attachment_image_alt') AND p.post_parent > '0'");

    $count = 0;
    if (!empty($posts))
        $count = count($posts);

    return $count;
}

function sscc_count_image_names()
{ // dt. Dateiname
    global $wpdb;
    $posts = $wpdb->get_results("SELECT id FROM $wpdb->posts WHERE 
    post_name <> '' AND 
    post_type <> 'revision' AND 
    post_type IN ('attachment') AND 
    post_status IN ('inherit')");

    $count = 0;
    if (!empty($posts))
        $count = count($posts);
    return $count;
}

function sscc_image_query($arg = '')
{
    global $wpdb;
    $query = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type<>'revision' AND post_type IN ('attachment') AND post_status IN ('inherit') AND post_parent <> '0' order by post_date DESC");

    return $query;
}

function sscc_images()
{
?>

    <div class="wrap">
        <h1><?php __('Simple SEO Criteria Check', 'simple-seo-criteria-check'); ?></h1>
        <?php echo sscc_admin_tabs(); ?>

        <h2><?php echo __('Images and Image Meta Data', 'simple-seo-criteria-check'); ?></h2>

        <?php
        $posts = sscc_image_query();

        if (!empty($posts)) {
            $timages = count($posts);
            $alttags = sscc_count_image_alttags();
            $excerpts = sscc_count_image_excerpts();
            $excerpts_prozent = ($excerpts / $timages) * 100;
            $alttags_prozent = ($alttags / $timages) * 100;
        ?>

            <div class="info-bar">
                <h3><?php echo __('Check Images and related image data', 'simple-seo-criteria-check'); ?></h3>

                <i><?php echo __('SEO Tip: Image Files should contain keywords and should be extended by additional meta data, such as an Alt attribute', 'simple-seo-criteria-check'); ?></i><br /><br />

                <span><?php echo __('Image Alt Tag:', 'simple-seo-criteria-check') . ' ' . $alttags . ' ' . '(' . __('of', 'simple-seo-criteria-check') . ' ' . $timages . ' ' . __('total images have an image alt tag', 'simple-seo-criteria-check'); ?>)</span>
                <?php echo sscc_info_progessbar($alttags_prozent); ?>

                <span><?php echo __('Image Excerpt:', 'simple-seo-criteria-check') . ' ' . $excerpts . ' ' . '(' . __('of', 'simple-seo-criteria-check') . ' ' . $timages . ' ' . __('total images have an image content', 'simple-seo-criteria-check'); ?>)</span>
                <?php echo sscc_info_progessbar($excerpts_prozent); ?>

            </div>

            <table class="sscc widefat striped js-sort-table" id="attachments">
                <tr>
                    <th class="js-sort-string"><?php echo __('Related Post/Page', 'simple-seo-criteria-check'); ?></th>
                    <th class="js-sort-string"><?php echo __('Alt Tag', 'simple-seo-criteria-check'); ?></th>
                    <th class="js-sort-string"><?php echo __('Excerpt', 'simple-seo-criteria-check'); ?></th>
                    <th class="js-sort-string"><?php echo __('File Name', 'simple-seo-criteria-check'); ?></th>
                </tr>

                <?php
                $ci = 1;
                foreach ($posts as $post) {
                    if ($post->post_type == 'attachment') {

                        if (!empty(get_the_title($post->post_parent))) {
                            $post_edit = '<br/><a href="' . get_edit_post_link($post->post_parent) . '" target="_Blank">(' . __('edit Post', 'simple-seo-criteria-check')  . ')</a>';

                            $meta_img_url = get_post_meta($post->ID, '_wp_attached_file', true);
                            $pos_last_slash = strrpos($meta_img_url, "/");
                            $img_file_name = substr($meta_img_url, $pos_last_slash + 1, strlen($meta_img_url));


                ?>
                            <tr>
                                <td><?php echo get_the_title($post->post_parent) . $post_edit; ?></td>
                                <td><?php echo get_post_meta($post->ID, '_wp_attachment_image_alt', true); ?></td>
                                <td><?php echo $post->post_excerpt; ?></td>
                                <td><?php echo $img_file_name; ?><br />
                                    (<a href="<?php echo get_edit_post_link($post->ID); ?>" target="_Blank"><?php echo __('edit Image Data', 'simple-seo-criteria-check'); ?></a> |
                                    <a href="<?php echo content_url() . '/uploads/' . $meta_img_url ; //esc_url($post->guid); ?>" target="_Blank"><?php echo __('show Image', 'simple-seo-criteria-check'); ?></a>)</td>

                            </tr>
                <?php
                        }
                    }
                    $ci++;
                }
                ?>
            </table>
        <?php
        }
        wp_reset_postdata();
        ?>

        <div>

        </div>
    </div>
<?php
}
