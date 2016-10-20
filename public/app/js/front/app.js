'use strict';

var app = angular.module('app', [
  'ui.router',
  'ui.uploader',
  'ui.validate',
  'ngValidate',
  'angularFileUpload',
  'load.mainControllers',
  'load.mainServices'
]);

app.config(function($stateProvider, $urlRouterProvider, $validatorProvider, $interpolateProvider) {
  //
  // Angular Tags
  $interpolateProvider.startSymbol('{{');
  $interpolateProvider.endSymbol('}}');
  // 
  // For any unmatched url, redirect to /state1 
  $urlRouterProvider.otherwise("/");
  // 
  // Now set up the states 
  $stateProvider  
    .state('file', { 
      url: "/",
      templateUrl: "app/tpls/front/index.html",
      controller: 'MainCtrl'
    });
}); 
 
app.config(["$httpProvider", "$validatorProvider", function($httpProvider, $validatorProvider) {
  
  var csrfToken = $('meta[name=csrf-token]').attr('content');
  $httpProvider.defaults.headers.common['X-CSRF-Token'] = csrfToken;
  $httpProvider.defaults.headers.post['X-CSRF-Token'] = csrfToken;
  $httpProvider.defaults.headers.put['X-CSRF-Token'] = csrfToken;
  $httpProvider.defaults.headers.patch['X-CSRF-Token'] = csrfToken;

  $validatorProvider.setDefaults({
    debug: true,
    errorElement: 'span',
    errorClass: 'help-block'
  });

  jQuery.extend(jQuery.validator.messages, {
    required: "This field is required.",
    remote: "Please fix this field.",
    email: "Please enter a valid email address.",
    url: "Please enter a valid URL.",
    date: "Please enter a valid date.",
    dateISO: "Please enter a valid date (ISO).",
    number: "Please enter a valid number.",
    digits: "Please enter only digits.",
    creditcard: "Please enter a valid credit card number.",
    equalTo: "Please enter the same value again.",
    accept: "Please enter a value with a valid extension.",
    maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
    minlength: jQuery.validator.format("Please enter at least {0} characters."),
    rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
    range: jQuery.validator.format("Please enter a value between {0} and {1}."),
    max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
    min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
  });
}]);