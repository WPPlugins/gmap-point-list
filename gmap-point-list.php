<?php 
/**
*
* Plugin Name: gMap Point List
* Plugin URI: http://www.rafelsanso.com/plugin-para-wordpress-gmap-point-list/
* Description: Plugin for create your own markers on Google Maps, as a Custom Type posts, and with an easy integration on your page.
* Version: 1.0.1
* Author: Rafel Sansó
* Author URI: http://www.rafelsanso.com
* License: GNU General Public License v2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*
**/

add_action('init', 'wpg_post_type');
function wpg_post_type() {
	$labels = array(
	'name' => __('Markers', 'wpg'),
	'singular_name' => __('Marker', 'wpg'),
	'add_new' => __('New marker', 'wpg'),
	'add_new_item' => __('Add new marker','wpg'),
	'edit_item' => __('Edit marker','wpg'),
	'new_item' => __('New marker','wpg'),
	'view_item' => __('View marker','wpg'),
	'search_items' => __('Search markers','wpg'),
	'not_found' =>  __('We can\'t find this marker','wpg'),
	'not_found_in_trash' => __('The trash was empty','wpg'),
	'parent_item_colon' => ''
	);
	$args = array(
	'labels' => $labels,
	'menu_position' => 30,
	'public' => true,
	'menu_icon' => get_bloginfo('url').'/wp-content/plugins/gmap-point-list/marker.png',
	'query_var' => true,
	'supports' => array( 'title' )

	);
	register_post_type('marker',$args);
};

function cmb_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'init.php';

}
add_action( 'add_meta_boxes', 'add_map_box' );
function add_map_box() {
add_meta_box('wpg_map_content', 'Map', 'wpg_load_map', 'marker', 'normal', 'default');
}

