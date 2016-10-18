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
          'success' => false,
          'error' => array('code' => '', 'msg' => '')
        );

        $curl = curl_init();
  

        $headers = array(
          // Request headers
          'Content-Type' => 'application/json',
          'Ocp-Apim-Subscription-Key' => '',
        );

        $url = "https://api.projectoxford.ai/face/v1.0/detect";

        // Set request options 
        curl_setopt_array($curl, array( CURLOPT_URL => $url,
          CURLOPT_POST => TRUE,
          CURLOPT_HTTPHEADER => $headers,
          CURLOPT_ENCODING => "UTF-8",
          CURLOPT_POSTFIELDS => http_build_query(
            array(
              'text' => "Hi Team, I know the times are difficult! Our sales have been disappointing for the past three quarters for our data analytics product suite. We have a competitive data analytics product suite in the industry. But we need to do our job selling it!"
            )),
          CURLOPT_RETURNTRANSFER => TRUE
        ));

        // Execute request and get response and status code
        $responsePaypal = curl_exec($curl);
        $statusConnetion  = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        Log::info('WATSON::INFO', array('RESPONSE' => $responsePaypal));    
        curl_close($curl);


        $response['success'] = true;

    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return json_encode($response);    
	}	    
}
