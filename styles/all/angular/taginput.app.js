angular.module('rhTopicTagsInputApp', ['ngTagsInput'])
	.config(function($interpolateProvider, $httpProvider) {
		$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	})
	.controller('rhTopicTagsInputCtrl', function($scope, $http) {
		$scope.tags = [];
		$scope.init = function (initTags) {
			initTags = JSON.parse(atob(initTags));
			for (var i = 0; i < initTags.length; i++) {
				this.tags.push(initTags[i]);
			}
		}
		$scope.loadTags = function(query) {
			var data = {
				'query': query,
				'exclude' : $scope.tags.map(function(tag) {
					return tag.text;
				})
			};
			return $http.post('/tags/suggest', data);
		};
		$scope.jsonRep = '';
		$scope.$watch('tags', function(t) {
			$scope.jsonRep = btoa(JSON.stringify(t));
		}, true);
	});
