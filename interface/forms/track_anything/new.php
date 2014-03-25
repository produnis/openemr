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
if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

// get vars posted by FORMs
if (!$formid){ 	
	$formid = $_GET['id']; 
	if (!$formid){ $formid = $_POST['formid'];	}
}

$myprocedureid =  $_POST['procedure2track'];

echo "<html><head>";
?> 
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $web_root; ?>/interface/forms/track_anything/style.css" type="text/css">  
<?php  
echo "</head><body class='body_top'>";
echo "<div id='track_anything'>";

// check if this Track is new
if (!$formid){
	// this is a new Track
	
	// check if procedure is selcted
	if ($_POST['bn_select']) {
		// "save"-Button was clicked, saving Form into db

		// save inbto db
		$query = "INSERT INTO form_track_anything (procedure_type_id) VALUES (?)";
  		$formid = sqlInsert($query, $myprocedureid);
		$spruch = "SELECT name FROM procedure_type WHERE procedure_type_id = ?";
		$query = sqlStatement($spruch,array($myprocedureid));
		while($myrow = sqlFetchArray($query)){$myprocedurename = $myrow["name"]; }
		$register_as = "Track: " . $myprocedurename;
		// adding Form
 		addForm($encounter, $register_as, $formid, "track_anything", $pid, $userauthorized);
		

	}else{
	// procedure is not yet selected
		echo "<table>";
		echo "<tr>";
		echo "<th>Select procedure to track:</th>";
		echo "</tr><tr>";
		echo "<td>";
		echo "<form method='post' action='" . $rootdir . "/forms/track_anything/new.php' onsubmit='return top.restoreSession()'>"; 

		echo "<select name='procedure2track' size='10' style='width: 300px'>";
		$testi = sqlStatement("SELECT * FROM procedure_type WHERE parent = 0 ORDER BY name ASC ");
		while($myrow = sqlFetchArray($testi)){ 
			$myprocedureid = $myrow["procedure_type_id"];
			$myprocedurename = $myrow["name"];
			echo "<option value='" . $myprocedureid . "'>" . $myprocedurename . "</option>";
		}
		echo "</select>";
		echo "</td></tr><tr><td align='center'>";
		echo "<input type='submit' name='bn_select' value='select' />";
?><input type='button' value='Back' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" /><?php
		echo "</form>";
		echo "</td></tr>";
		echo "</table>";
	}

}


