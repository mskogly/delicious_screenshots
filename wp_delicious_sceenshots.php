<?php

/*

Plugin Name: Delicious Screenshots

Plugin URI: http://pappmaskin.no#

Description: This plugin fetches your links from del.icio.us and presents them on posts or pages with screenshots / thumbnail created by the Thumbalizr.com service. Example use: [deliciousthumbs tag="design" count="9"] Remember to add your del.icio.us username under options <a href="options-general.php?page=wp_delicious_screenshots.php">Configure options...</a>

Author: Morten Skogly

Version: 0.7

Author URI: http://pappmaskin.no/

*/

/*  Copyright 2017  MORTEN SKOGLY  (email : morten.skogly@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software

    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    Or, if you aren't completely insane: Go to http://www.gnu.org/licenses/gpl.html and get it there.

*/

// sets up the variable deliciousLinks, which will hold a lovely lump of html later.

$deliciousLinks = "Test";

//Maybe for later
//$dir = plugin_dir_path( __FILE__ );

//$baseurl = $dir . "cache-rss.php?cachetime=3600&url=http://feeds.del.icio.us/v2/rss"; //this should be changed to use siteinfo and pluginpath etc
//http://feeds.del.icio.us/v2/rss/mskogly/art


$baseurl =""; //empty for now. Can be used to call an external script for caching later
$feedurl = $baseurl . "http://feeds.del.icio.us/v2/json"; //sets the feedurl which is eventually used to ask del.icio.us for links, we will add to this feedurl later

function deliciousthumbsbuilder($tag,$count)

{

	global $deliciousLinks, $feedurl;

		
$delicioususername = "/" . get_option('delicious_username'); //gets username from wordpress options page. Needs to be wrapped in if 

$feedurl .= $delicioususername; //dynamic username remember if -> insert default username?

//Make it allowed to pass no tag = fetch the latest links for a user, or everyone.
if($tag != ""){
	$feedurl .= "/" . urlencode($tag); //adds tag to the feedurl, but only if there is a tag provided in the shortcode. 
}

if($count != ""){

	$feedurl .= "?count=" . $count; //adds count to the feedurl.

}

$items = json_decode(file_get_contents($feedurl), true); //Todo: graceful fail and caching

$deliciousLinks = '<div class="deliciouscontainer">';

foreach($items as $item) { //foreach element in $items (json from delicious)
    $itemtitle = $item['d']; 
    $itemlink = $item['u'];     
 
    $deliciousLinks .= '<div class="deliciousbox">';
    $deliciousLinks .= '<a href="' . $itemlink .'" target="_blank">';

    //path needs to be changed here to incorporate thumbalizr key
    $deliciousLinks .= '<img src="https://api.thumbalizr.com/?width=250&amp;url=' . $itemlink . '" alt="Screenshot for ' . $itemlink . '"/></a>';
    $deliciousLinks .= '<p>' . $itemtitle . '</p></div>'; 

} //end foreach pr item

$deliciousLinks .= '<br style="clear:both;"/></div>';
return $deliciousLinks . $feedurl;

}


// We need some CSS to make things pretty

function delicious_screenshots_css() {

//do flexbox instead

	echo "

	<style type='text/css'>

        .deliciouscontainer {
            display: flex;
            flex-wrap: wrap;
            align-content: flex-start;
            justify-content: center; 
        }

        .deliciousbox {
        max-width: 50%;
        width: 250px;
        text-align: center;
        }

	</style>
	";
}

//ADMIN MENU http://codex.wordpress.org/Adding_Administration_Menus

function Delicious_Screenshots_admin_menu() {

   if (function_exists('add_options_page')) {

        add_options_page('Delicious Screenshots Options Page', 'Delicious Screenshots', 'manage_options', basename(__FILE__), 'delicious_screenshots_subpanel');

        }

}


// delicious_SnapCasa_subpanel() displays the page content for the Delicious SnapCasa submenu / optionpage

function delicious_screenshots_subpanel() {

    // variables for the field and option names 
    //https://codex.wordpress.org/Administration_Menus

    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    $hidden_field_name = 'mt_submit_hidden';

	$delicioususername_opt_name = 'delicious_username';
    $delicioususername_data_field_name = 'delicious_username';

	$thumbalizrapi_opt_name = 'thumbalizr_api';
   	$thumbalizrapi_data_field_name = 'thumbalizr_api';

    // Read in existing option value from database

	$opt_val = get_option( $delicioususername_opt_name );
    $thumbalizrapi_opt_val = get_option( $thumbalizrapi_opt_name );
	

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'

    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {

        // Read their posted value

        $opt_val = $_POST[ $delicioususername_data_field_name ];

	    $thumbalizrapi_opt_val = $_POST[ $thumbalizrapi_data_field_name ];

        // Save the posted value in the database

        update_option( $delicioususername_opt_name, $opt_val );

	update_option( $thumbalizrapi_opt_name, $thumbalizrapi_opt_val );

        // Put an options updated message on the screen


?>

<div class="updated"><p><strong><?php _e('Options saved.', 'delicious_screenshots_trans_domain' ); ?></strong></p></div>

<?php



    }

    // Now display the options editing screen
    echo '<div class="wrap">';

    // header
    echo "<h2>" . __( 'Delicious Screenshots Plugin Options', 'delicious_screenshots_trans_domain' ) . "</h2>";

    // options form
 
    ?>

    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <p><?php _e("Your Delicious user name:", 'delicious_screenshots_trans_domain' ); ?> 

    <input type="text" name="<?php echo $delicioususername_data_field_name; ?>" value="<?php echo $opt_val; ?>" size="20">

    </p>

    <p><?php _e("Your Thumbalizr API Key:", 'delicious_screenshots_trans_domain' ); ?> 

    <input type="text" name="<?php echo $thumbalizrapi_data_field_name; ?>" value="<?php echo $thumbalizrapi_opt_val; ?>" size="20">

    </p>

    <hr />


    <p class="submit">

    <input type="submit" name="Submit" value="<?php _e('Update Options', 'delicious_screenshots_trans_domain' ) ?>" />

    </p>

    </form>

</div>

<?php

}

// Adding Shortcode

// Example of use in a post or page : [deliciousthumbs tag="art" count="10"]

function deliciousthumbs_func($atts) {

		global $deliciousLinks; //probably not in use

	extract(shortcode_atts(array(

		'tag' => 'inspiration',

		'count' => '3',

	), $atts));



	return deliciousthumbsbuilder($tag,$count); //It works! Now change this to call function with tag and count as parameters.

}

add_shortcode('deliciousthumbs', 'deliciousthumbs_func');
add_action('admin_menu', 'Delicious_Screenshots_admin_menu');
add_action('wp_head', 'delicious_screenshots_css');
?>
