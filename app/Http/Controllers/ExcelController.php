<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Log;
use Excel; 

class ExcelController extends Controller{	
	
	private $formatSheetTitles = array('NOTICIAS');

    public function doLoad(Request $request){
    	try {
    		$response = [
    			'isUpload' => false,
                'success' => false,
                'error' => array('code' => '', 'msg' => '')
            ];

            // obtenemos el archivo
            $file = $request->file('fileNews');

			// verifica que el directorio exista si no lo crea
            if (!file_exists('uploads/files')) {
                mkdir('uploads/files', 0777, true);
            }

            // nuevo nombre a la imagen
            $name = date('YmdHis') . '.' . $file->getClientOriginalExtension();

            // ruta de almacenamiento temporal
            $destinationPath = public_path() . '/uploads/';

            $filePath = $destinationPath.$name;

            // mueve la foto al directorio espesificado
            $uploadSuccess = $file->move($destinationPath, $name);


            if(!empty($uploadSuccess)){
                // $result = $this->readExcelFile($filePath);// inicia proceso de lectura y procesamiento del archivo
            	$result = true;
                // if(!empty($result) && count($result) > 0){
            	if(!empty($result) && count($result) > 0){
	            	unlink($filePath); // Eliminar el archivo cargado
					$response['isUpload'] =  true;      	
            	}else{
					$response['isUpload'] =  false;      	
            	}
            }

			$response['success'] = true;

    	} catch (Exception $e) {
            $response['error']['code'] = $e->getCode();
            $response['error']['msg'] = $e->getMessage();
        }

        // Finalizar
        return json_encode($response);
    }

    /**
     * Lee el archivo Excel linea por linea y llama a los demas metodos encargados de 
     * validar y procesar la informacion leida
     * 
     * @param Object $filePath - ruta del archivo excel
     * 
     * @return array 	linea 	- numero de linea en la que se presenta el error
     * 					msg 	- mensaje de error, 'ok' si se proceso correctamente
     *
     * @author Diego Guevara  
     */
    private function readExcelFile($filePath_){
    	try{
    		$response = [ 
                'error' => array('code' => '', 'msg' => '')
            ];
 			
 			$sheetTitles = $this->formatSheetTitles;

        	$excel = Excel::selectSheets($sheetTitles[0])->load($filePath_);
 			$excel->ignoreEmpty(); //Por defecto celdas vacías no serán ignorados y presentados como nula dentro de la colección de células.
            echo "<pre>";
		    dd($filePath_);
		    echo "</pre>";
 			$sheet = $excel->getActiveSheet();

 			$rowStart = 2;
	        $rowEnd = $sheet->getHighestRow();
	        $columnStart = 1;
	        $columnEnd = $sheet->getHighestColumn();


	        Log::info('ExcelController@readExcelFile [rowEnd, columnEnd]: [' . $rowEnd . ', ' . $columnEnd . ']');

	        $total = 0;
	        $success = 0;
	        $fails = 0;

	        DB::beginTransaction();

	        for ($row = $rowStart; $row <= $rowEnd; $row++) {

	            // Leer una fila
	            $cell = $sheet->rangeToArray('A' . $row . ':' . $columnEnd . $row, NULL, TRUE, TRUE);
	            $employee = $cell[0];

	            // Validar si la fila tiene datos
	            if (!$this->isRowEmpty($employee)) {
	                $total++;

	                // Crear/Actualizar empleado
	                $errors = $this->setEmployeeData($companyId, $employee, $row);

	                if (!empty($errors)) {
	                    $fails++;

	                    // Agregar empleados con errores
	                    $employeeErrors[] = array(
	                        'row' => $row,
	                        'tipo_documento' => $employee[0],
	                        'documento' => $employee[1],
	                        'nombres' => $employee[3],
	                        'apellidos' => $employee[4],
	                        'errors' => $errors
	                    );
	                } else {
	                    $success++;

	                    // Agregar empleados sin errores
	                    $employeeSuccess[] = array(
	                        'row' => $row,
	                        'tipo_documento' => $employee[0],
	                        'documento' => $employee[1],
	                        'nombres' => $employee[3],
	                        'apellidos' => $employee[4]
	                    );
	                }
	            }
	        }

	        // Validar que todas las lineas son correctas
	        if (empty($employeeErrors)) {
	            // Confirmar transacción
	            DB::commit();
	        } else {
	            // Reversar transacción
	            DB::rollback();
	        }

	        Log::info('EmpleadosController@readExcelFile [total, success, fails]: [' . $total . ', ' . $success . ', ' . $fails . ']');

	        $result = [
		        'employeeErrors' => $employeeErrors,
		        'employeeSuccess' => $employeeSuccess,
		        'total' => $total,
		        'success' => $success,
		        'fails' => $fails
	        ];


    	} catch (Exception $e) {
            $response['error']['code'] = $e->getCode();
            $response['error']['msg'] = $e->getMessage();
        }

        return json_encode($response);
    }