function wpg_load_map() {
   global $post;
   $meta = get_post_meta( get_the_ID() );
   $lat = '39.56945695624538';
   if ($meta['_wpg__map_latitude'][0]!='') {
     $lat = $meta['_wpg__map_latitude'][0];
   }
   $lng = '2.6501673460006714';
   if ($meta['_wpg__map_longitude'][0]!='') {
     $lng = $meta['_wpg__map_longitude'][0];
   }
   ?>
   <style type="text/css">
		body {
			margin: 0;
			padding: 0;
			font-family: "Gill sans", sans-serif;
			background-color: #fff;
			color: #000;
		}
		div#bd {
			position: relative;
		}
		div#gmap {
			width: 100%;
			height: 300px;
		}
		</style>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
		<script type="text/javascript">
		var map;
		var marker=false;
		
	function initialize() {
	  var myLatlng = new google.maps.LatLng(<?php echo $lat.','.$lng; ?>);
	  var mapOptions = {
		center: new google.maps.LatLng(<?php echo $lat.','.$lng; ?>),
		zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	  };
	  var map = new google.maps.Map(document.getElementById('gmap'),
		mapOptions);
	
	  var input = /** @type {HTMLInputElement} */(document.getElementById('searchTextField'));
	  var autocomplete = new google.maps.places.Autocomplete(input);
	
	  autocomplete.bindTo('bounds', map);
	
	  var infowindow = new google.maps.InfoWindow();
	  var marker = new google.maps.Marker({
		map: map,
		draggable: true,
		position: myLatlng
	  });
	
	  google.maps.event.addListener(marker, 'dragend', function() {
		document.getElementById("_wpg__map_latitude").innerHTML = marker.getPosition().lat();
		document.getElementById("_wpg__map_longitude").innerHTML = marker.getPosition().lng();
		jQuery('#_wpg__map_latitude').val(marker.getPosition().lat());
		jQuery('#_wpg__map_longitude').val(marker.getPosition().lng());
	  });
	
	  google.maps.event.addListener(autocomplete, 'place_changed', function() {
		infowindow.close();
		marker.setVisible(false);
		input.className = '';
		var place = autocomplete.getPlace();
		if (!place.geometry) {
		  // Inform the user that the place was not found and return.
		  input.className = 'notfound';
		  return;
		}
	
		// If the place has a geometry, then present it on a map.
		if (place.geometry.viewport) {
		  map.fitBounds(place.geometry.viewport);
		} else {
		  map.setCenter(place.geometry.location);
		  map.setZoom(17);  // Why 17? Because it looks good.
		}
		/*
		marker.setIcon(({
		  url: place.icon,
		  size: new google.maps.Size(71, 71),
		  origin: new google.maps.Point(0, 0),
		  anchor: new google.maps.Point(17, 34),
		  scaledSize: new google.maps.Size(35, 35)
		}));*/
		marker.setPosition(place.geometry.location);
		marker.setVisible(true);
		document.getElementById("_wpg__map_latitude").innerHTML = marker.getPosition().lat();
		document.getElementById("_wpg__map_longitude").innerHTML = marker.getPosition().lng();
		jQuery('#_wpg__map_latitude').val(marker.getPosition().lat());
		jQuery('#_wpg__map_longitude').val(marker.getPosition().lng());
	
		var address = '';
		if (place.address_components) {
		  address = [
			(place.address_components[0] && place.address_components[0].short_name || ''),
			(place.address_components[1] && place.address_components[1].short_name || ''),
			(place.address_components[2] && place.address_components[2].short_name || '')
		  ].join(' ');
		}
	
		infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
		infowindow.open(map, marker);
	  });
	
	  // Sets a listener on a radio button to change the filter type on Places
	  // Autocomplete.
	  function setupClickListener(id, types) {
		var radioButton = document.getElementById(id);
		google.maps.event.addDomListener(radioButton, 'click', function() {
		  autocomplete.setTypes(types);
		});
	  }
	
	  setupClickListener('changetype-all', []);
	  setupClickListener('changetype-establishment', ['establishment']);
	  setupClickListener('changetype-geocode', ['geocode']);
	}
	
	google.maps.event.addDomListener(window, 'load', initialize);
		</script>
		<div id="panel" style="margin-left:-260px; display:none;">
          <input id="searchTextField" type="text" size="50">
          <input type="radio" name="type" id="changetype-all" checked="checked">
          <label for="changetype-all">All</label>
    
          <input type="radio" name="type" id="changetype-establishment">
          <label for="changetype-establishment">Establishments</label>
    
          <input type="radio" name="type" id="changetype-geocode">
          <label for="changetype-geocode">Geocodes</label>
        </div>
		<div><strong><?php echo __('Drag and drop the marker to the correct position.','wpg'); ?></strong></div>
		<div id="gmap"></div>
   <?php
}

function be_sample_metaboxes( $meta_boxes ) {
	$prefix = '_wpg_'; // Prefix for all fields
	
	$map_elements = array(); 
	$front_page_elements = get_option("pointer_map_elements"); 
	
	$element_counter = 0; 
    if (!empty($front_page_elements)) {
    foreach ($front_page_elements as $element) : 
		$map_elements[] = array( 'name' => $element, 'value' => $element_counter);
		$element_counter++; 
	endforeach; 
	}
	
	$meta_boxes[] = array(
		'id' => 'map_latitude',
		'title' => __('Marker definition','wpg'),
		'pages' => array('marker'), // post type
		'context' => 'normal',
		'priority' => 'high',
		'show_names' => true, // Show field names on the left
		'fields' => array( //searchTextField
			array(
				'name' => __('New marker options','wpg'),
				'desc' => __('The marker consists on fields that follow. To know the longitude and latitude, drag the marker on the map that you see on this screen, or search the full address.','wpg'),
				'id'   => $prefix . 'test_title',
				'type' => 'title',
			),
			array(
				'name' => __('Latitude','wpg'),
				'desc' => __('For the first value of Google Maps','wpg'),
				'id' => $prefix . '_map_latitude',
				'type' => 'text_medium'
			),
			array(
				'name' => __('Longitude','wpg'),
				'desc' => __('For the second value of Google Maps','wpg'),
				'id' => $prefix . '_map_longitude',
				'type' => 'text_medium'
			),
			array(
				'name' => __('Search address','wpg'),
				'desc' => __('Find the exact address on Google Maps.','wpg'),
				'id' => 'searchTextField',
				'type' => 'text_medium'
			),
			array(
				'name' => __('Full address','wpg'),
				'desc' => __('This address appears on the page. Not have to match the search address.','wpg'),
				'id' => $prefix . '_map_direction',
				'type' => 'wysiwyg'
			),
			array(
				'name' => __('Icon','wpg'),
				'desc' => __('Select an image for this marker. It is recommended to use a transparent PNG image of 24x24 pixels.','wpg'),
				'id'   => $prefix . 'marker_image',
				'type' => 'file'
			),
			array(
			'name' => __('Map','wpg'),
			'desc' => __('Select a map. If you not have created yet, you can create it in the Map List.','wpg'),
			'id' => $prefix . 'map_select',
			'type' => 'select',
			'options' => $map_elements
			),
			array(
				'name' => __('Order','wpg'),
				'desc' => '',
				'id' => $prefix . '_order',
				'type' => 'text_medium',
				'std' => '0'
			)
		),
	);

	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'be_sample_metaboxes' );

