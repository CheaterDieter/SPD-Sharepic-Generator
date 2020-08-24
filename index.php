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
$ver = "1.7";

include "data/config.php";
$db = new SQLite3("data/priv/database.sqlite");
$db->busyTimeout(5000);
$db-> exec("CREATE TABLE IF NOT EXISTS 'sharepics' ('ID' TEXT, 'headline' TEXT, 'subline1' TEXT, 'Ablauf' INTEGER, 'IP' TEXT, 'Pfad_Hintergrund' TEXT, 'Pfad_Logo' TEXT,'groessetext' INTEGER, 'horizontal' INTEGER, 'logobreite' INTEGER, 'quad' INTEGER, 'rechts' INTEGER, 'vertikal' INTEGER, 'zoom' INTEGER, 'Hash' TEXT, 'Breite' INTEGER, 'Hoehe' INTEGER, 'Salt' TEXT, 'Archiv' INTEGER)");
$db-> exec("CREATE TABLE IF NOT EXISTS 'vorlagen' ('name' TEXT, 'pfad_logo' TEXT, 'pfad_bk' TEXT, 'beschreibung' TEXT, 'Nr' INTEGER)");
$db-> exec("CREATE TABLE IF NOT EXISTS 'archiv' ('time' TEXT, 'IP' TEXT, 'Hash' TEXT, 'Token' TEXT)");


if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
    header('Location: http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI']);
    exit;
}

$ok = 1;
$laenge_id = 15;
$id = substr (bin2hex (random_bytes($laenge_id)), 0,$laenge_id);
while ($db->querySingle('SELECT * FROM "sharepics" WHERE "ID" = "'.$id.'" ') != FALSE){
	// ID schon vergeben -> neue generieren
	$laenge_id = $laenge_id + 1;
	$id = substr (bin2hex (random_bytes($laenge_id)), 0,$laenge_id);
}
//$id = time()."-".bin2hex (random_bytes(5));

// Wenn Paramenter ok gesetzt ist, neue ID vergeben
if (isset($_GET["ok"])){
	$ablaufzeit = 82800;
	$salt = bin2hex (random_bytes(3));
	$db->exec('INSERT INTO "sharepics" ("ID","headline","subline1","Ablauf","IP","Pfad_Hintergrund","Pfad_Logo","groessetext","horizontal","logobreite","quad","rechts","vertikal","zoom","Hash", "Breite", "Hoehe", "Salt") VALUES ("'.$id.'","'.base64_encode ($conf_std_headline).'","'.base64_encode($conf_std_subline).'","'.(time()+ $ablaufzeit) .'","'.$_SERVER['REMOTE_ADDR'].'","up/'.$id.'-bk.jpg","up/'.$id.'-logo.png","100","0","400","0","0","0","0","'.hash ("sha3-224", $id.$salt).'", "1500","1500", "'.$salt.'")');
	if (!file_exists("up/".$id."-logo.png")) {copy ("data/logo.png", "up/".$id."-logo.png");}
	if (!file_exists("up/".$id."-bk.jpg")) {copy ("data/background.jpg", "up/".$id."-bk.jpg");}
	header ("Location: index.php?id=".$id);
	exit ();
}		
		
