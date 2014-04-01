<?php
/**
* Encounter form to track any clinical parameter.
*
* Copyright (C) 2014 Joe Slam <trackanything@produnis.de>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package OpenEMR
* @author Joe Slam <trackanything@produnis.de>
* @link http://www.open-emr.org
*/

// Some initial api-inputs
$sanitize_all_escapes  = true;
$fake_register_globals = false;
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/acl.inc");
formHeader("Form: Track anything");

// check if we are inside an encounter
//if (! $encounter) { // comes from globals.php
 //die("Internal error: we do not seem to be in an encounter!");
//}



?>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $web_root; ?>/interface/forms/track_anything/style.css" type="text/css">  
<?php 
echo "<div id='ta_type'>";


// **** DB ACTION ******
$dbaction = $_POST['dbaction'];

// save new item to a track
//-----------------------------
if($dbaction == 'add'){
		$the_name 	= $_POST['name'];
		$the_descr 	= $_POST['description'];
		$the_pos 	= $_POST['position'];
		$the_parent = $_POST['parentid'];
		$the_type	= $_POST['the_type'];
		
		if($the_name != NULL){

			$insertspell  = "INSERT INTO form_track_anything_type ";
			$insertspell .= "(name, description, position, parent, active) VALUES (?,?,?,?,?)";
			$save_into_db = sqlInsert($insertspell, array($the_name, $the_descr, $the_pos, $the_parent,1));
		} else {
			if($the_type=='add') {
				echo "<br><span class='failure'>";
				echo xlt('Adding item to track failed') . ". ";
				echo xlt("Please enter at least the item's name") . ".";
				echo "</span><br><br>";
			}			
			if($the_type=='create') {
				echo "<br><span class='failure'>";
				echo xlt('Creating new track failed') . ". ";
				echo xlt("Please enter at least the track's name") . ".";
				echo "</span><br><br>";
			}
		}
}
// end save new item to track -----------------------------


// edit existing track/items
//-----------------------------
if($dbaction == 'edit'){
		$the_name 	= $_POST['name'];
		$the_descr 	= $_POST['description'];
		$the_pos 	= $_POST['position'];
		$the_item = $_POST['itemid'];
		
		if($the_name != NULL){
			
			$updatespell  = "UPDATE form_track_anything_type ";
			$updatespell .= "SET name = ?, description = ?, position = ? ";
			$updatespell .= "WHERE track_anything_type_id = ? ";
			sqlInsert($updatespell, array($the_name, $the_descr, $the_pos, $the_item));
		} else {
				echo "<br><span class='failure'>";
				echo xlt('Editing failed') . ". ";
				echo xlt("Field 'name' cannot be NULL") . ".";
				echo "</span><br><br>";

		}
}
// end edit -----------------------------

//-----------------------------
if($dbaction == 'delete'){
		$the_item 	= $_POST['itemid'];
		
		$deletespell  = "DELETE FROM form_track_anything_type ";
		$deletespell .= "WHERE track_anything_type_id = ? ";
		sqlInsert($deletespell,  $the_item);

		}

// end edit -----------------------------

// *** END DB ACTIONS


// Create a new track
$create_track = $_POST['create_track'];
if ($create_track){
	echo "<table class='create'><tr><td>";
	echo "<b>" . xlt('Create a new track')  . " </b><br>&nbsp;";	
	echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
	echo "<table>";
	echo "<tr>";
	echo "<th class='add'>" . xlt('Name') . "</th>";
	echo "<td class='add'><input type='text' size='12' name='name'></td>";
	echo "</tr><tr>";
	echo "<th class='add'>" . xlt('Description') . "</th>";
	echo "<td class='add'><input type='text' size='12' name='description'></td>";
	echo "</tr><tr>";
	echo "<th class='edit'>" . xlt('Position') . "</th>";
	echo "<td class='edit'><input type='text' size='12' name='position'></td>";
	echo "</tr>";
	echo "</table>";
	echo "<input type='hidden' name='parentid' value='0'>";
	echo "<input type='hidden' name='the_type' value='create'>";
	echo "<input type='hidden' name='dbaction' value='add'>";
	echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>";
	echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
	?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php 
	echo " />";
	echo "</form>";
	echo "</td></tr></table>";
	
} // end create new track

