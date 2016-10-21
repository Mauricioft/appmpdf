'use strict';

angular.module('uploadDirective', [])

.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
 
            element.bind('change', function(e){
                readURL(this);
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                }); 
            });

            function readURL(input) {
                console.info('readURL@input', input);
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    
                    reader.onload = function (e) {
                        $('#img').attr('src', e.target.result)
                                 .width(150)
                                 .height(200);
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                }
            }
        }
    };
}])

.directive('styleFile', function() {
    return function(scope, element, attrs) {
        element.filestyle({
            iconName: "glyphicon glyphicon-open",
            input: true,
            badge: false,
            buttonName: 'btn-success',
            buttonText: 'Cargar Imagen'
        });
    }
});