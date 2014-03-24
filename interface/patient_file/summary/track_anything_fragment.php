<?php
/*******************************************************************************\
 * Copyright (C) Joe Slam (trackanything@produnis.de)                           *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 ********************************************************************************/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");

?>
<div id='labdata' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br>
<?php
//retrieve tracks.
$spruch = "SELECT form_name, MAX(form_track_anything_results.track_timestamp) as maxdate, form_id " .
			"FROM forms " . 
			"JOIN form_track_anything_results ON forms.form_id = form_track_anything_results.track_anything_id " . 
			"WHERE forms.pid = ? " . 
			"AND form_name LIKE ? " .
			"GROUP BY form_name " .
			"ORDER BY maxdate DESC " . 
			"";
$result=sqlStatement($spruch, array($pid, "Track%") );
if ( !$result ) //If there are no disclosures recorded
{ ?>
  <span class='text'> <?php echo htmlspecialchars(xl("No tracks have been documented."),ENT_NOQUOTES); 
?>
  </span> 
<?php 
} else {  // We have some tracks here...
	echo "<span class='text'>";
	#echo "PID is " . $result;
	echo "Available Tracks:";
	echo "<ul>";
	$result=sqlStatement($spruch, array($pid, "Track%") );
	while($myrow = sqlFetchArray($result)){
		$formname = $myrow['form_name'];
		$thedate = $myrow['maxdate'];
		#$thedate = explode(" ", $thedate);
		$formid = $myrow['form_id'];
		echo "<li><a href='../../forms/track_anything/history.php?formid=" . $formid . "'>" . $formname . "</a></li> (" . $thedate . ")</li>";
	}
	echo "</ul>";
	echo "</span>";
} ?>
<br />
<br />
</div>
