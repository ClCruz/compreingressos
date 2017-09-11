<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination
$targetFolder = '../../image/uploads'; // Relative to the root

$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png','csv'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		echo '1';
	} else {
		echo 'Invalid file type.';
	}
}
/* uploadify.php antigo 2.1.0
<?php
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
	$targetFile =  str_replace('/', '\\', str_replace('//','\\',$targetPath)) . $_FILES['Filedata']['name'];
	
	$fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
	$fileTypes  = str_replace(';','|',$fileTypes);
	$typesArray = split('\|',$fileTypes);
	$fileParts  = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$typesArray)) {
		mkdir(str_replace('//','/',$targetPath), 0755, true);
		
		move_uploaded_file($tempFile,$targetFile);
		echo 'true?' . $_REQUEST['folder'] . '/' . $_FILES['Filedata']['name'];
	} else {
		echo 'Tipo de arquivo inválido!';
	}
}
?>
*/
?>