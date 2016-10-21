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
        // https://appmpdf.herokuapp.com/uploads/files/580a1e0820887.jpg
        $destinationPath = 'https://appmpdf.herokuapp.com/uploads/files/';

        $filePath = $destinationPath.$name;

        // mueve la foto al directorio espesificado
        $uploadSuccess = $file->move($destinationPath, $name);
        echo "<pre>";
        print_r($filePath);
        echo "</pre>";

        if(!empty($uploadSuccess)){

          $getFaceDetect = $this->getFaceDetect($filePath);
          

          if($getFaceDetect['success']){
            $response['attributes'] = $getFaceDetect['faceAttributes'];
          }
        }

        unlink($filePath); // Eliminar el archivo cargado
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
          'Ocp-Apim-Subscription-Key: 89604415004b4035bd4ccf5f317e5b68'
        ), 
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => TRUE
      );

      // Set request options 
      curl_setopt_array($curl, $options);

      // Execute request and get response and status code 
      $info = curl_exec($curl);
      $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      $arrInfo = preg_split("/[\s,]+/", $info);
      $arrNewInfo = array();

      curl_close($curl);

      if($status == 200 AND strpos($arrInfo, 'SUCCESS') === 0){
          if(!empty($arrInfo)){
              foreach ($arrInfo as $row){
                  $linea = explode("=",$row);
                  if(!empty($linea[0]) && !empty($linea[1])){
                      $arrNewInfo[$linea[0]] = urldecode($linea[1]); 
                  }
              }
          }
        $response['faceAttributes'] = $arrNewInfo;
        $response['success'] = true;
      }
    } catch (Exception $e) {
        $response['error']['code'] = $e->getCode();
        $response['error']['msg'] = $e->getMessage();
    }

    return $response;
  }    
}
