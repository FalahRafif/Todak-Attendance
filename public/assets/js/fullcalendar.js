(function (window) {
    "use strict";

    function normalizeDateToIso(dateValue) {
        if (!(dateValue instanceof Date) || Number.isNaN(dateValue.getTime())) {
            return "";
        }

        var year = dateValue.getFullYear();
        var month = String(dateValue.getMonth() + 1).padStart(2, "0");
        var day = String(dateValue.getDate()).padStart(2, "0");

        return year + "-" + month + "-" + day;
    }

    function setLoadingState(loadingElement, isLoading) {
        if (!loadingElement) {
            return;
        }

        loadingElement.classList.toggle("d-none", !isLoading);
    }

    function buildRequestUrl(baseUrl, params) {
        var requestUrl = new URL(baseUrl, window.location.origin);

        Object.keys(params).forEach(function (key) {
            var value = params[key];
            if (value === null || value === undefined) {
                return;
            }

            var normalized = String(value).trim();
            if (normalized === "") {
                return;
            }

            requestUrl.searchParams.set(key, normalized);
        });

        return requestUrl.toString();
    }

    function resolveEventsArray(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        if (payload && Array.isArray(payload.events)) {
            return payload.events;
        }

        return [];
    }

    function init(options) {
        var config = options || {};

        if (!window.FullCalendar || typeof window.FullCalendar.Calendar !== "function") {
            return null;
        }

        var calendarElementId = String(config.calendarElementId || "").trim();
        var eventsEndpoint = String(config.eventsEndpoint || "").trim();

        if (calendarElementId === "" || eventsEndpoint === "") {
            return null;
        }

        var calendarElement = document.getElementById(calendarElementId);
        if (!calendarElement) {
            return null;
        }

        var loadingElement = null;
        var loadingElementId = String(config.loadingElementId || "").trim();
        if (loadingElementId !== "") {
            loadingElement = document.getElementById(loadingElementId);
        }

        var getFilters = typeof config.getFilters === "function"
            ? config.getFilters
            : function () { return {}; };

        var onAfterLoad = typeof config.onAfterLoad === "function" ? config.onAfterLoad : function () {};
        var onError = typeof config.onError === "function" ? config.onError : function () {};
        var onEventNavigate = typeof config.onEventNavigate === "function" ? config.onEventNavigate : null;

        var locale = String(config.locale || "en").trim();

        var calendar = new window.FullCalendar.Calendar(calendarElement, {
            initialView: "dayGridMonth",
            locale: locale !== "" ? locale : "en",
            height: "auto",
            dayMaxEvents: true,
            navLinks: true,
            buttonText: {
                today: "Hari Ini",
                month: "Bulan",
                week: "Minggu",
                day: "Hari",
                list: "Daftar"
            },
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,listMonth"
            },
            events: function (fetchInfo, successCallback, failureCallback) {
                setLoadingState(loadingElement, true);

                var startDate = normalizeDateToIso(fetchInfo.start);
                var endDate = "";
                if (fetchInfo.end instanceof Date && !Number.isNaN(fetchInfo.end.getTime())) {
                    var inclusiveEnd = new Date(fetchInfo.end.getTime());
                    inclusiveEnd.setDate(inclusiveEnd.getDate() - 1);
                    endDate = normalizeDateToIso(inclusiveEnd);
                }

                var requestParams = Object.assign({}, getFilters() || {}, {
                    start: startDate,
                    end: endDate
                });

                fetch(buildRequestUrl(eventsEndpoint, requestParams), {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                    .then(function (response) {
                        return response.json().catch(function () {
                            return {};
                        }).then(function (payload) {
                            if (!response.ok) {
                                var message = String(payload.message || "Gagal mengambil data kalender booking.");
                                throw new Error(message);
                            }

                            return payload;
                        });
                    })
                    .then(function (payload) {
                        var events = resolveEventsArray(payload);
                        successCallback(events);
                        onAfterLoad(events, payload);
                    })
                    .catch(function (error) {
                        failureCallback(error);
                        onError(error);
                    })
                    .finally(function () {
                        setLoadingState(loadingElement, false);
                    });
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();

                var detailUrl = String(info.event.extendedProps && info.event.extendedProps.detail_url ? info.event.extendedProps.detail_url : "").trim();
                if (detailUrl === "") {
                    return;
                }

                if (onEventNavigate) {
                    onEventNavigate(detailUrl, info.event);
                    return;
                }

                window.location.href = detailUrl;
            },
            eventDidMount: function (info) {
                var statusLabel = String(info.event.extendedProps && info.event.extendedProps.status_label ? info.event.extendedProps.status_label : "").trim();
                var sessionLabel = String(info.event.extendedProps && info.event.extendedProps.session_label ? info.event.extendedProps.session_label : "").trim();

                var hint = [statusLabel, sessionLabel].filter(function (segment) {
                    return segment !== "" && segment !== "-";
                }).join(" | ");

                if (hint !== "") {
                    info.el.setAttribute("title", hint);
                }
            }
        });

        calendar.render();

        return {
            calendar: calendar,
            refetch: function () {
                calendar.refetchEvents();
            }
        };
    }

    window.EthernoFullCalendar = {
        init: init
    };
})(window);
