<?php
$spkname = $_GET["spk"];
$spk  = __DIR__ . '/' . $spkname;
$gpg  = __DIR__ . '/gpg';
$sign = __DIR__ . '/codesign.php';

if (file_exists($sign)) {
	$sign = "php codesign.php --sign=".$spk." --keydir=".$gpg." --keyfpr=@keyid@";
	
	if (is_readable($gpg.'/secring.gpg') && is_readable($gpg.'/pubring.gpg')) {
		if (is_writable($spk)) {
			exec($sign, $output, $retval);
	
			if ($retval > 0) {
				$message = "Something went wrong while signing this package.";
				file_put_contents('sspks.log', date("Y-m-d H:i:s").": ".$sign.PHP_EOL, FILE_APPEND);
				file_put_contents('sspks.log', date("Y-m-d H:i:s").": ".implode(",", $output).PHP_EOL, FILE_APPEND);
			} else 	{
				$message = "";
			}
		} else {
			$retval = -1;
			$message =  "Cannot sign ".basename($spkname)." because this package is read only.";
		}
	} else {
		$retval = -1;
		$message =  "Cannot read the encryption key because the key rings are protected.";	
	}
} else {
	$retval = -1;
	$message =  "Signing Package is not supported because gpg2 was not installed...";
}
$response = array('state' => $retval, 'message' => $message, 'details' => $output);

header('Content-Type: application/json');
echo json_encode($response);
?>