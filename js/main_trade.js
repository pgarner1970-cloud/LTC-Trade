$(document).ready(function () {
  $("#wrapper").toggleClass("toggled");
  $("#orderStatus").empty();

  // Cache loaded order lines so we don't re-fetch on every expand
  var linesCache = {}; // { [orderId]: Array }

  function escapeHtml(val) {
    return String(val === undefined || val === null ? "" : val)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function formatMoney(n) {
    var num = Number(n);
    if (!isFinite(num)) return escapeHtml(n);
    return num.toFixed(2);
  }

  function buildLinesTable(lines) {
    var html = "<table class='table table-sm mb-0'>";
    html += "<thead><tr style='font-weight:bold;background:#f2f2f2;'>";
    html += "<td style='width:180px;'>EAN</td>";
    html += "<td>TyreDesc</td>";
    html += "<td style='width:120px;'>Quantity</td>";
    html += "<td style='width:140px;'>Line Value</td>";
    html += "</tr></thead><tbody>";

    for (var i = 0; i < lines.length; i++) {
      // orderfetchsingle.php returns: [Supplier, EAN, TyreDesc, UnitSellPrice, Quantity, line_total]
      var ean = lines[i][1];
      var desc = lines[i][2];
      var qty = lines[i][4];
      var lineTotal = lines[i][5];

      html += "<tr>";
      html += "<td>" + escapeHtml(ean) + "</td>";
      html += "<td>" + escapeHtml(desc) + "</td>";
      html += "<td>" + escapeHtml(qty) + "</td>";
      html += "<td>" + formatMoney(lineTotal) + "</td>";
      html += "</tr>";
    }

    html += "</tbody></table>";
    return html;
  }

  function renderOrders(data) {
    $("#orderStatus").empty();

    var resultsHtml = "<h4>Your Orders</h4>";
    resultsHtml += "<table cellspacing='2' cellpadding='2' class='table table-striped w-75' id='yourOrdersTable'>";
    resultsHtml += "<thead><tr bgcolor='#cecece'>";
    resultsHtml += "<td style='width:40px;'></td>";
    resultsHtml += "<td>Order</td><td>Date</td><td>Placed by</td><td>Reference</td>";
    resultsHtml += "<td>Quantity</td><td>Total Price (excl.VAT)</td><td>Status</td>";
    resultsHtml += "</tr></thead><tbody>";

    if (!data || !data.length) {
      resultsHtml += "<tr><td colspan='8'><div class='alert alert-info mb-0'>You have no orders yet.</div></td></tr>";
      resultsHtml += "</tbody></table>";
      $("#orderStatus").html(resultsHtml);
      return;
    }

    for (var i = 0; i < data.length; i++) {
      var oid = data[i]["order_id"];
      var detailsRowId = "orderLines" + oid;

      resultsHtml += "<tr class='order-row' data-order-id='" + escapeHtml(oid) + "' data-target='#" + detailsRowId + "' style='cursor:pointer;'>";
      resultsHtml += "<td class='text-center'><span class='toggle-icon chevron' aria-hidden='true'>▶</span></td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_id"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_date"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_name"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_ref"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_quantity"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_value"]) + "</td>";
      resultsHtml += "<td>" + escapeHtml(data[i]["order_processed"]) + "</td>";
      resultsHtml += "</tr>";

      // hidden details row (line items)
      resultsHtml += "<tr id='" + detailsRowId + "' class='order-details' style='display:none;background:#fafafa;'>";
      resultsHtml += "<td></td>";
      resultsHtml += "<td colspan='7'><div class='p-2' data-order-id='" + escapeHtml(oid) + "'>Loading...</div></td>";
      resultsHtml += "</tr>";
    }

    resultsHtml += "</tbody></table>";
    $("#orderStatus").html(resultsHtml);
  }

  function loadLines(orderId, $detailsRow) {
    if (linesCache[orderId]) {
      $detailsRow.find("div[data-order-id]").html(buildLinesTable(linesCache[orderId]));
      return;
    }

    $detailsRow.find("div[data-order-id]").text("Loading...");

    $.ajax({
      url: "functions/orderfetchsingle.php",
      method: "POST",
      data: { id: orderId },
      dataType: "json"
    })
      .done(function (lines) {
        linesCache[orderId] = lines || [];
        if (!lines || !lines.length) {
          $detailsRow.find("div[data-order-id]").html(
            "<div class='alert alert-warning mb-0'>No line items found for this order.</div>"
          );
          return;
        }
        $detailsRow.find("div[data-order-id]").html(buildLinesTable(lines));
      })
      .fail(function () {
        $detailsRow.find("div[data-order-id]").html(
          "<div class='alert alert-danger mb-0'>Could not load line items. Please try again.</div>"
        );
      });
  }

  $.ajax({
    url: "functions/orderstatustrade.php",
    type: "get",
    dataType: "json"
  }).done(function (data) {
    renderOrders(data || []);
  });

  $(document).on("click", "#yourOrdersTable .order-row", function () {
    var $row = $(this);
    var orderId = $row.data("order-id");
    var targetSel = $row.data("target");
    var $detailsRow = $(targetSel);

    if (!$detailsRow.length) return;

    var isOpen = $detailsRow.is(":visible");
    $detailsRow.toggle(!isOpen);

    var $icon = $row.find(".toggle-icon.chevron");
    if ($icon.length) {
      $icon.toggleClass("open", !isOpen);
    }

    if (!isOpen) {
      loadLines(orderId, $detailsRow);
    }
  });
});
