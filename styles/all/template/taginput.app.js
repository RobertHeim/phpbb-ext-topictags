$.fn.flash = function(duration, iterations) {
    duration = duration || 1000; // Default to 1 second
    iterations = iterations || 1; // Default to 1 iteration
    var iterationDuration = Math.floor(duration / iterations);

    for (var i = 0; i < iterations; i++) {
        this.fadeOut(iterationDuration).fadeIn(iterationDuration);
    }
    return this;
}

/**
 * btoa() is not utf8 safe by default
 */
function utf8_to_b64( str ) {
    return window.btoa(encodeURIComponent(str));
}

function b64_to_utf8( str ) {
    return decodeURIComponent(window.atob(str));
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
				initTags = JSON.parse(b64_to_utf8(initTags));
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
			$scope.jsonRep = utf8_to_b64(JSON.stringify(t));
		}, true);
	});
