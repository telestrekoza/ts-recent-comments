<?php
/**
 * @package ts-recent-comments
 */
/*
Plugin Name: Telestrekoza recent comments
Plugin URI: https://telestrekoza.com/
Description: comment widget writen for the <a href="https://telestrekoza.com">Telestrekoza</a>
Version: 2.0
Author: Nazar Kulyk
Author URI: http://myeburg.net/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

namespace ts\widgets;

class TSRecentComments extends \WP_Widget {
	function __construct()
	{
		parent::__construct('ts_recent_comments', __('Telestrekoza recent comments','ts_recent_comments'),
			array( 'description' => __('Telestrekoza recent comments widget', 'ts_recent_comments'))
		);
	}

	function widget( $args, $instance )
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if($title)
		    echo $before_title . $title . $after_title;
		echo '<div class="textwidget">';
		echo $this->output_recent_comments($instance['com_count']);
		echo '</div>';
		echo $after_widget;
	}
	
	function update ( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$com_count = $new_instance['com_count'];
		if ($com_count > 0 && $com_count <= 20)
		    $instance['com_count'] = $new_instance['com_count'];
		return $instance;
	}
	
	function form( $instance )
	{
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$com_count = esc_attr( $instance[ 'com_count' ] );
		} else {
			$title = __( 'New title', 'text_domain' );
			$com_count = 5;
		}
		echo '<p>';
		echo '<label for="'. $this->get_field_id('title') .'">'. _e('Title:') .'</label>';
		echo '<input class="widefat" id="'. $this->get_field_id('title') .'" name="'. $this->get_field_name('title') .'" type="text" value="'. $title .'" />';
		echo '<label for="'. $this->get_field_id('com_count') .'">'. _e('Count:','ts_recent_comments') .'</label>';
		echo '<input class="widefat" id="'. $this->get_field_id('com_count') .'" name="'. $this->get_field_name('com_count') .'" type="text" value="'. $com_count .'" />';

		echo '</p>';
	}
	
	function output_recent_comments($no_comments = 5, $comment_lenth = 3, $before = '<li>', $after = '</li>', $show_pass_post = false, $comment_style = 0)
	{
		global $wpdb;
		
		$request = "SELECT ID, comment_ID, comment_content, comment_author_email, user_id, comment_author, comment_author_url, post_title FROM $wpdb->comments LEFT JOIN $wpdb->posts ON $wpdb->posts.ID=$wpdb->comments.comment_post_ID WHERE post_status IN ('publish','static') ";
		
		if(!$show_pass_post) $request .= "AND post_password ='' ";
		$request .= "AND comment_approved = '1' ORDER BY comment_ID DESC LIMIT $no_comments";
		$comments = $wpdb->get_results($request);
		$output = '';
		
		if ($comments) {
			foreach ($comments as $comment) {
				$comment_author = stripslashes($comment->comment_author);
					if (empty($comment_author))
						$comment_author = "anonymous";
				$comment_content = strip_tags($comment->comment_content);
				$comment_content = stripslashes($comment_content);
				$words=explode(' ',$comment_content);
				$comment_excerpt = join(" ",array_slice($words,0,$comment_lenth));
				$comment_excerpt1 = join(" ",array_slice($words,$comment_lenth));
				if(!empty($comment_excerpt1)) {
					$comment_excerpt .= '&nbsp;<span id="comment_resume_'.$comment->comment_ID.'" class="hide">'.$comment_excerpt1;
					$comment_excerpt .= '</span>';
					$comment_second_part = true;
				} else
					$comment_second_part = false;
				$permalink = get_permalink($comment->ID).'#comment-'.$comment->comment_ID;
				$post_title = stripslashes($comment->post_title);
				
				if ($comment_style == 1) {
					$url = $comment->comment_author_url;
					
					if (empty($url))
						$output .= $before . $comment_author . ' on ' . $post_title . '.' . $after;
					else
						$output .= $before . "<a href='$url' rel='external nofollow'>$comment_author</a>" . ' on ' . $post_title . '.' . $after;
				} else {
					$output .= '<div title="Этот комментар написан '.$comment_author.' к теме '.$post_title.'" class="comment_resume';
					if($comment == end($comments))
						$output .= " last comment_resume_last";
					$output .= '">';
					if($comment_second_part)
						$output .= '<a href="#" title="раскрыть/скрыть" class="expand_comment"><img src="/static/images/switch/exp_coll.png" width="16" hight="10" alt="раскрыть/скрыть"/></a>';
					$output .= $this->get_avatar($comment, $size='24');
					$output .= $comment_excerpt;
					$output .= '<a href="' . $permalink;
					$output .= '" class="more" title="Узнать больше"> дальше</a>';
					//$output .= '<div class="clear"></div>';
					$output .= '</div>';
				}
			}
			$output = convert_smilies($output);
		} else {
			$output .= $before . 'Пока нет' . $after;
		}
		return $output;
	}
	
	function get_avatar( $id_or_email, $size = '96', $default = '', $alt = false )
	{
		if ( ! get_option('show_avatars') )
			return false;
		
		if ( false === $alt)
			$safe_alt = '';
		else
			$safe_alt = esc_attr( $alt );
		
		if ( !is_numeric($size) )
			$size = '96';
		
		$email = '';
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		} elseif ( is_object($id_or_email) ) {
			if ( isset($id_or_email->comment_type) && '' != $id_or_email->comment_type && 'comment' != $id_or_email->comment_type )
				return false; // No avatar for pingbacks or trackbacks
			if ( !empty($id_or_email->user_id) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ( $user)
					$email = $user->user_email;
			} elseif ( !empty($id_or_email->comment_author_email) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}
		
		if ( empty($default) ) {
			$avatar_default = get_option('avatar_default');
			if ( empty($avatar_default) )
				$default = 'mystery';
			else
				$default = $avatar_default;
		}
		
		if ( is_ssl() )
			$host = 'https://secure.gravatar.com';
		else
			$host = 'http://www.gravatar.com';
		
		if ( 'mystery' == $default )
			$default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
		elseif ( 'blank' == $default )
			$default = includes_url('images/blank.gif');
		elseif ( !empty($email) && 'gravatar_default' == $default )
			$default = '';
		elseif ( 'gravatar_default' == $default )
			$default = "$host/avatar/s={$size}";
		elseif ( empty($email) )
			$default = "$host/avatar/?d=$default&amp;s={$size}";
		elseif ( strpos($default, 'https://') === 0 )
			$default = add_query_arg( 's', $size, $default );
		
		if ( !empty($email) ) {
			$out = "$host/avatar/";
			$out .= md5( strtolower( $email ) );
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );
			$rating = get_option('avatar_rating');
			if ( !empty( $rating ) )
				$out .= "&amp;r={$rating}";
			$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		} else {
			$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
		}
		return apply_filters('get_avatar_org', $avatar, $id_or_email, $size, $default, $alt);
	}
}

add_action('widgets_init', function() {
    load_plugin_textdomain('ts_recent_comments', false, basename( dirname( __FILE__ ) ) . '/languages' );
    return register_widget('ts\widgets\TSRecentComments');
});


?>