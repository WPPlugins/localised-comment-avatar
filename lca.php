<?php

/*
Plugin Name: Localised Comment Avatar
Plugin URI: http://www.sleepingmonkey.co.uk/localised-comment-avatar/
Description: Allows commenters to define an avatar for use on your site.
Version: 2
Author: Ben Cardy
Author URI: http://www.bencardy.co.uk
*/

/*  Copyright 2007  BEN CARDY  (email : BENBACARDI@GMAIL.COM)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function processURL( $entered ) {

	if ( strpos( $entered, "http://" ) === FALSE ) {
	
		$pos = strpos( $entered, "/" );
	
	} else {
	
		$entered2 = substr( $entered, 7 );
		$pos = strpos( $entered2, "/" );
		$pos = $pos + 7;
	
	}

	$final = $_SERVER['DOCUMENT_ROOT'].substr( $entered, $pos );

	return $final;

}

function lca_install() {

	global $wpdb;
	
	$table_name = $wpdb->prefix . "lca";
	
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	
		$sql = "CREATE TABLE $table_name (
					lca_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
					lca_name VARCHAR( 50 ) NOT NULL,
					lca_email VARCHAR( 100 ) NOT NULL,
					lca_url VARCHAR( 300 ) NOT NULL,
					lca_path VARCHAR( 300 ) NOT NULL
				);";
				
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta( $sql );
	
	}

	add_option( "lca_name" , "1" );
	add_option( "lca_email" , "1" );
	add_option( "lca_url" , "1" );
	add_option( "lca_max_width" , "80" );
	add_option( "lca_max_height" , "80" );
	add_option( "lca_blank_avatar" , "0" );
	add_option( "lca_thank_you" , "Thank you for uploading your avatar." );
	add_option( "lca_avatar" , get_option( "siteurl" ) . "/uploads" );

}

function lca_add_menus() {

	add_management_page('LCA Management', 'LCA Management', 8, __FILE__, 'lca_manage');
	
}

function lca_avatar( $comment ) {

	global $wpdb;
	
	$where = "1=1";
			
	if ( get_option( "lca_name" ) ) {
	
		$where .= " AND lca_name = '" . $comment->comment_author . "'";
		
	}
	
	if ( get_option( "lca_email" ) ) {
	
		$where .= " AND lca_email = '" . $comment->comment_author_email . "'";
		
	}
	
	if ( get_option( "lca_url" ) ) {
	
		$where .=  " AND lca_url = '" . $comment->comment_author_url . "'";
		
	}
	
	$table = $wpdb->prefix . "lca";
	
	$sql = "SELECT * FROM $table WHERE $where";
	
	$rows = $wpdb->get_results( $sql );
	
	if ( is_array( $rows ) ) {
	
		$count = 0;
	
		foreach ( $rows as $row ) {
		
			$count ++;
		
			echo "<img src='" . get_option( "lca_avatar" ) . "/" . $row->lca_path . ".jpg' alt='comment_image' class='lca_image' />";
		
		}
		
		if ( $count == 0 ) {
		
			if ( get_option( "lca_blank_avatar" ) ) {
	
				echo "<img src='" . get_option( "lca_avatar" ) . "/default.jpg' alt='Default LCA Avatar Image' class='lca_image' />";
	
			}
			
		}
	
	}
}

function lca_form() {

	if ( $_POST['submit'] ) {
	
		global $wpdb;
	
		if (strpos($_FILES['imgfile']['type'],"jpeg") === false) {
		
			$error = true;
			$error_text = "The file was not a JPEG";
			
		} elseif ( ( get_option( "lca_name" ) && !$_POST['name'] ) || ( get_option( "lca_email" ) && !$_POST['email'] ) || ( get_option( "lca_url" ) && !$_POST['url'] ) ) {
		
			$error = true;
			$error_text = "All fields must be filled in.";		
		
		} else {
		
			$filename = time();
  
			$uploadedfile = $_FILES['imgfile']['tmp_name'];
			
			$src = imagecreatefromjpeg($uploadedfile);
			
			list($width,$height)=getimagesize($uploadedfile);
			
			$newwidth=get_option("lca_max_width");
			$newheight=($height/$width)*get_option("lca_max_width");
			if ($newheight > get_option("lca_max_height")) {
			$newheight=get_option("lca_max_height");
			$newwidth=($width/$height)*get_option("lca_max_height");			
			}
			$tmp=imagecreatetruecolor($newwidth,$newheight);
			
			imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);
			
			$path = processURL( get_option( "lca_avatar" ) );
			$filename1 = $path . "/$filename.jpg";
			imagejpeg($tmp,$filename1);
			
			imagedestroy($src);
			imagedestroy($tmp);
			
			$name = $_POST['name'];
			$email = $_POST['email'];
			$url = $_POST['url'];
			
			if (strpos($url,"http://") != 0) {
			  $url = "http://$url";
			}
			
			$table = $wpdb->prefix . "lca";
			
			$where = "1=1";
			
			if ( get_option( "lca_name" ) ) {
			
				$where .= " AND lca_name = '$name'";
				
			}
			
			if ( get_option( "lca_email" ) ) {
			
				$where .= " AND lca_email = '$email'";
				
			}
			
			if ( get_option( "lca_url" ) ) {
			
				$where .=  " AND lca_url = '$url'";
				
			}
			
			$sql = "SELECT count(lca_id) FROM $table WHERE $where";
			
			$rows = $wpdb->get_var( $sql );
			
			if ( strpos( $url, "http://" ) === false ) {
				
				$url = "http://$url";
				
			}
						
			if ( $rows > 0 ) {
			
				$sql = "UPDATE $table SET lca_name='$name', lca_email='$email', lca_url='$url', lca_path='$filename' WHERE $where";
				
			} else {
			
				$sql = "INSERT INTO $table VALUES ('NULL','$name','$email','$url','$filename')";
				
			}
			
			$wpdb->query( $sql );
			
			echo "<div class='lca_thankyou'>" . get_option( "lca_thank_you" ) . "</div>";
		
		}
	
	}
	
	if ( !$_POST['submit'] || $_POST['submit'] && $error ) {

	?>
	
	<form method="post" action="" enctype="multipart/form-data" class="lca_form">

		<fieldset>
			<legend>Upload a Comment Avatar</legend>
			
			<?php if ( $error ) { ?>
			<div class="lca_error"><?php echo $error_text; ?></div>
			<?php } ?>

			<?php if ( get_option( "lca_name" ) ) { ?>
			<p><lable for="name">Name:</label>
			<input type="text" name="name" class="lca_form_input" value="<?php echo $_POST['name']; ?>" /></p>
			<?php } ?>
			<?php if ( get_option( "lca_email" ) ) { ?>
			<p><label for="email">E-mail:</label>
			<input type="text" name="email" class="lca_form_input" value="<?php echo $_POST['email']; ?>" /></p>
			<?php } ?>
			<?php if ( get_option( "lca_url" ) ) { ?>
			<p><label for="url">URL:</label>
			<input type="text" name="url" class="lca_form_input" value="<?php echo $_POST['url']; ?>" /></p>
			<?php } ?>
			<p><label for="imgfile">Avatar:</label>
			<input type="file" name="imgfile" class="lca_form_input" /></p>
			
			<p><input type="submit" value="Upload Avatar" class="lca_form_submit" name="submit" /></p>
			
		</fieldset>
		
		</form>
	
	<?php
	
	}

}

function lca_manage() {

	global $wpdb;
		
	if ( $_GET['delete'] && is_numeric( $_GET['delete'] ) ) {
	
		if ( !$_GET['confirm'] ) {
		
			?>
			
			<div class="wrap">
			
			<h2 style="color: #f00">LCA Management - Delete</h2>
			
			<p>Are you sure you want to delete this entry?</p>
			
			<p><a href="edit.php?page=lca.php&delete=<?php echo $_GET['delete']; ?>&confirm=true">Yes</a> | <a href="edit.php?page=lca.php">No</a></p>
			
			</div>
			
			<?php		
		
		} else {
		
			$wpdb->query( "DELETE FROM " . $wpdb->prefix . "lca WHERE lca_id=" . $_GET['delete'] );
		
		}
	
	}

	if ( $_POST['update_options'] ) {
		
		if ( $_POST['name'] ) {
		
			update_option( "lca_name" , "1" );
			
		} else {
		
			update_option( "lca_name" , "0" );
			
		}
	
		if ( $_POST['email'] ) {
		
			update_option( "lca_email" , "1" );
			
		} else {
		
			update_option( "lca_email" , "0" );
			
		}
	
		if ( $_POST['url'] ) {
		
			update_option( "lca_url" , "1" );
			
		} else {
		
			update_option( "lca_url" , "0" );
			
		}
		
		
		if ( !$_POST['name'] && !$_POST['email'] && !$_POST['url'] ) {
		
			update_option( "lca_name" , "1" );
			update_option( "lca_email" , "1" );
			update_option( "lca_url" , "1" );
		
		}
		
		if ( $_POST['width'] ) {
		
			if ( is_numeric( $_POST['width'] ) ) {
				update_option( "lca_max_width" , $_POST['width'] );
			} else {
				update_option( "lca_max_width" , "80" );
			}
			
		}
		
		if ( $_POST['height'] ) {

			if ( is_numeric( $_POST['height'] ) ) {		
				update_option( "lca_max_height" , $_POST['height'] );
			} else {
				update_option( "lca_max_height" , "80" );
			}
			
		}
		
		if ( $_POST['default'] ) {
		
			update_option( "lca_blank_avatar" , "1" );
			
		} else { 
		
			update_option( "lca_blank_avatar" , "0" );
			
		}
		
		if ( $_POST['avatar'] ) {
		
			update_option( "lca_avatar" , $_POST['avatar'] );
			
		}
		
		if ( $_POST['thanks'] ) {
		
			update_option( "lca_thank_you" , $_POST['thanks'] );
			
		}
	
	}

	?>

	<div class='wrap'>
	
		<h2>LCA Management</h2>
		
		<?php if ( !is_writable( processURL( get_option( "lca_avatar" ) ) ) ) { ?>
		<div class="error" style="padding: 5px">The specified location <em><?php echo get_option( "lca_avatar" ); ?></em> is not writable. Please make this writable before using this plugin.</div>
		<?php } ?>
	
		<p>Which of the following do you wish to use to identify commenters?</p>
		
		<form method="post" action="edit.php?page=lca.php">
		
		<p><input type="checkbox" value="name" name="name" id="name"<?php if ( get_option( "lca_name" ) ) { ?> checked="checked"<?php } ?>/> Commenter's Name<br />
		<input type="checkbox" value="email" name="email" id="email"<?php if ( get_option( "lca_email" ) ) { ?> checked="checked"<?php } ?> /> Commenter's E-mail<br />
		<input type="checkbox" value="url" name="url" id="url"<?php if ( get_option( "lca_url" ) ) { ?> checked="checked"<?php } ?> /> Commenter's URL</p>
		
		<p><input type="checkbox" value="default" name="default" id="default"<?php if ( get_option( "lca_blank_avatar" ) ) { ?> checked="checked"<?php } ?>/> Display a default image when a commenter has not defined an avatar<br /><em>Default image should be located in your avatar folder and called 'default.jpg'. Please upload an image to use as a default image before enabling this option.</em></p>
		
		<p>Maximum Width of Image (pixels):<br />
		<input type="text" name="width" value="<?php echo get_option( "lca_max_width" ); ?>" style="width: 20%" /></p>
		<p>Maximum Height of Image (pixels):<br />
		<input type="text" name="height" value="<?php echo get_option( "lca_max_height" ); ?>" style="width: 20%" /></p>
		
		<p>Location of the upload folder (as seen from the Web):<br />
		<input type="text" name="avatar" value="<?php echo get_option( "lca_avatar" ); ?>" style="width: 95%" /></p>
		
		<p>Thank you message after a user has uploaded file:<br />
		<input type="text" name="thanks" value="<?php echo str_replace( array( "<" , ">" , "\"" ), array( "&lt;" , "&gt;", "&quot;" ) , stripslashes( get_option( "lca_thank_you" ) ) ); ?>" style="width: 95%" /></p>
		
		<p><input type="submit" value="Update Options &#187;" name="update_options" /></p>
		
		</form>
	
	</div>
	
	<div class="wrap">
	
		<h2>Current LCA Entries</h2>
		
		<table class="widefat">
		<thead>
		<tr>
		<th scope="col"><div style="text-align: center">ID</div></th>
		<th scope="col">Name</th>
		<th scope="col">E-mail</th>
		<th scope="col">URL</th>
		<th scope="col">Avatar</th>
		<th scope="col"> </th>
		</tr>
		</thead>
		<tbody id="thelist">
		
		<?php
		
		$rows = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "lca" );
		
		if ( is_array( $rows ) ) {
		
			foreach ( $rows as $row ) {
			
				echo "<tr>";
				echo "<th scope='row' style='text-align: center'>" . $row->lca_id . "</th>";
				echo "<td>" . $row->lca_name . "</td>";
				echo "<td>" . $row->lca_email . "</td>";
				echo "<td>" . $row->lca_url . "</td>";
				echo "<td><img src='" . get_option( "lca_avatar" ) . "/" . $row->lca_path . ".jpg' /></td>";
				echo "<td><a href='edit.php?page=lca.php&delete=" . $row->lca_id . "' class='delete'>Delete</a></td>";
				echo "</tr>";
			
			}
		
		}
		
		?>
		
		</tbody>
		</table>
		
	</div>
	
	<?php

}

add_action( "admin_menu", "lca_add_menus" );
add_action( "activate_lca.php", "lca_install" );

?>