// instead of "else", we check again for "formid"
if ($formid){
	// this is an existing Track

	//----------------------------------------------------	
	// get submitted item-Ids
	$mylist = $_POST['liste'];
	$length = count($mylist);
	$thedate = $_POST['datetime'];

	//check if whole input is NULL
	$all_are_null = 0;
	for($i= 0; $i < $length; $i++){
		$thisid = $mylist[$i];
		$thisvalue = $_POST[$thisid];
		if ($thisvalue != NULL && $thisvalue != '') {
			$all_are_null++;
		}
	}

	// if all of the input is NULL, we do nothing
	// if at least one entrie is NOT NULL, we save all into db
	if ($all_are_null > 0) {
		for($i= 0; $i < $length; $i++){
			$thisid = $mylist[$i];
			$thisvalue = $_POST[$thisid];

			// store data to track_anything_db
			$query = "INSERT INTO form_track_anything_results (track_anything_id, track_timestamp, itemid, result) VALUES ($formid, '$thedate', $thisid, '$thisvalue')";	
			sqlInsert($query);
		}
	}
	//----------------------------------------------------	



	// update corrected old items
	// ---------------------------
	
	// getting old entries from <form>
	$old_id 	= $_POST['old_id'];
	$old_time 	= $_POST['old_time'];
	$old_value 	= $_POST['old_value'];

	$how_many = count($old_time);
	// do this for each data row	
	for ($x=0; $x<=$how_many; $x++) {  			
		#echo "Date: ". $old_time[$x] . "<br>"; // DEBUGG
		// how many columns do we have
		$how_many_cols = count($old_value[$x]);
		for($y=0; $y<$how_many_cols; $y++){
			#echo "----ID: ". $old_id[$x][$y] . "<br>"; //DEBUG
			#echo "----value: " . $old_value[$x][$y] . "<br><br>"; //DEBUG
			
			#if($old_value[$x][$y] != NULL) { // commented out, so user can NULL entries
				// here goes the UPDATE sql-spruch
				$sprech  = "UPDATE form_track_anything_results ";
				$sprech .= "SET track_timestamp = ? , result = ? ";
				$sprech .= "WHERE id = ? ";
				#$sprech .= " ";
				sqlInsert($sprech, array($old_time[$x], $old_value[$x][$y], $old_id[$x][$y]));
			#}
		}
		
	}
//--------------------------------------------------


	//get procedure ID
	if (!$myprocedureid){
		$spruch = "SELECT procedure_type_id FROM form_track_anything WHERE id = ?";
		$testi = sqlStatement($spruch, array($formid));
		while($myrow = sqlFetchArray($testi)){ 
			$myprocedureid = $myrow["procedure_type_id"];
		}
	}
	echo "<br><b>Enter new data</b>:<br>";
	echo "<form method='post' action='" . $rootdir . "/forms/track_anything/new.php' onsubmit='return top.restoreSession()'>"; 
	echo "<table>";
	echo "<tr><th class='item'>Item</th>";
	echo "<th class='value'>Value</th></tr>";
	echo "<tr><td>Date Time</td>  <td><input size='12' name='datetime' value='" . date('Y-m-d H:i:s', time()) . "'></td></tr>";
	
	
	// get items to track
	$liste = array();
	$spruch = "SELECT * FROM procedure_type WHERE parent = ? ORDER BY procedure_type.name ASC ";
	$query = sqlStatement($spruch, array($myprocedureid));
	while($myrow = sqlFetchArray($query)){ 	
		echo "<input type='hidden' name='liste[]' value='". $myrow['procedure_type_id'] . "'>";	
		echo "<tr><td> " . $myrow['name'] . "</td>";
		echo "<td><input size='12' type='text' name='" . $myrow['procedure_type_id']  . "'></td></tr>";
	}

	echo "</table>";	
	echo "<input type='hidden' name='formid' value='". $formid . "'>";
	echo "<input type='submit' name='bn_save' value='save' />";
?><input type='button' value='stop' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" /><?php


	// show old entries of track
	//-----------------------------------
	// get unique timestamps of track
	echo "<br><br><hr><br>";
	echo "<b>Edit your entered data:</b><br>";
	$shownameflag = 0;	// flag if this is <table>-headline 
	echo "<table border='1'>";

	$pruch = "SELECT DISTINCT track_timestamp FROM form_track_anything_results WHERE track_anything_id = ? ORDER BY track_timestamp DESC";
	$query = sqlStatement($pruch,array($formid));
	$main_counter=0; // this counts 'number of rows'  of old entries
	while($myrow = sqlFetchArray($query)){ 
		$thistime = $myrow['track_timestamp'];
		$shownameflag++;
		
		$spruch  = "SELECT form_track_anything_results.id AS result_id, form_track_anything_results.itemid, form_track_anything_results.result, procedure_type.name AS der_name ";
		$spruch .= "FROM form_track_anything_results ";
		$spruch .= "INNER JOIN procedure_type ON form_track_anything_results.itemid = procedure_type.procedure_type_id ";
		$spruch .= "WHERE track_anything_id = ? AND track_timestamp = ? ";
		$spruch .= "ORDER BY der_name ASC ";
		$query2  = sqlStatement($spruch,array($formid ,$thistime));
		
		// <table> heading line
		if ($shownameflag==1){
			echo "<tr><th class='time'>Time</th>";
			while($myrow2 = sqlFetchArray($query2)){
				echo "<th class='item'>" . $myrow2['der_name'] . "</th>";		
			}
			echo "</tr>";		
		}
		
		echo "<tr><td bgcolor=#eeeeec>";
		$main_counter++; // next row
		echo "<input type='text' size='12' name='old_time[" . $main_counter . "]' value='" . $thistime. "'></td>";				
		$query2  = sqlStatement($spruch,array($formid ,$thistime));
		
		$counter = 0; // this counts columns 
		while($myrow2 = sqlFetchArray($query2)){
			echo "<td>";
			echo "<input type='hidden' name='old_id[" . $main_counter . "][" . $counter . "]' value='". $myrow2['result_id'] . "'>";
			echo "<input type='text' size='12' name='old_value[" . $main_counter . "][" . $counter . "]' value='" . $myrow2['result'] . "'></td>";
			$counter++; // next cloumn
		} 
		echo "</tr>";

	}
	echo "</tr></table>";
	echo "<input type='hidden' name='formid' value='". $formid . "'>";
	echo "<input type='submit' name='bn_save' value='save' />";
?><input type='button' value='stop' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" /><?php
	
	echo "</form>";
}//end if($formid)
echo "</div>";
formFooter();
?>
