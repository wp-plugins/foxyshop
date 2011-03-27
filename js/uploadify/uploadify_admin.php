<?php
/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2009 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . '/'.$_REQUEST['folder'].'/';
	$targetPath = str_replace('//','/',$targetPath);
	
	$newfilename = urldecode($_FILES['Filedata']['name']);
	$newfilename = str_replace('[1]','',$newfilename);
	$newfilename = str_replace('[2]','',$newfilename);
	$newfilename = str_replace('[3]','',$newfilename);
	$newfilename = sanitize_file_name($newfilename);
	
	$targetFile =  $targetPath . $newfilename;

	$ext = strtolower(substr($newfilename, strrpos($newfilename, '.') + 1));
	if (stristr($ext, '.php')) die('unsupported file type');

	$i = 0;
	while (file_exists($targetFile)) {
		$i++;
		if (stristr($newfilename, '.')) {
			$ext = preg_replace('/^.*\./', '', $newfilename);
			$name = substr($newfilename, 0, strrpos($newfilename, '.')) . '_' . $i . '.' . $ext;
			$targetFile =  $targetPath . $name;
		} else {
			$name = $newfilename . '_' . $i . '';
			$targetFile =  $targetPath . $name;
		}
	}


	move_uploaded_file($tempFile,$targetFile);
	echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
} else {
	echo '1';
}

function sanitize_file_name( $filename ) {
	$filename_raw = $filename;
	$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
	$filename = str_replace($special_chars, '', $filename);
	$filename = preg_replace('/[\s-]+/', '-', $filename);
	$filename = strtolower(trim($filename, '.-_'));
	return $filename;
}

?>