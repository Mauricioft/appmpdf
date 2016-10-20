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
          'face' => [], 
          'success' => false,
          'error' => array('code' => '', 'msg' => '')
        );

        $curl = curl_init();
  
        $face_api_key = env('FACE_API_KEY', 'forge');

        $headers = array(
          // Request headers
          'Content-Type' => 'application/json',
          'Ocp-Apim-Subscription-Key' => 'd08a5f2639ce460e8acb7854c493acfb'
        );

        $url = "https://api.projectoxford.ai/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=true&returnFaceAttributes=age,gender,headPose,smile,facialHair,glasses";
        
        $data = array(
          'url' => "https://media.licdn.com/mpr/mpr/shrinknp_200_200/p/6/005/092/3ce/32b77f2.jpg"
        );

        $data_string = json_encode($data);

        // Set request options 
        curl_setopt_array($curl, array( 
          CURLOPT_URL => $url,
          CURLOPT_POST => TRUE,
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_ENCODING => "UTF-8",
          CURLOPT_POSTFIELDS => $data_string,
          CURLOPT_RETURNTRANSFER => TRUE
        ));

        // Execute request and get response and status code 
        $responsePaypal = curl_exec($curl);
        $statusConnetion  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
 
        curl_close($curl);

        $response['face'] = $responsePaypal;

        $response['success'] = true;

    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return json_encode($response);    
	}	    
}
