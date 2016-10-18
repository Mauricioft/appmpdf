'use strict';

/*
*   How to use "php artisan serve" in a remote server
*/ 
// php artisan serve --host=503.246.895.41 --port=8125
// http://laravel-recipes.com/recipes/282/running-phps-built-in-web-server

/*
*   Angular File Upload is a module for the AngularJS framework
*/ 
// https://github.com/nervgh/angular-file-upload

angular.module('load.mainControllers', [])

.controller('MainCtrl', ['$scope', '$location', '$rootScope', '$timeout', '$log', 'uiUploader', 'MainSrvc', 'FileUploader', function($scope, $location, $rootScope, $timeout, $log, uiUploader, MainSrvc, FileUploader){
	/*
	$scope.btn_remove = function(file) {
	    $log.info('deleting=' + file);
	    uiUploader.removeFile(file);
	};
               	
   	$scope.btn_clean = function() {
        uiUploader.removeAll();
    };

    $scope.btn_upload = function() {
        $log.info('uploading...');
        uiUploader.startUpload({
            url: 'https://posttestserver.com/post.php',
            concurrency: 2,
            onProgress: function(file) {
                $log.info(file.name + '=' + file.humanSize);
                $scope.$apply();
            },
            onCompleted: function(file, response) {
                $log.info(file + 'response' + response);
            }
        });
    };

    $scope.files = [];

    var element = document.getElementById('file1');

    element.addEventListener('change', function(e) {
        var files = e.target.files;
        uiUploader.addFiles(files);
        $scope.files = uiUploader.getFiles();
        $scope.$apply();
    });
    */

    $scope.validationOptions = {
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6
            }
        }/*,
        messages: {
            email: {
                required: "We need your email address to contact you",
                email: "Your email address must be in the format of name@domain.com"
            },
            password: {
                required: "You must enter a password",
                minlength: "Your password must have a minimum length of 6 characters"
            }
        }*/
    }

    $scope.register = function (form) {
        if(form.validate()) {
            // Form is valid!
        }
    }

    $scope.nameHasNotBeenUsed = function( value ) {
        var blacklist = ['bad@domain.com', 'verybad@domain.com', 'mauricioft93@gmail.com'];
        return blacklist.indexOf(value) === -1;
    }

    // do load
    var csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    console.log('csrf_token', csrf_token);

    var uploader = $scope.uploader = new FileUploader({
        url: 'http://dev.mpdf.com/api/v1/excel/load',
        alias: 'fileNews', // {String} ​​: Nombre del campo que contendrá el archivo, por defecto esfile
        autoUpload: false, // {Boolean} : cargar automáticamente archivos después de añadirlos a la cola
        removeAfterUpload: false, // {Boolean} : permiten CORS. Sólo los navegadores HTML5.
        method: 'POST', // {String} : Es un método de petición. De manera predeterminada POST. Sólo los navegadores HTML5.
        isHTML5: true, // {Boolean} : true si es cargador html5-Registro. Solo lectura.
        headers : {
            'X-CSRF-TOKEN': csrf_token, // X-CSRF-TOKEN is used for Ruby on Rails Tokens
        }
    });

    // FILTERS 
    uploader.filters.push({ 
        name: 'excelFilter',
        fn: function(item, options) {
            var type = '|' + item.name.slice(item.name.lastIndexOf('.') + 1) + '|';
            return '|xls|xlsx|'.indexOf(type) !== -1;
        } 
    });

    // CALLBACKS
    // Al añadir un archivo ha fallado.
    uploader.onWhenAddingFileFailed = function(item /*{File|FileLikeObject}*/, filter, options) {
        alert(item.name);
        console.info('onWhenAddingFileFailed', item, filter, options);
    };
    uploader.onAfterAddingFile = function(fileItem) {
        console.info('onAfterAddingFile', fileItem);
        fileItem.formData.push({name: fileItem.file.name});
    };
    uploader.onAfterAddingAll = function(addedFileItems) {
        console.info('onAfterAddingAll', addedFileItems);
    };
    uploader.onBeforeUploadItem = function(item) {
        console.info('onBeforeUploadItem', item);
    };
    uploader.onProgressItem = function(fileItem, progress) {
        console.info('onProgressItem', fileItem, progress);
    };
    uploader.onProgressAll = function(progress) {
        console.info('onProgressAll', progress);
    };
    uploader.onSuccessItem = function(fileItem, response, status, headers) {
        console.info('onSuccessItem', fileItem, response, status, headers);
    };
    uploader.onErrorItem = function(fileItem, response, status, headers) {
        console.info('onErrorItem', fileItem, response, status, headers);
    };
    uploader.onCancelItem = function(fileItem, response, status, headers) {
       console.info('onCancelItem', fileItem, response, status, headers);
    };
    uploader.onCompleteItem = function(fileItem, response, status, headers) {
        console.info('onCompleteItem', fileItem, response, status, headers);
    };
    uploader.onCompleteAll = function() {
        console.info('onCompleteAll');
    };

    console.info('uploader', uploader);
}]);