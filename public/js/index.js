var app = angular.module('Home', [
  'ui.bootstrap',
  'ui.router'
]);

app.config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise("index");

  $stateProvider
    .state('colors', {
        url: "/colors",
        templateUrl: "colors",
        controller: "ListDetail"
    })
    .state('videos', {
        url: "/videos",
        templateUrl: "videos",
        controller: "ListDetail"
    });
});

// app.factory('visionList', function($resource) { return $resource('visionlist', {}); });

app.controller('ListDetail',function($scope,$window){ // visionList
  $window.scrollTo(0,0);
  // $('#active_ev, #active_rp, #active_al, #active_db').removeClass('active');

  $scope.poll = function () {
    // visionList.query(queryParams, function (response) {}); // VisionList.query
  }; // poll()

});

//ng-enter="function()"
app.directive('ngEnter', function () {
  return function (scope, element, attrs) {
    element.bind("keydown keypress", function (event) {
      if (event.which === 13) {
        scope.$apply(function () {
          scope.$eval(attrs.ngEnter);
        });
        event.preventDefault();
      }
    });
  };
});
