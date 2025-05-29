/**
 * Destiny Destination Widget JavaScript
 */
(function ($) {
  "use strict";

  var DestinyDestination = {
    init: function () {
      this.bindEvents();
    },

    bindEvents: function () {
      // Initialize widgets when Elementor loads
      $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction(
          "frontend/element_ready/destiny_destination.default",
          DestinyDestination.initWidget
        );
      });
    },

    initWidget: function ($scope) {
      var $widget = $scope.find(".destiny-destination-widget");

      if ($widget.length > 0) {
        DestinyDestination.setupWidget($widget);
      }
    },

    setupWidget: function ($widget) {
      var fallbackSource =
        $widget.data("fallback-source") || "Stockholm, Sweden";

      // Show loading state
      DestinyDestination.showLoading($widget);

      // Try to get user's current location
      if (navigator.geolocation) {
        var options = {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 300000, // 5 minutes
        };

        navigator.geolocation.getCurrentPosition(
          function (position) {
            var origin =
              position.coords.latitude + "," + position.coords.longitude;
            DestinyDestination.getDestinationInfo($widget, origin);
          },
          function (error) {
            console.log("Geolocation error:", error);
            // Fallback to predefined source
            DestinyDestination.getDestinationInfo($widget, fallbackSource);
          },
          options
        );
      } else {
        // Geolocation not supported, use fallback
        DestinyDestination.getDestinationInfo($widget, fallbackSource);
      }
    },

    showLoading: function ($widget) {
      $widget.find(".destiny-loading").show();
      $widget.find(".destiny-error, .destiny-results").hide();
    },

    showError: function ($widget, message) {
      $widget.find(".destiny-error").show();
      $widget.find(".destiny-loading, .destiny-results").hide();

      if (message) {
        $widget.find(".destiny-error p").text(message);
      }
    },

    showResults: function ($widget, data) {
      $widget.find(".destiny-results").show();
      $widget.find(".destiny-loading, .destiny-error").hide();

      if (data.distance) {
        $widget.find('[data-field="distance"]').text(data.distance);
      }
      if (data.duration) {
        $widget.find('[data-field="duration"]').text(data.duration);
      }
    },

    getDestinationInfo: function ($widget, origin) {
      $.ajax({
        url: destiny_destination_ajax.ajax_url,
        type: "POST",
        data: {
          action: "get_destination_info",
          origin: origin,
          nonce: destiny_destination_ajax.nonce,
        },
        timeout: 15000,
        success: function (response) {
          if (response && response.status === "success") {
            DestinyDestination.showResults($widget, response);
          } else {
            DestinyDestination.showError(
              $widget,
              response.message || "Unable to get destination information."
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX error:", status, error);
          var errorMessage =
            "Network error. Please check your connection and try again.";

          if (status === "timeout") {
            errorMessage = "Request timed out. Please try again.";
          }

          DestinyDestination.showError($widget, errorMessage);
        },
      });
    },

    // Utility function to format distance
    formatDistance: function (distance) {
      if (typeof distance === "string") {
        return distance;
      }

      if (distance >= 1000) {
        return (distance / 1000).toFixed(1) + " km";
      } else {
        return Math.round(distance) + " m";
      }
    },

    // Utility function to format duration
    formatDuration: function (duration) {
      if (typeof duration === "string") {
        return duration;
      }

      var hours = Math.floor(duration / 3600);
      var minutes = Math.floor((duration % 3600) / 60);

      if (hours > 0) {
        return hours + "h " + minutes + "m";
      } else {
        return minutes + " min";
      }
    },
  };

  // Initialize when DOM is ready
  $(document).ready(function () {
    DestinyDestination.init();
  });

  // Expose to global scope for debugging
  window.DestinyDestination = DestinyDestination;
})(jQuery);
