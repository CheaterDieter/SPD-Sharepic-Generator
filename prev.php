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

$db = new SQLite3("data/priv/database.sqlite");
if(isset($_GET["logo"])){
	if (isset ($_GET["id"])){
		$db = new SQLite3("data/priv/database.sqlite");
		if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false  or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
		$ins = SQLite3::escapeString ($_GET["id"]);	
		$prev = $db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$ins.'" ');
		unset($db);
		if ($prev == FALSE){
			die ("ID ungültig!");
		}
	}
	else {
		die ("ID fehlt!");
	}
	/*
		function drawImage(Imagick $i) 
		{
			$i->setImageFormat("png");
			header("Content-Type: image/" . $i->getImageFormat());
			echo $i;
			exit;
		}

		$o = new Imagick($prev);
		$o->setImageBackgroundColor('white'); // handle tranparent images
		$o = $o->flattenImages(); // flatten after setting background
		$o->blurImage(5, 30);
		$o->whiteThresholdImage( "#F8F8F8" );
		$o->blackThresholdImage( "#FFFFFF" );
		$o->edgeImage(5);
		$o->negateImage(false);
		$o->paintTransparentImage($o->getImagePixelColor(0, 0), 0, 2000);
		$o->colorizeImage("red", 1);

		drawImage($o);
	*/

	//echo ($prev);
	// maximale Breite und Höhe
	$breite = 150;
	$hoehe = 150;
	// originale Breite und Höhe
	list($breite_orig, $hoehe_orig) = getimagesize($prev);
	if($breite_orig <= $breite && $hoehe_orig <= $hoehe)
	{
		// Bild hat bereits korrekte Größe
	}
	else
	{
		//echo ("kleiner");
		// Bild verkleinern
		// Verhältnis Breite / Höhe bestimmen
		$ratio = $breite_orig / $hoehe_orig;
		if($breite / $hoehe > $ratio) {
			$breite = $hoehe * $ratio;
		}
		else {
			$hoehe = $breite / $ratio;
		}
		// neues Bild erstellen
		$bild_neu = imagecreatetruecolor($breite, $hoehe);
		imagesavealpha($bild_neu, true);
		$color = imagecolorallocatealpha($bild_neu, 0, 0, 0, 127);
		imagefill($bild_neu, 0, 0, $color);
		
		$bild_orig = imagecreatefrompng($prev);


		// original Bild verkleinern

		imagecopyresampled($bild_neu, $bild_orig, 0, 0, 0, 0, $breite, $hoehe, $breite_orig, $hoehe_orig);


	} 

	header('Content-Type: image/png');

	imagepng($bild_neu, NULL, 9); 
}
elseif(isset($_GET["logoiframe"])){
	echo '<meta http-equiv="refresh" content="5">';
	echo '<img src="prev.php?logo&id='.$_GET["id"].'" width="150"> ';
}

if(isset($_GET["bk"])){
	if (isset ($_GET["id"])){
		$db = new SQLite3("data/priv/database.sqlite");
		if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false  or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
		$ins = SQLite3::escapeString ($_GET["id"]);			
		$prev = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$ins.'" ');
		unset($db);
		if ($prev == FALSE){
			die ("ID ungültig!");
		}
		
	}
	else {
		die ("ID fehlt!");
	}

	// maximale Breite und Höhe
	$breite = 150;
	$hoehe = 150;
	// originale Breite und Höhe
	list($breite_orig, $hoehe_orig) = getimagesize($prev);
	if($breite_orig <= $breite && $hoehe_orig <= $hoehe)
	{
		// Bild hat bereits korrekte Größe
	}
	else
	{
		// Bild verkleinern
		// Verhältnis Breite / Höhe bestimmen
		$ratio = $breite_orig / $hoehe_orig;
		if($breite / $hoehe > $ratio)
			$breite = $hoehe * $ratio;
		else
			$hoehe = $breite / $ratio;
		// neues Bild erstellen
		$bild_neu = imagecreatetruecolor($breite, $hoehe);
		$bild_orig = imagecreatefromjpeg($prev);
		// original Bild verkleinern
		imagecopyresampled($bild_neu, $bild_orig, 0, 0, 0, 0, $breite, 
	$hoehe, $breite_orig, $hoehe_orig);

	} 

	header('Content-Type: image/jpeg');

	imagejpeg($bild_neu, NULL, 100); 
}
elseif(isset($_GET["bkiframe"])){
	echo '<meta http-equiv="refresh" content="1">';
	echo '<img src="prev.php?bk&id='.$_GET["id"].'"  style="width:350;"> ';
}

?>