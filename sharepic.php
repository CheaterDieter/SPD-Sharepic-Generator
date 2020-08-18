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

	$target_dir = "up";
	//$target_file = $target_dir."/bk.jpg";

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
				//echo ("Copy logo");

				copy ("data/vorlagen/".$vorlage_pfad_logo,$db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" '));
				//die ($vorlage_pfad_logo."|".$db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" '));
			}
			if ($vorlage_name == $_GET["vorlage"] && $vorlage_pfad_bk != "" && $_GET["lade"]=="bk"){
				//echo ("Copy bk");
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
		//die ("rotate");	
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
	
		//die ("rotate");	
		
		header('Location: index.php?id='.$id);
		exit ();
	}	

	if (isset ($_POST["headline"])){
		$tmp_str = str_replace ("ß","%S%" ,$_POST["headline"]);
		$tmp_str=mb_strtoupper ($tmp_str);
		$tmp_str = str_replace ("%S%","ß" ,$tmp_str);
		$tmp_str = base64_encode ($tmp_str);
		$tmp_str = SQLite3::escapeString ($tmp_str);
		$db->exec('UPDATE "sharepics" SET "headline"="'.$tmp_str.'" WHERE "ID"="'.$id.'"');
		//file_put_contents ($target_dir."/headline.txt",$tmp_str);
	}
	if (isset ($_POST["subline1"])){
		$tmp_str = str_replace ("ß","%S%" ,$_POST["subline1"]);
		$tmp_str=mb_strtoupper ($tmp_str);
		$tmp_str = str_replace ("%S%","ß" ,$tmp_str);
		$tmp_str = str_replace ("%br%","\n" ,$tmp_str);
		$tmp_str = base64_encode ($tmp_str);
		$tmp_str = SQLite3::escapeString ($tmp_str);
		$db->exec('UPDATE "sharepics" SET "subline1"="'.$tmp_str.'" WHERE "ID"="'.$id.'"');
		//file_put_contents ($target_dir."/subline1.txt",$tmp_str);
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
				//file_put_contents ($target_dir."/quad.txt","1");

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
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);

$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');

//$path_logo = "up/".$id."/logo.png";
//if (!file_exists ($path_logo)) {$path_logo = "logo.png";}
$path_logo = $db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$id.'" ');




//SQLITE ABFRAGE
$result = $db->query('SELECT * FROM "sharepics" WHERE "ID" = "'.$id.'"');
while ($row = $result->fetchArray())
{
	$headline = base64_decode ($row['headline']);
	$subline1 = base64_decode ($row['subline1']);
	$subline1 = str_replace ("%br%","\n" ,$subline1);
	$groessetext = $row['groessetext'];
	$zoom = $row['zoom'];

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
//imagecopyresized($dest,$dest_zoom, 0,0,0,0,1500,1500,1500,1500);




$new_width = $width * $zoom / 100;
$new_height = $height * $zoom / 100;

//imagecopyresampled($dest, $dest_zoom, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

//imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2*$height/$width),$bk_size[1]+($zoom*2*$height/$width),$bk_size[0],$bk_size[1]);

if ($quad == 0){
	if (isset ($_GET["prev"])){
		imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2),$bk_size[1]+($zoom*2*$height/$width),$bk_size[0],$bk_size[1]);
	} else{
		imagecopyresampled($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2),$bk_size[1]+($zoom*2*$height/$width),$bk_size[0],$bk_size[1]);
	}
}
else {
	if (isset ($_GET["prev"])){
		imagecopyresized($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[1]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[0],$bk_size[1]);
	} else{
		imagecopyresampled($dest,$dest_zoom, 0-$zoom-$horizontal,0-$zoom-$vertikal,0,0,$bk_size[0]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[1]+($zoom*2*$bk_size[1]/$bk_size[0]),$bk_size[0],$bk_size[1]);
	}	
}


imagedestroy ($dest_zoom);

if(!$dest){echo "Fehler (2)! ID ungültig?"; exit;}

$image_kasten = imagecreatefromjpeg($path_kasten);

$color = imagecolorallocate($image_kasten, 227, 6, 19);
imagettftext($image_kasten, 55, 0, 40, 100, $color, realpath("data/priv/TheSans-B9Black.otf"), $headline);
imagettftext($image_kasten, 55, 0, 40, 190, $color, realpath("data/priv/TheSans-B7BoldItalic.otf"), $subline1);

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



$kastenbreite = (1385 * $groessetext/100)/(1500/$bk_size[0]);
$kastenhoehe = (331 * $groessetext/100)/(1500/$bk_size[0]);

if ($groessetext > 0){
	if (isset ($_GET["prev"])){
		imagecopyresized($dest, $image_kasten, ($bk_size[0]-$kastenbreite)/2, $bk_size[1]-$kastenhoehe-40, 0, 0, $kastenbreite, $kastenhoehe,1385,331);
	} else{
		imagecopyresampled($dest, $image_kasten, ($bk_size[0]-$kastenbreite)/2, $bk_size[1]-$kastenhoehe-40, 0, 0, $kastenbreite, $kastenhoehe,1385,331);
	}
}

//imagecopy($dest, $image_kasten, 57, $bk_size[1]-400, 0, 0, $kastenbreite, $kastenhoehe);
imagedestroy($image_kasten);
//imagecopy($dest, $image_logo, 10, 10, 0, 0, 600, 600);
imagedestroy($image_logo);

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
	
	imagejpeg($smallver, NULL, 40); 

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
} else {
	if (!isset ($_GET["debug"])){header('Content-Type: image/jpeg');}
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	if (isset ($_GET["download"])){
		header('Content-Disposition: attachment; filename="Sharepic-'.substr ($db->querySingle('SELECT "Hash" FROM "sharepics" WHERE "ID" = "'.$id.'" '), 0, 5).'.jpg"');
	} else {
		header('Content-Disposition: inline; filename="Sharepic-'.substr ($db->querySingle('SELECT "Hash" FROM "sharepics" WHERE "ID" = "'.$id.'" '), 0, 5).'.jpg"');
	}
	unset($db);	    
	
	imagejpeg($dest, NULL, 100);

	imagedestroy($dest);

}



?>
