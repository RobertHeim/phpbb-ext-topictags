$.fn.flash = function(duration, iterations) {
    duration = duration || 1000; // Default to 1 second
    iterations = iterations || 1; // Default to 1 iteration
    var iterationDuration = Math.floor(duration / iterations);

    for (var i = 0; i < iterations; i++) {
        this.fadeOut(iterationDuration).fadeIn(iterationDuration);
    }
    return this;
}

angular.module('rhTopicTagsInputApp', ['ngTagsInput'])
	.config(function($interpolateProvider, $httpProvider) {
		$interpolateProvider.startSymbol('{[{').endSymbol('}]}');
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	})
	.controller('rhTopicTagsInputCtrl', function($scope, $http) {
		$scope.tags = [];
		$scope.init = function (initTags) {
			if ('' != initTags) {
				initTags = JSON.parse(atob(initTags));
				for (var i = 0; i < initTags.length; i++) {
					this.tags.push(initTags[i]);
				}
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
		$scope.addTag = function(tag) {
			var found = false;
			this.tags.every(function(element, index, array) {
				if (element.text == tag) {
					found = true;
					return false;
				}
				return true;
			});
			if (!found) {
				this.tags.push({"text": tag});
			} else {
				$("span:contains('"+tag+"')")
				.filter(function() {
				    return $(this).text() === tag;
				})
				.parent()
				.flash(200, 3);
			}
		}
		$scope.jsonRep = '';
		$scope.$watch('tags', function(t) {
			$scope.jsonRep = btoa(JSON.stringify(t));
		}, true);
	});