    /**
    * Comprueba si una fila está vacía
    * @param type $row
    * @return array|int
    */
    private function isRowEmpty($row) {

    	foreach ($row as $key => $value) {
    		if ($value) {
    			return false;
    		}
    	}

    	return true;
    }

    /**
    * Procesa los datos de la linea cargada, verificando que el jefe que reportan si existe
    * luego crea los objetos de base de datos y almacena la informacion
    * 
    * @param Object $data - Objeto con los datos del registro leido
    * @param int $linea - Numero de linea que se lee del archivo
    * 
    * @return array 	linea 	- numero de linea en la que se presenta el error
    * 					msg 	- mensaje de error, 'ok' si se proceso correctamente
    *
    * @author Diego Guevara  
    */
    private function setEmployeeData($companyId, $employee, $row) {

        $errors = array();
        $catalog = new CatalogoController();

        // Validar los campos del registro
        $verify = $this->verifyEmployeeAttributes($employee);

        if (!$verify['isValid']) {
            return $verify['errors'];
        }

        // Validar si existe un empleado con el tipo de documento y documento en la empresa
        $employeeExist = AppEmployeeModel::where('tipo_documento', '=', $catalog->sanitizeString($employee[0]))
                ->where('documento', '=', $catalog->sanitizeString($employee[1]))
                ->where('company_id', '=', $companyId)
                ->first();

        $businessEmail = $catalog->validateEmail($employee[11], false, $isValid);

        if (!$isValid) {
            array_push($errors, array('CORREO_EMPRESARIAL no es válido.'));

            return $errors;
        }

        // Validar si existe un empleado con el email
        $emailExist = AppEmployeeModel::where('employee_email', '=', $businessEmail)->first();

        $bossId = null;
        $employeeId = null;

        // No existe el empleado
        if (empty($employeeExist)) {

            // Existe el email
            if (!empty($emailExist)) {

                array_push($errors, array('CORREO_EMPRESARIAL ya existe en el sistema.'));

                return $errors;
            }

            // Instanciar empleado
            $employeeExist = new AppEmployeeModel;
            // 1 - A - TIPO_DOCUMENTO
            $employeeExist->tipo_documento = $catalog->sanitizeString($employee[0]);
            // 2 - B - NUMERO_DOCUMENTO
            $employeeExist->documento = $catalog->sanitizeString($employee[1]);
            $employeeExist->company_id = $companyId;
        } else { // Existe el empleado
            $employeeId = $employeeExist->id;

            // El correo existe asociado a otro empleado
            if (!empty($emailExist) && $emailExist->id != $employeeExist->id) {

                array_push($errors, array('CORREO_EMPRESARIAL ya se encuentra asociado a otro empleado.'));

                return $errors;
            }
        }

        // 4 - D - NOMBRES
        $employeeExist->nombre = $catalog->sanitizeString($employee[3]);
        // 5 - E - APELLIDOS
        $employeeExist->apellido = $catalog->sanitizeString($employee[4]);
        // 12 - L - CORREO_EMPRESARIAL
        $employeeExist->employee_email = $businessEmail;
        // 24 - X - TIPO_DOCUMENTO_JEFE
        $documentTypeBoss = $catalog->sanitizeString($employee[23]);
        // 25 - Y - DOCUMENTO_JEFE
        $documentBoss = $catalog->sanitizeString($employee[24]);

        // Validar si hay tipo de documento y documento del jefe
        if (empty($documentTypeBoss) && empty($documentBoss)) {

            $employeeExist->parent_employee_id = null;
        } else {

            $bossData = null;

            if (!empty($documentTypeBoss) && !empty($documentBoss)) {

                // Obtener jefe
                $bossData = AppEmployeeModel::where('tipo_documento', '=', $documentTypeBoss)
                        ->where('documento', '=', $documentBoss)
                        ->where('company_id', '=', $companyId)
                        ->first();
            }

            // Validar si existe el jefe
            if ($bossData == null) {

                array_push($errors, array('TIPO_DOCUMENTO_JEFE y DOCUMENTO_JEFE no son válidos.'));

                return $errors;
            } else {
                $employeeExist->parent_employee_id = $bossData->id;
            }
        }

        $employeeStatus = Str::upper(trim($employee[2]));

        if (strcmp($employeeStatus, 'INACTIVO') == 0 || strcmp($employeeStatus, 'FALLECIDO') == 0) {

            $subordinates = count($catalog->getSubordinates($companyId, $employeeId));

            if ($subordinates > 0) {

                array_push($errors, array('El empleado tiene ' . $subordinates . ' pesonas a su cargo, no se puede cambiar el ESTADO.'));

                return $errors;
            } else {
                // 3 - C - ESTADO
                $employeeExist->status = $employeeStatus;
                $employeeExist->deleted_at = date("Y-m-d H:i:s");
                $catalog->disableEmployeeTasks($employeeId);
            }
        } elseif (strcmp($employeeStatus, 'ACTIVO') == 0) {
            // 3 - C - ESTADO
            $employeeExist->status = $employeeStatus;
            $employeeExist->deleted_at = null;
        } else {

            array_push($errors, array('ESTADO no es válido.'));

            return $errors;
        }

        // Guardar empleado
        $employeeExist->save();

        // seccion de actualizacion de datos adicionales del empleado
        $emp_ad_data = AppEmpleadoAdicionalModel::where('id', '=', $employeeExist->id)->first();

        if ($emp_ad_data == null) {
            $emp_ad_data = new AppEmpleadoAdicionalModel;
            $emp_ad_data->id = $employeeExist->id;
        }

        // 6 - F - SEXO
        $gender = $catalog->validateGender($employee[5], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('SEXO no es válido.'));
        } else {
            $emp_ad_data->sexo = $gender;
        }

