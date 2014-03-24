<?php
/**
* How to present clinical parameter.
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
*
*
* this script needs $pid to run...
* 
* if you copy this file to another place,
* make sure you set $path_to_this_script
* to the propper path...


* Prepare your data:
* this script expects propper 'result_code' entries 
* in table 'procedure_results'. If your data miss 
* 'result_code' entries, you won't see anything, 
* so make sure they are there.
* [additionally, the script will also look for 'units', 
* 'range' and 'code_text'. If these data are not available, 
* the script will run anyway...]
* 
* the script will list all available patient's 'result_codes' 
* from table 'procedure_results'. Check those you wish to view. 
* If you see nothing to select, then
*    a) there is actually no lab data of this patient available
*    b) the lab data are missing 'result_code'-entries in table 'procedure_results'
* 

*/
// Some initial api-inputs
$sanitize_all_escapes  = true;
$fake_register_globals = false;
require_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

// Set the path to this script
$path_to_this_script = $rootdir . "/patient_file/summary/labdata.php";


// is this the printable HTML-option?
$printable = $_POST['print'];


// some styles and javascripts
// ####################################################
echo "<html><head>";
?> 
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css"> 
<link rel="stylesheet" href="<?php echo $web_root; ?>/interface/themes/labdata.css" type="text/css"> 
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" language="JavaScript">
function checkAll(bx) {
    for (var tbls=document.getElementsByTagName("table"), i=tbls.length; i--; )
      for (var bxs=tbls[i].getElementsByTagName("input"), j=bxs.length; j--; )
         if (bxs[j].type=="checkbox")
            bxs[j].checked = bx.checked;
}
</script>
<?php ##############################################################################
echo "</head><body class='body_top'>";
echo "<div id='labdata'>";
echo "<h2>LABDATA</h2>";
echo "<span class='text'>";
// some patient data...
$spruch  = "SELECT * ";
$spruch .= "FROM patient_data "; 
$spruch .= "WHERE id = ?";
//---
$query = sqlStatement($spruch,array($pid));
while($myrow = sqlFetchArray($query)){

	$nachname = $myrow["lname"];
	$vorname  = $myrow["fname"];
	$DOB  = $myrow["DOB"];
}
echo "<table border=0>";
echo "<tr><td>Patient: </td><td><b>" . $nachname . ", " . $vorname . "</b></td></tr>";
echo "<tr><td>Patient-ID: </td><td>" . $pid . "</td></tr>";
echo "<tr><td>Day of birth: </td><td>" . $DOB . "</td></tr>";
if($printable) {
	echo "<tr><td>Access date: </td><td>" . date('Y-m-d - H:i:s') . "</td></tr>";
	}

