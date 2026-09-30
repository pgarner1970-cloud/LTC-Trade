$(document).ready(function () {
  function escapeHtml(value) {
    return $("<div>").text(value == null ? "" : String(value)).html();
  }

  function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, "&#96;");
  }

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
      wetgrip: $("#wetgrip").val() || "ALL",
      season: $("#season-filter").val() || "all",
      runflat: $("#runflat-filter").val() || "any",
      availability: $("#availability-filter").val() || "all",
      sort: $("#sort-results").val() || "price"
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

    ["season", "runflat", "availability", "sort"].forEach(function (key) {
      var sel = {season:"#season-filter",runflat:"#runflat-filter",availability:"#availability-filter",sort:"#sort-results"}[key];
      if (saved[key]) $(sel).val(saved[key]);
    });
    toggleClearBrandButton();
  }

  // Save whenever a filter changes
  $(document).on("change", "#tyresize,#width,#profile,#rim,#speed,#manufacturer,#fuel,#wetgrip,#season-filter,#runflat-filter,#availability-filter,#sort-results", function () {
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

  $(document).on("change", "#season-filter,#runflat-filter,#availability-filter,#sort-results", function () {
    saveFilters();
    if ($("#resultsDiv").children().length) $("#search").trigger("submit");
  });
  $("#reset-extra-filters").on("click", function () {
    $("#season-filter").val("all"); $("#runflat-filter").val("any");
    $("#availability-filter").val("all"); $("#sort-results").val("price");
    saveFilters();
    if ($("#resultsDiv").children().length) $("#search").trigger("submit");
  });

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
        var selectedBrand = $("#manufacturer").val();
        var products = [];
        var seen = {};

        // Preserve the existing public search/filter behaviour, but keep one product row per EAN.
        for (var i = 0; i < data.length; i++) {
          if (selectedBrand !== "*") {
            if (String(selectedBrand).indexOf("band:") === 0) {
              var selectedBand = String(selectedBrand).split("band:")[1];
              var mName = String(data[i]["Manufacturer"] || "").toUpperCase();
              var mBand = manufacturerBandingMap[mName] || "Budget";
              if (mBand !== selectedBand) continue;
            } else if (data[i]["Manufacturer"] != selectedBrand) {
              continue;
            }
          }

          var ean = String(data[i]["EAN"] || "");
          if (!ean || seen[ean]) continue;
          seen[ean] = true;
          products.push(data[i]);
        }

        var season = $("#season-filter").val();
        var runflat = $("#runflat-filter").val();
        products = products.filter(function (p) {
          var desc = String(p.TyreDesc || "").toUpperCase();
          var cls = String(p.LTCClass || "").toUpperCase();
          var text = desc + " " + cls;
          var knownWinter = /WINTER|3PMSF/.test(text);
          var knownAllSeason = /ALL[ -]?SEASON|4[ -]?SEASON|ALL[ -]?WEATHER/.test(text);
          if (season === "winter" && !knownWinter) return false;
          if (season === "allseason" && !knownAllSeason) return false;
          // Summer is only confirmed where supplier descriptions explicitly identify it.
          if (season === "summer" && !/SUMMER/.test(text)) return false;
          var positiveRunflat = /RUN[ -]?FLAT|\\bRFT\\b|\\bROF\\b/.test(text);
          if (runflat === "yes" && !positiveRunflat) return false;
          // Avoid asserting unknown products are non-runflat.
          if (runflat === "no" && !/NON[ -]?RUNFLAT|NOT RUNFLAT/.test(text)) return false;
          return true;
        });

        if (!products.length) {
          $("#resultsDiv").html(
            "<div class='alert alert-warning mt-3'><strong>No results found.</strong><br>Try adjusting the tyre size or filters.</div>"
          );
          return;
        }

        $.ajax({
          url: "functions/stock_multi_search.php",
          type: "post",
          dataType: "json",
          data: { eans: products.map(function (p) { return p.EAN; }) }
        }).done(function (offers) {
          var byEan = {};
          offers.forEach(function (offer) {
            var key = String(offer.EAN);
            if (!byEan[key]) byEan[key] = [];
            byEan[key].push(offer);
          });

          var todayParts = new Intl.DateTimeFormat("en-GB", {
            timeZone:"Europe/London", year:"numeric",month:"2-digit",day:"2-digit"
          }).formatToParts(new Date());
          var parts = {};
          todayParts.forEach(function (part) { parts[part.type] = part.value; });
          var today = parts.year + "-" + parts.month + "-" + parts.day;
          if ($("#availability-filter").val() === "today") {
            Object.keys(byEan).forEach(function (ean) {
              byEan[ean] = byEan[ean].filter(function (o) { return o.DeliveryDate === today; });
            });
          }
          products = products.filter(function (p) { return (byEan[String(p.EAN)] || []).length > 0; });
          var sort = $("#sort-results").val();
          function minPrice(p) {
            return Math.min.apply(null, byEan[String(p.EAN)].map(function (o) { return Number(o.UnitTrade); }));
          }
          function earliestDate(p) {
            return byEan[String(p.EAN)].map(function (o) { return o.DeliveryDate; }).sort()[0];
          }
          products.sort(function (a,b) {
            if (sort === "brand") return String(a.Manufacturer).localeCompare(String(b.Manufacturer));
            if (sort === "delivery") return earliestDate(a).localeCompare(earliestDate(b)) || minPrice(a)-minPrice(b);
            return sort === "price-desc" ? minPrice(b)-minPrice(a) : minPrice(a)-minPrice(b);
          });
          var html = "<div class='table-responsive mt-3 multi-stock-desktop'><table class='table table-sm align-middle multi-stock-table'>";
          var mobileHtml = "<div class='stock-mobile-list'>";
          html += "<thead><tr><th>Manufacturer</th><th>Description</th><th>Class</th><th>Fuel</th><th>Wet</th><th>Noise</th><th>Trade (exc VAT)</th><th>Delivery</th><th></th></tr></thead><tbody>";
          var rendered = 0;

          products.forEach(function (product, productIndex) {
            var list = byEan[String(product.EAN)] || [];
            if (!list.length) return;

            list.sort(function (a, b) {
              return parseFloat(a.UnitTrade) - parseFloat(b.UnitTrade);
            });

            var earliest = list.reduce(function (min, o) {
              return (!min || o.DeliveryDate < min) ? o.DeliveryDate : min;
            }, null);

            var rowspan = list.length;
            var groupClass = (rendered % 2 === 0) ? "stock-group-even" : "stock-group-odd";

            list.forEach(function (offer, offerIndex) {
              html += "<tr class='" + groupClass + (offerIndex > 0 ? " stock-offer-extra" : "") + "'>";

              if (offerIndex === 0) {
                html += "<td rowspan='" + rowspan + "' class='tyre-manufacturer'>" + escapeHtml(product.Manufacturer) + "</td>";
                html += "<td rowspan='" + rowspan + "' class='tyre-description'>" + escapeHtml(product.TyreDesc) + "</td>";
                html += "<td rowspan='" + rowspan + "'>" + escapeHtml(product.LTCClass) + "</td>";
                html += "<td rowspan='" + rowspan + "'>" + escapeHtml(product.RollingRes) + "</td>";
                html += "<td rowspan='" + rowspan + "'>" + escapeHtml(product.WetGrip) + "</td>";
                html += "<td rowspan='" + rowspan + "'>" + escapeHtml(product.NoisePerf) + "</td>";
              }

              html += "<td class='trade-price'>£" + escapeHtml(offer.UnitTrade) + "</td>";
              html += "<td class='delivery-cell'><span class='delivery-label" + (/^(today|tomorrow)$/i.test(String(offer.DeliveryLabel || "").trim()) ? "" : " delivery-later") + "'>" + escapeHtml(offer.DeliveryLabel) + "</span>";
              if (offer.DeliveryDate === earliest && list.some(function (x) { return x.DeliveryDate !== earliest; })) {
                html += " <span class='badge text-bg-success'>Earliest</span>";
              }
              html += "</td>";
              html += "<td><span class='offer-actions'><span class='qty-stepper'><button type='button' class='qty-minus' aria-label='Decrease quantity'>−</button><input class='offer-qty' type='number' min='1' max='99' value='2' aria-label='Quantity'><button type='button' class='qty-plus' aria-label='Increase quantity'>+</button></span><button type='button' data-offer='" + escapeAttr(product.EAN + "~" + offer.Supplier) + "' class='btn btn-info btn-sm buy'>Add</button></span></td>";
              html += "</tr>";
            });
            mobileHtml += "<article class='stock-mobile-card'>";
            mobileHtml += "<div class='d-flex justify-content-between align-items-center gap-2'><span class='stock-card-manufacturer'>" + escapeHtml(product.Manufacturer) + "</span><span class='badge text-bg-light'>" + escapeHtml(product.LTCClass) + "</span></div>";
            mobileHtml += "<div class='stock-card-description'>" + escapeHtml(product.TyreDesc) + "</div>";
            mobileHtml += "<div class='stock-card-labels'><span>Fuel: " + escapeHtml(product.RollingRes) + "</span><span>Wet: " + escapeHtml(product.WetGrip) + "</span><span>Noise: " + escapeHtml(product.NoisePerf) + " dB</span></div>";
            list.forEach(function (offer) {
              var isEarliest = offer.DeliveryDate === earliest && list.some(function (x) { return x.DeliveryDate !== earliest; });
              mobileHtml += "<div class='stock-card-offer'><div><div class='stock-card-price'>£" + escapeHtml(offer.UnitTrade) + " <small class='text-muted fw-normal'>exc VAT</small></div>";
              mobileHtml += "<div class='stock-card-delivery" + (/^(today|tomorrow)$/i.test(String(offer.DeliveryLabel || "").trim()) ? "" : " delivery-later") + "'>Delivery: " + escapeHtml(offer.DeliveryLabel);
              if (isEarliest) mobileHtml += " <span class='badge text-bg-success'>Earliest</span>";
              mobileHtml += "</div></div><span class='offer-actions'><span class='qty-stepper'><button type='button' class='qty-minus' aria-label='Decrease quantity'>−</button><input class='offer-qty' type='number' min='1' max='99' value='2' aria-label='Quantity'><button type='button' class='qty-plus' aria-label='Increase quantity'>+</button></span><button type='button' data-offer='" + escapeAttr(product.EAN + "~" + offer.Supplier) + "' class='btn btn-info btn-sm buy'>Add</button></span></div>";
            });
            mobileHtml += "</article>";
            rendered++;
          });

          html += "</tbody></table></div>";
          mobileHtml += "</div>";

          if (!rendered) {
            html = "<div class='alert alert-warning mt-3'><strong>No Trade stock is currently available for these tyres.</strong></div>";
          }
          $("#resultsDiv").html(rendered ? html + mobileHtml : html);
        }).fail(function (xhr) {
          var message = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : "Unable to load supplier offers.";
          $("#resultsDiv").html("<div class='alert alert-danger mt-3'>" + escapeHtml(message) + "</div>");
        });
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

  // Quantity stepper works for both dynamically rendered desktop and mobile offers.
  $(document).on("click", ".qty-minus, .qty-plus", function () {
    var $input = $(this).closest(".qty-stepper").find(".offer-qty");
    var current = Number.parseInt($input.val(), 10);
    if (!Number.isFinite(current)) current = 2;
    var delta = $(this).hasClass("qty-plus") ? 1 : -1;
    $input.val(Math.max(1, Math.min(99, current + delta)));
  });

  $(document).on("change blur", ".offer-qty", function () {
    var quantity = Number.parseInt($(this).val(), 10);
    if (!Number.isFinite(quantity)) quantity = 1;
    $(this).val(Math.max(1, Math.min(99, quantity)));
  });

  // -----------------------------
  // Buy button
  // -----------------------------
  $(document).on("click", ".buy", function () {
    var $button = $(this);
    var id = $button.attr("data-offer");
    var qty = Number($button.closest(".offer-actions").find(".offer-qty").val());
    if (!Number.isInteger(qty) || qty < 1 || qty > 99) {
      toastr.error("Choose a quantity between 1 and 99.");
      return;
    }
    $button.prop("disabled", true);
    $.ajax({
      url: "functions/addbasket.php",
      method: "POST",
      data: { id: id, quantity: qty },
      success: function (data) {
        if (String(data).indexOf("Error:") === 0) toastr.error(data);
        else toastr.success(data);
      },
      error: function () { toastr.error("Unable to update basket."); },
      complete: function () { $button.prop("disabled", false); }
    });
  });

  // Optional reset:
  // localStorage.removeItem("ltc_trade_search_filters_v1");
});
