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
	parent::__construct('ts_recent_comments', 'Telestrekoza recent comments widget', 
	    array( 'description' => 'Telestrekoza recent comments widget')
	    );
    }
    
    function widget( $args, $instance )
    {
	echo "Hello World";
    }
}

add_action('widgets_init', function() {
    return register_widget('ts\widgets\TSRecentComments');
});

?>