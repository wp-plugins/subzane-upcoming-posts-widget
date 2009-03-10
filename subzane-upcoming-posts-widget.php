<?php
/*
Plugin Name: SubZane Upcoming Posts Widget
Plugin URI: 
Description: 
Author: Andreas Norman
Version: 1.0
Author URI: http://www.subzane.com
*/
function subzane_upcoming_posts_widget_init() {
	if ( !function_exists('register_sidebar_widget') ) {
		return;
	}
		
	function subzane_upcoming_posts_widget($args) {
		extract($args);
		global $wpdb;
				
		$options = get_option('subzane_upcoming_posts_widget');
		
		$subzane_upcoming_posts_category = !empty($options['subzane_upcoming_posts_category']) ? htmlspecialchars($options['subzane_upcoming_posts_category'], ENT_QUOTES) : -1;
		$title = !empty($options['title']) ? htmlspecialchars($options['title'], ENT_QUOTES) : 'Upcoming posts';
		$subzane_upcoming_posts_upcoming_count = !empty($options['subzane_upcoming_posts_upcoming_count']) ? htmlspecialchars($options['subzane_upcoming_posts_upcoming_count'], ENT_QUOTES) : 5;
		
		// Get unpublished posts in the selected category
	 	$querystr = "
			SELECT $wpdb->posts.post_name, $wpdb->posts.post_title, $wpdb->posts.post_date FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
			LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->posts.post_status = 'future'
			";
		if ($subzane_upcoming_posts_category > 0) {
		$querystr .= "
			AND $wpdb->term_taxonomy.term_id = ".$subzane_upcoming_posts_category."
			AND $wpdb->term_taxonomy.taxonomy = 'category'
		";
		}
		$querystr .= "
			GROUP BY $wpdb->posts.ID
			ORDER BY $wpdb->posts.post_date DESC
			LIMIT ".($subzane_upcoming_posts_upcoming_count). "
	 	";
	 	$upcoming_posts = $wpdb->get_results($querystr, OBJECT);

		echo $before_widget;
		echo $before_title . $title . $after_title;

		if (count($upcoming_posts) > 0) {
			echo '
			<div>
			<ul>
			';
			for ($i=0; $i<count($upcoming_posts); $i++) {
				echo '<li><span class="date">'.subzane_upcoming_posts_get_date($upcoming_posts[$i]->post_date).'</span> '.$upcoming_posts[$i]->post_title.'</li>';
			}
			echo '
			</ul>
			</div>
			';
			$nextpost['post_title'] = $upcoming_posts[3]->post_title;
			$nextpost['post_date'] = $upcoming_posts[3]->post_date;
		} else {
			$nextpost['post_title'] = $upcoming_posts[0]->post_title;
			$nextpost['post_date'] = $upcoming_posts[0]->post_date;
		}
		
		echo $after_widget;
	}
	
	function subzane_upcoming_posts_get_date($datestring) {
		$parseddate = date_parse($datestring);
		$date = date  ("Y-m-d", mktime(0, 0, 0, $parseddate['month'], $parseddate['day'], $parseddate['year']));
		return $date;
	}
	
	function subzane_upcoming_posts_widget_control() {

		$options = get_option('subzane_upcoming_posts_widget');
		if ( isset($_POST['subzane_upcoming_posts_widget_submit']) ) {
			$options['title'] = strip_tags(stripslashes($_POST['subzane_upcoming_posts_title']));
			$options['subzane_upcoming_posts_upcoming_count'] = strip_tags(stripslashes($_POST['subzane_upcoming_posts_upcoming_count']));
			$options['subzane_upcoming_posts_category'] = strip_tags(stripslashes($_POST['subzane_upcoming_posts_category']));
			
			update_option('subzane_upcoming_posts_widget', $options);
		}
		
		$subzane_upcoming_posts_category = htmlspecialchars($options['subzane_upcoming_posts_category'], ENT_QUOTES);
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$subzane_upcoming_posts_upcoming_count = htmlspecialchars($options['subzane_upcoming_posts_upcoming_count'], ENT_QUOTES);
		$categories = get_categories('hide_empty=0&orderby=ID&order=asc');
						
		echo '
		<p>
			<label for="subzane_upcoming_posts_title">
			' . __('Title:') . '
				<input style="width: 250px;" name="subzane_upcoming_posts_title" id="subzane_upcoming_posts_title" type="text" value="'.$title.'" />
			</label>
		</p>
		<p>
			<label for="subzane_upcoming_posts_upcoming_count">
			' . __('Upcoming Count:') . '
				<input style="width: 250px;" name="subzane_upcoming_posts_upcoming_count" id="subzane_upcoming_posts_upcoming_count" type="text" value="'.$subzane_upcoming_posts_upcoming_count.'" />
			</label>
		</p>
		<p>
			<label for="subzane_upcoming_posts_category">
			' . __('Category:') . '
				<select name="subzane_upcoming_posts_category">';
					if ($subzane_upcoming_posts_category == -1) {
						echo '<option selected="selected" value="-1">All categories</option>';
					} else {
						echo '<option value="-1">All categories</option>';
					}
				foreach ($categories as $cat) {
					if (empty($cat->category_parent)) {
						if ($subzane_upcoming_posts_category == $cat->cat_ID) {
							echo '<option selected="selected" value="'.$cat->cat_ID.'">'.$cat->name.'</option>';
						} else {
							echo '<option value="'.$cat->cat_ID.'">'.$cat->name.'</option>';
						}
					}
				}
				echo '	
				</select>
			</label>
		</p>
		
		<input type="hidden" name="subzane_upcoming_posts_widget_submit" id="subzane_upcoming_posts_widget_submit" value="1" />
		';
	}
	
	register_widget_control(array('SZ Upcoming Posts', 'widgets'), 'subzane_upcoming_posts_widget_control', 350, 350);
	register_sidebar_widget(array('SZ Upcoming Posts', 'widgets'), 'subzane_upcoming_posts_widget');
}
add_action('plugins_loaded', 'subzane_upcoming_posts_widget_init');