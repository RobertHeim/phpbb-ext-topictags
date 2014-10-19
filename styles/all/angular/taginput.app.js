angular.module('rhTopicTagsInputApp', ['ngTagsInput'])
	.config(function($interpolateProvider){
		$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
	})
	.controller('rhTopicTagsInputCtrl', function($scope, $http, $filter) {
		$scope.tags = [];
		$scope.init = function (initTags) {
			initTags = JSON.parse(atob(initTags));
			for (var i = 0; i < initTags.length; i++) {
				this.tags.push(initTags[i]);
			}
		}
		$scope.jsonRep = '';
		$scope.$watch('tags', function(t) {
			$scope.jsonRep = btoa(JSON.stringify(t));
		}, true);
	});
