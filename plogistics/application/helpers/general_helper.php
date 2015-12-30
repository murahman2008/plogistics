<?php

/**
 * This function get the GST inclusive price of a given price
 * @param int/double $price
 * @return number
 */
function getGSTInclusivePrice($price)
{
	return ($price + ((GST_AMOUNT / 100) * $price));
}

/**
 * This function gets the GST Excluding price of a given price
 * @param int/double $price
 * @return number
 */
function getGSTExclusivePrice($price)
{
	return ($price * (100 / (100 + GST_AMOUNT)));
}

/**
 * This function checks if a date is a valid date based on the format provided
 * @param String $date
 * @param string $format
 * @return boolean
 */
function validateDateTime($date, $format = 'Y-m-d H:i:s')
{
	$d = DateTime::createFromFormat($format, $date);
	return (($d && $d->format($format) == $date) ? true : false);
}

/**
 * This function converts the date string into the provided format
 * @param unknown $date
 * @param string $format
 * @return string
 */
function convertDateTime($date, $format = 'Y-m-d H:i:s')
{
	return date($format, strtotime($date));
}

function checkFileExtension($fileName, Array $extension)
{
	if(($fileName = trim($fileName)) === '')
		throw new Exception('File Name is Empty');
	
	if(count($extension) <= 0)
		throw new Exception('Must specify a list of acceptable extensions');
	
	$fileNameArray = explode(".", $fileName);
	$fileExtension = trim($fileNameArray[count($fileNameArray) - 1]);
	
	if(!in_array(strtolower($fileExtension), $extension))
		throw new Exception('File ['.$fileName.'] has invalid extension. Acceptable extension(s) are : ['.implode(", ", $extension).']');
	
	return true;
}

function inspect($data, $raw = false)
{
	if($raw === true)
		var_dump($data);
	else
	{	
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}	
}

function ajaxResponse($output)
{
	echo json_encode($output);
}

function convertBarcodeImageToWebLink($barcodeImageFullPath)
{
	if(file_exists($barcodeImageFullPath))
		return base_url(str_replace(FCPATH, '', $barcodeImageFullPath));
	
	return false;
}

