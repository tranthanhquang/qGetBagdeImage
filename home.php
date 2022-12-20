<?php
/**

 * Get Bagde Image

 * @author Tran Thanh Quang

 */

	require_once 'lib/PHPExcel/Classes/PHPExcel.php';

	const baseqURL = "<URL>"; // <URL>
	const baseqExt = ".jpg";
	const qfolder = "qimages/";

	// Constant number sheet data
	define ('qcolumn_user_No', 0);
	define ('qcolumn_user_ID', 1);
	define ('qcolumn_user_name', 2);
	define ('qcolumn_user_position', 3);
	define ('qcolumn_user_email', 4);
	define ('qcolumn_user_project', 5);

	if(!is_dir(qfolder)) {
	    $output=null;
		$retval=null;
		exec("mkdir ".qfolder, $output, $retval);
	}

	function qdownloadImage($image_url, $image_file, $qdir) {
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

	function qgetDataFromExcelFileDetails() {
		$file = "data/data.xlsx";
		$data = [];
		$array_department = array();
		$array_about_user = array();

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
			// Get data from column
			$quser_id = $sheet->getCellByColumnAndRow(qcolumn_user_ID, $i)->getValue();
			$quser_name = $sheet->getCellByColumnAndRow(qcolumn_user_name, $i)->getValue();
			$quser_project = $sheet->getCellByColumnAndRow(qcolumn_user_project, $i)->getValue();
			$qimage_user_id = $quser_id;

			// Remove character before badge id
			if(!is_numeric($quser_id)) {
				$qimage_user_id = substr($quser_id, 1);
			}

			// Add to array
			$quser_department = array("company" => $quser_project, "title" => strval($quser_id));
			$quser_info = array("name" => $quser_name, "avatar" => "./".qfolder.$qimage_user_id.baseqExt, "data" => $quser_department);
			array_push($array_about_user, $quser_info);

			// array to download images
			$data[$i] = $qimage_user_id;
		}

		qWriteJsonFile($array_about_user);

		return $data;
	}

	function qWriteJsonFile($json_array) {
		// encode array to json
		$qjson = json_encode(array('qdata' => $json_array), JSON_UNESCAPED_UNICODE);

		// write json to file
		if (file_put_contents(qfolder."qdata.json", $qjson)) {
			echo "JSON file created successfully...";
		}
		else {
		    echo "Oops! Error creating json file...";
		}
	}

	$qmembers = qgetDataFromExcelFileDetails();
	// $memberListRealID = qgetDataFromExcelFile();
	foreach ($qmembers as $value) {
		$qparrams = $value.baseqExt;
		// $q_image_name = $memberListRealID[$i].baseqExt;

		// Processing Download Images List
		qdownloadImage(baseqURL.$qparrams, "{$qparrams}", qfolder);
	}
?>