if (!isset ($_GET["impdat"])){
	if(isset($_GET["id"])){
		if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false  or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
		$ins = SQLite3::escapeString ($_GET["id"]);
		if ($db->querySingle('SELECT * FROM "sharepics" WHERE "ID" = "'.$ins.'" ') == FALSE){
			// ID ungültig oder nicht vorhanden
			$ok = 0;
		} else {
			$salt = $db->querySingle('SELECT "Salt" FROM "sharepics" WHERE "ID" = "'.$ins.'" ');
			$id = $_GET["id"];
		}
	} 
	else {
		$ok = 0;
	}
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<title><?php echo ($conf_titel); ?></title>
	<meta name="viewport" content="width=770" />
	<meta name="description" content="<?php echo ($conf_website_beschreibung); ?>"/>
	<link rel="stylesheet" href="style.css">
	<?php echo (file_get_contents("data/priv/header.html")); ?>
</head>
<body>
<br><a href="index.php<?php if(isset($_GET["id"])){ echo ("?id=".$_GET["id"]); } ?>"><img id="ico" alt="" width="120" height="120" src="data/icon.jpg"></a>
<h1><?php echo ($conf_titel); ?></h1>
<noscript>
<h2>Bitte aktivieren Sie JavaScript in Ihrem Browser, um diese Seite nutzen zu können.</h2><br><br>
</noscript>
<?php

if (isset ($_GET["fehler"])) {
	echo ("<h2>FEHLER:</h2><div class=head>".$_GET["fehler"]."</div><br><br>");
}

if (isset ($_GET["impdat"])) {
	echo ('<div class="nutzungsbedinungen">');
	echo (str_replace ("\n","<br>",file_get_contents("data/priv/impressum.txt")));
	echo ("</div>");
}
elseif (isset ($_GET["archiv"])) {
	echo ('<div class="nutzungsbedinungen">');
	?>
	Du bist dabei, dieses Bild zu archvieren:
	<br>
	<img id="prev" width="350" alt="" src="sharepic.php?prev&amp;id=<?php echo $id; ?>">
	<br><br>
	Nach dem Archivieren kann dein Bild nicht mehr bearbeitet werden und wird dauerhaft auf dem Server gespeichert. Ein manuelles Löschen ist nicht möglich! Weiter?
	<br><br>
	<a href="sharepic.php?archiv&id=<?php echo $id; ?>">archivieren</a><br><br><br>
	
	<?php
	echo ("</div>");
}
elseif (isset ($_GET["archivgesetzt"])) {
	echo ('<div class="nutzungsbedinungen">');
	?>
	Das Bild wurde ins Archiv verschoben.
	<br>
	<img id="prev" width="350" alt="" src="archiv/<?php echo $_GET["id"]; ?>.jpg">
	<br><br>
	<textarea id="imgURL" style="width: 300px; height: 40px; font-size: 90%;" readonly="TRUE"><?php echo ("https://".str_replace ("index.php", "" ,$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'])); ?>archiv/<?php echo $_GET["id"]; ?>.jpg</textarea><br><br>
	<button onclick="copy()">In die Zwischenablage kopieren</button> 
	<br><br>
	<script>
	function copy() {
	  var copyText = document.getElementById("imgURL");
	  copyText.select();
	  copyText.setSelectionRange(0, 99999); /*For mobile devices*/
	  document.execCommand("copy");
	} 
	</script>
	<?php
	echo ("</div>");
}
elseif ($ok == 1) {
	?>
	<!-- <a href="index.php?design=idnz&id=<?php echo $id; ?>">In die neue Zeit</a>
	<br><br> -->
	
	<form id="settings" target="transFrame" action="sharepic.php?iframe&prev&amp;id=<?php echo $id; ?>" method="post">
	<div class="links">
	<?php 
	$breite = (int)$db->querySingle('SELECT "Breite" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
	$hoehe = (int)$db->querySingle('SELECT "Hoehe" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
	if ($breite<$hoehe){ // Hochformat
		$faktor = $breite/$hoehe;
	}
	else {
		$faktor = 1;
	}	
	$hoehe = $hoehe+(40*$faktor);
	$hoehe = round($hoehe/4.184782608695652/$faktor);
	?>
	<iframe scrolling="no" src="sharepic.php?iframe&prev&amp;id=<?php echo $id; ?>" style="border:0px;	width: 360px; height:<?php echo ($hoehe+10); ?>px;" name="transFrame" id="transFrame"></iframe>	
	<div style="position: absolute;  width: 350px; z-index: -1; top: <?php echo (200+($hoehe/2)); ?>px;">
		<img width=100 src="lade.gif">
	</div>
	<a href="sharepic.php?download&hash=<?php echo hash ("sha3-224", $id.$salt); ?>">Download</a>&nbsp;&nbsp;&nbsp;<a href="sharepic.php?hash=<?php echo hash ("sha3-224", $id.$salt); ?>">im Browser öffnen</a>
	
	<?php if ($conf_archiv == 1) { echo '&nbsp;&nbsp;&nbsp;<a href="index.php?archiv&id='.$id.'">archivieren</a>'; } ?>
	<br><br>
	<div class="hidden" status="hidden">
	<input class="enter" type="submit" value="Eingaben absenden und aktualisieren">
	</div>
	<div class="smalltext"><a href="remove.php?weiter&id=<?php echo ($id); ?>">oder meine Daten löschen und von vorne beginnen</a></div>
	<br><br><br>
	<div class=head>VORLAGEN</div><br>
	<table>
		<tr>
			<th width=250></th>
			<th ></th>
			<th width=100></th>
		</tr>
		<?php
		$result = $db->query('SELECT * FROM "vorlagen"');
		$i = 1;
		while ($i < 20)
		{
			if ($db->querySingle('SELECT "Nr" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ') != "")
			{
				echo ("<tr>");			
				$vorlage_name = $db->querySingle('SELECT "name" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
				$vorlage_pfad_logo = $db->querySingle('SELECT "pfad_logo" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
				$vorlage_pfad_bk = $db->querySingle('SELECT "pfad_bk" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
				$beschreibung = $db->querySingle('SELECT "beschreibung" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');		

				echo ('<td><div align="left">'.$vorlage_name.'</div></td>');
				if ($vorlage_pfad_logo != "") {echo ('<td><a href="sharepic.php?vorlage='.str_replace(" ", "+", $vorlage_name).'&lade=logo&weiter&id='.$id.'">Logo</a></td>');}
				if ($vorlage_pfad_bk != "") {echo ('<td><a href="sharepic.php?vorlage='.str_replace(" ", "+", $vorlage_name).'&lade=bk&weiter&id='.$id.'">Hintergrund</a></td>');}
				echo ("</tr>");
				echo ('<tr><td colspan="3"><div align="left" style="font-size:80%; margin-bottom: 5px;"><i>'.$beschreibung.'</i></div></tr>');
				
			}
			$i = $i + 1;
		}
	?>		  

	</table>
	<br>
	<div style="font-size:80%"><?php echo ($conf_copyright); ?></div>
	</div>

	<?php
	//SQLITE ABFRAGE
	$result = $db->query('SELECT * FROM "sharepics" WHERE "ID" = "'.$id.'"');
	while ($row = $result->fetchArray())
	{
		$headline = base64_decode ($row['headline']);
		$headline = str_replace ("'","&apos;" ,$headline);
		$subline1 = base64_decode ($row['subline1']);
		$subline1 = str_replace ("'","&apos;" ,$subline1);
		$subline1 = str_replace ("%br%","\n" ,$subline1);
		$groessetext = $row['groessetext'];
		$horizontal = $row['horizontal'];
		$logobreite = $row['logobreite'];
		$quad = $row['quad'];
		$rechts = $row['rechts'];
		$vertikal = $row['vertikal'];
		$zoom = $row['zoom'];
	}	
	?>

	<div class="rechts">
	
	<div class=head>ÜBERSCHRIFT</div>
	<input oninput="absenden()" type="text" class="headline" id="headline" name="headline" value='<?php echo ($headline); ?>'>
	
	<br>
	<br>

	<div class=head>UNTERSCHRIFT</div>
	<div class="smalltext"><i>Soll der Text über mehrere Zeilen gehen, bitte Umbrüche setzen.</i></div>
	<textarea oninput="absenden()" id="subline1" name="subline1" rows="3" cols="35"><?php echo ($subline1); ?></textarea>
	
	<br>
	<br>
	
	<div class=head>LOGO RECHTS POSITIONIEREN</div>
	<input type="hidden" name="rechts" value="0">
	<input onchange="absenden()" type="checkbox" id="rechts" value="1" name="rechts" <?php if ($rechts == 1){echo ("checked");} ?> >
	
	<br>
	<br>
	
	<div class=head>BREITE DES LOGOS</div>
	<div class="smalltext"><i>auf 0 stellen, um Logo auszublenden</i><br></div>
	<div onchange="absenden()" class="range"><input type="range" step="20" min="80" max="800" value="<?php echo ($logobreite); ?>" name="logobreite" id="logobreite">
	<br>
	<div class="smalltext"><span><?php echo ($logobreite); ?></span> Pixel</div></div>

	<br>
	
	<div class=head>GRÖSSE DER TEXTBOX</div>
	<div class="smalltext"><i>auf 0 stellen, um Textbox auszublenden</i><br></div>
		<input onchange="absenden()" type="range" step="5" min="40" max="100" value="<?php echo ($groessetext); ?>" name="groessetext" id="groessetext">
		<br>
		<div class="smalltext"><div class="range5"><span><?php echo ($groessetext); ?></span> Prozent</div></div>
	<br>
	
	<?php
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
	unset($db);	
	if (!file_exists ($path_background)) {$path_background = "data/background.jpg";}
	$bk_size_org = getimagesize($path_background);	
	if ($bk_size_org[0] != $bk_size_org[1]) {
	?>
		<div class=head>QUADRATISCH ZUSCHNEIDEN</div>
		<input type="hidden" name="quad" value="0">
		<input onchange="reload()" type="checkbox" id="quad" value="1" name="quad" <?php if ($quad == 1){echo ("checked");} ?> >
		<br><br>
	<?php
	}
	?>
	

	<div class=head>HINTERGRUND ZOOMEN</div>

	<?php
	$zoomempfehlung = 0;
	if ($quad == 1){
	echo ('<div class="smalltext">um schwarzen Rand zu verhindern: mind.');
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	$path_background = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$id.'" ');
	unset($db);
	if (!file_exists ($path_background)) {$path_background = "data/background.jpg";}
	
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

	echo ($zoomempfehlung);
	echo ("</div>");
	} 
	?>

	<input onchange="absenden()" type="range" step="10" min="<?php if ($quad == 1){echo ($zoomempfehlung);} else {echo "0";} ?>" max="<?php if ($zoomempfehlung < 500) {echo "500";} else {echo $zoomempfehlung+500;} ?>" value="<?php echo ($zoom); ?>" name="zoom" id="zoom">
	<br>
	<div class="smalltext"><div class="range2"><span><?php echo ($zoom); ?></span> Pixel</div></div>

	<div class="hidden" id="hiddendiv" status="<?php if ($zoom == 0){echo "hidden";}else {echo "visible";}; ?>">
	<br>
	<div class=head>HINTERGRUND HORIZONTAL VERSCHIEBEN</div>
	<div class="smalltext"><i>funktioniert nur bei aktiviertem Zoom</i><br></div>
	<div class="einr">
		<input onchange="absenden()" type="range" step="20" min="-500" max="500" value="<?php echo ($horizontal); ?>" name="horizontal" id="horizontal">
		<br>
		<div class="smalltext"><div class="range3"><span><?php echo ($horizontal); ?></span> Pixel</div></div>
	</div>
	<br>
	<div class=head>HINTERGRUND VERTIKAL VERSCHIEBEN</div>
	<div class="smalltext"><i>funktioniert nur bei aktiviertem Zoom</i><br></div>
	<div class="einr">
		<input onchange="absenden()" type="range" step="20" min="-500" max="500" value="<?php echo ($vertikal); ?>" name="vertikal" id="vertikal">
		<br>
		<div class="smalltext"><div class="range4"><span><?php echo ($vertikal); ?></span> Pixel</div></div>
	</div>
	
	</div>
	</div>
	</div>
	</form>

	<div class="rechts">
	<br>
	<div class=head>HINTERGRUNDBILD</div>
	<a href="sharepic.php?rotate=left&amp;id=<?php echo $id;?>"><img width="25" alt="links drehen" src="links.png"></a>
	<img class="bkprev" width="150" alt="" src="prev.php?bk&amp;id=<?php echo ($id); ?>">
	<a href="sharepic.php?rotate=right&amp;id=<?php echo $id; ?>"><img width="25" alt="rechts drehen" src="rechts.png"></a>
	<br>
	<form action="upload.php?id=<?php echo $_GET["id"]; ?>" method="post" enctype="multipart/form-data">
		<div class="smalltext">nur JPG&amp;JPEG, max. 20 MB<br></div>
		<input type="file" name="fileToUpload" id="fileToUpload">
		<br>
		<input class="upload" type="submit" value="hochladen" name="upload">  

	</form>
	<br>
	<div class=head>LOGO</div>
	<a href="sharepic.php?rotatelogo=left&amp;id=<?php echo $_GET["id"];?>"><img width="25" alt="links drehen" src="links.png"></a>
	<img class="bkprev" width="150" alt="" src="prev.php?logo&amp;id=<?php echo ($id); ?>">
	<a href="sharepic.php?rotatelogo=right&amp;id=<?php echo $_GET["id"];?>"><img width="25" alt="rechts drehen" src="rechts.png"></a>	
	<br>
	<form action="upload.php?id=<?php echo $_GET["id"]; ?>" method="post" enctype="multipart/form-data">
		<div class="smalltext">nur PNG, JPG&amp;JPEG, max. 10 MB<br></div>
		<input type="file" name="fileToUploadLogo" id="fileToUploadLogo">
		<br>
		<input class="upload" type="submit" value="hochladen" name="uploadLogo"> 
	</form>
	</div>

	<div class="legal">
	<br><br>
	<div class="smalltext"><br>SPD Sharepic-Generator Version <?php echo ($ver); ?> entwickelt von David Heger, 2020<br>Diese Software ist OpenSource und unterliegt der GNU General Public License.<br><a href="https://github.com/CheaterDieter/SPD-Sharepic-Generator">Zum GitHub-Projekt</a><br><br><?php echo ($conf_copyright); ?><br><br></div>
	</div>
	
	<?php
} else {
	?>
	
	<div class="nutzungsbedinungen">
	<?php echo ($conf_hallo); ?>
	<br>
	<br><hr><br>
	<div class=head>NUTZUNGSBEDINUNGEN</div><br>
	<div class=smalltext>
	<?php echo (str_replace ("\n","<br>",file_get_contents("data/priv/nutzungsbedinungen.txt"))); ?>
	<br><br><br><div class=smalltext><i>Technischer Hinweis: Bitte nutze Firefox, Chrome oder Edge und aktiviere Javascript, um diese Seite optimal nutzen zu können.</i></div>
	<br></div></div>
	<br>
	<a href="index.php?ok">Ich akzeptiere die Nutzungsbedingungen</a><br><br>
	<br>
	
	<?php
}
	?>


<div class="smalltext"><a id="impdat" href="index.php?impdat">Impressum und Datenschutz</a><br><br></div>
<script>
	window.addEventListener("load", function(){
    	var slider = document.querySelector("input[id='logobreite']");
     	slider.addEventListener("mousemove", function(){
         	document.querySelector(".range span").innerHTML = this.value;
			if (this.value == 80) {
			  document.querySelector(".range span").innerHTML = "0";
			} else {
			  document.querySelector(".range span").innerHTML = this.value;
			}			
   		});			
		
    	var slider2 = document.querySelector("input[id='zoom']");
     	slider2.addEventListener("mousemove", function(){
         	document.querySelector(".range2 span").innerHTML = this.value;
   		});
		
    	var slider3 = document.querySelector("input[id='horizontal']");
     	slider3.addEventListener("mousemove", function(){
         	document.querySelector(".range3 span").innerHTML = this.value;
   		});		
		
    	var slider4 = document.querySelector("input[id='vertikal']");
     	slider4.addEventListener("mousemove", function(){
         	document.querySelector(".range4 span").innerHTML = this.value;
   		});			
		
    	var slider5 = document.querySelector("input[id='groessetext']");
     	slider5.addEventListener("mousemove", function(){
         	document.querySelector(".range5 span").innerHTML = this.value;
			if (this.value == 40) {
			  document.querySelector(".range5 span").innerHTML = "0";
			} else {
			  document.querySelector(".range5 span").innerHTML = this.value;
			}			
   		});	
		
		
    	var slider = document.querySelector("input[id='logobreite']");
     	slider.addEventListener("change", function(){
         	document.querySelector(".range span").innerHTML = this.value;
			if (this.value == 80) {
			  document.querySelector(".range span").innerHTML = "0";
			} else {
			  document.querySelector(".range span").innerHTML = this.value;
			}			
   		});	
		
    	var slider2 = document.querySelector("input[id='zoom']");
     	slider2.addEventListener("change", function(){
         	document.querySelector(".range2 span").innerHTML = this.value;
   		});
		
    	var slider3 = document.querySelector("input[id='horizontal']");
     	slider3.addEventListener("change", function(){
         	document.querySelector(".range3 span").innerHTML = this.value;
   		});		
		
    	var slider4 = document.querySelector("input[id='vertikal']");
     	slider4.addEventListener("change", function(){
         	document.querySelector(".range4 span").innerHTML = this.value;
   		});			
		
    	var slider5 = document.querySelector("input[id='groessetext']");
     	slider5.addEventListener("change", function(){
         	document.querySelector(".range5 span").innerHTML = this.value;
			if (this.value == 40) {
			  document.querySelector(".range5 span").innerHTML = "0";
			} else {
			  document.querySelector(".range5 span").innerHTML = this.value;
			}			
   		});	


    	var slider = document.querySelector("input[id='logobreite']");
     	slider.addEventListener("onmousemove", function(){
         	document.querySelector(".range span").innerHTML = this.value;
			if (this.value == 80) {
			  document.querySelector(".range span").innerHTML = "0";
			} else {
			  document.querySelector(".range span").innerHTML = this.value;
			}			
   		});	
		
    	var slider2 = document.querySelector("input[id='zoom']");
     	slider2.addEventListener("onmousemove", function(){
         	document.querySelector(".range2 span").innerHTML = this.value;
   		});
		
    	var slider3 = document.querySelector("input[id='horizontal']");
     	slider3.addEventListener("onmousemove", function(){
         	document.querySelector(".range3 span").innerHTML = this.value;
   		});		
		
    	var slider4 = document.querySelector("input[id='vertikal']");
     	slider4.addEventListener("onmousemove", function(){
         	document.querySelector(".range4 span").innerHTML = this.value;
   		});			
		
    	var slider5 = document.querySelector("input[id='groessetext']");
     	slider5.addEventListener("onmousemove", function(){
         	document.querySelector(".range5 span").innerHTML = this.value;
			if (this.value == 40) {
			  document.querySelector(".range5 span").innerHTML = "0";
			} else {
			  document.querySelector(".range5 span").innerHTML = this.value;
			}			
   		});		

    	var slider = document.querySelector("input[id='logobreite']");
     	slider.addEventListener("touchmove", function(){
         	document.querySelector(".range span").innerHTML = this.value;
			if (this.value == 80) {
			  document.querySelector(".range span").innerHTML = "0";
			} else {
			  document.querySelector(".range span").innerHTML = this.value;
			}			
   		});	
		
    	var slider2 = document.querySelector("input[id='zoom']");
     	slider2.addEventListener("touchmove", function(){
         	document.querySelector(".range2 span").innerHTML = this.value;
   		});
		
    	var slider3 = document.querySelector("input[id='horizontal']");
     	slider3.addEventListener("touchmove", function(){
         	document.querySelector(".range3 span").innerHTML = this.value;
   		});		
		
    	var slider4 = document.querySelector("input[id='vertikal']");
     	slider4.addEventListener("touchmove", function(){
         	document.querySelector(".range4 span").innerHTML = this.value;
   		});			
		
    	var slider5 = document.querySelector("input[id='groessetext']");
     	slider5.addEventListener("touchmove", function(){
         	document.querySelector(".range5 span").innerHTML = this.value;
			if (this.value == 40) {
			  document.querySelector(".range5 span").innerHTML = "0";
			} else {
			  document.querySelector(".range5 span").innerHTML = this.value;
			}			
   		});			
		
	});
	
	function absenden() {
        var form = document.getElementById("settings");
        form.submit();
		if (document.getElementById('zoom').value == 0){
			document.getElementById('hiddendiv').setAttribute('status','hidden');

		} else {
			document.getElementById('hiddendiv').setAttribute('status','visible');
		}
		
	}
	
	function reload(){
		document.getElementById('settings').setAttribute('action','sharepic.php?weiter&prev&id=<?php echo $id; ?>');
		document.getElementById('settings').setAttribute('target','');

		absenden();
	}
</script>

<!-- start feedback button -->
<?php if ($conf_feedback == 1){ ?>
<div id="feedbackcontainer" style="position: fixed; right: 0px; top: 30px; background: #ffffff; height: 0px; width: 0px; font-size: 14px; font-family: the-sansb;">
	<button id="feedbackbutton" style="transform: rotate(-90.0deg); background: #E30613; border-radius: 4px; width: 120px; border: solid 1px #ffffff; letter-spacing:0.2em; padding: 5px 5px; color: #FFF; font-weight: bold; cursor: pointer; float: right; margin-top: 45px; margin-right: -45px" onclick="extendFeedback();">Feedback</button>
	
	<div id="feedbackform" style="display: none; position: relative; top: -70px; left: 5px">
		<div style="font-family: the-sans; margin-top: 80px; margin-right: 5px;">Der Sharepic-Generator befindet sich noch in der Testphase. Hast Du Kritik, Lob oder Anregungen?<br><br></div>

		<input type="text" id="feedbackemail" name="email" placeholder="deine@mail.de" style="width: 290px; border-radius: 3px; border: 5px solid #CCC;  padding: 2px; margin-bottom: 5px;" /><br>
		<textarea id="feedbackmessage" style="width: 290px; height: 150px; border: 5px solid #CCC; border-radius: 3px; padding: 2px; margin-bottom: 5px; font-size: 20px;"></textarea><br>
		<button onclick="submitFeedback();" style="padding: 3px; background: #E30613; border-radius: 4px; width: 120px; border: solid 1px #e3e3e3; color: #FFF; font-weight: bold; cursor: pointer;">senden</button>
		 
	</div>
</div>

<script>
var feedbackform_url = 'contact.php';
var feedbackform_emailsubject = 'Feedback Form';

var feedbackform_fc = document.getElementById('feedbackcontainer');
var feedbackform_fb = document.getElementById('feedbackbutton');
var feedbackform_ff = document.getElementById('feedbackform');
var feedbackform_fe = document.getElementById('feedbackemail');
var feedbackform_fm = document.getElementById('feedbackmessage');
function extendFeedback() {
	feedbackform_fc.style.width = '320px';
	feedbackform_fc.style.height = '300px';
	feedbackform_fc.style.bottom = '5px';
	feedbackform_fb.style.marginRight = '272px'
	feedbackform_ff.style.display = 'block';
	feedbackform_fb.onclick = function() { closeFeedback(); }
}
function closeFeedback() {
	feedbackform_fc.style.width = '0px';
	feedbackform_fc.style.height = '0px';
	feedbackform_fc.style.bottom = '180px';
	feedbackform_fb.style.marginRight = '-45px'
	feedbackform_ff.style.display = 'none';
	feedbackform_fb.onclick = function() { extendFeedback(); }
}
function submitFeedback() {
	if (feedbackform_fe.value.indexOf('@') == -1) { alert('Bitte gib eine gültige Mailadresse an.'); return; }
	feedbackform_ff.innerHTML = '<div class=head><p style="text-align: center; font-size: 16px; margin-top: 20px;">Danke für Deine Nachricht!</p></div>';
	setTimeout(function() { closeFeedback(); }, 5000);

	// Ajax Post
	var feedbackform_lookup = "email=" + encodeURIComponent(feedbackform_fe.value) + '&subject=' + encodeURIComponent(feedbackform_emailsubject) + '&message=' + encodeURIComponent(feedbackform_fm.value); // $_POST['email']
	if (window.XMLHttpRequest) { feedbackform_xmlhttp=new XMLHttpRequest(); } else { feedbackform_xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); }
	feedbackform_xmlhttp.onreadystatechange=function() {
		if (feedbackform_xmlhttp.readyState==4 && feedbackform_xmlhttp.status==200) {
			console.log(feedbackform_xmlhttp.responseText);
		}
	}
	feedbackform_xmlhttp.open("POST",feedbackform_url,true);
	feedbackform_xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	feedbackform_xmlhttp.setRequestHeader("Content-length", feedbackform_lookup.length);
	feedbackform_xmlhttp.setRequestHeader("Connection", "close");
	feedbackform_xmlhttp.send(feedbackform_lookup);
}
</script>
<?php } ?>
<!-- end feedback button -->

</body>
</html>
