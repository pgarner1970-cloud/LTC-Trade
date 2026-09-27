/*
  Improved registration validation + live username availability check + live email availability check.

  Fix in this version:
  - Do NOT overwrite the field message with "Checking availability..." during normal validation.
    Only show "Checking availability..." when an actual AJAX check is in-flight.
  - This prevents the UI getting stuck showing "Checking..." if the user tabs around and triggers
    local validators after the availability check has completed.
*/

$(document).ready(function () {
  var $form = $("#user_form");

  // Track username status
  var usernameTimer = null;
  var usernameIsAvailable = null; // null = unknown/checking
  var usernameLastChecked = "";
  var usernameInflight = false;

  // Track email status
  var emailTimer = null;
  var emailIsAvailable = null; // null = unknown/checking
  var emailLastChecked = "";
  var emailInflight = false;

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

  function normaliseUsername(u) {
    return String(u || "").trim();
  }

  function normaliseEmail(e) {
    return String(e || "").trim().toLowerCase();
  }

  function validateCompany() {
    var $i = $("#company");
    var v = String($i.val() || "").trim();
    if (v.length < 4) {
      setState($i, "error", "Company name must be at least 4 characters.");
      return false;
    }
    setState($i, "success", "");
    return true;
  }

  function validateTelephone() {
    var $i = $("#telephone");
    var v = String($i.val() || "").trim();
    var ok = /^(?:0|\+?44)(?:\d\s?){9,10}$/.test(v);
    if (!ok) {
      setState($i, "error", "Enter a valid UK telephone number (11 digits).");
      return false;
    }
    setState($i, "success", "");
    return true;
  }

  function validateEmailLocal() {
    var $i = $("#email");
    var v = normaliseEmail($i.val());
    var ok = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,})+$/.test(v);

    if (!ok) {
      setState($i, "error", "Enter a valid email address.");
      emailIsAvailable = null;
      emailLastChecked = "";
      return false;
    }

    // If we already know availability for this exact value, keep that state.
    if (emailIsAvailable === true && v === emailLastChecked) {
      setState($i, "success", "Email address is available.");
      return true;
    }
    if (emailIsAvailable === false && v === emailLastChecked) {
      setState($i, "error", "That email address is already registered.");
      return false;
    }

    // Otherwise, local rules are OK but availability is unknown.
    // Only show "checking" while an ajax request is in-flight.
    if (emailInflight) {
      setState($i, "neutral", "Checking availability...");
    } else {
      setState($i, "success", "");
    }
    return true;
  }

  function validateConfirmEmail() {
    var $i = $("#confemail");
    var v = normaliseEmail($i.val());
    var email = normaliseEmail($("#email").val());
    if (!v) {
      setState($i, "error", "Please confirm your email address.");
      return false;
    }
    if (v !== email) {
      setState($i, "error", "Email addresses do not match.");
      return false;
    }
    setState($i, "success", "");
    return true;
  }

  function passwordRules(pw) {
    var rules = [];
    if ((pw || "").length < 8) rules.push("at least 8 characters");
    if ((pw || "").search(/[a-z]/) < 0) rules.push("one lowercase letter");
    if ((pw || "").search(/[A-Z]/) < 0) rules.push("one uppercase letter");
    if ((pw || "").search(/[0-9]/) < 0) rules.push("one number");
    return rules;
  }

  function validatePassword() {
    var $i = $("#userpass");
    var v = String($i.val() || "");
    var rules = passwordRules(v);
    if (rules.length) {
      setState($i, "error", "Password must contain: " + rules.join(", ") + ".");
      return false;
    }
    setState($i, "success", "");
    return true;
  }

  function validateConfirmPassword() {
    var $i = $("#confpass");
    var v = String($i.val() || "");
    var pw = String($("#userpass").val() || "");
    if (!v) {
      setState($i, "error", "Please confirm your password.");
      return false;
    }
    if (v !== pw) {
      setState($i, "error", "Passwords do not match.");
      return false;
    }
    setState($i, "success", "");
    return true;
  }

  function validateUsernameLocal() {
    var $i = $("#username");
    var v = normaliseUsername($i.val());

    if (v.length < 8) {
      setState($i, "error", "Username must be at least 8 characters.");
      usernameIsAvailable = null;
      usernameLastChecked = "";
      return false;
    }
    if (!/^[a-zA-Z0-9._-]+$/.test(v)) {
      setState($i, "error", "Use only letters, numbers, dots, underscores, or hyphens.");
      usernameIsAvailable = null;
      usernameLastChecked = "";
      return false;
    }

    // If we already know availability for this exact value, keep that state.
    if (usernameIsAvailable === true && v === usernameLastChecked) {
      setState($i, "success", "Username is available.");
      return true;
    }
    if (usernameIsAvailable === false && v === usernameLastChecked) {
      setState($i, "error", "That username is already taken. Try another.");
      return false;
    }

    if (usernameInflight) {
      setState($i, "neutral", "Checking availability...");
    } else {
      setState($i, "success", "");
    }
    return true;
  }

  function checkUsernameAvailability() {
    var $i = $("#username");
    var v = normaliseUsername($i.val());

    if (!validateUsernameLocal()) return;

    // Avoid re-checking same value
    if (v && v === usernameLastChecked && usernameIsAvailable !== null) return;

    usernameInflight = true;
    usernameIsAvailable = null;
    usernameLastChecked = v;
    setState($i, "neutral", "Checking availability...");

    $.ajax({
      url: "functions/registerupd.php",
      method: "POST",
      data: { username: v },
      cache: false,
      timeout: 8000
    })
      .done(function (data) {
        usernameInflight = false;
        var count = parseInt(String(data).trim(), 10);
        if (isNaN(count)) count = 0;

        if (count > 0) {
          usernameIsAvailable = false;
          setState($i, "error", "That username is already taken. Try another.");
        } else {
          usernameIsAvailable = true;
          setState($i, "success", "Username is available.");
        }
      })
      .fail(function () {
        usernameInflight = false;
        usernameIsAvailable = null;
        setState($i, "error", "Could not check username availability. Please try again.");
      });
  }

  function checkEmailAvailability() {
    var $i = $("#email");
    var v = normaliseEmail($i.val());

    if (!validateEmailLocal()) return;

    // Avoid re-checking same value
    if (v && v === emailLastChecked && emailIsAvailable !== null) return;

    emailInflight = true;
    emailIsAvailable = null;
    emailLastChecked = v;
    setState($i, "neutral", "Checking availability...");

    $.ajax({
      url: "functions/registerupd.php",
      method: "POST",
      data: { email: v },
      cache: false,
      timeout: 8000
    })
      .done(function (data) {
        emailInflight = false;
        var count = parseInt(String(data).trim(), 10);
        if (isNaN(count)) count = 0;

        if (count > 0) {
          emailIsAvailable = false;
          setState($i, "error", "That email address is already registered.");
        } else {
          emailIsAvailable = true;
          setState($i, "success", "Email address is available.");
        }

        if ($("#confemail").val()) validateConfirmEmail();
      })
      .fail(function () {
        emailInflight = false;
        emailIsAvailable = null;
        setState($i, "error", "Could not check email availability. Please try again.");
      });
  }

  function debounceUsernameCheck() {
    if (usernameTimer) clearTimeout(usernameTimer);
    usernameTimer = setTimeout(checkUsernameAvailability, 400);
  }

  function debounceEmailCheck() {
    if (emailTimer) clearTimeout(emailTimer);
    emailTimer = setTimeout(checkEmailAvailability, 400);
  }

  function validateAll() {
    var ok = true;
    ok = validateCompany() && ok;
    ok = validateTelephone() && ok;

    ok = validateEmailLocal() && ok;
    ok = validateConfirmEmail() && ok;

    ok = validatePassword() && ok;
    ok = validateConfirmPassword() && ok;

    ok = validateUsernameLocal() && ok;

    // Block submit until both availability checks are confirmed true
    if (usernameIsAvailable !== true) ok = false;
    if (emailIsAvailable !== true) ok = false;

    return ok;
  }

  // --- Wire up interactive validation ---
  $("#company").on("input blur", validateCompany);
  $("#telephone").on("input blur", validateTelephone);

  $("#email").on("input", function () {
    validateEmailLocal();
    debounceEmailCheck();
    if ($("#confemail").val()) validateConfirmEmail();
  });
  $("#email").on("blur", function () {
    validateEmailLocal();
    checkEmailAvailability();
    if ($("#confemail").val()) validateConfirmEmail();
  });

  $("#confemail").on("input blur", validateConfirmEmail);

  $("#userpass").on("input blur", function () {
    validatePassword();
    if ($("#confpass").val()) validateConfirmPassword();
  });
  $("#confpass").on("input blur", validateConfirmPassword);

  $("#username").on("input", function () {
    validateUsernameLocal();
    debounceUsernameCheck();
  });
  $("#username").on("blur", function () {
    validateUsernameLocal();
    checkUsernameAvailability();
  });

  // On submit, run full validation. Only prevent submit when invalid.
  $form.on("submit", function (event) {
    var ok = validateAll();

    if (!ok) {
      event.preventDefault();

      var msg = "Please fix the highlighted fields before submitting.";
      if (usernameInflight || emailInflight) {
        msg = "Please wait for the availability checks to complete.";
      } else if (usernameIsAvailable === false) {
        msg = "Please choose a different username.";
      } else if (emailIsAvailable === false) {
        msg = "That email address is already registered. Try logging in instead.";
      }

      alert(msg);
      return false;
    }

    return true;
  });
});
