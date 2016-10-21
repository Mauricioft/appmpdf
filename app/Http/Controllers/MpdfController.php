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

	public function doLoad(Request $request){ 
    try{
        $response = array(
          'attributes' => [],  
          'success' => false,
          'error' => array('code' => '', 'msg' => '')
        );

        
        $file = $request['phone'];

        if (!file_exists('uploads/files')) {
          mkdir('uploads/files', 0777, true);
        }

        // nuevo nombre a la imagen
        $name = uniqid().'.'.$file->getClientOriginalExtension();
        
        // ruta de almacenamiento temporal
        $destinationPath = public_path().'/uploads/files/';

        // mueve la foto al directorio espesificado
        $uploadSuccess = $file->move($destinationPath, $name);

        // http://mauricio-io.gfourmis.com/uploads/files/580a653d98ce2.jpg
        $filePath = 'http://'.$_SERVER['SERVER_NAME'].'/uploads/files/'.$name;
        
        if(!empty($uploadSuccess)){

          $getFaceDetect = $this->getFaceDetect($filePath);

          if($getFaceDetect['success']){
            $response['attributes'] = $getFaceDetect['faceAttributes'];
          }
        }  
        $response['success'] = true;

    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return json_encode($response);    
  } 


  private function getFaceDetect($filePath){
    try{
      $response = array(
        'faceAttributes' =>[],
        'success' => false,
        'error' => array('code' => '', 'msg' => '')
      );

      $curl = curl_init();

      $data = array(
        'url' => $filePath
      );

      $options = array( 
        CURLOPT_URL => "https://api.projectoxford.ai/face/v1.0/detect?returnFaceId=true&returnFaceLandmarks=true&returnFaceAttributes=age,gender,headPose,smile,facialHair,glasses",
        CURLOPT_POST => TRUE,
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Ocp-Apim-Subscription-Key: 355fcd3cdee141bcb1a09ff820166f0b'
        ), 
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => TRUE
      );

      // Set request options 
      curl_setopt_array($curl, $options);

      // Execute request and get response and status code 
      $info = curl_exec($curl);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      
      curl_close($curl);// cerrar curl 
      /*
      echo "<pre>";
      print_r($info);
      echo "</pre>";
      */
      $response['faceAttributes'] = json_decode($info);
      $response['success'] = true;   
    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return $response;
  }    
}
