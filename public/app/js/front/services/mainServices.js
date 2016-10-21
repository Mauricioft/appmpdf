'use strict';

angular.module('load.mainServices', [])

.factory('MainSrvc', ['$q', '$http', function ($q, $http) {
	return{
	  	uploadImage: function(file){
	  		var defered = $q.defer();  
			var promise = defered.promise;
	  		var fd = new FormData();
        	fd.append('phone', file);

	      	// $http.post('https://appmpdf.herokuapp.com/api/v1/'+'face/detect', fd, {
	      	$http.post('http://dev.mpdf.com/api/v1/'+'face/detect', fd, {
	      		transformRequest: angular.identity, 
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: angular.identity
	        }).success(function(data) {
                defered.resolve(data);
            })
            .error(function(err) {
                defered.reject(err)
            });

            return promise;
	  	}
	}
}]);