// user clicked some buttons...
$the_item = $_POST['typeid'];
if($the_item){
	$add = $_POST['add'];
	$edit = $_POST['edit'];
	$delete = $_POST['delete'];

	// add a new item to track
	//------------------------	
	if ($add){
		// add item to parent
		echo "<table class='add'><tr><td>";
		$spell  = "SELECT name FROM form_track_anything_type ";
		$spell .= "WHERE track_anything_type_id = ?";
		$myrow = sqlQuery($spell, array($the_item));
		echo "<br>&nbsp;&nbsp;";
		echo xlt('Add item to track')  . " <b>" . text($myrow['name']) . "</b><br>&nbsp;";
		echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
		echo "<table>";
		echo "<tr>";
		echo "<th class='add'>" . xlt('Name') . "</th>";
		echo "<td class='add'><input type='text' size='12' name='name'></td>";
		echo "</tr><tr>";
		echo "<th class='add'>" . xlt('Description') . "</th>";
		echo "<td class='add'><input type='text' size='12' name='description'></td>";
		echo "</tr><tr>";
		echo "<th class='edit'>" . xlt('Position') . "</th>";
		echo "<td class='edit'><input type='text' size='12' name='position'></td>";

		echo "</tr>";
		echo "</table>";
		echo "<input type='hidden' name='parentid' value='" . attr($the_item) . "'>";
		echo "<input type='hidden' name='dbaction' value='add'>";
		echo "<input type='hidden' name='the_type' value='add'>";
		echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>";
		echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
		?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php 
		echo " />";
		echo "</form>";
		echo "</td></tr></table>";
		
	}// end add item------------------

	
	if ($edit){
		echo "<table class='edit'><tr><td>";
		$spell  = "SELECT name, description, position FROM form_track_anything_type ";
		$spell .= "WHERE track_anything_type_id = ?";
		$myrow = sqlQuery($spell, array($the_item));		
		$the_name 	= $myrow['name'];		
		$the_descr 	= $myrow['description'];
		$the_pos 	= $myrow['position'];
		echo "<br>&nbsp;&nbsp;";
		echo xlt('Edit')  . " <b>" . text($the_name) . "</b><br>&nbsp;";
		echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
		echo "<table>";
		echo "<tr>";
		echo "<th class='edit'>" . xlt('Name') . "</th>";
		echo "<td class='edit'><input type='text' size='12' name='name' value='" . attr($the_name) . "'></td>";
		echo "</tr><tr>";
		echo "<th class='edit'>" . xlt('Description') . "</th>";
		echo "<td class='edit'><input type='text' size='12' name='description' value='" . attr($the_descr) . "'></td>";
		echo "</tr><tr>";
		echo "<th class='edit'>" . xlt('Position') . "</th>";
		echo "<td class='edit'><input type='text' size='12' name='position' value='" . attr($the_pos) . "'></td>";
		echo "</tr>";
		echo "</table>";
		echo "<input type='hidden' name='itemid' value='" . attr($the_item) . "'>";
		echo "<input type='hidden' name='dbaction' value='edit'>";
		echo "<input type='submit' name='addsave' value='" . xla('Save') . "'>";
		echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
		?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php 
		echo " />";
		echo "</form>";
		echo "</td></tr></table>";
		}
	
	if ($delete){
		echo "<table class='del'><tr><td>";
		$spell  = "SELECT name FROM form_track_anything_type ";
		$spell .= "WHERE track_anything_type_id = ?";
		$myrow = sqlQuery($spell, array($the_item));		
		$the_name 	= $myrow['name'];	

		echo "<br>&nbsp;&nbsp;<span class='failure'>";
		echo xlt('Are you sure you want to delete ') . " <b>" . text($the_name) . "</b>?</span><br>";	
		echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
		echo "<input type='hidden' name='itemid' value='" . attr($the_item) . "'>";
		echo "<input type='hidden' name='dbaction' value='delete'>";
		echo "&nbsp;&nbsp;<input type='submit' class='delete_button' name='addsave' value='" . xla('Delete') . "'>";
		echo "&nbsp;&nbsp;<input type='button' class='nodelete_button' name='stop' value='" . xla('Back') . "' ";
		?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/create.php'"<?php 
		echo " />";
		echo "</form><br><br>";	
		echo "</td></tr></table>";
		}
} //end user clicked button