echo "</table>";
echo "<div id='reports_list'>";
if(!$printable){
	echo "<form method='post' action='" . $path_to_this_script . "' onsubmit='return top.restoreSession()'>"; 
	// What items are there for patient $pid?
	// -----------------------------------------------
	$werteliste = array();
	$wertewahl = $_POST['wert_code']; // what items are checkedboxed?
	$tab = 0;
	echo "Select items:";
	echo "<table border='1'>";
	echo "<tr><td>";
	
	$spruch  = "SELECT DISTINCT procedure_result.result_code AS wert_code ";
	$spruch .= "FROM procedure_result ";
	$spruch .= "JOIN procedure_report ";
	$spruch .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
	$spruch .= "JOIN procedure_order ";
	$spruch .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
	$spruch .= "WHERE procedure_order.patient_id = ? ";
	$spruch .= "AND procedure_result.result IS NOT NULL ";
	$spruch .= "AND procedure_result.result != ''";
	$spruch .= "ORDER BY procedure_result.result_code ASC ";
	#echo $spruch . "<br>";
	$query  = sqlStatement($spruch,array($pid));
	

	// Select which items to view...
	$i = 0;
	while($myrow = sqlFetchArray($query)){

		echo "<input type='checkbox' name='wert_code[]' value=" . $myrow['wert_code'] . " ";
		if($wertewahl){
			if (in_array($myrow['wert_code'], $wertewahl)){ echo "checked='checked' ";}
		}
		echo " /> " . $myrow['wert_code'] . "<br />";
		$werteliste[$i][wert_code] = $myrow['wert_code'];
		$i++;	
		$tab++;
		if($tab == 10) {
			echo "</td><td>";
			$tab=0;
		}	
	}
	echo "</tr>";
	echo "</table>";
	echo "</div>";
	
	?><input type='checkbox' onclick="checkAll(this)" /> Toggle All<br/> <?php
	echo "<table><tr>";
	// Choose output mode [list vs. matrix]
	echo "<td>Select output:</td>";
	echo "<td><input type='radio' name='mode' ";
	$mode = $_POST['mode'];
	if($mode == 'list'){ echo "checked='checked' ";}
	echo " value='list'> List<br>";
	
	echo "<input type='radio' name='mode' ";
	if($mode != 'list'){ echo "checked='checked' ";}
	echo " value='matrix'> Matrix<br>";

	echo "<td></td></td>";
	echo "</tr><tr>";
	echo "<td>";

    echo "<a href='../summary/demographics.php' ";
    if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
    echo " class='css_button' onclick='top.restoreSession()'>";
    echo "<span>" . htmlspecialchars(xl('Back to Patient'),ENT_NOQUOTES) . "</span></a>";

	echo "</td>";
	echo "<td><input type='submit' name='submit' value='submit' /></td>";
	echo "</tr></table>";
	echo "</form>";

} // end "if printable"
	echo "<br><br><hr><br>";
	
