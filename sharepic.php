<?php
/*	This file is part of SPD Sharepic-Generator.

    SPD Sharepic-Generator is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    SPD Sharepic-Generator is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with SPD Sharepic-Generator.  If not, see <http://www.gnu.org/licenses/>.

    Diese Datei ist Teil von SPD Sharepic-Generator.

    SPD Sharepic-Generator ist Freie Software: Sie können es unter den Bedingungen
    der GNU General Public License, wie von der Free Software Foundation,
    Version 3 der Lizenz oder (nach Ihrer Wahl) jeder neueren
    veröffentlichten Version, weiter verteilen und/oder modifizieren.

    SPD Sharepic-Generator wird in der Hoffnung, dass es nützlich sein wird, aber
    OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
    Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
    Siehe die GNU General Public License für weitere Details.

    Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
    Programm erhalten haben. Wenn nicht, siehe <https://www.gnu.org/licenses/>. 
*/  

include "data/config.php";

$path_background = "data/background.jpg";
$path_logo = "data/logo.png";
$headline = $conf_std_headline;
$subline1 = str_replace ("%br%", "\n", $conf_std_subline);
$id_auth = FALSE;

$design = "idnz";
if (isset ($_GET["design"]) && $_GET["id"]){
	if ($_GET["design"] == "klar"){
		$design = "klar";
	}
	if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	$ins = SQLite3::escapeString ($_GET["id"]);		
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);		
	$db->exec('UPDATE "sharepics" SET "design"="'.$design.'" WHERE "ID"="'.$ins.'"');
	unset($db);
}
	
