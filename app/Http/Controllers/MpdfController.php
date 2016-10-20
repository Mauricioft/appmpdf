<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\PdfWrapper as PDF;
use Log;
 

class MpdfController extends Controller{

    public function index(){

		$pdf = new PDF('utf-8');
		$pdf->mirrorMargins(1);

		$html = \View::make('pdf.index');				
		// $style = file_get_contents('assets/css/bootstrap.min.css');
		$style = file_get_contents('assets/css/mpdf.css');
		
		$pdf->getTest($html, $style);

		/*
		return \View::make('pdf.index');
		*/
    }

	public function doCreate(){ 
    try{
        $response = array(
          'content' => [], 
          'status' => [], 
          'success' => false,
          'error' => array('code' => '', 'msg' => '')
        );

        $curl = curl_init();
  
        $data = array(
          'url' => "https://media.licdn.com/mpr/mpr/shrinknp_200_200/p/6/005/092/3ce/32b77f2.jpg"
        );

        $options = array( 
          CURLOPT_URL => "https://api.projectoxford.ai/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=true&returnFaceAttributes=age,gender,headPose,smile,facialHair,glasses",
          CURLOPT_POST => TRUE,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Ocp-Apim-Subscription-Key: xxxxxxx'
          ), 
          CURLOPT_POSTFIELDS => json_encode($data),
          CURLOPT_RETURNTRANSFER => TRUE
        );

        // Set request options 
        curl_setopt_array($curl, $options);

        // Execute request and get response and status code 
        $info = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
        curl_close($curl);

        $arrInfo = preg_split("/[\s,]+/", $info);
        $arrNewInfo = array();

        if(!empty($arrInfo)){
            foreach ($arrInfo as $row){
                $linea = explode("=",$row);
                if(!empty($linea[0]) && !empty($linea[1])){
                    $arrNewInfo[$linea[0]] = urldecode($linea[1]); 
                }
            }
        }

        if($status == 200 AND strpos($arrInfo, 'SUCCESS') === 0){
          $response['info'] = $arrNewInfo;
          $response['status'] = $status;
        }
 
        $response['success'] = true;

    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return json_encode($response);    
	}	    
}
