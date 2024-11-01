<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('admin_init', 'sscc_link_tabs_preinit');
function sscc_link_tabs_preinit() {
    if (apply_filters('simple-seo-criteria-check_filter_show_link_tabs', true)) {
        add_filter('simple-seo-criteria-check_filter_settingsscreen_tabs', 'sscc_add_link_tabs');
    }
}

function sscc_add_link_tabs($in) {
    $in = array_merge($in, array('sscc_links' => __('Internal/External Links', 'simple-seo-criteria-check')));
    return $in;
}

add_action('admin_menu', 'sscc_links_init');
function sscc_links_init()
{
    if (apply_filters('simple-seo-criteria-check_filter_show_link_tabs', true)) {
        $hook = add_submenu_page(NULL, 'sscc link', 'sscc link', 'manage_options', 'sscc_links', 'sscc_links');
    }
}

function sscc_internal_links_total() {
    global $wpdb;
    $regex = 'href="\s?([^ ]*)\s?"[^>]*>';
    $result = $wpdb->get_results("SELECT * FROM wp_posts WHERE (post_content REGEXP '$regex') and post_status = 'publish' and post_type='post'"); 
    $count = 0;

    if ($result) {
        foreach ($result as $r) {
            $matches = sscc_is_link_available($r->post_content);

            if ($matches === null) {
                return 0;
            }

            // loop through each links
            for ($i = 0; $i < count($matches); $i++) {
                $url  = esc_url($matches[$i][1]);

                if (sscc_is_internal_link($url)) {
                    $count++;
                    break;
                }
            } // end for loop

        }
    }

    return $count;
}

function sscc_external_links_total()
{
    global $wpdb;
    $regex = 'href="\s?([^ ]*)\s?"[^>]*>';
    $result = $wpdb->get_results("SELECT * FROM wp_posts WHERE (post_content REGEXP '$regex') and post_status = 'publish' and post_type='post'");
    $count = 0;

    if ($result) {
        foreach ($result as $r) {
            $matches = sscc_is_link_available($r->post_content);

            if ($matches === null) {
                return 0;
            }

            // loop through each links
            for ($i = 0; $i < count($matches); $i++) {
                $url  = esc_url($matches[$i][1]);

                if (sscc_is_internal_link($url)) {
                    continue;
                }

                $count++;
                break;
            } // end for loop

        }
    }

    return $count;
}

