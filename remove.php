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



if(isset($_GET["id"])){
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);
	if ($_GET["id"] == "") {die ("keine ID");}
	if(strpos($_GET["id"],"'") != false or strpos($_GET["id"],'"') != false  or strpos($_GET["id"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	$ins = SQLite3::escapeString ($_GET["id"]);
	$result = $db->querySingle('SELECT "Pfad_Hintergrund" FROM "sharepics" WHERE "ID" = "'.$ins.'" ');
	unlink ($result);
	$result = $db->querySingle('SELECT "Pfad_Logo" FROM "sharepics" WHERE "ID" = "'.$ins.'" ');
	unlink ($result);
	$db->exec('DELETE FROM "sharepics" WHERE "ID"="'.$ins.'"');
	unset($db);
	
	if (isset($_GET["weiter"])){
		header ("Location: index.php?ok");
	}
	else {
		if (isset($_GET["admin"])){
			header ("Location: admin.php");
		} else {
			header ("Location: index.php");
		}
	}
}
elseif(isset($_GET["archiv"]) && isset($_GET["tk"])){
	if(strpos($_GET["archiv"],"'") != false or strpos($_GET["archiv"],'"') != false  or strpos($_GET["archiv"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	if(strpos($_GET["tk"],"'") != false or strpos($_GET["tk"],'"') != false  or strpos($_GET["tk"],'’')  != false){die ("FEHLER<br>Ein potentieller Angriffsversuch auf diese Webseite wurde erkannt und blockiert.<br>Wenn dieser Fehler willkürlich auftritt, benachrichtichtigen Sie bitte den Administrator per Mail an sharepic@spd-waghaeusel.de");}
	
	$ins = SQLite3::escapeString ($_GET["tk"]);
	
	$db = new SQLite3("data/priv/database.sqlite");
	$db->busyTimeout(5000);	
	$result = $db->querySingle('SELECT "Hash" FROM "archiv" WHERE "Token" = "'.$ins.'" ');
	unlink ("archiv/".$result.".jpg");
	$db->exec('DELETE FROM "archiv" WHERE "Token"="'.$ins.'"');
	unset($db);
	header ("Location: admin.php?archiv");
}

else {
	echo ("keine ID angegeben");
	header ("Location: index.php");
}
	


?>