// print results of patient's items
//-------------------------------------------
$mode = $_POST['mode'];
$wertewahl = $_POST['wert_code'];
// are some Items selected?
if($wertewahl){

	// print in List-Mode
	if($mode=='list'){

		#$wertewahl = $_POST['wert_code'];
		$i = 0;
		$rowspan = count($wertewahl);
		echo "<table border='1' cellspacing='3'>";
		echo "<tr><th class='list'>Item</td>";
		echo "<th class='list'>Descr.</th> ";
		echo "<th class='list'>&nbsp;Result&nbsp;</th> ";
		echo "<th class='list'>Range</th> ";
		echo "<th class='list'>Units</th> ";
		echo "<th class='list'>Date</th> ";
		echo "<th class='list'>Review</th> ";
		echo "<th class='list'>Enc</th> ";
		echo "<th class='list'>resultID</th> ";
		echo "</tr>";
		// get complete data of each item
		foreach($wertewahl as $derwert){
		
			$spruch  = "SELECT procedure_result.procedure_result_id, procedure_result.result, procedure_result.result_text,  procedure_result.result_code, procedure_result.units, procedure_result.abnormal, procedure_result.range, ";
			$spruch .= "procedure_report.date_collected, procedure_report.review_status, ";
			$spruch .= "procedure_order.encounter_id ";
			$spruch .= "FROM procedure_result ";
			#$spruch .= " ";
			$spruch .= "JOIN procedure_report ";
			$spruch .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
			$spruch .= "JOIN procedure_order ";
			$spruch .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
			#$spruch .= " ";
			$spruch .= "WHERE procedure_result.result_code = ? "; // '?'
			$spruch .= "AND procedure_order.patient_id = ? ";
			$spruch .= "AND procedure_result.result IS NOT NULL ";
			$spruch .= "AND procedure_result.result != ''";
			$spruch .= "ORDER BY procedure_report.date_collected DESC ";
			#echo $spruch . "<br>";
			$query  = sqlStatement($spruch,array($derwert,$pid));	
	
			while($myrow = sqlFetchArray($query)){
				echo "<tr>";
				echo "<td class='list_item'>" . $myrow['result_code'] . "</td>";
				echo "<td class='list_item'>" . $myrow['result_text'] . "</td>";


				if($myrow['abnormal'] == 'No' || $myrow['abnormal'] == 'no'  || $myrow['abnormal'] == '' || $myrow['abnormal'] == NULL ) {
					echo "<td class='list_result'>&nbsp;&nbsp;&nbsp;" . $myrow['result'] . "&nbsp;&nbsp;</td>";
				} else {
					echo "<td class='list_result_abnorm'>&nbsp;" ;
					if($myrow['abnormal'] == 'high') {
						echo "+ ";
					} elseif ($myrow['abnormal'] == 'low') {
						echo "- ";
					} else {
						echo "&nbsp;&nbsp;";
					}
					echo $myrow['result'] . "&nbsp;&nbsp;</td>";
				}
				echo "<td class='list_item'>" . $myrow['range'] . "</td>";
				echo "<td class='list_item'>" . $myrow['units'] . "</td>";
				echo "<td class='list_log'>" . $myrow['date_collected'] . "</td>";
				echo "<td class='list_log'>" . $myrow['review_status'] . "</td>";
				echo "<td class='list_log'>" . $myrow['encounter_id'] . "</td>";
				echo "<td class='list_log'>" . $myrow['procedure_result_id'] . "</td>";
				echo "</tr>";
			}
			echo "<tr><td colspan='9'  class='list_spacer'><hr></td></tr>";
		}
		echo "</table><br>";
	
	}// end if mode = list
	
	//##########################################################################################################################
	if($mode=='matrix'){
	
		$wertematrix = array();
		$datelist = array();
		$i = 0;
		// get all data of patient's items
		foreach($wertewahl as $derwert){
				
			$spruch  = "SELECT procedure_result.procedure_result_id, procedure_result.result, procedure_result.result_text,  procedure_result.result_code, procedure_result.units, procedure_result.range, procedure_result.abnormal,  ";
			$spruch .= "procedure_report.date_collected, procedure_report.review_status, ";
			$spruch .= "procedure_order.encounter_id ";
			$spruch .= "FROM procedure_result ";
			#$spruch .= " ";
			$spruch .= "JOIN procedure_report ";
			$spruch .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
			$spruch .= "JOIN procedure_order ";
			$spruch .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
			#$spruch .= " ";
			$spruch .= "WHERE procedure_result.result_code = ? "; // '?'
			$spruch .= "AND procedure_order.patient_id = ? ";
			$spruch .= "AND procedure_result.result IS NOT NULL ";
			$spruch .= "AND procedure_result.result != ''";
			$spruch .= "ORDER BY procedure_report.date_collected DESC ";
			#echo $spruch . "<br>";
			$query  = sqlStatement($spruch,array($derwert,$pid));	
	
			while($myrow = sqlFetchArray($query)){
				$wertematrix[$i][procedure_result_id] = $myrow['procedure_result_id'];
				$wertematrix[$i][result_code] 	= $myrow['result_code'];
				$wertematrix[$i][result_text] 	= $myrow['result_text'];
				$wertematrix[$i][result] 	= $myrow['result'];
				$wertematrix[$i][units] 		= $myrow['units'];
				$wertematrix[$i][range] 		= $myrow['range'];
				$wertematrix[$i][abnormal] 		= $myrow['abnormal'];
				$wertematrix[$i][review_status] 	= $myrow['review_status'];
				$wertematrix[$i][encounter_id] 	= $myrow['encounter_id'];
				$wertematrix[$i][date_collected] = $myrow['date_collected'];
				$datelist[] 			= $myrow['date_collected'];	
				$i++;
			}
		}
	
		// get unique datetime
		$datelist = array_unique($datelist);
	
		// sort datetime DESC
		rsort($datelist);
	
		// sort item-data
		foreach($wertematrix as $key => $row) {
			#echo "huhu";
			$result_code[$key] = $row['result_code'];
			$date_collected[$key] = $row['date_collected'];
			}
		array_multisort(array_map('strtolower',$result_code), SORT_ASC, $date_collected, SORT_DESC, $wertematrix);
	
		$cellcount = count($datelist);
		$itemcount = count($wertematrix);
	
		// print matrix
		echo "<table border='1' cellpadding='2'>"; 
		echo "<tr>";
		echo "<th class='matrix'>Item</th>";
		echo "<th class='matrix'>Descr.</th>";
		echo "<th class='matrix'>Range</th>";
		echo "<th class='matrix'>Unit</th>";
		echo "<th class='matrix_spacer'>|</td>";
		foreach($datelist as $dasdatum){
			echo "<th width='30' class='matrix_time'>" . $dasdatum . "</th>";	
		}
		echo "</tr>";
	
		$i=0;
		$a=TRUE;
		while($a==TRUE){
			echo "<tr>";
			echo "<td class='matrix_item'>" . $wertematrix[$i]['result_code'] . "</td>";
			echo "<td class='matrix_item'>" . $wertematrix[$i]['result_text'] . "</td>";
			echo "<td class='matrix_item'>" . $wertematrix[$i]['range'] . "</td>";
			echo "<td class='matrix_item'>" . $wertematrix[$i]['units'] . "</td>";
			echo "<td class='matrix_spacer'> | </td>";
			
			$z=0;
			while($z < $cellcount){
			
				if($wertematrix[$i]['date_collected'] == $datelist[$z]){
					if($wertematrix[$i]['result'] == NULL){
						echo "<td class='matrix_result'> </td>";
					} else {

						if($wertematrix[$i]['abnormal'] == 'No' || $wertematrix[$i]['abnormal'] == 'no'  || $wertematrix[$i]['abnormal'] == '' || $wertematrix[$i]['abnormal'] == NULL ) {
							echo "<td class='matrix_result'>&nbsp;&nbsp;&nbsp;" . $wertematrix[$i]['result'] . "&nbsp;&nbsp;</td>";	
																	
						} else {
							echo "<td class='matrix_result_abnorm'>&nbsp;&nbsp;" ;
							if($wertematrix[$i]['abnormal'] == 'high') {
								echo "+ ";
							} elseif ($wertematrix[$i]['abnormal'] == 'low') {
								echo "- ";
							} 
							echo $wertematrix[$i]['result'] . "&nbsp;&nbsp;</td>";

						}
	
					}
					$j = $i;
					$i++;	
					
					if($wertematrix[$i]['result_code'] != $wertematrix[$j]['result_code']){
						$z = $cellcount;
					}			
				} else {
					echo "<td class='matrix_result'>&nbsp;</td>";
				}		
				$z++;
			}
			if( $i == $itemcount){$a=FALSE;}
		}
	
		echo "</table>";
	
	}// end if mode = matrix
} else { // end of "are items selected"
	echo "<p>No parameters selected.</p>"; 
	$nothing = TRUE;
	}


if(!$printable){
	if(!$nothing){
		echo "<p>";
		echo "<form method='post' action='" . $path_to_this_script . "' target='_new' onsubmit='return top.restoreSession()'>";
		echo "<input type='hidden' name='mode' value='". $mode . "'>";	
		foreach($_POST['wert_code'] as $derwertcode) {
			echo "<input type='hidden' name='wert_code[]' value='". $derwertcode . "'>";
		}	
		echo "<input type='submit' name='print' value='printable HTML' />";
		echo "</form>";
  	    echo "<br><a href='../summary/demographics.php' ";
		if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
		echo " class='css_button' onclick='top.restoreSession()'>";
		echo "<span>" . htmlspecialchars(xl('Back to Patient'),ENT_NOQUOTES) . "</span></a>";
	}
 

} else {
	echo "<p>End of report.</p>";
}
echo "</span>";
echo "<br><br>";
echo "</div>";
echo "</body></html>";
?>
