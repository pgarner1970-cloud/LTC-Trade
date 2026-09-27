/*
  Dynamic validation for Reset Password form (userpassupd.php)

  Improvements:
  - Live validation as user types (no more blocking alerts for common issues)
  - Inline feedback under fields (Bootstrap has-error/has-success + help text)
  - Submit button disabled until form is valid
  - Keeps existing AJAX submit to functions/userpassupd.php
*/

$(document).ready(function () {
  var $form = $("#user_form");
  if (!$form.length) return;

  var $otp = $("#otp");
  var $newpass = $("#newpass");
  var $confpass = $("#confpass");
  var $submit = $form.find("button[type='submit'], input[type='submit']").first();

  function getOrCreateHelp($input) {
    var $group = $input.closest(".form-group");
    var $help = $group.find(".help-block.validation-msg");
    if ($help.length === 0) {
      $help = $("<span class='help-block validation-msg'></span>");
      $group.append($help);
    }
    return $help;
  }

  function setState($input, state, message) {
    // state: 'error' | 'success' | 'neutral'
    var $group = $input.closest(".form-group");
    var $help = getOrCreateHelp($input);

    $group.removeClass("has-error has-success");
    if (state === "error") $group.addClass("has-error");
    if (state === "success") $group.addClass("has-success");

    if (message) {
      $help.text(message).show();
    } else {
      $help.text("").hide();
    }
  }

  function passwordRules(pw) {
    var rules = [];
    if ((pw || "").length < 8) rules.push("at least 8 characters");
    if ((pw || "").search(/[a-z]/) < 0) rules.push("one lowercase letter");
    if ((pw || "").search(/[A-Z]/) < 0) rules.push("one uppercase letter");
    if ((pw || "").search(/[0-9]/) < 0) rules.push("one number");
    return rules;
  }

  function validateOtp() {
    if (!$otp.length) return true; // if not on page for some reason
    var v = String($otp.val() || "").trim();
    if (!v) {
      setState($otp, "error", "Passcode is required.");
      return false;
    }
    // Your passcodes appear hex-like (e.g. c606f64) but keep it permissive.
    if (v.length < 4) {
      setState($otp, "error", "Passcode looks too short.");
      return false;
    }
    setState($otp, "success", "");
    return true;
  }

  function validateNewPass() {
    var pw = String($newpass.val() || "");
    if (!pw) {
      setState($newpass, "error", "New password is required.");
      return false;
    }
    var rules = passwordRules(pw);
    if (rules.length) {
      setState($newpass, "error", "Password must contain: " + rules.join(", ") + ".");
      return false;
    }
    setState($newpass, "success", "Looks good.");
    return true;
  }

  function validateConfirmPass() {
    var pw = String($newpass.val() || "");
    var cpw = String($confpass.val() || "");
    if (!cpw) {
      setState($confpass, "error", "Please confirm your password.");
      return false;
    }
    if (pw !== cpw) {
      setState($confpass, "error", "Passwords do not match.");
      return false;
    }
    setState($confpass, "success", "Passwords match.");
    return true;
  }

  function setSubmitEnabled(enabled) {
    if (!$submit.length) return;
    $submit.prop("disabled", !enabled);
    $submit.css("opacity", enabled ? "1" : "0.6");
    $submit.css("cursor", enabled ? "pointer" : "not-allowed");
  }

  function validateAll() {
    var ok = true;
    ok = validateOtp() && ok;
    ok = validateNewPass() && ok;
    ok = validateConfirmPass() && ok;
    setSubmitEnabled(ok);
    return ok;
  }

  // Live validation
  if ($otp.length) $otp.on("input blur", validateAll);
  $newpass.on("input blur", function () {
    validateNewPass();
    // keep confirm in sync
    if ($confpass.val()) validateConfirmPass();
    setSubmitEnabled(validateOtp() && validateNewPass() && validateConfirmPass());
  });
  $confpass.on("input blur", function () {
    validateConfirmPass();
    setSubmitEnabled(validateOtp() && validateNewPass() && validateConfirmPass());
  });

  // Initial state
  validateAll();

  // AJAX submit (existing behaviour), but only if valid
  $(document).off("submit", "#user_form"); // remove previous handler if loaded twice
  $(document).on("submit", "#user_form", function (event) {
    event.preventDefault();

    if (!validateAll()) {
      return false;
    }

    var formData = new FormData(this);

    $.ajax({
      url: "functions/userpassupd.php",
      method: "POST",
      data: formData,
      contentType: false,
      cache: false,
      processData: false,
      dataType: "json"
    })
      .done(function (data) {
        if (data && data.success) {
          // Friendly inline success message
          var $box = $("#resetSuccess");
          if (!$box.length) {
            $box = $("<div id='resetSuccess' class='alert alert-success' style='margin-top:10px;'></div>");
            $form.prepend($box);
          }
          $box.text("Password changed successfully. Redirecting to your orders...");
          setTimeout(function () {
            window.location.href = "index.php";
          }, 700);
        } else {
          var $box2 = $("#resetError");
          if (!$box2.length) {
            $box2 = $("<div id='resetError' class='alert alert-danger' style='margin-top:10px;'></div>");
            $form.prepend($box2);
          }
          $box2.text("Password update failed. Please check your passcode and try again.");
        }
      })
      .fail(function () {
        var $box3 = $("#resetError");
        if (!$box3.length) {
          $box3 = $("<div id='resetError' class='alert alert-danger' style='margin-top:10px;'></div>");
          $form.prepend($box3);
        }
        $box3.text("Could not update password right now. Please try again.");
      });

    return false;
  });
});