        // 7 - G - FECHA_NACIMIENTO
        $birthDate = $catalog->validateBirthDateDate($employee[6], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_NACIMIENTO no es válida.'));
        } else {
            $emp_ad_data->fecha_nacimiento = $birthDate;
        }

        // 8 - H - PAIS_NACIMIENTO
        $emp_ad_data->birth_country_id = $catalog->getCountryIdByName($employee[7]);

        // 9 - I - CIUDAD_NACIMIENTO
        $emp_ad_data->ciudad_nacimiento = $catalog->validateBirthCity($employee[8]);

        // 10 - J - NACIONALIDAD
        $emp_ad_data->nationality_id = $catalog->getNationalityIdByName($employee[9]);

        // 11 - K - NIVEL_ACADEMICO        
        $academicLevel = $catalog->validateAcademicLevel($employee[10], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('NIVEL_ACADEMICO no es válido.'));
        } else {
            $emp_ad_data->nivel_academico = $academicLevel;
        }

        // 13 - M - ESTADO_CIVIL
        $maritalStatus = $catalog->validateMaritalStatus($employee[12], true, $isValid);
        if (!$isValid) {
            array_push($errors, array('ESTADO_CIVIL no es válido.'));
        } else {
            $emp_ad_data->estado_civil = $maritalStatus;
        }

        // 14 - N - TIPO_CONTRATO
        $emp_ad_data->tipo_contrato = $catalog->validateContractType($employee[13]);

        // 15 - O -FECHA_INGRESO
        $joiningDate = $catalog->validateJoiningDate($employee[14], false, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_INGRESO ' . $employee[14] . ' no es válida.'));
        } else {
            $emp_ad_data->fecha_ingreso = $joiningDate;
        }

        // 16 - P - FECHA_FIN_CONTRATO                
        $terminationDate = $catalog->validateTerminationDate($employee[15], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_FIN_CONTRATO no es válida.'));
        } else {
            $emp_ad_data->fecha_terminacion_contrato = $terminationDate;
        }

        // 17 - Q - FECHA_RETIRO
        $inactiveDate = $catalog->validateInactiveDate($employee[16], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_RETIRO no es válida.'));
        } else {
            $emp_ad_data->fecha_retiro = $inactiveDate;
        }

        // 18 - R - AREA
        $emp_ad_data->area = $catalog->validateArea($employee[17]);

        // 19 - S - NIVEL_CARGO
        $positionLevel = $catalog->validatePositionLevel($employee[18], false, $isValid);

        if (!$isValid) {
            array_push($errors, array('NIVEL_CARGO no es válido.'));
        } else {

            $emp_ad_data->nivel_cargo = $positionLevel;
        }

        // 20 - T - CARGO
        $emp_ad_data->cargo = $catalog->validatePosition($employee[19]);

        // 21 - U - PAIS_TRABAJA
        $emp_ad_data->work_country_id = $catalog->getCountryIdByName($employee[20]);

        // 22 - V - CIUDAD_TRABAJA
        $emp_ad_data->ciudad_donde_trabaja = $catalog->validateWorkCity($employee[21]);

        // 23 - W - REGIONAL
        $emp_ad_data->regional = $catalog->validateRegional($employee[22]);

        // 26 - Z - MONEDA
        $emp_ad_data->currency_id = $catalog->getCurrencyIdByName($employee[25]);

        // 27 - AA - REGIMEN_SALARIAL
        $emp_ad_data->regimen_salarial = $catalog->validateWageRegimes($employee[26]);

        // 28 - AB - SALARIO
        $salary = $catalog->validateSalary($employee[27], false, $isValid);

        if (!$isValid) {
            array_push($errors, array('SALARIO no es válido.'));
        } else {
            $emp_ad_data->sueldo_mensual = $salary;
        }

        // 29 - AC - SUELDOS_ANUALES
        $annualSalaries = $catalog->validateAnnualSalaries($employee[28], false, $isValid);

        if (!$isValid) {
            array_push($errors, array('SUELDOS_ANUALES no es válido.'));
        } else {
            $emp_ad_data->numero_sueldos_anuales = $annualSalaries;
        }

        // 30 - AD - PROMEDIO_COMISIONES
        $averageCommissions = $catalog->validateAverageCommissions($employee[29], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('PROMEDIO_COMISIONES no es válido.'));
        } else {
            $emp_ad_data->promedio_comision_ultimo_anio = $averageCommissions;
        }

        // 31 - AE - FACTOR_SALARIAL
        $wageFactor = $catalog->validateWageFactor($employee[30], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FACTOR_SALARIAL no es válido.'));
        } else {
            $emp_ad_data->factor_salarial = $wageFactor;
        }

        // 32 - AF - VALOR_ULTIMO_BONO                
        $lastValueBonus = $catalog->validateLastValueBonus($employee[31], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('VALOR_ULTIMO_BONO no es válido.'));
        } else {
            $emp_ad_data->bonos_anuales = $lastValueBonus;
        }

        // 33 - AG - ULTIMO_INCREMENTO_SALARIAL        
        $lastSalaryIncrease = $catalog->validateLastSalaryIncrease($employee[32], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('ULTIMO_INCREMENTO_SALARIAL no es válido.'));
        } else {
            $emp_ad_data->incremento_salarial = $lastSalaryIncrease;
        }

        // 34 - AH - FECHA_INCREMENTO        
        $lastDateIncrease = $catalog->validateLastDateIncrease($employee[33], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_INCREMENTO no es válida.'));
        } else {
            $emp_ad_data->last_salary_increment_date = $lastDateIncrease;
        }

        // 35 - AI - FECHA_ULTIMA_PROMOCION                
        $lastDatePromotion = $catalog->validateLastDatePromotion($employee[34], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('FECHA_ULTIMA_PROMOCION no es válida.'));
        } else {
            $emp_ad_data->fecha_ultima_promocion = $lastDatePromotion;
        }

        // 36 - AJ - ULTIMA_FECHA_VACACIONES        
        $lastVacationDate = $catalog->validateLastVacationDate($employee[35], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('ULTIMA_FECHA_VACACIONES no es válida.'));
        } else {
            $emp_ad_data->fecha_ultimas_vacaciones = $lastVacationDate;
        }

        // 37 - AK - DIAS_VACACIONES_PENDIENTES       
        $vacationDays = $catalog->validateVacationDays($employee[36], true, $isValid);

        if (!$isValid) {
            array_push($errors, array('DIAS_VACACIONES_PENDIENTES no es válido.'));
        } else {
            $emp_ad_data->dias_vacaciones_pendientes = $vacationDays;
        }

        // Guardar información adicional del empleado
        $emp_ad_data->save();

        return $errors;
    }
}
