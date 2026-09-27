$(document).ready(function () {
  $("#wrapper").toggleClass("toggled");

  // -----------------------------
  // Persist filter selections
  // -----------------------------
  var STORAGE_KEY = "ltc_trade_search_filters_v1";

  // Manufacturer -> banding lookup used for "band:" filtering
  var manufacturerBandingMap = {};

  function getSavedFilters() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}");
    } catch (e) {
      return {};
    }
  }

  function saveFilters() {
    var filters = {
      tyresize: $("#tyresize").val() || "",
      width: $("#width").val() || "",
      profile: $("#profile").val() || "",
      rim: $("#rim").val() || "",
      speed: $("#speed").val() || "*",
      manufacturer: $("#manufacturer").val() || "*",
      fuel: $("#fuel").val() || "ALL",
      wetgrip: $("#wetgrip").val() || "ALL"
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(filters));
  }

  // Robust option match (handles whitespace in option values)
  function findOptionValueByTrimmedMatch($select, target) {
    var wanted = String(target).trim();
    var found = null;

    $select.find("option").each(function () {
      var v = String($(this).val()).trim();
      if (v === wanted) {
        found = $(this).val(); // keep original value exactly as stored
        return false; // break
      }
    });

    return found;
  }

  function applySelectValue($select, value) {
    if (value === undefined || value === null || value === "") return;

    // Try exact first
    if ($select.find("option[value='" + String(value).replace(/'/g, "\\'") + "']").length > 0) {
      $select.val(value);
      return;
    }

    // Fallback: trimmed match
    var match = findOptionValueByTrimmedMatch($select, value);
    if (match !== null) {
      $select.val(match);
    }
  }

  // -----------------------------
  // Tyre size quick input parsing
  // Examples: 1656515, 2255519 (digits only)
  // -----------------------------
  function parseTyreSize(raw) {
    if (!raw) return null;
    var s = String(raw).replace(/\s+/g, "").replace(/[^\d]/g, "");
    if (s.length !== 7) return null; // expecting 3+2+2 digits

    var width = s.substr(0, 3);
    var profile = s.substr(3, 2);
    var rim = s.substr(5, 2);

    return { width: width, profile: profile, rim: rim };
  }

  function applyTyreSizeToSelectsFromInput() {
    var parsed = parseTyreSize($("#tyresize").val());
    if (!parsed) return false;

    var $w = $("#width");
    var $p = $("#profile");
    var $r = $("#rim");

    var wVal = findOptionValueByTrimmedMatch($w, parsed.width);
    var pVal = findOptionValueByTrimmedMatch($p, parsed.profile);
    var rVal = findOptionValueByTrimmedMatch($r, parsed.rim);

    var changed = false;

    if (wVal !== null) {
      $w.val(wVal).trigger("change");
      changed = true;
    }
    if (pVal !== null) {
      $p.val(pVal).trigger("change");
      changed = true;
    }
    if (rVal !== null) {
      $r.val(rVal).trigger("change");
      changed = true;
    }

    if (changed) saveFilters();
    return changed;
  }

  function toggleClearBrandButton() {
    var v = $("#manufacturer").val();
    var isAll = (v === "*" || v === "** ALL **" || v === null || v === undefined);
    $("#clear-brand").toggle(!isAll);
  }

  // Restore from storage. Call after dropdowns are populated.
  function restoreFilters() {
    var saved = getSavedFilters();

    // restore typed box
    if (saved.tyresize !== undefined && saved.tyresize !== null) {
      $("#tyresize").val(saved.tyresize);
    }

    // If tyresize already contains a full 7-digit size, let that drive the selects
    var raw = $("#tyresize").val();
    var digits = String(raw || "").replace(/\s+/g, "").replace(/[^\d]/g, "");
    if (digits.length === 7) {
      applyTyreSizeToSelectsFromInput();
      // still restore other selects
      applySelectValue($("#speed"), saved.speed);
      applySelectValue($("#manufacturer"), saved.manufacturer);
      applySelectValue($("#fuel"), saved.fuel);
      applySelectValue($("#wetgrip"), saved.wetgrip);
      toggleClearBrandButton();
      return;
    }

    // Otherwise restore selects normally
    applySelectValue($("#width"), saved.width);
    applySelectValue($("#profile"), saved.profile);
    applySelectValue($("#rim"), saved.rim);
    applySelectValue($("#speed"), saved.speed);
    applySelectValue($("#manufacturer"), saved.manufacturer);
    applySelectValue($("#fuel"), saved.fuel);
    applySelectValue($("#wetgrip"), saved.wetgrip);

    toggleClearBrandButton();
  }

  // Save whenever a filter changes
  $(document).on("change", "#tyresize,#width,#profile,#rim,#speed,#manufacturer,#fuel,#wetgrip", function () {
    saveFilters();
  });

  // When user types a full size, update selects immediately
  $(document).on("input", "#tyresize", function () {
    applyTyreSizeToSelectsFromInput();
  });

  // Optional: pressing Enter triggers search
  $(document).on("keypress", "#tyresize", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $("#search").submit();
    }
  });

  // Quick clear Brand filter (button next to Brand select)
  $(document).on("click", "#clear-brand", function () {
    $("#manufacturer").val("*").trigger("change");
    saveFilters();
    toggleClearBrandButton();

    if ($("#resultsDiv").children().length) {
      $("#search").submit();
    }
  });

  $(document).on("change", "#manufacturer", function () {
    toggleClearBrandButton();
  });

  // -----------------------------
  // Populate dropdowns (AJAX)
  // After each list loads, restore saved selection AND apply typed size
  // -----------------------------
  $.ajax({
    url: "functions/list_width.php",
    type: "post",
    success: function (response) {
      var len = response.length;
      $("#width").empty();
      for (var i = 0; i < len; i++) {
        var widthDesc = response[i]["widthDesc"];
        $("#width").append("<option value='" + widthDesc + "'>" + widthDesc + "</option>");
      }
      restoreFilters();
      applyTyreSizeToSelectsFromInput();
    }
  });

  $.ajax({
    url: "functions/list_profile.php",
    type: "post",
    success: function (response) {
      var len = response.length;
      $("#profile").empty();
      for (var i = 0; i < len; i++) {
        var profileDesc = response[i]["profileDesc"];
        $("#profile").append("<option value='" + profileDesc + "'>" + profileDesc + "</option>");
      }
      restoreFilters();
      applyTyreSizeToSelectsFromInput();
    }
  });

  $.ajax({
    url: "functions/list_rim.php",
    type: "post",
    success: function (response) {
      var len = response.length;
      $("#rim").empty();
      for (var i = 0; i < len; i++) {
        var rimDesc = response[i]["rimDesc"];
        $("#rim").append("<option value='" + rimDesc + "'>" + rimDesc + "</option>");
      }
      restoreFilters();
      applyTyreSizeToSelectsFromInput();
    }
  });

  $.ajax({
    url: "functions/list_speed.php",
    type: "post",
    success: function (response) {
      var len = response.length;
      $("#speed").empty();
      $("#speed").append("<option value='*'>ALL</option>");
      for (var i = 0; i < len; i++) {
        var speedDesc = response[i]["speedDesc"];
        $("#speed").append("<option value='" + speedDesc + "'>" + speedDesc + "</option>");
      }
      restoreFilters();
    }
  });

  // Manufacturers: now include banding options at top and build a map for filtering
  $.ajax({
    url: "functions/list_manufacturers.php",
    type: "post",
    success: function (response) {
      var len = response.length;

      $("#manufacturer").empty();

      // Top items
      $("#manufacturer").append("<option value='*'>** ALL **</option>");
      $("#manufacturer").append("<option value='band:Budget'>Budget</option>");
      $("#manufacturer").append("<option value='band:Mid-range'>Mid-range</option>");
      $("#manufacturer").append("<option value='band:Premium'>Premium</option>");
      $("#manufacturer").append("<option disabled>──────────</option>");

      manufacturerBandingMap = {};

      for (var i = 0; i < len; i++) {
        var m = response[i]["Manufacturer"];
        var b = response[i]["banding"] || "Budget";

        manufacturerBandingMap[String(m || "").toUpperCase()] = b;

        $("#manufacturer").append("<option value='" + m + "'>" + m + "</option>");
      }

      restoreFilters();
    }
  });

  // fuel/wetgrip are hard-coded in HTML, restore once at start too
  restoreFilters();
  toggleClearBrandButton();

  // -----------------------------
  // Search submit
  // -----------------------------
  $("#search").submit(function (event) {
    event.preventDefault();

    // Save filters right before searching
    saveFilters();

    $("#resultsDiv").empty();

    var formData = {
      t: "enquiry",
      w: $("#width option:selected").val(),
      p: $("#profile option:selected").val(),
      r: $("#rim option:selected").val(),
      s: $("#speed option:selected").val(),
      f: $("#fuel option:selected").val(),
      wg: $("#wetgrip option:selected").val()
    };

    console.log(formData);

    $.ajax({
      url: "https://tyres4sale.com/_api/apiSearch.php",
      type: "get",
      data: formData,
      dataType: "json"
    })
      .done(function (data) {
        console.log(data);

        var len = data.length;
        var rowsAdded = 0;

        $("#resultsDiv").empty();

        var resultsHtml = "<table cellspacing=2 cellpadding=2 class='table table-striped'>";
        resultsHtml +=
          "<thead><tr bgcolor=#cecece><td>EAN</td><td>Manufacturer</td><td>Description</td><td>Class</td><td>Fuel</td><td>Wet</td><td>Noise</td><td>Trade (exc VAT)</td><td></td></tr></thead>";

        var selectedBrand = $("#manufacturer").val();

        for (var i = 0; i < len; i++) {
          // Brand filter handling:
          // * = no filter
          // band:Budget/Mid-range/Premium = band filter
          // otherwise = exact manufacturer match
          if (selectedBrand !== "*") {
            if (String(selectedBrand).indexOf("band:") === 0) {
              var selectedBand = String(selectedBrand).split("band:")[1]; // Budget/Mid-range/Premium
              var mName = String(data[i]["Manufacturer"] || "").toUpperCase();
              var mBand = manufacturerBandingMap[mName] || "Budget";

              if (mBand !== selectedBand) continue;
            } else {
              if (data[i]["Manufacturer"] != selectedBrand) continue;
            }
          }

          rowsAdded++;

          resultsHtml += '<tr><td width="12%">' + data[i]["EAN"] + "</td>";
          resultsHtml += '<td width="15%">' + data[i]["Manufacturer"] + "</td>";
          resultsHtml += '<td width="35%">' + data[i]["TyreDesc"] + "</td>";
          resultsHtml += '<td width="5%">' + data[i]["LTCClass"] + "</td>";
          resultsHtml += '<td width="5%">' + data[i]["RollingRes"] + "</td>";
          resultsHtml += '<td width="5%">' + data[i]["WetGrip"] + "</td>";
          resultsHtml += '<td width="5%">' + data[i]["NoisePerf"] + "</td>";
          resultsHtml += '<td width="5%">' + data[i]["UnitTrade"] + "</td>";
          resultsHtml +=
            '<td width="5%"><button type="button" name="buy" id="' +
            data[i]["EAN"] +
            "~" +
            data[i]["Supplier"] +
            '" class="btn btn-info btn-sm buy">Buy</button></td></tr>';
        }

        resultsHtml += "</table>";

        // No results message
        if (rowsAdded === 0) {
          $("#resultsDiv").html(
            "<div class='alert alert-warning mt-3'>" +
              "<strong>No results found.</strong><br>" +
              "Try adjusting the tyre size or filters." +
              "</div>"
          );
        } else {
          $("#resultsDiv").html(resultsHtml);
        }
      })
      .fail(function () {
        $("#resultsDiv").html(
          "<div class='alert alert-danger mt-3'>" +
            "<strong>Search failed.</strong><br>" +
            "Please try again." +
            "</div>"
        );
      });
  });

  // -----------------------------
  // Buy button
  // -----------------------------
  $(document).on("click", ".buy", function () {
    var id = $(this).attr("id");
    $.ajax({
      url: "functions/addbasket.php",
      method: "POST",
      data: { id: id },
      success: function (data) {
        toastr.success(data);
        dataTable.ajax.reload();
      }
    });
  });

  // Optional reset:
  // localStorage.removeItem("ltc_trade_search_filters_v1");
});
