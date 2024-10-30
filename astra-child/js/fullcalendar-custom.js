document.addEventListener("DOMContentLoaded", function () {
  //   var calendarEl = document.getElementById("mec-gCalendar-wrap");
  //   var calendar = new FullCalendar.Calendar(calendarEl, {
  //     initialView: "dayGridMonth",
  //     headerToolbar: {
  //       left: "prev,next,prevYear,nextYear",
  //       center: "",
  //       right: "",
  //     },
  //     // Other FullCalendar options
  //   });
  //   calendar.render();
  //   // Function to update the current month display
  //   function updateCurrentMonthDisplay() {
  //     var currentDate = new Date();
  //     var monthName = currentDate.toLocaleString("default", { month: "long" });
  //     var year = currentDate.getFullYear();
  //     var prevButton = document.querySelector(".fc-prev-button");
  //     if (prevButton) {
  //       var monthDisplay = document.createElement("span");
  //       monthDisplay.className = "fc-month-display";
  //       monthDisplay.textContent = monthName + " " + year;
  //       prevButton.insertAdjacentElement("afterend", monthDisplay);
  //     }
  //   }
  //   // Update the display initially
  //   updateCurrentMonthDisplay();
  //   // Add a MutationObserver to watch for changes in the calendar
  //   var targetNode = document.querySelector(".fc-header-toolbar");
  //   if (targetNode) {
  //     var observer = new MutationObserver(function (mutations) {
  //       updateCurrentMonthDisplay();
  //     });
  //     observer.observe(targetNode, { childList: true, subtree: true });
  //   }
  //   // Inject custom buttons into the FullCalendar header toolbar
  //   var headerToolbar = document.querySelector(
  //     ".fc-header-toolbar .fc-toolbar-chunk:first-child"
  //   );
  //   if (headerToolbar) {
  //     var customButtonsHTML = `
  //             <div class="mec-custom-buttons">
  //                 <button id="prevMonthButton">Previous Month</button>
  //                 <span id="currentMonthDisplay"></span>
  //                 <button id="nextMonthButton">Next Month</button>
  //                 <button id="prevYearButton">Previous Year</button>
  //                 <span id="currentYearDisplay"></span>
  //                 <button id="nextYearButton">Next Year</button>
  //             </div>
  //         `;
  //     headerToolbar.insertAdjacentHTML("beforeend", customButtonsHTML);
  //   }
  //   // Function to update the current month display
  //   function updateCurrentMonthDisplay() {
  //     var currentDate = calendar.getDate();
  //     var monthName = currentDate.toLocaleString("default", { month: "long" });
  //     var monthDisplay = document.getElementById("currentMonthDisplay");
  //     if (monthDisplay) {
  //       monthDisplay.textContent = monthName;
  //     }
  //   }
  //   // Function to update the current year display
  //   function updateCurrentYearDisplay() {
  //     var currentDate = calendar.getDate();
  //     var year = currentDate.getFullYear();
  //     var yearDisplay = document.getElementById("currentYearDisplay");
  //     if (yearDisplay) {
  //       yearDisplay.textContent = year;
  //     }
  //   }
  //   // Update the display initially
  //   updateCurrentMonthDisplay();
  //   updateCurrentYearDisplay();
  //   // Add datesSet event listener to update the display when the view changes
  //   calendar.on("datesSet", function () {
  //     updateCurrentMonthDisplay();
  //     updateCurrentYearDisplay();
  //   });
  //   // Custom button click handlers
  //   document
  //     .getElementById("prevMonthButton")
  //     .addEventListener("click", function () {
  //       calendar.prev(); // Move to the previous month
  //     });
  //   document
  //     .getElementById("nextMonthButton")
  //     .addEventListener("click", function () {
  //       calendar.next(); // Move to the next month
  //     });
  //   document
  //     .getElementById("prevYearButton")
  //     .addEventListener("click", function () {
  //       var currentDate = calendar.getDate();
  //       calendar.gotoDate(
  //         new Date(currentDate.setFullYear(currentDate.getFullYear() - 1))
  //       ); // Move to the previous year
  //     });
  //   document
  //     .getElementById("nextYearButton")
  //     .addEventListener("click", function () {
  //       var currentDate = calendar.getDate();
  //       calendar.gotoDate(
  //         new Date(currentDate.setFullYear(currentDate.getFullYear() + 1))
  //       ); // Move to the next year
  //     });
});

// document.addEventListener("DOMContentLoaded", function () {
//   // Function to update the current month display
//   function updateCurrentMonthDisplay() {
//     var currentDate = new Date();
//     var monthName = currentDate.toLocaleString("default", { month: "long" });
//     var year = currentDate.getFullYear();

//     var prevButton = document.querySelector(".fc-prev-button");
//     if (prevButton) {
//       var monthDisplay = document.createElement("span");
//       monthDisplay.className = "fc-month-display";
//       monthDisplay.textContent = monthName + " " + year;
//       prevButton.insertAdjacentElement("afterend", monthDisplay);
//     }
//   }

//   // Update the display initially
//   updateCurrentMonthDisplay();

//   // Add a MutationObserver to watch for changes in the calendar
//   var targetNode = document.querySelector(".fc-header-toolbar");
//   if (targetNode) {
//     var observer = new MutationObserver(function (mutations) {
//       updateCurrentMonthDisplay();
//     });

//     observer.observe(targetNode, { childList: true, subtree: true });
//   }
// });

// ... rest of the existing commented-out code ...
