'use strict';

angular.module('load.mainServices', [])

.factory('MainSrvc', ['$q', '$http', function ($q, $http) {
	return{
	  	getLanguage: function(){
	      	return $http.post('tets').then(function (response) {
	            return response.data;
	      	});
	  	}
	}
}]);