function sscc_links()
{

    $qargs = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1 
    );
    // The Query
    $the_query = new WP_Query($qargs);
    ?>

    <div class="wrap">
        <h1><?php __('Simple SEO Criteria Check', 'simple-seo-criteria-check'); ?></h1>
        <?php echo sscc_admin_tabs(); ?>
        <?php
            echo '<h2>' . __('Internal and External Post Links', 'simple-seo-criteria-check') . '</h2>';

            if ($the_query->have_posts()) {
                $tposts = $the_query->post_count;
                $tinlinks = sscc_internal_links_total();
                $texlinks = sscc_external_links_total();
                $in_prozent = ($tinlinks / $tposts) * 100;
                $ex_prozent = ($texlinks / $tposts) * 100;
                ?>
            <div class="info-bar">
                <h3><?php echo __('Check posts for internal and external links in content', 'simple-seo-criteria-check'); ?></h3>

                <span><?php echo __('Internal Links:', 'simple-seo-criteria-check') . ' ' . $tinlinks . ' (' . __('of', 'simple-seo-criteria-check') . ' ' . $tposts . ' ' . __('total posts have internal links', 'simple-seo-criteria-check'); ?>)</span>
                <?php echo sscc_info_progessbar($in_prozent); ?>

                <span><?php echo __('External Links:', 'simple-seo-criteria-check') . ' ' . $texlinks . ' (' . __('of', 'simple-seo-criteria-check') . ' ' . $tposts . ' ' . __('total posts have external links', 'simple-seo-criteria-check'); ?>)</span>
                <?php echo sscc_info_progessbar($ex_prozent); ?>
            </div>
        <?php
                echo '<table class="sscc widefat striped js-sort-table" id="permaurls">';
                echo '  <tr>';
                echo '      <th class="js-sort-string" width="35%">' . __('Post', 'simple-seo-criteria-check') . '</th>';
                echo '      <th class="js-sort-number" width="10%">' . __('Total Internal', 'simple-seo-criteria-check') . '</th>';
                echo '      <th class="js-sort-string">' . __('Internal Links', 'simple-seo-criteria-check') . '</th>';
                echo '      <th class="js-sort-number" width="10%">' . __('Total External', 'simple-seo-criteria-check') . '</th>';
                echo '      <th class="js-sort-string">' . __('External Links', 'simple-seo-criteria-check') . '</th>';
                echo '  </tr>';

                while ($the_query->have_posts()) {
                    $the_query->the_post();
                    $the_content = apply_filters('the_content', get_the_content());

                    $ex_links = sscc_external_links($the_content);
                    $in_links = sscc_internal_links($the_content);

                    echo '<tr>';
                    echo '  <td>' . get_the_title() . '<br/><a href="' . get_edit_post_link(get_the_ID()) . '" target="_Blank">(' . __('edit Post', 'simple-seo-criteria-check')  . ')</a>' . '</td>';
                    if (!empty($in_links)) {
                        echo '<td>' . count($in_links) . '</td>';
                        echo '<td>';
                        foreach ($in_links as $inli) {
                            echo '' . esc_attr($inli) . '<br>';
                        }
                        echo '</td>';
                    } else {
                        echo '<td>';
                        echo '0';
                        echo '</td>';
                        echo '<td></td>';
                    }
                    echo '</td>';
                    if (!empty($ex_links)) {
                        echo '<td>';
                        echo count($ex_links) . '</td>';
                        echo '<td>';
                        foreach ($ex_links as $exli) {
                            echo '' . substr($exli, 0, 140) . '<br>';
                        }
                        echo '</td>';
                    } else {
                        echo '<td>';
                        echo '0';
                        echo '</td>';
                        echo '<td></td>';
                    }
                    echo '		</tr>';
                }
                echo '</table>';
            }
            wp_reset_postdata();

            ?>
    </div>

<?php
}

function sscc_get_domain()
{
    // return get_option('home');
    return $_SERVER['HTTP_HOST'];
}

function sscc_is_link_available($content = '')
{
    if ($content == '') {
        return null;
    }

    $regexp = '<a[^>]*href="\s?([^ ]*)\s?"[^>]*>(.*?(?=<\/a>))<\/a>';

    if (preg_match_all("/$regexp/", $content, $matches, PREG_SET_ORDER)) {
        return $matches;
    }

    return null;
}


function sscc_is_internal_link($url) {
    $url = esc_url($url);
    // bypass #more type internal link
    $result = preg_match('/href(\s)*=(\s)*"[#|\/]*[a-zA-Z0-9-_\/]+"/', $url);

    if ($url == "") {
        //	return true;
    }

    if ($result) {
        return true;
    }

    $pos = strpos($url, sscc_get_domain());
    if ($pos !== false) {
        return true;
    }

    return false;
}

function sscc_external_links($content) {
    $matches = sscc_is_link_available($content);

    if ($matches === null) {
        return null;
    }

    $ex_links = array();

    // loop through each links
    for ($i = 0; $i < count($matches); $i++) {
        $url  = esc_url($matches[$i][1]);

        if (sscc_is_internal_link($url)) {
            continue;
        }

        array_push($ex_links, $url);
    } // end for loop

    return $ex_links;
}

function sscc_internal_links($content) {
    $matches = sscc_is_link_available($content);

    if ($matches === null) {
        return null;
    }

    $in_links = array();

    // loop through each links
    for ($i = 0; $i < count($matches); $i++) {
        $url  = esc_url($matches[$i][1]);

        if (sscc_is_internal_link($url)) {
            array_push($in_links, $url);
        }
    } // end for loop

    return $in_links;
}
