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
        }
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
        url: 'https://appmpdf.herokuapp.com/api/v1/face/detect',
        alias: 'phone', // {String} ​​: Nombre del campo que contendrá el archivo, por defecto esfile
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
        name: 'imageFilter',
        fn: function(item, options) {
            var type = '|' + item.name.slice(item.name.lastIndexOf('.') + 1) + '|';
            return '|jpg|jpge|png|'.indexOf(type) !== -1;
        } 
    });

    // File must not be larger then some size
    uploader.filters.push({ 
        name: 'sizeFilter', 
        fn: function(item) { 
            return item.size < 5000000;
        }
    });

    // CALLBACKS
    // Al añadir un archivo ha fallado.
    uploader.onWhenAddingFileFailed = function(item , filter, options) {
        var type = '|' + item.name.slice(item.name.lastIndexOf('.') + 1) + '|';
        var format = ('|jpg|jpge|png|'.indexOf(type) !== -1);
        var size = (item.size < 5000000);

        if(!format){
            alert('La extensión del archivo no es valida');
        }

        if(format && !size){
            alert('El archivo '+ item.name +' no debe sobre pasar las 5MB');
        }
    }

    uploader.onAfterAddingFile = function(fileItem) {
        console.info('onAfterAddingFile', fileItem);
        fileItem.formData.push({name: fileItem.file.name});
    }

    uploader.onAfterAddingAll = function(addedFileItems) {
        console.info('onAfterAddingAll', addedFileItems);
    }

    uploader.onBeforeUploadItem = function(item) {
        console.info('onBeforeUploadItem', item);
    }

    uploader.onProgressItem = function(fileItem, progress) {
        console.info('onProgressItem', fileItem, progress);
    }

    uploader.onProgressAll = function(progress) {
        console.info('onProgressAll', progress);
    }

    uploader.onSuccessItem = function(fileItem, response, status, headers) {
        console.info('onSuccessItem', fileItem, response, status, headers);
        console.info('onSuccessItem@response', response);
        if(response['success'] === true){ 
            if(angular.isUndefined(response['attributes'])){
                console.log('onSuccessItem@attributes', response['attributes']); 
            }
        }
    }

    uploader.onErrorItem = function(fileItem, response, status, headers) {
        console.info('onErrorItem', fileItem, response, status, headers);
    }

    uploader.onCancelItem = function(fileItem, response, status, headers) {
       console.info('onCancelItem', fileItem, response, status, headers);
    }

    uploader.onCompleteItem = function(fileItem, response, status, headers) {
        console.info('onCompleteItem', fileItem, response, status, headers);
    }

    uploader.onCompleteAll = function() {
        console.info('onCompleteAll');
    }

    console.info('uploader', uploader);
}]);