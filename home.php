<?php
/**

 * Get Bagde Image

 * @author Tran Thanh Quang

 */

	require_once 'lib/PHPExcel/Classes/PHPExcel.php';

	const qfolder = "qimages/";
	const qbase_ext = ".jpg";
	const qdata_folder = "data/";
	const qidentity_url_name = "qurl:";
	const qidentity_data_name = "qexcelname:";
	const qarray_resource_file = array("qresource_data.qfile");
	// Constant number sheet data
	define ('qcolumn_user_No', 0);
	define ('qcolumn_user_ID', 1);
	define ('qcolumn_user_name', 2);
	define ('qcolumn_user_position', 3);
	define ('qcolumn_user_email', 4);
	define ('qcolumn_user_project', 5);
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
		$file = readResourceFile(qarray_resource_file, 1);
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
			$quser_info = array("name" => $quser_name, "avatar" => "./".qfolder.$qimage_user_id.qbase_ext, "data" => $quser_department);
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
		$qparrams = $value.qbase_ext;
		// $q_image_name = $memberListRealID[$i].qbase_ext;

		// Processing Download Images List
		qdownloadImage($qbase_url.$qparrams, "{$qparrams}", qfolder);
	}
?>