if (isset($_GET["hash"])){
	if ($_GET["hash"] == "") {
		header ("Location: index.php");
		die ("Hash ungültig!");
	}	
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);
	if(strpos($_GET["hash"],"'") != false or strpos($_GET["hash"],'"') or strpos($_GET["hash"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	$ins = SQLite3::escapeString ($_GET["hash"]);	
	$id = $db->querySingle('SELECT "ID" FROM "sharepics" WHERE "Hash" = "'.$ins.'" ');
	unset($db);
	
	if ($id == FALSE){
		{
			header ("Location: index.php");
			die ("Hash ungültig!");
		};
	}
}
elseif(isset($_GET["id"])){
	if ($_GET["id"] == "") {die ("fehlerhafte ID");}
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	$ins = SQLite3::escapeString ($_GET["id"]);
	if ($db->querySingle('SELECT * FROM "sharepics" WHERE "ID" = "'.$ins.'" ') != FALSE){
		$id = $ins;
	}
	else {
		header ("Location: index.php");
		
		die ("ID ungültig!");
	}
	unset($db);
	
	$id_auth = TRUE;

	$target_dir = "up";

	if (isset ($_GET["vorlage"])){
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);
		$result = $db->query('SELECT * FROM "vorlagen"');
		while ($row = $result->fetchArray())
		{
			$vorlage_name = $row['name'];
			$vorlage_pfad_logo = $row['pfad_logo'];
			$vorlage_pfad_bk = $row['pfad_bk'];
			
			if ($vorlage_name == $_GET["vorlage"] && $vorlage_pfad_logo != "" && $_GET["lade"]=="logo"){
				copy ("data/vorlagen/".$vorlage_pfad_logo,$db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" '));
			}
			if ($vorlage_name == $_GET["vorlage"] && $vorlage_pfad_bk != "" && $_GET["lade"]=="bk"){
				copy ("data/vorlagen/".$vorlage_pfad_bk,$db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" '));
				$bk_size = getimagesize("data/vorlagen/".$vorlage_pfad_bk);
				$db->exec('UPDATE "sharepics" SET "zoom"=0 WHERE "ID"="'.$id.'"');
				$db->exec('UPDATE "sharepics" SET "Breite"='.$bk_size[0].' WHERE "ID"="'.$id.'"');
				$db->exec('UPDATE "sharepics" SET "Hoehe"='.$bk_size[1].' WHERE "ID"="'.$id.'"');
			}			
		}			
	unset($db);		
	}

	if (isset ($_GET["rotate"])){
		$db = new SQLite3("data/priv/database.sqlite");
		$db->busyTimeout(5000);
		$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
		if (!file_exists ($path_background)) {
			copy ("data/background.jpg", $path_background);
		}		
		$source = imagecreatefromjpeg ($path_background);
		if ($_GET["rotate"] == "left") {$rotate = imagerotate($source, 90,0);}
		if ($_GET["rotate"] == "right") {$rotate = imagerotate($source, -90,0);}
		imagedestroy($source);
		imagejpeg($rotate, $path_background, 100);
		
		imagedestroy($rotate);
		if ($db->querySingle('SELECT "quad" FROM "sharepics" WHERE "ID" = "'.$id.'" ') != 1) {
			$bk_size = getimagesize($path_background);
			$db->exec('UPDATE "sharepics" SET "Breite"='.$bk_size[0].' WHERE "ID"="'.$id.'"');
			$db->exec('UPDATE "sharepics" SET "Hoehe"='.$bk_size[1].' WHERE "ID"="'.$id.'"');		
		}	
		unset($db);
		header('Location: index.php?id='.$id);
		exit ();
	}
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	if (isset ($_GET["rotatelogo"])){
		$path_background = $db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
		if (!file_exists ($path_background)) {
			copy ("data/logo.png", $path_background);
		}		
		$source = imagecreatefrompng ($path_background);
		if ($_GET["rotatelogo"] == "left") {$rotate = imagerotate($source, 90,0);}
		if ($_GET["rotatelogo"] == "right") {$rotate = imagerotate($source, -90,0);}
		imagedestroy($source);
		imagesavealpha($rotate, true);
		imagepng($rotate, $path_background, 0);
		imagedestroy($rotate);
		header('Location: index.php?id='.$id);
		exit ();
	}	

	if (isset ($_POST["headline"])){
		$tmp_str = str_replace ("ß","%S%" ,$_POST["headline"]);
		$tmp_str = base64_encode ($tmp_str);
		$tmp_str = SQLite3::escapeString ($tmp_str);
		$db->exec('UPDATE "sharepics" SET "headline"="'.$tmp_str.'" WHERE "ID"="'.$id.'"');
	}
	if (isset ($_POST["subline1"])){
		$tmp_str = str_replace ("ß","%S%" ,$_POST["subline1"]);
		$tmp_str = str_replace ("%br%","\n" ,$tmp_str);
		$tmp_str = base64_encode ($tmp_str);
		$tmp_str = SQLite3::escapeString ($tmp_str);
		$db->exec('UPDATE "sharepics" SET "subline1"="'.$tmp_str.'" WHERE "ID"="'.$id.'"');
	}
	if (isset ($_POST["autor"])){
		$tmp_str = str_replace ("ß","%S%" ,$_POST["autor"]);
		$tmp_str = base64_encode ($tmp_str);
		$tmp_str = SQLite3::escapeString ($tmp_str);
		$db->exec('UPDATE "sharepics" SET "AUTOR"="'.$tmp_str.'" WHERE "ID"="'.$id.'"');
	}	
	if (isset ($_POST["logobreite"])){
		$ins = SQLite3::escapeString ($_POST["logobreite"]);
		if ($ins == 80){$ins=0;}
		$db->exec('UPDATE "sharepics" SET "logobreite"="'.$ins.'" WHERE "ID"="'.$id.'"');
	}
	if (isset ($_POST["zoom"])){
		$ins = SQLite3::escapeString ($_POST["zoom"]);
		$db->exec('UPDATE "sharepics" SET "zoom"="'.$ins.'" WHERE "ID"="'.$id.'"');
	}	
	if (isset ($_POST["horizontal"])){
		$ins = SQLite3::escapeString ($_POST["horizontal"]);
		$db->exec('UPDATE "sharepics" SET "horizontal"="'.$ins.'" WHERE "ID"="'.$id.'"');
	}	
	if (isset ($_POST["vertikal"])){
		$ins = SQLite3::escapeString ($_POST["vertikal"]);
		$ins = $ins * -1;
		$db->exec('UPDATE "sharepics" SET "vertikal"="'.$ins.'" WHERE "ID"="'.$id.'"');
	}	
	if (isset ($_POST["groessetext"])){
		$ins = SQLite3::escapeString ($_POST["groessetext"]);
		if ($ins == 40){$ins=0;}
		$db->exec('UPDATE "sharepics" SET "groessetext"="'.$ins.'" WHERE "ID"="'.$id.'"');
	}		
	if (isset ($_POST["rechts"])){
		if ($_POST["rechts"] == 1) {
			$db->exec('UPDATE "sharepics" SET "rechts"=1 WHERE "ID"="'.$id.'"');
		} else {
			$db->exec('UPDATE "sharepics" SET "rechts"=0 WHERE "ID"="'.$id.'"');
		}
	}
	if (isset ($_POST["quad"])){
		if ($_POST["quad"] == 1) {
				$db->exec('UPDATE "sharepics" SET "Breite"=1500 WHERE "ID"="'.$id.'"');
				$db->exec('UPDATE "sharepics" SET "Hoehe"=1500 WHERE "ID"="'.$id.'"');			
			if ($db->querySingle('SELECT "quad" FROM "sharepics" WHERE "ID" = "'.$id.'" ') == 0){ // wenn Option neu angewählt wurde
				$db->exec('UPDATE "sharepics" SET "quad"=1 WHERE "ID"="'.$id.'"');

				$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
				if (!file_exists ($path_background)) {$path_background = "data/background.jpg";}
				$zoomempfehlung = 0;
				$bk_size_org = getimagesize($path_background);

				if ($bk_size_org[0] > $bk_size_org[1]) {
					$zoomempfehlung = 1500-$bk_size_org[1];
					$zoomempfehlung = $zoomempfehlung*$bk_size_org[0]/$bk_size_org[1]/2;
				} 
				if ($bk_size_org[0] < $bk_size_org[1]) {
					$zoomempfehlung = 1500-$bk_size_org[0];
					$zoomempfehlung = $zoomempfehlung*$bk_size_org[1]/$bk_size_org[0]/2;
				}	
				$zoomempfehlung = 10+round ($zoomempfehlung,-1,PHP_ROUND_HALF_DOWN);
				if ($zoomempfehlung == 10) {$zoomempfehlung = 0;}
				$db->exec('UPDATE "sharepics" SET "zoom"="'.$zoomempfehlung.'" WHERE "ID"="'.$id.'"');
			}
		} else {
			$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
			$bk_size = getimagesize($path_background);
			$db->exec('UPDATE "sharepics" SET "Breite"='.$bk_size[0].' WHERE "ID"="'.$id.'"');
			$db->exec('UPDATE "sharepics" SET "Hoehe"='.$bk_size[1].' WHERE "ID"="'.$id.'"');			
			$db->exec('UPDATE "sharepics" SET "quad"=0 WHERE "ID"="'.$id.'"');		
		}
	}	
	unset($db);
	
	if (isset ($_GET["weiter"])) {
		header('Location: index.php?id='.$id);
		exit ();
	}
}

if (isset ($_GET["iframe"]) && isset ($_GET["id"])){
	echo '<!DOCTYPE html><img src="sharepic.php?prev&dummy='.bin2hex(random_bytes(5)).'&id='.$_GET["id"].'"  style=" position: absolute;left:-0px; top:-0px; width:350px; border-style: solid;color: #FFFFFF; margin-right: 10px;"> ';
	exit ();
}

$db = new SQLite3("data/priv/database.sqlite");
$db->busyTimeout(5000);

$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
$path_logo = $db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" ');

//SQLITE ABFRAGE
$result = $db->query('SELECT * FROM "sharepics" WHERE "ID" = "'.$id.'"');
while ($row = $result->fetchArray())
{
	$headline = base64_decode ($row['headline']);
	$autor = base64_decode ($row['AUTOR']);
	$subline1 = base64_decode ($row['subline1']);
	$subline1 = str_replace ("%br%","\n" ,$subline1);
	$groessetext = $row['groessetext'];
	$zoom = $row['zoom'];
	$design = $row['Design'];

	$logobreite = $row['logobreite'];
	$quad = $row['quad'];
	$rechts = $row['rechts'];
	$horizontal = $row['horizontal'];
	if (abs ($horizontal) > $zoom){
		if ($horizontal < 0) { $horizontal = 0- $zoom; } else {$horizontal = $zoom;}
	}		
	$vertikal = $row['vertikal'];
	if (abs ($vertikal) > $zoom){
		if ($vertikal < 0) { $vertikal = 0- $zoom; } else {$vertikal = $zoom;}
	}	
}	

unset($db);

$path_kasten = "data/priv/kasten.jpg";
$logo_size = getimagesize($path_logo);
if(!$logo_size){echo "Fehler (1)!"; exit;}

$bk_size = getimagesize($path_background);
$bk_size_org[0] = $bk_size[0];
$bk_size_org[1] = $bk_size[1];
$zoomempfehlung = 0;

if ($quad == 0){
	$dest = imagecreatefromjpeg($path_background);
	imagesavealpha($dest, true);
	$width = $bk_size[0];
	$height = $bk_size[1];
} else {
	$dest = imagecreatetruecolor(1500, 1500);
	imagesavealpha($dest, true);

	$bk_size[0] = 1500;
	$bk_size[1] = 1500;
	$width = $bk_size_org[0];
	$height = $bk_size_org[1];
	
	if ($width > $height) {
		$zoomempfehlung = 1500-$bk_size_org[1];
		$zoomempfehlung = $zoomempfehlung*$bk_size_org[0]/$bk_size_org[1]/2;
	} 
	if ($width < $height) {
		$zoomempfehlung = 1500-$bk_size_org[0];
		$zoomempfehlung = $zoomempfehlung*$bk_size_org[1]/$bk_size_org[0]/2;
	}	
}

$db = new SQLite3("data/priv/database.sqlite");
$db->busyTimeout(5000);
$db->exec('UPDATE "sharepics" SET "Breite"='.$bk_size[0].' WHERE "ID"="'.$id.'"');
$db->exec('UPDATE "sharepics" SET "Hoehe"='.$bk_size[1].' WHERE "ID"="'.$id.'"');
unset($db);

if ($quad == 0){
	$dest_zoom = imagecreatefromjpeg($path_background);
} else {
	$dest_zoom = imagecreatetruecolor(1500, 1500);
	$tmp = imagecreatefromjpeg($path_background);

	if (isset ($_GET["prev"])){
		imagecopyresized($dest_zoom,$tmp, (($bk_size[0]-$bk_size_org[0])/2),(($bk_size[1]-$bk_size_org[1])/2),0,0,1500,1500,1500,1500);
	} else {
		imagecopyresampled($dest_zoom,$tmp, (($bk_size[0]-$bk_size_org[0])/2),(($bk_size[1]-$bk_size_org[1])/2),0,0,1500,1500,1500,1500);
	}
	imagedestroy ($tmp);
}

$new_width = $width * $zoom / 100;
$new_height = $height * $zoom / 100;

// Zoom
if ($quad == 0){
	if (isset ($_GET["prev"])){
		//imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-($zoom)-$vertikal,0,0,$bk_size[0]+($zoom*2),$bk_size[1]+($zoom*2),$bk_size[0],$bk_size[1]);
		  imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-($zoom*$height/$width)-$vertikal,0,0,$bk_size[0]+($zoom*2),$bk_size[1]+($zoom*2*$height/$width),$bk_size[0],$bk_size[1]);
	} else{
		imagecopyresampled($dest,$dest_zoom, 0-$zoom-$horizontal,0-($zoom*$height/$width)-$vertikal,0,0,$bk_size[0]+($zoom*2),$bk_size[1]+($zoom*2*$height/$width),$bk_size[0],$bk_size[1]);
	}
}
else {
	if (isset ($_GET["prev"])){
		imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[1]+($zoom*2),$bk_size[0],$bk_size[1]);
	} else {
		imagecopyresampled($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[1]+($zoom*2),$bk_size[0],$bk_size[1]);
	}	
}

imagedestroy ($dest_zoom);

if(!$dest){echo "Fehler (2)! ID ungültig?"; exit;}


if (isset ($_GET["designtemp"])){
	if ($_GET["designtemp"] == "klar"){
		$design = "klar";
	}
	if ($_GET["designtemp"] == "idnz"){
		$design = "idnz";
	}	
}


$autor = str_replace ("%S%","ß" ,$autor);
$white = imagecolorallocate($dest, 255, 255, 255);
if ($bk_size[0]<$bk_size[1]){ // Hochformat
	$faktor = $bk_size[0]/$bk_size[1];
}
else {
	$faktor = 1;
}
if ($design != "klar"){
	$xtext = 1490;
} else {
	$xtext = 25;
}
imagettftext ($dest, 20*$faktor, 90, $bk_size[0]/(1500/$xtext), $bk_size[1]/(1500/1490), $white, realpath("data/priv/TheSans-B9Black.otf"), $autor);

	

// DESIGN-ELEMENTE EINFÜGEN
// IN DIE NEUE ZEIT
if ($design == "idnz"){
	$i = substr_count($subline1, "\n");
	$gesamt_kastenhoehe = 245 + 86*$i;
	if ($subline1 == "") {$gesamt_kastenhoehe = 150;}
	if (substr($subline1, -1) == "\n") {
		$gesamt_kastenhoehe = $gesamt_kastenhoehe-86;
		$i = $i - 1;
	}
	
	// Logo einfügen
	$image_logo = imagecreatefrompng($path_logo);
	$logoxpos = 30;
	if ($rechts == 1) {
		$logoxpos = $bk_size[0] - $logobreite -30;
	}
	if ($logobreite > 0){
		if (isset ($_GET["prev"])){
			imagecopyresized($dest, $image_logo,  $logoxpos, 30, 0, 0, $logobreite, $logobreite*($logo_size[1]/$logo_size[0]), $logo_size[0], $logo_size[1]);
		} else{
			imagecopyresampled($dest, $image_logo,  $logoxpos, 30, 0, 0, $logobreite, $logobreite*($logo_size[1]/$logo_size[0]), $logo_size[0], $logo_size[1]);
		}
	}
	imagedestroy($image_logo);	
	// Kasten erzeugen
	$image_kasten = imagecreatetruecolor (1385, $gesamt_kastenhoehe);
	$bkcolor = imagecolorallocate ($image_kasten,255,255,255);
	imagefill ($image_kasten,0,0,$bkcolor);
	// Rose in Kasten einfügen
	$image_kasten_icon = imagecreatefrompng("data/priv/icon-kasten.png");
	if ($subline1 == "") {
		$image_kasten_icon_kantenlange = 150;
	} else {
		$image_kasten_icon_kantenlange = 250; // 250
	}
	$image_logo_x = 0;
	if (isset ($_GET["prev"])){
		imagecopyresized ($image_kasten, $image_kasten_icon, 1385-$image_kasten_icon_kantenlange,$gesamt_kastenhoehe-$image_kasten_icon_kantenlange, 0,0, $image_kasten_icon_kantenlange,$image_kasten_icon_kantenlange,imagesx ($image_kasten_icon),imagesy ($image_kasten_icon));
	} else{
		imagecopyresampled ($image_kasten, $image_kasten_icon, 1385-$image_kasten_icon_kantenlange,$gesamt_kastenhoehe-$image_kasten_icon_kantenlange, 0,0, $image_kasten_icon_kantenlange,$image_kasten_icon_kantenlange,imagesx ($image_kasten_icon),imagesy ($image_kasten_icon));
	}
	// Text in Kasten einfügen
	$headline=mb_strtoupper ($headline);
	$headline = str_replace ("%S%","ß" ,$headline);
	$subline1=mb_strtoupper ($subline1);
	$subline1 = str_replace ("%S%","ß" ,$subline1);	
	$color = imagecolorallocate($image_kasten, 227, 6, 19);
	imagettftext($image_kasten, 55, 0, 40, 100, $color, realpath("data/priv/TheSans-B9Black.otf"), $headline);
	imagettftext($image_kasten, 55, 0, 40, 190, $color, realpath("data/priv/TheSans-B7BoldItalic.otf"), $subline1);
	// Kasten ins Bild legen
	$kastenbreite = (1385 * $groessetext/100)/(1500/$bk_size[0]);
	$kastenhoehe = ($gesamt_kastenhoehe * $groessetext/100)/(1500/$bk_size[0]);
	if ($groessetext > 0){
		if (isset ($_GET["prev"])){
			imagecopyresized($dest, $image_kasten, ($bk_size[0]-$kastenbreite)/2, $bk_size[1]-$kastenhoehe-40, 0, 0, $kastenbreite, $kastenhoehe,1385,$gesamt_kastenhoehe);
		} else{
			imagecopyresampled($dest, $image_kasten, ($bk_size[0]-$kastenbreite)/2, $bk_size[1]-$kastenhoehe-40, 0, 0, $kastenbreite, $kastenhoehe,1385,$gesamt_kastenhoehe);
		}
	}
	imagedestroy($image_kasten);	
}
// KLARE WORTE
if ($design == "klar"){
	$i = substr_count($subline1, "\n");
	$gesamt_kastenhoehe = 140 + 86*$i;
	if ($subline1 == "") {$gesamt_kastenhoehe = 150;}
	if (substr($subline1, -1) == "\n") {
		$gesamt_kastenhoehe = $gesamt_kastenhoehe-86;
		$i = $i - 1;
	}
	
	// Logo einfügen
	$image_logo = imagecreatefrompng($path_logo);
	$logoxpos = 30;
	if ($rechts == 1) {
		$logoxpos = $bk_size[0] - $logobreite -30;
	}
	if ($logobreite > 0){
		if (isset ($_GET["prev"])){
			imagecopyresized($dest, $image_logo,  $logoxpos, 30, 0, 0, $logobreite, $logobreite*($logo_size[1]/$logo_size[0]), $logo_size[0], $logo_size[1]);
		} else{
			imagecopyresampled($dest, $image_logo,  $logoxpos, 30, 0, 0, $logobreite, $logobreite*($logo_size[1]/$logo_size[0]), $logo_size[0], $logo_size[1]);
		}
	}
	imagedestroy($image_logo);
	
	// Kasten erzeugen
	$image_kasten = imagecreatetruecolor (1500, 1500);
	$bkcolor = imageColorAllocateAlpha($image_kasten, 0, 0, 0, 127);
	imagefill ($image_kasten,0,0,$bkcolor);	

	// Fettes Wort in Bild legen
	$headline = str_replace ("%S%","ß" ,$headline);
	$subline1 = str_replace ("%S%","ß" ,$subline1);		
	$headline_sub = explode (" ", $headline,2);
	$headline_sub[0]=mb_strtoupper ($headline_sub[0]);
	$color = imagecolorallocate($dest, 227, 1, 15);
	$color_out = imagecolorallocate($image_kasten, 255, 255, 255);
	//imagettftext($image_kasten, 200, 0, 0, $bk_size[1]/3, $color, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[0]);
	$textsize_head_array = imagettfbbox (200, 6, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[0]);
	$textsize_head = 200;
	while ($textsize_head_array[2]-$textsize_head_array[0] > $bk_size[0]/(1500/1350)){
		$textsize_head = $textsize_head - 1;
		$textsize_head_array = imagettfbbox ($textsize_head, 0, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[0]);
	}

	imagettfshadowtext ($image_kasten, $textsize_head, 0, 20, $textsize_head+30, $color, $color_out, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[0], $textsize_head/10);

	// weitere Worte aus Überschrift ins Bild legen
	if (isset ($headline_sub[1])){
		$color = imagecolorallocate($image_kasten, 0, 0, 0);
		//imagettftext($image_kasten, 120, 0, 0, ($bk_size[1]/3)+160, $color, realpath("data/priv/Tasman-Medium.ttf"), $headline_sub[1]);
		$textsize = 120;
		$textsizearray = imagettfbbox ($textsize, 0, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[1]);

		while ($textsizearray[2]-$textsizearray[0] > $bk_size[0]/(1500/1350)){
			$textsize = $textsize - 1;
			$textsizearray = imagettfbbox ($textsize, 0, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[1]);
		}
		imagettfshadowtext ($image_kasten, $textsize, 0, 20, $textsize_head+$textsize+80, $color, $color_out, realpath("data/priv/Tasman-Black.ttf"), $headline_sub[1], $textsize/10);
	} else {
		$textsize = -50;
	}
	
	// Kasten ins Bild legen
	$kastenbreite = 1500;
	$kastenhoehe = $gesamt_kastenhoehe;
	$image_white_bk = imagecreatetruecolor ($kastenbreite, $kastenhoehe);
	$bkcolor = imageColorAllocateAlpha($image_white_bk, 255, 255, 255, 30);
	imagefill ($image_white_bk,0,0,$bkcolor);
	if ($subline1 != "") {
		imagecopy ($image_kasten, $image_white_bk,20,$textsize_head+$textsize+140, 0,0,imagesx ($image_white_bk),imagesy ($image_white_bk));
		imagesavealpha($image_white_bk, true);
	} else {
		$kastenhoehe = 0;
	}
	
	// Unterschrift
	$color = imagecolorallocate($image_kasten, 227, 1, 15);
	imagettftext($image_kasten, 55, 0, 50, $textsize_head+$textsize+235, $color, realpath("data/priv/TheSans-B9Black.otf"), $subline1);
	
	// Drehen
	$rotate = imagerotate ($image_kasten, 6, imageColorAllocateAlpha($image_kasten, 0, 0, 0, 127));
	imagesavealpha($rotate, true);

	//imagecopy ($dest, $rotate,0,$bk_size[1]/3-$textsize-$kastenhoehe+250, 0,0,imagesx ($rotate),imagesy ($rotate));
	$pos_x = $bk_size[0]-((imagesx ($rotate)-150)*$groessetext/100);
	if ($pos_x < 0) {$pos_x = 0;};
	imagecopyresampled($dest, $rotate, $pos_x, $bk_size[1]-$textsize_head*$groessetext/100-$textsize*$groessetext/100-$kastenhoehe*$groessetext/100-340*$groessetext/100, 0, 0, imagesx ($rotate)*$groessetext/100, imagesy ($rotate)*$groessetext/100,imagesx ($rotate), imagesy ($rotate));

}



// http://www.johnciacia.com/2010/01/04/using-php-and-gd-to-add-border-to-text/
function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
	if (isset ($_GET["prev"])) {
		$c1 = $x+abs($px);
		$c2 = $y+abs($px);
		$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
		
		$c1 = $x-abs($px);
		$c2 = $y-abs($px);
		$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
	} else {
		for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
			for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
				$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

	}
    return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}