// ================================================================0
// Here comes the page...

echo "<br>&nbsp;&nbsp;<b>";
echo xlt('Create and modify tracks');
echo "</b><br><br>";
echo "<table width='100%'>";
 echo "<tr>";
  echo "<th width='30%'>" . xlt('Name') . "</th>";
  echo "<th width='45%'>" . xlt('Description') . "</th>";
  echo "<th width='5%'>" . xlt('Pos') . ".</th>";
  echo "<th width='20%'>&nbsp; </th>";
 echo "</tr>";
// get all track-setups
$spell  = "SELECT * FROM form_track_anything_type ";
$spell .= "WHERE parent = 0 AND active = 1 ";
$spell .= "ORDER BY position ASC, name ASC";
$result = sqlStatement($spell);
while($myrow = sqlFetchArray($result)){ 
	$type_id 	= $myrow['track_anything_type_id'];
	$type_name 	= $myrow['name'];
	$type_pos = $myrow['position'];
	$type_descr = $myrow['description'];
	echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
	
	echo "<tr>";
	echo "<td class='parent'>&nbsp;&nbsp;" . text($type_name) . "</td>";
	echo "<td class='parent'>&nbsp;&nbsp;" . text($type_descr) . "</td>";
	echo "<td class='parent'>&nbsp;&nbsp;" . text($type_pos) . "</td>";
	echo "<td class='op'>";#[" . xlt('Edit') . "] [" . xlt('Add') . "]</td>";
	echo "<input type='submit' class='ta_button' name='add' value='" . xla('Add') . "'>";
	echo "<input type='submit' class='ta_button' name='edit' value='" . xla('Edit') . "'>";
	echo "<input type='submit' class='delete_button' name='delete' value='" . xla('Del') . "'>";
	echo "<input type='hidden' name='typeid' value='" . attr($type_id) . "'>";
	echo "</td></tr>";
	echo "</form>";
	$spell2  = "SELECT * FROM form_track_anything_type ";
	$spell2 .= "WHERE parent = ? AND active = 1 ";
	$spell2 .= "ORDER BY position ASC, name ASC";
	$result2 = sqlStatement($spell2, array($type_id));
	while($myrow2 = sqlFetchArray($result2)){ 
		$item_id		= $myrow2['track_anything_type_id'];
		$item_name		= $myrow2['name'];
		$item_pos = $myrow2['position'];
		$item_descr	= $myrow2['description'];
		echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
		echo "<tr>";
		echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_name) . "</td>";
		echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_descr) . "</td>";	
		echo "<td class='child'>&nbsp;&nbsp;&nbsp;&nbsp; | " . text($item_pos) . "</td>";
		echo "<td class='op'>";
		echo "<input type='submit' class='ta_button' name='edit' value='" . xla('Edit') . "'>";
		echo "<input type='submit' class='delete_button' name='delete' value='" . xla('Del') . "'>";
		echo "<input type='hidden' name='typeid' value='" . attr($item_id) . "'>";
		echo "</td></tr>";
		echo "</form>";
	} // end while $myrow2
	echo "</tr>";
} // end while $myrow
echo "</table>";

echo "<p align='center'>";
echo "<form method='post' action='" . $rootdir . "/forms/track_anything/create.php' onsubmit='return top.restoreSession()'>"; 
echo "<input type='submit' name='create_track' value='" . xla('Create new Track') . "' >";
echo "<input type='button' name='stop' value='" . xla('Back') . "' ";
// if in an encounter, go back to "select track"
if ($encounter){ 
?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/forms/track_anything/new.php'"<?php 
// if not in an encounter, go back to "demographics"
} elseif(!$encounter){ 
?> onclick="top.restoreSession();location='<?php echo $web_root ?>/interface/patient_file/summary/demographics.php'"<?php 
}
echo " />";
echo "</p>";
echo "</form>";
echo "</div>";
?>
