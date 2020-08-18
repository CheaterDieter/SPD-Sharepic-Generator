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
$db->busyTimeout(5000);	
if(isset($_GET["id"])){
	if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false  or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	$ins = SQLite3::escapeString ($_GET["id"]);
	if ($db->querySingle('SELECT * FROM "sharepics" WHERE "ID" = "'.$ins.'" ') != FALSE){
		$id = $_GET["id"];
	}
	else {
		die ("ID ungültig!");
	}
}
unset($db);

$target_dir = "up";




if(isset($_POST['upload'])){

$target_file = $target_dir."/".$id."-bk.jpg";	
		
$name       = $_FILES['fileToUpload']['name'];  
$temp_name  = $_FILES['fileToUpload']['tmp_name'];  
if(isset($name) and !empty($name)){

$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
if($check !== false) {
	echo "File is an image - " . $check["mime"] . ".";
	$uploadOk = 1;
} else {
	header ("Location: index.php?fehler=Nur Bilder im JPG/JPEG Format sind erlaubt&id=".$_GET["id"]);
	exit ();

}
}

$imageFileType = strtolower(pathinfo($target_dir . basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION));
if($imageFileType != "jpg" && $imageFileType != "jpeg") {
	header ("Location: index.php?fehler=Nur Bilder im JPG/JPEG Format sind erlaubt&id=".$_GET["id"]);
	exit ();
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 20971520) {
	header ("Location: index.php?fehler=Datei zu groß&id=".$_GET["id"]);
	exit ();		
}
	if(move_uploaded_file($temp_name, $target_file)){
		//BILD SKALIEREN
		
		list($breite_orig, $hoehe_orig) = getimagesize($target_file);
		$ratio = $breite_orig / $hoehe_orig;
		$hoehe = $breite / $ratio;
		
		/*
		if (breite_orig > 1500){ //verkleinern
			
			if($breite / $hoehe > $ratio){
				$breite = $hoehe * $ratio;
			} else {
				$hoehe = $breite / $ratio;
			}
		}
		*/
		if ($breite_orig > $hoehe_orig) { //Querformat
			$breite = 1500;
			$hoehe = $breite / $ratio;
		}
		
		if ($breite_orig < $hoehe_orig) { //Hochformat
			$hoehe = 1500;
			$breite = $hoehe * $ratio;
		}		

		if ($breite_orig == $hoehe_orig) { //Quadrat
			$hoehe = 1500;
			$breite = 1500;
		}		
	
		
		$bild_neu = imagecreatetruecolor($breite, $hoehe);
		$bild_orig = imagecreatefromjpeg($target_file);

		imagecopyresampled($bild_neu, $bild_orig, 0, 0, 0, 0, $breite, $hoehe, $breite_orig, $hoehe_orig);		
		imagejpeg($bild_neu, $target_file,100);

		$db = new SQLite3("data/priv/database.sqlite");
		$db->busyTimeout(5000);	
	
		//file_put_contents ($target_dir."/quad.txt","0");
		$db->exec('UPDATE "sharepics" SET "quad"="0" WHERE "ID"="'.$id.'"');
		//file_put_contents ($target_dir."/zoom.txt","0");
		$db->exec('UPDATE "sharepics" SET "zoom"="0" WHERE "ID"="'.$id.'"');
		//file_put_contents ($target_dir."/horizontal.txt","0");
		$db->exec('UPDATE "sharepics" SET "horizontal"="0" WHERE "ID"="'.$id.'"');
		//file_put_contents ($target_dir."/vertikal.txt","0");
		$db->exec('UPDATE "sharepics" SET "vertikal"="0" WHERE "ID"="'.$id.'"');
		
		$db->exec('UPDATE "sharepics" SET "Pfad_Hintergrund"="'.$target_file.'" WHERE "ID"="'.$id.'"');
		$bk_size = getimagesize($target_file);
		$db->exec('UPDATE "sharepics" SET "Breite"='.$bk_size[0].' WHERE "ID"="'.$id.'"');
		$db->exec('UPDATE "sharepics" SET "Hoehe"='.$bk_size[1].' WHERE "ID"="'.$id.'"');		
		
		unset($db);
		
		header('Location: index.php?id='.$id);
		exit ();
		//echo 'File uploaded successfully';
	}
	else {
		header ("Location: index.php?fehler=Keine Datei angegeben&id=".$_GET["id"]);
		exit ();		
	}
}
elseif(isset($_POST['uploadLogo'])){

$target_dir = "up";
		
		
$name       = $_FILES['fileToUploadLogo']['name'];  
$temp_name  = $_FILES['fileToUploadLogo']['tmp_name'];  
if(isset($name) and !empty($name)){

$check = getimagesize($_FILES["fileToUploadLogo"]["tmp_name"]);
if($check !== false) {
	echo "File is an image - " . $check["mime"] . ".";
	$uploadOk = 1;
} else {
	header ("Location: index.php?fehler=Nur Logos im PNG/JPG/JPEG Format sind erlaubt&id=".$_GET["id"]);
	exit ();

}
}
$imageFileType = strtolower(pathinfo($target_dir . basename($_FILES["fileToUploadLogo"]["name"]),PATHINFO_EXTENSION));
if($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
	header ("Location: index.php?fehler=Nur Logos im PNG/JPG/JPEG Format sind erlaubt&id=".$_GET["id"]);
	exit ();
}

// Check file size
if ($_FILES["fileToUploadLogo"]["size"] > 10485760) {
	header ("Location: index.php?fehler=Datei zu groß&id=".$_GET["id"]);
	exit ();	
}

	$target_file = $target_dir."/".$id."-logo.".$imageFileType;
	if(move_uploaded_file($temp_name, $target_file)){
		if ($imageFileType == "png") {
			
		} else {
			imagepng(imagecreatefromstring(file_get_contents($target_file)), $target_dir."/".$id."-logo.png");
			unlink ($target_file);
			$target_file = $target_dir."/".$id."-logo.png";
		}
		
		//BILD SKALIEREN
		$breite = 700;
		list($breite_orig, $hoehe_orig) = getimagesize($target_file);
		$ratio = $breite_orig / $hoehe_orig;
		$hoehe = $breite / $ratio;
		
		if ($breite_orig > 700){ //verkleinern
			
			if($breite / $hoehe > $ratio){
				$breite = $hoehe * $ratio;
			} else {
				$hoehe = $breite / $ratio;
			}
		}
		$bild_neu = imagecreatetruecolor($breite, $hoehe);
		imagesavealpha($bild_neu, true);
		$color = imagecolorallocatealpha($bild_neu, 0, 0, 0, 127);
		imagefill($bild_neu, 0, 0, $color);
		
		$bild_orig = imagecreatefrompng($target_file);	
		
		imagecopyresampled($bild_neu, $bild_orig, 0, 0, 0, 0, $breite, $hoehe, $breite_orig, $hoehe_orig);		

		imagepng($bild_neu, $target_file,0);
		
		$db = new SQLite3("data/priv/database.sqlite");
		$db->busyTimeout(5000);	
		$db->exec('UPDATE "sharepics" SET "Pfad_Logo"="'.$target_file.'" WHERE "ID"="'.$id.'"');
		unset($db);
		
		header('Location: index.php?id='.$id);
		exit ();
		//echo 'File uploaded successfully';
	}
	else {
		header ("Location: index.php?fehler=Keine Datei angegeben&id=".$_GET["id"]);
		exit ();
	}
}



else {
		header ("Location: index.php");
		exit ();
}


?>
