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
          'success' => false,
          'error' => array('code' => '', 'msg' => '')
        );

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', "https://api.projectoxford.ai/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=true&returnFaceAttributes=age,gender,headPose,smile,facialHair,glasses", [
          // un array con la data de los headers como tipo de peticion, etc.
          'headers' => [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => 'd08a5f2639ce460e8acb7854c493acfb'
          ],
          // array de datos del formulario
          'json' => [
            'url' => "https://media.licdn.com/mpr/mpr/shrinknp_200_200/p/6/005/092/3ce/32b77f2.jpg"
          ]
        ]);  

        $response['content'] = $response;

        $response['success'] = true;

    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return json_encode($response);    
	}	    
}
