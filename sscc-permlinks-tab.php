<?php

function sscc_permlinks()
{
	$qargs = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$the_query = new WP_Query($qargs);

	?>
	<div class="wrap">

		<h1><?php __('Simple SEO Criteria Check', 'simple-seo-criteria-check'); ?></h1>
		<?php echo sscc_admin_tabs(); ?>
		<?php echo '<h2>' . __('Permalink of your posts', 'simple-seo-criteria-check') . '</h2>'; ?>
		<div class="info-bar">
			<h3><?php echo __('Check for SEO friendly URLs', 'simple-seo-criteria-check'); ?></h3>
			<ul>
				<?php
					// https://neilpatel.com/blog/seo-urls/
					echo '<li>- ' . __('Check post\'s permalink lenght of URL.', 'simple-seo-criteria-check') . '</li>';
					echo '<li>- ' . __('The shorter the URL the better for Ranking', 'simple-seo-criteria-check') . '</li>';
					echo '<li>- ' . __('Check also for keywords within permalink', 'simple-seo-criteria-check');
					echo '<li>- ' . __('Use 3 to 5 words within a permalink', 'simple-seo-criteria-check') . '</li>';
					?>
			</ul>
		</div>
	<?php

		$surl = get_site_url();

		// The Loop
		if ($the_query->have_posts()) {
			echo '<table class="sscc widefat striped js-sort-table" id="permaurls">';
			echo '<tr>';
			echo '<th class="js-sort-string">' . __('Permalink', 'simple-seo-criteria-check') . '</th>';
			echo '<th class="js-sort-number">' . __('Length of Permalink', 'simple-seo-criteria-check') . '</th>';
			echo '<th class="js-sort-string">' . __('Full Permalink', 'simple-seo-criteria-check') . '</th>';
			echo '<th class="js-sort-number">' . __('Lenght of URL', 'simple-seo-criteria-check') . '</th>';
			echo '</tr>';
			while ($the_query->have_posts()) {
				$the_query->the_post();

				echo '<tr>';
				echo '	<td>';
				echo str_replace('/', '', str_replace($surl, "", get_permalink())) . '<br/><a href="' . get_edit_post_link(get_the_ID()) . '" target="_Blank">(' . __('edit', 'simple-seo-criteria-check') . ')</a>';
				echo '	</td>';
				echo '	<td align="center">' . strlen(str_replace($surl, "", get_permalink())) . '</td>';
				echo '	<td>';
				echo get_permalink();
				echo '	</td>';
				echo '	<td align="center">' . strlen(get_permalink()) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
		wp_reset_postdata();

		echo '</div>';
	}
