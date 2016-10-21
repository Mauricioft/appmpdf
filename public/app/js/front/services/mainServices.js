'use strict';

angular.module('load.mainServices', [])

.factory('MainSrvc', ['$q', '$http', function ($q, $http) {
	return{
	  	uploadImage: function(file){
	  		var fd = new FormData();
        	fd.append('phone', file);
	      	return $http.post('http://dev.appmpdf.com/api/v1/'+'face/detect', fd, {
	      		transformRequest: angular.identity, 
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: angular.identity
	        }).then(function (response) {
	                return response.data;
	        }); 
	  	}
	}
}]);