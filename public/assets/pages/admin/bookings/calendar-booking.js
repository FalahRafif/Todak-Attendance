(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("booking_calendar_filter_form");
        var calendarElement = document.getElementById("booking_calendar");
        if (!form || !calendarElement) {
            return;
        }

        var eventsUrl = String(form.getAttribute("data-events-url") || "").trim();
        if (eventsUrl === "") {
            return;
        }

        var statusSelect = document.getElementById("calendar_status_filter");
        var dateStartInput = document.getElementById("calendar_date_start");
        var dateEndInput = document.getElementById("calendar_date_end");
        var applyButton = document.getElementById("booking_calendar_apply_filter");
        var resetButton = document.getElementById("booking_calendar_reset_filter");
        var activeFilterBadge = document.getElementById("booking_calendar_active_filter");
        var statusPills = Array.from(document.querySelectorAll(".booking-calendar-status-pill"));

        function getStatusValue() {
            if (!statusSelect) {
                return "";
            }

            return String(statusSelect.value || "").trim();
        }

        function getStatusLabel() {
            if (!statusSelect || !statusSelect.options || statusSelect.selectedIndex < 0) {
                return "Semua Status";
            }

            var option = statusSelect.options[statusSelect.selectedIndex];
            if (!option) {
                return "Semua Status";
            }

            var rawLabel = String(option.textContent || "").trim();
            if (rawLabel === "") {
                return "Semua Status";
            }

            return rawLabel;
        }

        function syncStatusPills() {
            var currentStatus = getStatusValue().toUpperCase();
            statusPills.forEach(function (pill) {
                var code = String(pill.getAttribute("data-status-code") || "").toUpperCase();
                var isActive = code === currentStatus;
                if (code === "" && currentStatus === "") {
                    isActive = true;
                }

                pill.classList.toggle("is-active", isActive);
            });
        }

        function updateActiveFilterBadge() {
            if (!activeFilterBadge) {
                return;
            }

            var statusLabel = getStatusLabel();
            var startDate = dateStartInput ? String(dateStartInput.value || "").trim() : "";
            var endDate = dateEndInput ? String(dateEndInput.value || "").trim() : "";

            var rangeLabel = "Semua Tanggal";
            if (startDate !== "" && endDate !== "") {
                rangeLabel = startDate + " s/d " + endDate;
            } else if (startDate !== "") {
                rangeLabel = "Mulai " + startDate;
            } else if (endDate !== "") {
                rangeLabel = "Sampai " + endDate;
            }

            activeFilterBadge.textContent = statusLabel + " • " + rangeLabel;
        }

        function getFilters() {
            return {
                status: getStatusValue(),
                date_start: dateStartInput ? String(dateStartInput.value || "").trim() : "",
                date_end: dateEndInput ? String(dateEndInput.value || "").trim() : ""
            };
        }

        if (!window.EthernoFullCalendar || typeof window.EthernoFullCalendar.init !== "function") {
            return;
        }

        var calendarApi = window.EthernoFullCalendar.init({
            calendarElementId: "booking_calendar",
            loadingElementId: "booking_calendar_loading",
            eventsEndpoint: eventsUrl,
            locale: "id",
            getFilters: getFilters,
            onEventNavigate: function (detailUrl) {
                window.location.href = detailUrl;
            },
            onAfterLoad: function () {
                updateActiveFilterBadge();
            }
        });

        if (!calendarApi || typeof calendarApi.refetch !== "function") {
            return;
        }

        if (statusSelect) {
            statusSelect.addEventListener("change", function () {
                syncStatusPills();
            });
        }

        if (applyButton) {
            applyButton.addEventListener("click", function () {
                syncStatusPills();
                updateActiveFilterBadge();
                calendarApi.refetch();
            });
        }

        if (resetButton) {
            resetButton.addEventListener("click", function () {
                if (statusSelect) {
                    statusSelect.value = "";
                }
                if (dateStartInput) {
                    dateStartInput.value = "";
                }
                if (dateEndInput) {
                    dateEndInput.value = "";
                }

                syncStatusPills();
                updateActiveFilterBadge();
                calendarApi.refetch();
            });
        }

        statusPills.forEach(function (pill) {
            pill.addEventListener("click", function () {
                if (!statusSelect) {
                    return;
                }

                statusSelect.value = String(pill.getAttribute("data-status-code") || "").trim();
                syncStatusPills();
                updateActiveFilterBadge();
                calendarApi.refetch();
            });
        });

        syncStatusPills();
        updateActiveFilterBadge();
    });
})();
