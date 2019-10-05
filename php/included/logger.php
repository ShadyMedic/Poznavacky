<?php
	function fileLog($message, $file='log.txt', $prefix='DATE')
	{
		//Checking for custom prefix and setting default one
		if($prefix == 'DATE'){$prefix = '['.date('d. m. y - H:i:s').'] ';}
		
		//Opening the file
		$logFile = fopen($file, 'a');
		
		//Writing the message into the file
		fwrite($logFile, $prefix.$message.PHP_EOL);
		
		//Closing the file
		fclose($logFile);
	}
?>
