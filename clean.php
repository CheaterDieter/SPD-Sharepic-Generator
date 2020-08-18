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

$result = $db->query('SELECT * FROM "sharepics"');
while ($row = $result->fetchArray())
{
	$headline = $row['ID'];
	echo ("<br>".$row['ID']);
	if (time() >= $row['Ablauf']){
		unlink ($row['Pfad_Hintergrund']);
		unlink ($row['Pfad_Logo']);
		$db->exec('DELETE FROM "sharepics" WHERE "ID"="'.$row['ID'].'"');
		echo (" - gelöscht");
	} else {
		echo (" - zu neu");
	}
}

echo ("<br><br>--Verwaiste Dateien--<br>");
$dirHandle = dir("./up");
 
// Verzeichnis Datei für Datei lesen
while (($f = $dirHandle->read()) != false) {
   // Nur ausgeben, wenn nicht . oder ..
    if ($f != "." && $f != ".." && $f != ".htaccess"){
		$f = "up/".$f;
		$result = $db->querySingle('SELECT * FROM "sharepics" WHERE "Pfad_Hintergrund" = "'.$f.'" ');
		if ($result == FALSE){
			$result = $db->querySingle('SELECT * FROM "sharepics" WHERE "Pfad_Logo" = "'.$f.'" ');
			if ($result == FALSE){	
				echo ($f." - gelöscht<br>");
				unlink ($f);
			}
		}
    }
}
 
// Verzeichnis wieder schließen
$dirHandle->close();

if (isset($_GET["admin"])){
	header ("Location: admin.php");
}

?>