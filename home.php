<?php
/**

 * Get Bagde Image

 * @author Tran Thanh Quang

 */

	require_once 'lib/PHPExcel/Classes/PHPExcel.php';

	const qfolder = "images/";
	const qidentity_url_name = "qurl:";
	const qidentity_data_name = "qexcelname:";
	const qarray_resource_file = array("qresource_data.qfile");
	const qbase_ext = ".jpg";
	$qbase_url = readResourceFile(qarray_resource_file, 0);

	function readResourceFile($resource_file_list, $content_id) {
		if (sizeof($resource_file_list) > 1) {
			foreach ($resource_file_list as &$file_name) { 
			    // Create new SplFile Object 
			    $file = new SplFileObject($file_name, "r");
			    $qpost_url = strpos($file, qidentity_url_name);
				$qpost_data = strpos($file, qidentity_data_name);
				if ($content_id == 0 && $qpost_url !== false) {
					return trim(substr($file, strlen(qidentity_url_name)));
				}

				if ($content_id == 1 && $qpost_data !== false) {
					return qdata_folder.trim(substr($file, strlen(qidentity_data_name)));
				}
			}
		} else {
			foreach (new SplFileObject($resource_file_list[0]) as $line) {
				$qpost_url = strpos($line, qidentity_url_name);
				$qpost_data = strpos($line, qidentity_data_name);
				if ($content_id == 0 && $qpost_url !== false) {
					return trim(substr($line, strlen(qidentity_url_name)));
				}

				if ($content_id == 1 && $qpost_data !== false) {
					return qdata_folder.trim(substr($line, strlen(qidentity_data_name)));
				}
			}
		}
	}

	if(!is_dir(qfolder)) {
	    $output=null;
		$retval=null;
		exec("mkdir ".qfolder, $output, $retval);
	}

	function getDataFromExcelFile() {
		$file = readResourceFile(qarray_resource_file, 1);
		$data = [];

		$objFile = PHPExcel_IOFactory::identify($file);
		$objData = PHPExcel_IOFactory::createReader($objFile);

		$objData->setReadDataOnly(true);
		$objPHPExcel = $objData->load($file);
		// get total page use getSheetCount() method;
		// get name sheet focusing use getSheetNames();
		$sheet = $objPHPExcel->setActiveSheetIndex(0);

		$Totalrow = $sheet->getHighestRow();
		$LastColumn = $sheet->getHighestColumn();
		$TotalCol = PHPExcel_Cell::columnIndexFromString($LastColumn);

		for ($i = 2; $i <= $Totalrow; $i++) {
			$tempA = $sheet->getCellByColumnAndRow(1, $i)->getValue();
			if(!is_numeric($tempA)) {
				$tempA = substr($tempA, 1);
			}
			$data[$i] = $tempA;
		}

		return $data;
	}

	function dfCurl($image_url, $image_file, $qdir){
	    $fp = fopen ($qdir.$image_file, 'w+');// open file handle

	    $ch = curl_init($image_url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // enable if you want (optional)
	    curl_setopt($ch, CURLOPT_FILE, $fp);// output to file
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);// some large value to allow curl to run for a long time
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
	    // curl_setopt($ch, CURLOPT_VERBOSE, true);// Enable this line to see debug prints
	    curl_exec($ch);

	    curl_close($ch);// closing curl handle
	    fclose($fp);// closing file handle
	}

	$memberList = getDataFromExcelFile();
	// $memberListRealID = getDataFromExcelFile();
	foreach ($memberList as $value) {
		$qParams = $value.qbase_ext;
		// $qImageName = $memberListRealID[$i].qbase_ext;
		dfCurl($qbase_url.$qParams, "{$qParams}", qfolder);
	}
?>