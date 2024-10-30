document.addEventListener("DOMContentLoaded", function () {
  var calendarEl = document.getElementById("mec-gCalendar-wrap");
  var customNavigation;

  var calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    initialDate: "2024-09-01", // Example date
    editable: false,
    selectable: false,
    businessHours: false,
    height: "auto",
    direction: "ltr",
    locale: "en",
    headerToolbar: {
      left: "title", // Keep the title
      center: "",
      right: "",
    },
    events: {
      url: mec_calendar.rest_url,
      method: "GET",
      extraParams: {},
      failure: function () {
        alert("There was an error while fetching events!");
      },
    },
    eventSourceSuccess: function (content, xhr) {
      console.log("Events fetched successfully:", content);
      return content;
    },
    loading: function (bool) {
      document.getElementById("gCalendar-loading").style.display = bool
        ? "block"
        : "none";
    },
    datesSet: function (info) {
      updateDisplays();
    },
  });

  calendar.render();

  // Create and insert custom navigation
  customNavigation = document.createElement("div");
  customNavigation.className = "fc-custom-navigation";
  customNavigation.innerHTML = `
      <button class="fc-prev-button fc-button fc-button-primary" type="button">prev</button>
      <span class="fc-displayMonth-button fc-button fc-button-primary"></span>
      <button class="fc-next-button fc-button fc-button-primary" type="button">next</button>
      <button class="fc-prevYear-button fc-button fc-button-primary" type="button">prevYear</button>
      <span class="fc-displayYear-button fc-button fc-button-primary"></span>
      <button class="fc-nextYear-button fc-button fc-button-primary" type="button">nextYear</button>
    `;

  // Insert the custom navigation before the title
  var titleElement = calendarEl.querySelector(".fc-toolbar-title");
  if (titleElement && titleElement.parentNode) {
    titleElement.parentNode.insertBefore(customNavigation, titleElement);
  } else {
    calendarEl.insertBefore(customNavigation, calendarEl.firstChild);
  }

  // Get references to the month and year display containers
  var monthDisplayContainer = customNavigation.querySelector(
    ".fc-displayMonth-button"
  );
  var yearDisplayContainer = customNavigation.querySelector(
    ".fc-displayYear-button"
  );

  function updateDisplays() {
    var currentDate = calendar.getDate();
    var month = currentDate.toLocaleString("default", { month: "long" });
    var year = currentDate.getFullYear();

    monthDisplayContainer.innerText = month;
    yearDisplayContainer.innerText = year;
    console.log("Date updated to:", month, year);
  }

  // Initial update of the displays
  updateDisplays();

  // Add event listeners to navigation buttons
  customNavigation
    .querySelector(".fc-prev-button")
    .addEventListener("click", () => {
      calendar.prev();
      updateDisplays();
    });
  customNavigation
    .querySelector(".fc-next-button")
    .addEventListener("click", () => {
      calendar.next();
      updateDisplays();
    });
  customNavigation
    .querySelector(".fc-prevYear-button")
    .addEventListener("click", () => {
      calendar.prevYear();
      updateDisplays();
    });
  customNavigation
    .querySelector(".fc-nextYear-button")
    .addEventListener("click", () => {
      calendar.nextYear();
      updateDisplays();
    });
});