add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );

/* 
 * Create admin menu
 */
 
function setup_gmap_admin_menus() {  
    add_submenu_page('edit.php?post_type=marker',   
        'Map List', 'Map List', 'manage_options',   
        'gmap-point-list', 'gmap_page_settings'); 
}  

function gmap_page_settings() {
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	} 
	if (isset($_POST["update_settings"])) {  
		
		$front_page_elements = array();  
		  
		$max_id = esc_attr($_POST["element-max-id"]);  
		for ($i = 0; $i < $max_id; $i ++) {  
			$field_name = "element-page-id-" . $i;  
				$front_page_elements[] = esc_attr($_POST[$field_name]);
		}  
				
		update_option("pointer_map_elements", $front_page_elements);
		
		?>  
            <div id="message" class="updated"><?php echo __('Settings saved','wpg'); ?></div>  
        <?php
	}   
    ?>
    <div class="wrap">  
	<?php screen_icon('plugins'); ?> <h2><?php echo __('Gmap Point List Settings','wpg'); ?></h2>  
    
    <form method="post" action="">  
        <p><?php echo __('With this plugin you can create as many different maps as you need. Then, when you create a new marker, you can specify which of the maps you want to add. Finally, just add you shortocode appears next to the name of the map, to add it to the page or post you want. Please do not use special characters in the names of the maps.','wpg'); ?></p>
        <p><strong><?php echo __('Note:','wpg'); ?></strong> <?php echo __('If you delete a map with content, points will not be erased. So that you can create another map or assign them to an existing one.','wpg'); ?></p>
        <h3><?php echo __('Maps list','wpg'); ?></h3> 
        <table id="featured-posts-list" class="wp-list-table widefat"> 
        	<thead>
            	<tr>
                	<th><?php echo __('Map name','wpg'); ?></th>
                    <th><?php echo __('Page/post shortcode','wpg'); ?></th>
                    <th><?php echo __('Widget text shortcode','wpg'); ?></th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
        	<?php $front_page_elements = get_option("pointer_map_elements"); ?> 
			<?php $element_counter = 0; 
			if (!empty($front_page_elements)) {
			foreach ($front_page_elements as $element) : ?>  
                <tr class="front-page-element" id="front-page-element-<?php echo $element_counter; ?>">  
                    <td><input type="text" name="element-page-id-<?php echo $element_counter; ?>" value="<?php echo $element; ?>" /></td>
                    <td><span>[gmaplist map="<?php echo $element; ?>"]</span>  </td>
                    <td><span>[gmapwidget map="<?php echo $element; ?>"]</span>  </td>
                    <td><a href="#" onclick="removeElement(jQuery(this).closest('.front-page-element'));"><?php echo __('Remove','wpg'); ?></a> </td>
                </tr>
                     
            <?php $element_counter++; endforeach; } ?>  
        </table>  
        
        <input type="hidden" name="element-max-id"  value="<?php echo $element_counter; ?>" />  
        <input type="hidden" name="update_settings" value="Y" /> 
		<br>        
        <input type="button" value="<?php echo __('Add new map','wpg'); ?>" class="button-primary" id="add-featured-post" /> <input type="submit" value="Save settings" class="button-primary"/>    
    </form>  
    <table style="display:none;">
    <tr class="front-page-element" id="front-page-element-placeholder">  
        <td><input type="text" name="element-page-id" placeholder="<?php echo __('Map name','wpg'); ?>" />  </td>
        <td colspan="3"><a href="#" onclick="removeElement(jQuery(this).closest('.front-page-element'));"><?php echo __('Remove','wpg'); ?></a>  </td>
    </tr>
    </table>
    </div>
    
    <script type="text/javascript">  
		var elementCounter = jQuery("input[name=element-max-id]").val();  
		var elementRow = jQuery("#front-page-element-placeholder").clone(); 
		
		jQuery(document).ready(function() {             
			jQuery("#add-featured-post").click(function() {  
				var elementRow = jQuery("#front-page-element-placeholder").clone();  
				var newId = "front-page-element-" + elementCounter;  
					 
				elementRow.attr("id", newId);  
				elementRow.show();  
					 
				var inputField = jQuery("input", elementRow);  
				inputField.attr("name", "element-page-id-" + elementCounter);   
					  
				var labelField = jQuery("label", elementRow);  
				labelField.attr("for", "element-page-id-" + elementCounter);   
	  
				elementCounter++;  
				jQuery("input[name=element-max-id]").val(elementCounter);  
					  
				jQuery("#featured-posts-list").append(elementRow);  
					 
				return false;  
			});
			
			jQuery('input[name*="element-page-id-"]').change(function() {
				jQuery(this).parent().find('span').text('[gmaplist map="'+jQuery(this).val()+'"]');
				
			});
			         
		});  
		
		function removeElement(element) {  
			jQuery(element).remove();
			jQuery("input[name=element-max-id]").val(--elementCounter); 
		}  
		
		var removeLink = jQuery("a", elementRow).click(function() {  
			removeElement(elementRow);    
			return false;  
		});  
	</script>
    <?php
	
} 

