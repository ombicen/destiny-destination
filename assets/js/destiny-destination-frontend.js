/**
 * Destiny Destination Frontend JavaScript
 * Handles frontend-specific functionality
 */
(function ($) {
  "use strict";

  // Initialize when document is ready
  $(document).ready(function () {
    // Handle refresh button if added in the future
    $(document).on("click", ".destiny-refresh-btn", function (e) {
      e.preventDefault();
      var $widget = $(this).closest(".destiny-destination-widget");

      if (window.DestinyDestination) {
        window.DestinyDestination.setupWidget($widget);
      }
    });

    // Handle manual address input if added in the future
    $(document).on("submit", ".destiny-manual-form", function (e) {
      e.preventDefault();
      var $form = $(this);
      var $widget = $form.closest(".destiny-destination-widget");
      var address = $form.find(".destiny-manual-address").val();

      if (address && window.DestinyDestination) {
        window.DestinyDestination.getDestinationInfo($widget, address);
      }
    });

    // Auto-refresh on page visibility change (when user comes back to tab)
    $(document).on("visibilitychange", function () {
      if (!document.hidden) {
        // Page became visible, refresh widgets after 1 second
        setTimeout(function () {
          $(".destiny-destination-widget").each(function () {
            var $widget = $(this);
            var lastUpdate = $widget.data("last-update") || 0;
            var now = Date.now();

            // Refresh if it's been more than 5 minutes
            if (now - lastUpdate > 300000) {
              if (window.DestinyDestination) {
                window.DestinyDestination.setupWidget($widget);
                $widget.data("last-update", now);
              }
            }
          });
        }, 1000);
      }
    });
  });
})(jQuery);
