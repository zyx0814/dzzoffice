<?php

function Qiniu_Encode($str) // URLSafeBase64Encode
{
	$find = ['+', '/'];
	$replace = ['-', '_'];
	return str_replace($find, $replace, base64_encode($str));
}


function Qiniu_Decode($str)
{
	$find = ['-', '_'];
	$replace = ['+', '/'];
	return base64_decode(str_replace($find, $replace, $str));
}