add_action("admin_menu", "setup_gmap_admin_menus");

// Load shortcodes
function gmaplist_func( $atts ) {
	extract( shortcode_atts( array(
		'map' => 'map_name',
	), $atts ) );
	// Use the var with $map
	
	$map_elements = array(); 
	$front_page_elements = get_option("pointer_map_elements"); 
	
	$element_counter = 0; 
    if (!empty($front_page_elements)) {
    foreach ($front_page_elements as $element) : 
		if ($element == $map) {
			$code = $element_counter;
		}  
		//$map_elements[] = array( 'name' => $element, 'value' => $element_counter);
		$element_counter++; 
	endforeach; 
	}
	
	 $args = array(
	   'post_type' => 'marker',
	   'meta_key' => '_wpg_map_select',
	   'orderby' => '_wpg_order',
	   'posts_per_page' => '-1',
	   'order' => 'ASC',
	   'meta_query' => array(
		   array(
			   'key' => '_wpg_map_select',
			   'value' => $code,
			   'compare' => 'IN',
		   )
	   )
	 );
	 $query = new WP_Query($args);
	 
	 $output .= '
	<script src="http://maps.google.com/maps/api/js?v=3.5&amp;sensor=false" type="text/javascript"></script>
    <script src="'.plugins_url().'/gmap-point-list/js/markermanager.js" type="text/javascript"></script>
    <script src="'.plugins_url().'/gmap-point-list/js/StyledMarker.js" type="text/javascript"></script>
    <script src="'.plugins_url().'/gmap-point-list/js/jquery.metadata.js" type="text/javascript"></script>
    <script src="'.plugins_url().'/gmap-point-list/js/jquery.jmapping.min.js" type="text/javascript"></script>
    <style type="text/css">
		#map {
			width: 100%;
			height: 400px;
		}
		#map-side-bar {
			margin: 20px 0;
			width:100%;
		}
		.map-location {
			width:22%;
			margin-right:4%;
			float:left;
			min-height: 160px;
		}
		#map-side-bar .map-location:nth-child(5n+5) {
			margin-right:0;
		}

		#map-side-bar h2 {
			margin: 20px 0;
			border-bottom:1px solid #ccc;
			padding-bottom:5px; 
		}

		#map img, #canvas_map img { 
		  max-width: none;
		}

		#map label, #canvas_map label { 
		  width: auto; display:inline; 
		} 
		.info-box {display:none;}
		.clear { clear:both;}
	</style>
    <div id="map"></div>
	<div id="map-side-bar">
    ';
	$id = 1;
	 while ( $query->have_posts() ) :
	 	$query->the_post();
		$meta = get_post_meta( get_the_ID() );
		$icon = 'http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png';
		if ($meta['_wpg_marker_image'][0] != '') {
			$icon = $meta['_wpg_marker_image'][0];
		}
		//print_r($meta);
		$output .= '<div class="map-location" data-position="'.$meta['_wpg__order'][0].'" data-jmapping="{id: '.$id++.', point: {lng: '.$meta['_wpg__map_longitude'][0].', lat: '.$meta['_wpg__map_latitude'][0].'}, category: \''.$icon.'\'}">
            <div class="description-point">
                <h3><a href="#" class="map-link"><img src="'.$icon.'" alt="'.get_the_title().'" class="pull-left marRig10" /> '.get_the_title().'</a></h3>
                <p>'.($meta['_wpg__map_direction'][0]).'</p>
            </div>
            <div class="info-box">
            	<h3>'.get_the_title().'</h3>
                <p>'.($meta['_wpg__map_direction'][0]).'</p>
            </div>
        </div>';
		endwhile; 
		$output .= '
        <div class="clear"></div>
    </div>
    
	<script type="text/javascript">
      jQuery(document).ready(function(){
        jQuery(\'#map\').jMapping({
          category_icon_options: function(category){
			 return new google.maps.MarkerImage(category);
            
          }';
		  if ($id<=2) { 
		  $output .= '
		  ,
		  default_zoom_level:15';
		  }
	$output .= '	  
        });
      });
    </script>
    ';
	return $output;
}
add_shortcode( 'gmaplist', 'gmaplist_func' );
// Then use echo do_shortcode('[gmaplist map="mapname"]');

