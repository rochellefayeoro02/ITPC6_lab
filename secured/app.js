(function () {
    "use strict";

    var MAX_LENGTH = 500;

    var messageForm   = document.getElementById("messageForm");
    var messageInput  = document.getElementById("messageInput");
    var messageOutput = document.getElementById("messageOutput");

    var domInput  = document.getElementById("domInput");
    var domButton = document.getElementById("domButton");
    var domOutput = document.getElementById("domOutput");

    var status = document.getElementById("status");

    function setStatus(text, ok) {
        status.textContent = text;
        status.className = ok ? "success" : "warning";
    }

    // Everything is written with textContent, so HTML in the input is shown
    // as plain text and never parsed or executed.
    function show(value, target) {
        if (value.trim() === "") {
            setStatus("Please type a message first.", false);
            return;
        }
        if (value.length > MAX_LENGTH) {
            setStatus("Message is too long (max " + MAX_LENGTH + " characters).", false);
            return;
        }

        target.textContent = value;

        if (/[<>]/.test(value)) {
            setStatus("Markup was detected and displayed as plain text. Nothing was executed.", true);
        } else {
            setStatus("Message displayed as plain text (" + value.length + " characters).", true);
        }
    }

    messageForm.addEventListener("submit", function (event) {
        event.preventDefault();
        show(messageInput.value, messageOutput);
    });

    domButton.addEventListener("click", function () {
        show(domInput.value, domOutput);
    });
})();