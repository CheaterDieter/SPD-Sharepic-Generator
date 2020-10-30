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

if ($conf_forcessl == 1){
	if (!isset($_SERVER['HTTPS']) OR $_SERVER['HTTPS']!='on') {
		$url = 'https://';
		$url .= $_SERVER['HTTP_HOST'];
		$url .= $_SERVER['REQUEST_URI']; // $url enthält jetzt die komplette URL
		header ("Location: ".$url);
		exit();
	}
}

$login_passwort = $conf_password;


if (substr($_SERVER['HTTP_HOST'], 0, 4) === 'www.') {
    header('Location: http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 's':'').'://' . substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI']);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo ($conf_titel); ?></title>

<style>
table {
  border-collapse: collapse;
}

table, th, td {
  border: 1px solid black;
  text-align: center;
}
</style>
</head>
<body>
<a href="index.php"><img id="ico" alt="" width="120" src="data/icon.jpg"></a><br>
<?php
//Verzeichnisse sperren
$handle = fopen ("data/priv/.htaccess", "w+");
fwrite ($handle, "order deny,allow"."\n"."deny from all");
fclose ($handle);

$handle = fopen ("data/index.php", "w+");
fwrite ($handle, "<?php ?>");
fclose ($handle);

$handle = fopen ("up/.htaccess", "w+");
fwrite ($handle, "order deny,allow"."\n"."deny from all");
fclose ($handle);

$handle = fopen ("data/vorlagen/.htaccess", "w+");
fwrite ($handle, "Options +Indexes");
fclose ($handle);


$handle = fopen (".htaccess", "w+");
//fwrite ($handle, "RewriteEngine on"."\n"."ErrorDocument 404 index.php"."\n"."ErrorDocument 403 index.php");
fclose ($handle);

// phpliteadmin Passwort anpassen
$handle = fopen ("phpliteadmin.config.php", "r");
$inhalt = fread($handle, filesize("phpliteadmin.config.php"));
fclose ($handle);
$aktuellespw = string_between_two_string($inhalt, '$password = "', '";');
$inhalt = str_replace ('$password = "'.$aktuellespw.'";','$password = "'.$login_passwort.'";',$inhalt);
$handle = fopen ("phpliteadmin.config.php", "w");
fwrite ($handle, $inhalt);
fclose ($handle);
// 

// LOGIN
session_start();
if ( isset($_GET['logout'])){
	$_SESSION['eingeloggt'] = false;
}

 
if (isset($_POST['kennwort']) and $_POST['kennwort'] != ""  )
{
    // Kontrolle, ob Benutzername und Kennwort korrekt
    // diese werden i.d.R. aus Datenbank ausgelesen
    if ($_POST['kennwort'] == $login_passwort)
    {
        $_SESSION['benutzername'] = "SPDAdmin";
        $_SESSION['eingeloggt'] = true;
    }
    else
    {
        echo "<b>Login-Daten falsch</b><br><br>";
        $_SESSION['eingeloggt'] = false;
    }
}
 
if ( isset($_SESSION['eingeloggt']) and $_SESSION['eingeloggt'] == true )
{
    // Benutzer begruessen
    echo 'authentifizierter Zugriff<br><a href="admin.php?logout">ausloggen</a><br><br>';
}
else
{
    // Einloggformular anzeigen
    echo "Geschützer Bereich - Login notwendig";
 
    $url = $_SERVER['SCRIPT_NAME'];
    echo '<form action="'. $url .'" method="POST">';
    echo '<p>Kennwort:<br>';
    echo '<input type="password" name="kennwort" value="">';
    echo '<p><input type="Submit" value="einloggen">';
    echo '</form>';
 
    // Programm wird hier beendet, denn Benutzer ist noch nicht
    // eingeloggt
	echo ("</body></html>");
    exit;
}
// LOGIN ENDE

$db = new SQLite3("data/priv/database.sqlite");
$db->busyTimeout(5000);

if(isset($_GET["remall"])){
	$db->exec('UPDATE "sharepics" SET "Ablauf"="'.time().'"');
}
if($login_passwort == "123456"){
	echo ('<b>Sicherheitswarnung: Das Standardpasswort wurde nicht verändert!<br>-> unbedingt in config.php anpassen!</b><br><br>');
}
?>

<a href="admin.php">Sharepic Übersicht</a><br><a href="admin.php?vorlagen">Vorlagen bearbeiten</a><br><a href="admin.php?archiv">Archiv ansehen</a><br><hr>
<?php
if (isset ($_GET["vorlagen"])){
	if (isset ($_GET["del"]) && isset($_GET["nr"])){
		$i = SQLite3::escapeString ($_GET["nr"]);
		$db->exec('DELETE FROM "vorlagen" WHERE "Nr"="'.$i.'"');
		$i = 1;
		$ineu = 1;
		while ($i < 20)
		{
			
			if ($db->querySingle('SELECT "Nr" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ') != "")
			{
				echo ($i . " zu " . $ineu ."<br>");
				$db->querySingle('UPDATE "vorlagen" SET "Nr"='. $ineu .' WHERE "Nr"='.$i.'');	
				$ineu = $ineu +1 ;
				
			}
			$i = $i + 1;
		}
		
		header("Location: admin.php?vorlagen");
		die("gelöscht");
	}
		
	if (isset($_GET["move"])){
		if ($_GET["move"] == "up"){
			(int)$i = SQLite3::escapeString ($_GET["nr"]);
			if ($i > 1){
			$db->querySingle('UPDATE "vorlagen" SET "Nr"=9999 WHERE "Nr"='.$i.'');
			$db->querySingle('UPDATE "vorlagen" SET "Nr"='. ($i) .' WHERE "Nr"='.($i-1));
			$db->querySingle('UPDATE "vorlagen" SET "Nr"='.($i-1).' WHERE "Nr" = 9999');
			header("Location: admin.php?vorlagen");
			die("verschoben");
			}
		}
		if ($_GET["move"] == "down"){
			(int)$i = SQLite3::escapeString ($_GET["nr"]);
			if ($db->querySingle('SELECT "Nr" FROM "vorlagen" WHERE "Nr" = "'.($i+1) .'" ') != ""){
			$db->querySingle('UPDATE "vorlagen" SET "Nr"=9999 WHERE "Nr"='.$i.'');
			$db->querySingle('UPDATE "vorlagen" SET "Nr"='. ($i) .' WHERE "Nr"='.($i+1));
			$db->querySingle('UPDATE "vorlagen" SET "Nr"='.($i+1).' WHERE "Nr" = 9999');
			header("Location: admin.php?vorlagen");
			die("verschoben");
			}
		}		
	}

	if (isset($_GET["save"])){	
		$i = 1;
		while ($db->querySingle('SELECT "Nr" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ') != "")
		{	
			$i = $i + 1;
		}
		$db->querySingle('INSERT INTO "vorlagen" ("name","pfad_logo","pfad_bk","beschreibung","Nr") VALUES(NULL, NULL, NULL, NULL, '. ($i) .')');
		$db->querySingle('UPDATE "vorlagen" SET "name"="'. SQLite3::escapeString($_POST["name"]) .'" WHERE "Nr"='.($i));
		$db->querySingle('UPDATE "vorlagen" SET "pfad_logo"="'. SQLite3::escapeString($_POST["logo"]) .'" WHERE "Nr"='.($i));
		$db->querySingle('UPDATE "vorlagen" SET "pfad_bk"="'. SQLite3::escapeString($_POST["hintergrund"]) .'" WHERE "Nr"='.($i));
		$db->querySingle('UPDATE "vorlagen" SET "beschreibung"="'. SQLite3::escapeString($_POST["beschreibung"]) .'" WHERE "Nr"='.($i));
		header("Location: admin.php?vorlagen");
		die("gespeichert");
	
	}
	
	?>
	<b>Vorlagen</b><br>
	<a href="data/vorlagen" target="_blank">vorhandene Bilddateien ansehen</a><br>
	<a href="phpliteadmin.php" target="_blank">phpLiteAdmin</a><br><br>
	<table>
	  <tr>
		<th width=50>Nr</th>
		<th width=220>Name</th>
		<th width=220>Logo</th>
		<th width=150>Hintergrund</th>
		<th width=150>Beschreibung</th>
		<th width=100>verschieben</th>
		<th width=100>löschen</th>
	  </tr>	
	<?php
	$i = 1;

	while ($db->querySingle('SELECT "Nr" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ') != "")
	{
		
		$name = $db->querySingle('SELECT "name" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
		$pfad_logo = $db->querySingle('SELECT "pfad_logo" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
		$pfad_bk = $db->querySingle('SELECT "pfad_bk" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
		$beschreibung = $db->querySingle('SELECT "beschreibung" FROM "vorlagen" WHERE "Nr" = "'.$i.'" ');
		
		echo ("<tr>");
		echo ("<td>".$i."</td>");
		echo ("<td>".$name."</td>");
		echo ("<td>".$pfad_logo."<br>");
		echo ('<img height=50 src="data/vorlagen/'.$pfad_logo.'"</td>');
		
		if ($pfad_bk != ""){
			echo ("<td>".$pfad_bk."<br>");
			echo ('<img height=50 src="data/vorlagen/'.$pfad_bk.'"</td>');
		} else {
			echo ('<td>n.v.</td>');
		}
		
		echo ("<td>".$beschreibung."</td>");
		
		echo ('<td><a href=admin.php?vorlagen&move=up&nr='.$i.'>hoch</a><br><a href=admin.php?vorlagen&move=down&nr='.$i.'>runter</a></td>');
		
		echo ('<td><a href=admin.php?vorlagen&del&nr='.$i.'>löschen</a></td>');
		
		$i = $i+1;
		
		echo ("</tr>");
	}	
	echo ("</table>");
?>
<br><br>
<b>Neue Vorlage erstellen</b><br>
<form action="admin.php?vorlagen&save" method='post' enctype='multipart/form-data'>
	Name*<br>
	<input type="text" name="name"><br><br>
	Logo<br>
	<input type="text" name="logo"><br><br>
	Hintergrund<br>
	<input type="text" name="hintergrund"><br><br>
	Beschreibungstext<br>
	<input type="text" name="beschreibung"><br><br>
	<input type="submit" value="Submit">
</form>

<?php
}
elseif (isset ($_GET["archiv"])){
	?>
	<b>Archiv</b><br>
	<table>
	  <tr>
		<th width=220>Bild</th>
		<th width=150>archiviert am</th>
		<th width=100>IP</th>
		<th width=100>Löschen</th>
	  </tr>
	<?php
	$archivgrosse = 0;
	$result = $db->query('SELECT * FROM "archiv"');
	while ($row = $result->fetchArray())
	{
		$archivgrosse = $archivgrosse + filesize('archiv/'.$row['Hash'].'.jpg');
		echo ("<tr>");
		echo ('<td><a href= "archiv/'.$row['Hash'].'.jpg" target="_blank"><img width="200" src="archiv/'.$row['Hash'].'.jpg"></a></td>');
		echo ("<td>".date("Y-m-d H:i:s",$row['time']));
		echo ("</td>");
		echo ("<td>".$row['IP']."</td>");
		echo ('<td><a href="remove.php?archiv&tk='.$row['Token'].'">Löschen</a></td>');
		echo ("</tr>");
	}
	echo ("</table>");
	echo ("<br> Größe des Archivs: ".round ($archivgrosse/1048576,2)." MB / ".round ($archivgrosse/1073741824,3)." GB");
}
else {
	echo ("Abfrage um ".date("Y-m-d H:i:s"));
	?>
	<br>
	<table>
	<td height=25 width=200><a href="admin.php">aktualisieren</a></td>
	<td height=25 width=200><a href="clean.php?admin">abgelaufene Dateien entfernen</a></td>
	<td height=25 width=200><a href="admin.php?remall">alle Dateien ablaufen lassen</a></td>
	<td height=25 width=200><a href="phpliteadmin.php" target="_blank">phpLiteAdmin</a></td>
	</table>
	<br><br>

	<table>
	  <tr>
		<th width=220>Bild</th>
		<th width=150>ID</th>
		<th width=150>Ablauf</th>
		<th width=150>IP</th>
		<th width=100>Löschen</th>
	  </tr>
	  
	<?php
	$result = $db->query('SELECT * FROM "sharepics"');
	while ($row = $result->fetchArray())
	{
		echo ("<tr>");
		echo ('<td><a href= "sharepic.php?hash='.$row['Hash'].'" target="_blank"><img width="200" src="sharepic.php?prev&id='.$row['ID'].'"></a></td>');
		echo ("<td>".$row['ID']."</td>");
		echo ("<td>".date("Y-m-d H:i:s",$row['Ablauf']));
		if ($row['Ablauf'] <= time()) {
			echo ("<br>abgelaufen");
		}
		echo ("</td>");
		echo ("<td>".$row['IP']."</td>");
		echo ('<td><a href="remove.php?admin&id='.$row['ID'].'">Löschen</a></td>');
		
		
		echo ("</tr>");
	}
	echo ("</table>");
}



unset($db);
function string_between_two_string($str, $starting_word, $ending_word) 
{ 
    $subtring_start = strpos($str, $starting_word); 
    //Adding the strating index of the strating word to  
    //its length would give its ending index 
    $subtring_start += strlen($starting_word);   
    //Length of our required sub string 
    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;   
    // Return the substring from the index substring_start of length size  
    return substr($str, $subtring_start, $size);   
} 


?>
</body>
</html>