function gmapwidget_func( $atts ) {
	extract( shortcode_atts( array(
		'map' => 'map_name',
	), $atts ) );
	// Use the var with $map
	
	$map_elements = array(); 
	$front_page_elements = get_option("pointer_map_elements"); 
	
	$element_counter = 0; 
    if (!empty($front_page_elements)) {
    foreach ($front_page_elements as $element) : 
		if ($element == $map) {
			$code = $element_counter;
		}  
		//$map_elements[] = array( 'name' => $element, 'value' => $element_counter);
		$element_counter++; 
	endforeach; 
	}
	
	 $args = array(
	   'post_type' => 'marker',
	   'meta_key' => '_wpg_map_select',
	   'orderby' => '_wpg_order',
	   'posts_per_page' => '-1',
	   'order' => 'ASC',
	   'meta_query' => array(
		   array(
			   'key' => '_wpg_map_select',
			   'value' => $code,
			   'compare' => 'IN',
		   )
	   )
	 );
	 $query = new WP_Query($args);
	 ?>	
	<div id="gmapWidget">
     <dl class="dl-horizontal">
	 <?php
	 while ( $query->have_posts() ) :
	 	$query->the_post();
		$meta = get_post_meta( get_the_ID() );
		?>
        	
        	<dt>
            	<img src="<?php echo plugins_url().'/gmap-point-list'; ?>/images/mappointer.png" width="24" height="25" alt="<?php the_title(); ?>" />
            </dt>
            <dd>
            	<address>
                	<strong><?php the_title(); ?></strong><br>
                    <?php echo nl2br($meta['_wpg__map_direction'][0]); ?>
                </address>
            </dd>
        <?php endwhile; ?>
        </dl>
    </div>
    <?php
}
add_shortcode( 'gmapwidget', 'gmapwidget_func' );
add_filter('widget_text', 'do_shortcode');
// Then use echo do_shortcode('[gmapwidget map="mapname"]');
?>