function imagettfshadowtext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {

		$c1 = $x+abs($px);
		$c2 = $y+abs($px);
		$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

    return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}

// Ausgabe
if (isset ($_GET["prev"])){
	if ($bk_size[0]<$bk_size[1]){ // Hochformat
		$faktor = $bk_size[0]/$bk_size[1];
	}
	else {
		$faktor = 1;
	}	
	$smallver = imagecreatetruecolor($bk_size[0]/1.875, ($bk_size[1]/1.875)+(40*$faktor));
	$bkcolor = imagecolorallocate ($smallver,255,255,255);
	imagefill ($smallver,0,0,$bkcolor);
	imagecopyresized($smallver, $dest, 0,0,0,0,800,$bk_size[1]/1.875,1500,$bk_size[1]);

	$colorprevtext = imagecolorallocate ($smallver,227, 19, 6);

	imagettftext($smallver, 20*$faktor, 0, 10*$faktor, ($bk_size[1]/1.875)+(30*$faktor), $colorprevtext, realpath("data/priv/TheSans-B7BoldItalic.otf"), "komprimierte Vorschau");
	
	if (!isset ($_GET["debug"])){header('Content-Type: image/jpeg');}
	
	if (!isset ($_GET["verysmall"])){
		imagejpeg($smallver, NULL, 40);
	} else {
		imagejpeg($smallver, NULL, 5);
	}

	imagedestroy($dest);
	imagedestroy($smallver);
} elseif (isset ($_GET["klein"])){
	if ($bk_size[0]<$bk_size[1]){ // Hochformat
		$faktor = $bk_size[0]/$bk_size[1];
	}
	else {
		$faktor = 1;
	}	
	$smallver = imagecreatetruecolor($bk_size[0]/(1500/1050), ($bk_size[1]/(1500/1050)));
	$bkcolor = imagecolorallocate ($smallver,255,255,255);
	imagefill ($smallver,0,0,$bkcolor);
	imagecopyresampled($smallver, $dest, 0,0,0,0,$bk_size[0]/(1500/1050),$bk_size[1]/(1500/1050),1500*$faktor,$bk_size[1]);

	if (!isset ($_GET["debug"])){header('Content-Type: image/jpeg');}
	
	imagejpeg($smallver, NULL, 100); 

	imagedestroy($dest);
	imagedestroy($smallver);
}
else {
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	$hash = $db->querySingle('SELECT "Hash" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
	unset($db);

	if (isset ($_GET["archiv"]) && $id_auth == TRUE && $conf_archiv==1){ // Bild archivieren
		$db = new SQLite3("data/priv/database.sqlite");
		$db->busyTimeout(5000);	
		$result = $db->query('SELECT * FROM "sharepics" WHERE "ID"="'.$ins.'"');
		while ($row = $result->fetchArray())
		{
			unlink ($row['Pfad_Hintergrund']);
			unlink ($row['Pfad_Logo']);
			$db->exec('DELETE FROM "sharepics" WHERE "ID"="'.$row['ID'].'"');
		}
		
		$laenge_id = 5;
		$id = substr (bin2hex (random_bytes($laenge_id)), 0,$laenge_id);
		while ($db->querySingle('SELECT * FROM "archiv" WHERE "Hash" = "'.$id.'" ') != FALSE){
			// ID schon vergeben -> neue generieren
			$laenge_id = $laenge_id + 1;
			$id = substr (bin2hex (random_bytes($laenge_id)), 0,$laenge_id);
		}		
				
		$db->exec('INSERT INTO "archiv" ("time","IP","Hash", "Token") VALUES ("'.time().'","'.$_SERVER['REMOTE_ADDR'].'","'.$id.'", "'.hash ("sha3-224", $id.bin2hex(random_bytes(200))).'")');
		unset($db);
		imagejpeg($dest, "archiv/".$id.".jpg", 80);
		imagedestroy($dest);	
		include "remove.php?id=".$id;	
		header ("Location: index.php?archivgesetzt&id=".$id);
	} else {
		if (!isset ($_GET["debug"])){header('Content-Type: image/jpeg');}
		if (isset ($_GET["download"])){		
			header('Content-Disposition: attachment; filename="Sharepic-'.substr ($hash, 0, 5).'.jpg"');
		} else {
			header('Content-Disposition: inline; filename="Sharepic-'.substr ($hash, 0, 5).'.jpg"');
		}		
		imagejpeg($dest, NULL, 100);
		imagedestroy($dest);
	}
}
?>