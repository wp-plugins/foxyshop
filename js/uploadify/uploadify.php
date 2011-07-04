<?php
/*
Uploadify v2.1.0
Release Date: August 24, 2009

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

	$foxyshop_document_root = $_SERVER['DOCUMENT_ROOT'];
	if ($foxyshop_document_root == "" || $foxyshop_document_root == "/") $foxyshop_document_root = str_replace("/wp-content/plugins/foxyshop/js/uploadify","",dirname(__FILE__));

	$tempFile = $_FILES['Filedata']['tmp_name'];
	if (isset($_REQUEST['folder'])) {
		$targetPath = realpath($foxyshop_document_root) . '/'.$_REQUEST['folder'].'/';
	} else {
		$targetPath = realpath($foxyshop_document_root) . '/customuploads/';
	}
	
	$ext = strtolower(substr($_FILES['Filedata']['name'], strrpos($_FILES['Filedata']['name'], '.') + 1));
	$allowed_extensions = array("jpg","gif","jpeg","png","doc","docx","odt","xmls","xlsx","txt","tif","psd","pdf");
	
	if (!in_array($ext, $allowed_extensions)) {
		die('unsupported file type');
	}
	
	$newfilename = str_replace('.','',$_REQUEST['newfilename']).'.'.$ext;
	$targetFile =  str_replace('//','/',$targetPath) . $newfilename;
	echo $newfilename;
	move_uploaded_file($tempFile,$targetFile);

} else {
	echo '1';
}
?>