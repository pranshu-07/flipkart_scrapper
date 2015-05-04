<?php
include 'simple_html_dom.php';
include 'correlation.php';

$postData = @mysqli_real_escape_string($_POST['q']);
$pre_q = explode(' ', $postData);
$q = implode('+', $pre_q);

$html = getCurlData($q);

$response = getItemsFromFlipkart($html);


if($response == false){
	print_r('There is some problem in parsing DOM. Try another one..!');
	exit();
}

$result = saveToCsv($response);

$prices_array = getArray('prices', $response);
$scores_array = getArray('scores', $response);

$correlation = Correlation($prices_array, $scores_array);

print_r('Result is saved in data.csv in root folder and Correlation is : ' . $correlation);
exit();

function getArray($get, $response){
	$data = array();
	for($i=0; $i<10; $i++){
		if($get == 'prices'){
			array_push($data, $response[$i][1]);
		}
		if($get == 'scores'){
			array_push($data, $response[$i][2]);
		}
	}
	return $data;
	
}

function getCurlData($q){
	$url = 'http://www.flipkart.com/search?q=' . $q . '&as=on&as-show=on&otracker=start';
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 10.10; labnol;) ctrlq.org");
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$html = curl_exec($curl);
	curl_close($curl);
	return $html;
}

function getItemsFromFlipkart($str) {

 	$html = str_get_html($str);
 	
 	if($html == false){
 		return false;
 	}

 	$data = array();

 	for($i=0; $i<10; $i++){
 		$i_title = get_title($html, $i);
	 	$i_price = get_price($html, $i);
	 	$i_score = get_score($html, $i);
	 	$i_image = get_image($html, $i);
	 	$item = array($i_title, $i_price, $i_score, $i_image);
	 	array_push($data, $item);
 	}
 	return $data;
}

function saveToCsv($resp){
	$fp = fopen('data.csv', 'w');

	foreach ($resp as $fields) {
	    fputcsv($fp, $fields); ///* Save the response in CSV format */
	}

	fclose($fp);
	return 1;
}

function get_image($html, $rank){
	try{
		$image = $html->find('div[class=pu-visual-section]', $rank);
		if(is_object($image)){
			$src = $image->find('img', 0)->attr['src'];
			return $src;
		} else{
			$image = 'NA';
		}
	} catch(Exception $e){
		$image = 'NA';
	}
	return $image;
}

function get_title($html, $rank){
	try{
		$final = $html->find('div[class=pu-title]', $rank)->plaintext;
		$title = str_replace(' ', '', $final);
	} catch(Exception $e){
		$title = '';
	}
	return $title;
}

function get_price($html, $rank){
	try{
		$price = $html->find('div[class=pu-final]', $rank)->plaintext;
		$final = str_replace(' ', '', $price);
		$cut_price = str_replace('Rs.', '', $final);
		$fprice = str_replace(',', '', $cut_price);
		return intval($fprice);
	} catch(Exception $e){
		return 'NA';
	}	
}

function get_score($html, $rank){
	try{
		$score = @$html->find('div[class=fk-stars-small]', $rank)->title;
		$final = str_replace(' stars', '', $score);
		return $final;
	} catch(Exception $e){
		return 'NA';
	}	
}