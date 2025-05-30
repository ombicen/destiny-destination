/**
 * Destiny Destination Widget JavaScript
 * External file version
 */
jQuery(document).ready(function ($) {
  // Check if destiny_destination_ajax is available
  if (typeof destiny_destination_ajax === "undefined") {
    console.error("destiny_destination_ajax not available");
    return;
  }

  // Initialize all widgets on the page
  $(".destiny-destination-widget").each(function () {
    initializeDestinyWidget($(this));
  });

  function initializeDestinyWidget(widget) {
    // Get widget data from data attributes
    var widgetId = widget.attr("id");
    var fallbackSource = widget.data("fallback-source") || "Stockholm, Sverige";
    var destination =
      widget.data("destination") || "Vallentuna bil och däckservice";
    var destinationAddress =
      widget.data("destination-address") ||
      "Moränvägen 13, 186 40 Vallentuna, Sweden";
    var enableCache = widget.data("enable-cache") || "yes";
    var cacheTime = widget.data("cache-time") || 60;

    console.log("Widget initializing:", widgetId);
    console.log(
      "Cache settings - enableCache:",
      enableCache,
      "cacheTime:",
      cacheTime
    );
    console.log("Fallback source:", fallbackSource);

    // Global state to prevent multiple requests across all widget instances
    window.destinyDestinationGlobal = window.destinyDestinationGlobal || {
      initialized: false,
      locationPromise: null,
      dataPromise: null,
      cachedResponse: null,
      pendingWidgets: [],
      finalOrigin: null,
      usedFallback: false,
    };

    var global = window.destinyDestinationGlobal;

    console.log(
      "Global state check - initialized:",
      global.initialized,
      "locationPromise exists:",
      !!global.locationPromise,
      "dataPromise exists:",
      !!global.dataPromise
    );

    // Utility: Toggle visibility by class
    function setWidgetState(state) {
      console.log(
        "STATE CHANGE: Setting widget state to:",
        state,
        "for widget:",
        widgetId
      );
      widget.removeClass("is-loading is-error is-results");
      if (state === "loading") {
        widget.addClass("is-loading");
      } else if (state === "error") {
        widget.addClass("is-error");
      } else if (state === "results") {
        widget.addClass("is-results");
      }
      console.log("STATE CHANGE: Widget classes now:", widget.attr("class"));
    }

    // Utility: Update widget DOM with results
    function updateWidgetResults(data) {
      let timeText = data.duration_in_traffic || data.duration;
      let distanceText = data.distance ? " (" + data.distance + ")" : "";
      let color = colorForTraffic(data.duration, data.duration_in_traffic);
      widget.find(".destiny-time-value").css("color", color).text(timeText);
      widget.find(".destiny-distance-value").text(distanceText);
    }

    function showLoading() {
      console.log("DISPLAY: Showing loading state for widget:", widgetId);
      setWidgetState("loading");
    }

    function showError() {
      console.log("DISPLAY: Showing error state for widget:", widgetId);
      setWidgetState("error");
    }

    function showResults(data) {
      console.log("DISPLAY: Showing results state for widget:", widgetId);
      updateWidgetResults(data);
      setWidgetState("results");
    }

    function colorForTraffic(normal, traffic) {
      // Convert "12 min" to minutes
      function toMinutes(str) {
        if (!str) return 0;
        var m = str.match(/(\d+)(?:\s*min)?/);
        return m ? parseInt(m[1], 10) : 0;
      }
      var n = toMinutes(normal);
      var t = toMinutes(traffic);
      if (n === 0 || t === 0) return "";
      var ratio = t / n;
      if (ratio <= 1.15) return "green"; // up to 15% longer
      if (ratio <= 1.4) return "orange"; // up to 40% longer
      return "red"; // much longer
    }

    function updateDirectionsLink(origin) {
      let url =
        "https://www.google.com/maps/dir/?api=1&destination=" +
        encodeURIComponent(destination) +
        "&origin=" +
        encodeURIComponent(origin);
      widget.find(".destiny-directions-link").attr("href", url);
    }

    function setSourceLabel(usedFallback) {
      let label = usedFallback ? fallbackSource : "Din position";
      widget.find(".destiny-source-label").text(label);
    }

    // Convert geolocation to promise - only called once globally
    function getLocationPromiseGlobal() {
      if (global.locationPromise) {
        console.log("Reusing existing location promise");
        return global.locationPromise;
      }

      console.log("Creating new location promise");
      global.locationPromise = new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
          // Fallback immediately if geolocation is not supported
          resolve({
            origin: fallbackSource,
            usedFallback: true,
          });
          return;
        }

        navigator.geolocation.getCurrentPosition(
          (position) => {
            const origin =
              position.coords.latitude + "," + position.coords.longitude;
            console.log("GPS location success:", origin);
            resolve({
              origin,
              usedFallback: false,
            });
          },
          (error) => {
            console.log("GPS location failed:", error.message);
            // Fallback to fallbackSource if geolocation fails
            resolve({
              origin: fallbackSource,
              usedFallback: true,
            });
          },
          {
            timeout: 10000,
            maximumAge: 300000,
            enableHighAccuracy: false,
          }
        );
      });

      return global.locationPromise;
    }

    // Global function to get destination info - only called once
    function getDestinationDataGlobal(origin, usedFallback) {
      if (global.dataPromise) {
        console.log(
          "AJAX REUSE: Reusing existing data promise for origin:",
          origin
        );
        return global.dataPromise;
      }

      console.log(
        "AJAX NEW: Making NEW AJAX request to:",
        destiny_destination_ajax.ajax_url,
        "with origin:",
        origin
      );
      console.log("AJAX NEW: Used fallback:", usedFallback);

      global.dataPromise = $.ajax({
        url: destiny_destination_ajax.ajax_url,
        type: "POST",
        data: {
          action: "get_destination_info",
          origin: origin,
          widget_id: widgetId,
          nonce: destiny_destination_ajax.nonce,
        },
      })
        .done(function (response) {
          console.log("AJAX response received:", response);
          if (response.status === "success") {
            global.cachedResponse = response;
            global.finalOrigin = origin;
            global.usedFallback = usedFallback;

            // Update all pending widgets
            global.pendingWidgets.forEach(function (widgetData) {
              widgetData.updateWidget(response);
            });
            global.pendingWidgets = [];
          } else {
            console.log("API returned error status");
            global.pendingWidgets.forEach(function (widgetData) {
              widgetData.showError();
            });
            global.pendingWidgets = [];
          }
        })
        .fail(function (xhr, status, error) {
          console.log("AJAX request failed:", error);
          global.pendingWidgets.forEach(function (widgetData) {
            widgetData.showError();
          });
          global.pendingWidgets = [];
        });

      return global.dataPromise;
    }

    // Initialize the global process only once
    function initializeGlobalProcess() {
      if (global.initialized) {
        console.log("Global process already initialized, skipping");
        return Promise.resolve();
      }

      console.log("Starting global initialization process");
      global.initialized = true;

      return getLocationPromiseGlobal().then(({ origin, usedFallback }) => {
        if (usedFallback) {
          console.log("FALLBACK PATH: Using fallback address:", origin);
        } else {
          console.log("GPS SUCCESS PATH: Got GPS coordinates:", origin);
        }
        console.log("Used fallback:", usedFallback);
        return getDestinationDataGlobal(origin, usedFallback);
      });
    }

    // Widget-specific initialization
    function initializeWidget() {
      console.log("WIDGET INIT: Initializing widget:", widgetId);

      // If we already have cached data, use it immediately without showing loading
      if (global.cachedResponse && global.finalOrigin !== null) {
        console.log(
          "WIDGET INIT: Using cached global data immediately for widget:",
          widgetId
        );
        updateDirectionsLink(global.finalOrigin);
        setSourceLabel(global.usedFallback);
        showResults(global.cachedResponse);
        return;
      }

      // Show loading state for new requests
      showLoading();

      console.log(
        "WIDGET INIT: No cached data, adding to pending widgets list"
      );
      // Add this widget to the pending list
      global.pendingWidgets.push({
        widgetId: widgetId,
        updateWidget: function (response) {
          console.log(
            "PENDING UPDATE: Updating widget from pending list:",
            widgetId
          );
          updateDirectionsLink(global.finalOrigin);
          setSourceLabel(global.usedFallback);
          showResults(response);
        },
        showError: function () {
          console.log(
            "PENDING ERROR: Showing error from pending list for widget:",
            widgetId
          );
          showError();
        },
      });

      // Start the global process
      initializeGlobalProcess();
    }

    // Initialize this widget instance
    initializeWidget();

    // Initialize Tippy.js tooltips for this widget
    if (typeof tippy !== "undefined") {
      tippy(widget.find(".destiny-destination-label")[0], {
        allowHTML: true,
        theme: "light-border",
        placement: "top",
        animation: "fade",
        duration: [200, 150],
        maxWidth: 250,
        delay: [0, 1000],
        interactive: true,
        hideOnClick: true,
      });
    }
  }
});
