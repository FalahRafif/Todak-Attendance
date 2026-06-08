(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("booking_form_preview");
        if (!form) {
            return;
        }

        var $ = window.jQuery || null;
        var hasSelect2 = Boolean($ && $.fn && typeof $.fn.select2 === "function");

        function initSelect2(selectEl) {
            if (!hasSelect2 || !selectEl || !selectEl.classList.contains("booking-select2")) {
                return;
            }

            var $el = $(selectEl);
            if ($el.hasClass("select2-hidden-accessible")) {
                return;
            }

            $el.select2({
                width: "100%",
                allowClear: true,
                placeholder: selectEl.getAttribute("data-placeholder") || ""
            });
        }

        function refreshSelect2(selectEl) {
            if (!hasSelect2 || !selectEl) {
                return;
            }

            var $el = $(selectEl);
            if ($el.hasClass("select2-hidden-accessible")) {
                $el.trigger("change.select2");
                return;
            }

            initSelect2(selectEl);
        }

        function initSelect2Group(selector) {
            var inFormNodes = Array.from(form.querySelectorAll(selector));
            var outOfFormNodes = Array.from(document.querySelectorAll(selector + '[form="' + form.id + '"]'));
            var uniqueNodes = Array.from(new Set(inFormNodes.concat(outOfFormNodes)));

            uniqueNodes.forEach(function (node) {
                initSelect2(node);
            });
        }

        function bindEnhancedSelectChange(selectEl, handler) {
            if (!selectEl) {
                return;
            }

            selectEl.addEventListener("change", handler);

            if (!hasSelect2) {
                return;
            }

            $(selectEl).on("select2:select", function () {
                handler();
            });

            $(selectEl).on("select2:clear", function () {
                handler();
            });
        }

        function setSelectOptions(selectEl, options, selectedValue) {
            var placeholder = selectEl.getAttribute("data-placeholder") || "";
            var normalizedSelected = String(selectedValue || "").trim();

            selectEl.innerHTML = "";
            var placeholderOption = document.createElement("option");
            placeholderOption.value = "";
            placeholderOption.textContent = placeholder !== "" ? placeholder : "";
            selectEl.appendChild(placeholderOption);

            options.forEach(function (option) {
                var item = document.createElement("option");
                item.value = String(option.id);
                item.textContent = option.name;
                selectEl.appendChild(item);
            });

            if (normalizedSelected !== "" && options.some(function (opt) { return String(opt.id) === normalizedSelected; })) {
                selectEl.value = normalizedSelected;
            } else {
                selectEl.value = "";
            }

            selectEl.disabled = options.length === 0;
            refreshSelect2(selectEl);
        }

        function clearSelect(selectEl) {
            setSelectOptions(selectEl, [], "");
        }

        function normalizeToRelativeUrl(rawUrl) {
            try {
                var parsed = new URL(rawUrl, window.location.href);
                return parsed.pathname + parsed.search;
            } catch (_error) {
                return rawUrl;
            }
        }

        function buildUrlWithParams(baseUrl, params) {
            var segments = [];
            Object.keys(params).forEach(function (key) {
                var value = params[key];
                if (value === null || value === undefined || String(value).trim() === "") {
                    return;
                }

                segments.push(encodeURIComponent(key) + "=" + encodeURIComponent(String(value)));
            });

            if (segments.length === 0) {
                return baseUrl;
            }

            return baseUrl + (baseUrl.indexOf("?") === -1 ? "?" : "&") + segments.join("&");
        }

        function setupPackageFilter() {
            var packageTypeSelect = document.getElementById("booking_package_type");
            var packageSelect = document.getElementById("booking_package");
            var packageAddressPreview = document.getElementById("booking_package_address_preview");
            if (!packageTypeSelect || !packageSelect) {
                return;
            }

            var packageOptionsCache = Array.from(packageSelect.querySelectorAll("option[data-package-type]")).map(function (option) {
                return {
                    value: String(option.value),
                    text: String(option.textContent || "").trim(),
                    packageType: String(option.getAttribute("data-package-type") || "").trim(),
                    packageAddress: String(option.getAttribute("data-package-address") || "").trim()
                };
            });

            function renderPackageAddress(packageId, options) {
                if (!packageAddressPreview) {
                    return;
                }

                var defaultText = String(packageAddressPreview.getAttribute("data-default-text") || "Pilih paket untuk melihat referensi alamat paket.");
                var normalizedPackageId = String(packageId || "").trim();
                if (normalizedPackageId === "") {
                    packageAddressPreview.textContent = defaultText;
                    return;
                }

                var selectedPackage = options.find(function (item) {
                    return item.value === normalizedPackageId;
                });

                if (!selectedPackage || selectedPackage.packageAddress === "") {
                    packageAddressPreview.textContent = "Alamat paket belum tersedia.";
                    return;
                }

                packageAddressPreview.textContent = "Alamat paket: " + selectedPackage.packageAddress;
            }

            function renderPackageOptions(selectedTypeId, preferredPackageId) {
                var normalizedTypeId = String(selectedTypeId || "").trim();
                var normalizedPreferredPackageId = String(preferredPackageId || "").trim();

                var matched = packageOptionsCache.filter(function (item) {
                    return normalizedTypeId !== "" && item.packageType === normalizedTypeId;
                });

                packageSelect.innerHTML = "";

                var emptyOption = document.createElement("option");
                emptyOption.value = "";
                emptyOption.textContent = "Pilih paket";
                packageSelect.appendChild(emptyOption);

                matched.forEach(function (item) {
                    var option = document.createElement("option");
                    option.value = item.value;
                    option.textContent = item.text;
                    option.setAttribute("data-package-type", item.packageType);
                    option.setAttribute("data-package-address", item.packageAddress);
                    packageSelect.appendChild(option);
                });

                packageSelect.disabled = normalizedTypeId === "" || matched.length === 0;
                if (normalizedPreferredPackageId !== "" && matched.some(function (item) { return item.value === normalizedPreferredPackageId; })) {
                    packageSelect.value = normalizedPreferredPackageId;
                } else {
                    packageSelect.value = "";
                }

                refreshSelect2(packageSelect);
                renderPackageAddress(packageSelect.value, matched);
                packageSelect.dispatchEvent(new CustomEvent("booking:packageOptionsRendered", { bubbles: true }));
            }

            bindEnhancedSelectChange(packageTypeSelect, function () {
                renderPackageOptions(packageTypeSelect.value, "");
            });

            bindEnhancedSelectChange(packageSelect, function () {
                var selectedTypeId = String(packageTypeSelect.value || "").trim();
                var matched = packageOptionsCache.filter(function (item) {
                    return selectedTypeId !== "" && item.packageType === selectedTypeId;
                });
                renderPackageAddress(packageSelect.value, matched);
            });

            renderPackageOptions(packageTypeSelect.value, packageSelect.value);
        }

        function setupLocationCascade() {
            var rawOptionsUrl = form.getAttribute("data-location-options-url") || "";
            if (rawOptionsUrl === "") {
                return;
            }

            var optionsUrl = normalizeToRelativeUrl(rawOptionsUrl);
            var provinceSelect = document.getElementById("booking_location_province");
            var citySelect = document.getElementById("booking_location_city");
            var districtSelect = document.getElementById("booking_location_district");
            var villageSelect = document.getElementById("booking_location_village");
            var locationHiddenInput = document.getElementById("booking_location");

            if (!provinceSelect || !citySelect || !districtSelect || !villageSelect || !locationHiddenInput) {
                return;
            }

            var initialCityId = citySelect.getAttribute("data-selected") || "";
            var initialDistrictId = districtSelect.getAttribute("data-selected") || "";
            var initialVillageId = villageSelect.getAttribute("data-selected") || "";

            function syncLocationHiddenInput() {
                locationHiddenInput.value = villageSelect.value || "";
            }

            function emitLocationChanged() {
                document.dispatchEvent(new CustomEvent("booking:locationChanged"));
            }

            function fetchLocationOptions(levelCode, parentId) {
                var requestUrl = buildUrlWithParams(optionsUrl, {
                    level: levelCode,
                    parent_id: parentId
                });

                return fetch(requestUrl, {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error("Gagal mengambil opsi lokasi");
                    }

                    return response.json();
                }).then(function (payload) {
                    return Array.isArray(payload.options) ? payload.options : [];
                });
            }

            function loadLocationSelect(selectEl, levelCode, parentId, selectedId) {
                if (!parentId) {
                    clearSelect(selectEl);
                    return Promise.resolve([]);
                }

                return fetchLocationOptions(levelCode, parentId)
                    .then(function (options) {
                        setSelectOptions(selectEl, options, selectedId);
                        return options;
                    })
                    .catch(function () {
                        clearSelect(selectEl);
                        return [];
                    });
            }

            function onProvinceChanged() {
                clearSelect(citySelect);
                clearSelect(districtSelect);
                clearSelect(villageSelect);
                syncLocationHiddenInput();

                if (!provinceSelect.value) {
                    emitLocationChanged();
                    return;
                }

                loadLocationSelect(citySelect, "LL_CT", provinceSelect.value, "")
                    .finally(function () {
                        emitLocationChanged();
                    });
            }

            function onCityChanged() {
                clearSelect(districtSelect);
                clearSelect(villageSelect);
                syncLocationHiddenInput();

                if (!citySelect.value) {
                    emitLocationChanged();
                    return;
                }

                loadLocationSelect(districtSelect, "LL_KC", citySelect.value, "")
                    .finally(function () {
                        emitLocationChanged();
                    });
            }

            function onDistrictChanged() {
                clearSelect(villageSelect);
                syncLocationHiddenInput();

                if (!districtSelect.value) {
                    emitLocationChanged();
                    return;
                }

                loadLocationSelect(villageSelect, "LL_KL", districtSelect.value, "")
                    .finally(function () {
                        emitLocationChanged();
                    });
            }

            function onVillageChanged() {
                syncLocationHiddenInput();
                emitLocationChanged();
            }

            function bindCascadeHandler(selectEl, handler) {
                bindEnhancedSelectChange(selectEl, handler);
            }

            bindCascadeHandler(provinceSelect, onProvinceChanged);
            bindCascadeHandler(citySelect, onCityChanged);
            bindCascadeHandler(districtSelect, onDistrictChanged);
            bindCascadeHandler(villageSelect, onVillageChanged);

            if (provinceSelect.value) {
                loadLocationSelect(citySelect, "LL_CT", provinceSelect.value, initialCityId)
                    .then(function () {
                        if (!citySelect.value) {
                            return;
                        }

                        return loadLocationSelect(districtSelect, "LL_KC", citySelect.value, initialDistrictId);
                    })
                    .then(function () {
                        if (!districtSelect.value) {
                            return;
                        }

                        return loadLocationSelect(villageSelect, "LL_KL", districtSelect.value, initialVillageId);
                    })
                    .finally(function () {
                        syncLocationHiddenInput();
                        emitLocationChanged();
                    });
            } else {
                clearSelect(citySelect);
                clearSelect(districtSelect);
                clearSelect(villageSelect);
                syncLocationHiddenInput();
                emitLocationChanged();
            }

            document.addEventListener("booking:tabChanged", function (event) {
                if (!event || !event.detail || event.detail.tab !== "form") {
                    return;
                }

                refreshSelect2(provinceSelect);
                refreshSelect2(citySelect);
                refreshSelect2(districtSelect);
                refreshSelect2(villageSelect);
            });
        }

        function setupBookingEstimate() {
            var estimatePanel = document.getElementById("booking_estimate_panel");
            var packageTypeSelect = document.getElementById("booking_package_type");
            var packageSelect = document.getElementById("booking_package");
            var provinceSelect = document.getElementById("booking_location_province");
            var citySelect = document.getElementById("booking_location_city");
            var districtSelect = document.getElementById("booking_location_district");
            var villageSelect = document.getElementById("booking_location_village");
            var eventDateInput = document.getElementById("booking_date_check");
            var packageNameEl = document.getElementById("estimate_package_name");
            var packageTypeEl = document.getElementById("estimate_package_type");
            var packagePriceEl = document.getElementById("estimate_package_price");
            var packageAddressEl = document.getElementById("estimate_package_address");
            var packageBenefitsEl = document.getElementById("estimate_package_benefits");
            var locationRuleEl = document.getElementById("estimate_location_rule");
            var dpPercentageEl = document.getElementById("estimate_dp_percentage");
            var dpAmountEl = document.getElementById("estimate_dp_amount");
            var dpNoteEl = document.getElementById("estimate_dp_note");
            var finalNoteEl = document.getElementById("estimate_final_note");
            var finalDueDateEl = document.getElementById("estimate_final_due_date");

            if (!estimatePanel || !packageSelect || !packageNameEl || !locationRuleEl || !dpPercentageEl || !dpAmountEl || !dpNoteEl || !finalNoteEl || !finalDueDateEl) {
                return;
            }

            var rawEstimateUrl = estimatePanel.getAttribute("data-estimate-url") || form.getAttribute("data-estimate-url") || "";
            if (rawEstimateUrl === "") {
                return;
            }

            var estimateUrl = normalizeToRelativeUrl(rawEstimateUrl);
            var renderRequestId = 0;
            var debounceTimer = null;

            function setText(node, text) {
                if (!node) {
                    return;
                }

                node.textContent = text;
            }

            function renderBenefitList(benefits) {
                if (!packageBenefitsEl) {
                    return;
                }

                packageBenefitsEl.innerHTML = "";
                if (!Array.isArray(benefits) || benefits.length === 0) {
                    packageBenefitsEl.hidden = true;
                    return;
                }

                benefits.slice(0, 4).forEach(function (benefitText) {
                    var item = document.createElement("li");
                    item.textContent = String(benefitText || "").trim();
                    packageBenefitsEl.appendChild(item);
                });

                packageBenefitsEl.hidden = false;
            }

            function renderIdleState() {
                setText(packageNameEl, "Pilih tipe paket dan paket untuk melihat perkiraan biaya.");
                setText(packageTypeEl, "-");
                setText(packagePriceEl, "Harga paket: -");
                setText(packageAddressEl, "Alamat paket: -");
                renderBenefitList([]);
                setText(locationRuleEl, "Lengkapi lokasi acara untuk melihat kategori tambahan biaya.");
                setText(dpPercentageEl, "DP: -");
                setText(dpAmountEl, "Nominal DP: -");
                setText(dpNoteEl, "Batas waktu DP: -");
                setText(finalNoteEl, "Batas pelunasan: -");
                setText(finalDueDateEl, "Tanggal batas pelunasan: -");
            }

            function renderLoadingState() {
                setText(packageNameEl, "Menghitung perkiraan biaya...");
                setText(locationRuleEl, "Memuat kategori tambahan lokasi...");
            }

            function renderErrorState() {
                setText(packageNameEl, "Perkiraan biaya belum bisa dimuat.");
                setText(locationRuleEl, "Kategori tambahan lokasi belum dapat dimuat.");
                setText(dpPercentageEl, "DP: -");
                setText(dpAmountEl, "Nominal DP: -");
                setText(dpNoteEl, "Batas waktu DP: -");
                setText(finalNoteEl, "Batas pelunasan: -");
                setText(finalDueDateEl, "Tanggal batas pelunasan: -");
            }

            function renderEstimate(payload) {
                var packagePayload = payload && payload.package ? payload.package : {};
                var locationRulePayload = payload && payload.location_pricing_rule ? payload.location_pricing_rule : {};
                var paymentPayload = payload && payload.payment ? payload.payment : {};

                var packageName = String(packagePayload.name || "").trim();
                var packageType = String(packagePayload.type || "").trim();
                var packagePrice = String(packagePayload.price_formatted || "").trim();
                var packageAddress = String(packagePayload.address || "").trim();

                setText(packageNameEl, packageName !== "" ? packageName : "Paket belum ditemukan.");
                setText(packageTypeEl, packageType !== "" ? "Jenis paket: " + packageType : "Jenis paket: -");
                setText(packagePriceEl, packagePrice !== "" ? "Harga paket: " + packagePrice : "Harga paket: -");
                setText(packageAddressEl, packageAddress !== "" ? "Alamat paket: " + packageAddress : "Alamat paket: -");
                renderBenefitList(packagePayload.benefits || []);

                if (Boolean(locationRulePayload.found)) {
                    var levelLabel = String(locationRulePayload.level_label || "").trim();
                    var locationName = String(locationRulePayload.location_name || "").trim();
                    var ruleLabel = String(locationRulePayload.price_type_label || "").trim();
                    var locationDetail = [levelLabel, locationName].filter(function (part) {
                        return part !== "";
                    }).join(" - ");
                    var text = locationDetail !== ""
                        ? "Level prioritas " + locationDetail + ": " + (ruleLabel !== "" ? ruleLabel : "-")
                        : (ruleLabel !== "" ? ruleLabel : "-");
                    setText(locationRuleEl, text);
                } else {
                    setText(locationRuleEl, String(locationRulePayload.detail || "Kategori tambahan lokasi belum ditemukan."));
                }

                var dpPercentage = Number(paymentPayload.dp_percentage || 0);
                var dpAmount = String(paymentPayload.dp_amount_formatted || "").trim();
                var remainingAmount = String(paymentPayload.remaining_amount_formatted || "").trim();
                var dpRule = String(paymentPayload.dp_rule || "").trim();
                var dpNote = String(paymentPayload.dp_note || "").trim();
                var finalRule = String(paymentPayload.final_rule || "").trim();
                var finalNote = String(paymentPayload.final_note || "").trim();
                var finalDueDateLabel = String(paymentPayload.final_due_date_label || "").trim();

                setText(dpPercentageEl, dpPercentage > 0 ? "DP: " + dpPercentage + "%" : "DP: -");
                if (dpAmount !== "" || remainingAmount !== "") {
                    setText(dpAmountEl, "Nominal DP: " + (dpAmount || "-") + " | Estimasi sisa pelunasan: " + (remainingAmount || "-"));
                } else {
                    setText(dpAmountEl, "Nominal DP: -");
                }

                var dpRuleText = [dpRule, dpNote].filter(function (part) {
                    return part !== "";
                }).join(" - ");
                setText(dpNoteEl, "Batas waktu DP: " + (dpRuleText !== "" ? dpRuleText : "-"));

                var finalRuleText = [finalRule, finalNote].filter(function (part) {
                    return part !== "";
                }).join(" - ");
                setText(finalNoteEl, "Batas pelunasan: " + (finalRuleText !== "" ? finalRuleText : "-"));

                setText(finalDueDateEl, finalDueDateLabel !== "" ? "Tanggal batas pelunasan: " + finalDueDateLabel : "Tanggal batas pelunasan: -");
            }

            function fetchEstimate() {
                var packageId = String(packageSelect.value || "").trim();
                if (packageId === "") {
                    renderIdleState();
                    return;
                }

                var params = {
                    package_id: packageId,
                    location_province_id: provinceSelect ? provinceSelect.value : "",
                    location_city_id: citySelect ? citySelect.value : "",
                    location_district_id: districtSelect ? districtSelect.value : "",
                    location_village_id: villageSelect ? villageSelect.value : "",
                    event_date: eventDateInput ? eventDateInput.value : ""
                };

                var requestUrl = buildUrlWithParams(estimateUrl, params);
                var currentRequestId = ++renderRequestId;
                renderLoadingState();

                fetch(requestUrl, {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error("Gagal mengambil estimasi harga");
                        }

                        return response.json();
                    })
                    .then(function (payload) {
                        if (currentRequestId !== renderRequestId) {
                            return;
                        }

                        renderEstimate(payload);
                    })
                    .catch(function () {
                        if (currentRequestId !== renderRequestId) {
                            return;
                        }

                        renderErrorState();
                    });
            }

            function queueEstimateRender() {
                if (debounceTimer) {
                    clearTimeout(debounceTimer);
                }

                debounceTimer = setTimeout(function () {
                    fetchEstimate();
                }, 160);
            }

            if (packageTypeSelect) {
                bindEnhancedSelectChange(packageTypeSelect, queueEstimateRender);
            }
            bindEnhancedSelectChange(packageSelect, queueEstimateRender);
            bindEnhancedSelectChange(provinceSelect, queueEstimateRender);
            bindEnhancedSelectChange(citySelect, queueEstimateRender);
            bindEnhancedSelectChange(districtSelect, queueEstimateRender);
            bindEnhancedSelectChange(villageSelect, queueEstimateRender);

            if (eventDateInput) {
                eventDateInput.addEventListener("change", queueEstimateRender);
            }

            packageSelect.addEventListener("booking:packageOptionsRendered", queueEstimateRender);
            document.addEventListener("booking:locationChanged", queueEstimateRender);

            renderIdleState();
            queueEstimateRender();
        }

        function setupBookingSubmitConfirmation() {
            var modal = document.getElementById("booking_confirmation_modal");
            var confirmSubmitButton = document.getElementById("booking_confirm_submit");
            var confirmCheckbox = document.getElementById("booking_confirm_checkbox");
            if (!modal || !confirmSubmitButton || !confirmCheckbox) {
                return;
            }

            var closeButtons = Array.from(modal.querySelectorAll("[data-booking-confirm-close]"));
            var isConfirmedSubmit = false;
            var lastFocusedElement = null;

            function setSummaryValue(id, value) {
                var node = document.getElementById(id);
                if (!node) {
                    return;
                }

                var normalized = String(value || "").trim();
                node.textContent = normalized !== "" ? normalized : "-";
            }

            function getValue(id) {
                var el = document.getElementById(id);
                if (!el) {
                    return "";
                }

                return String(el.value || "").trim();
            }

            function getSelectedText(id) {
                var el = document.getElementById(id);
                if (!el || !el.options || el.selectedIndex < 0) {
                    return "";
                }

                var selectedOption = el.options[el.selectedIndex];
                if (!selectedOption) {
                    return "";
                }

                return String(selectedOption.textContent || "").trim();
            }

            function formatDateForSummary(rawDate) {
                var normalized = String(rawDate || "").trim();
                if (normalized === "") {
                    return "";
                }

                var parts = normalized.split("-");
                if (parts.length !== 3) {
                    return normalized;
                }

                return parts[2] + "/" + parts[1] + "/" + parts[0];
            }

            function joinNonEmpty(values, separator) {
                return values.filter(function (value) {
                    return String(value || "").trim() !== "";
                }).join(separator);
            }

            function populateSummary() {
                var dateLabel = formatDateForSummary(getValue("booking_date_check"));
                var sessionLabel = getSelectedText("booking_session");
                var packageTypeLabel = getSelectedText("booking_package_type");
                var packageLabel = getSelectedText("booking_package");
                var provinceLabel = getSelectedText("booking_location_province");
                var cityLabel = getSelectedText("booking_location_city");
                var districtLabel = getSelectedText("booking_location_district");
                var villageLabel = getSelectedText("booking_location_village");
                var latitude = getValue("booking_pin_lat");
                var longitude = getValue("booking_pin_lng");
                var pinLabel = latitude !== "" && longitude !== ""
                    ? latitude + ", " + longitude
                    : "";

                setSummaryValue("confirm_name", getValue("booking_name"));
                setSummaryValue("confirm_whatsapp", getValue("booking_whatsapp"));
                setSummaryValue("confirm_schedule", joinNonEmpty([dateLabel, sessionLabel], " - "));
                setSummaryValue("confirm_package", joinNonEmpty([packageTypeLabel, packageLabel], " - "));
                setSummaryValue("confirm_location", joinNonEmpty([provinceLabel, cityLabel, districtLabel, villageLabel], ", "));
                setSummaryValue("confirm_pin", pinLabel);
                setSummaryValue("confirm_address_detail", getValue("booking_pin_address"));
                setSummaryValue("confirm_event_detail", getValue("booking_detail"));
            }

            function openModal() {
                modal.hidden = false;
                document.body.classList.add("booking-confirm-open");
                confirmCheckbox.checked = false;
                confirmSubmitButton.disabled = true;
                lastFocusedElement = document.activeElement;
                confirmCheckbox.focus();
            }

            function closeModal() {
                modal.hidden = true;
                document.body.classList.remove("booking-confirm-open");
                if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                    lastFocusedElement.focus();
                }
            }

            closeButtons.forEach(function (button) {
                button.addEventListener("click", function () {
                    closeModal();
                });
            });

            confirmCheckbox.addEventListener("change", function () {
                confirmSubmitButton.disabled = !confirmCheckbox.checked;
            });

            confirmSubmitButton.addEventListener("click", function () {
                if (!confirmCheckbox.checked) {
                    return;
                }

                isConfirmedSubmit = true;
                closeModal();
                form.submit();
            });

            form.addEventListener("submit", function (event) {
                if (isConfirmedSubmit) {
                    return;
                }

                event.preventDefault();
                if (typeof form.reportValidity === "function" && !form.reportValidity()) {
                    return;
                }

                populateSummary();
                openModal();
            });

            document.addEventListener("keydown", function (event) {
                if (event.key !== "Escape" || modal.hidden) {
                    return;
                }

                closeModal();
            });
        }

        function setupMapPinPicker() {
            var mapElement = document.getElementById("booking_map_picker");
            var latInput = document.getElementById("booking_pin_lat");
            var lngInput = document.getElementById("booking_pin_lng");
            var coordinateHint = document.getElementById("booking_pin_coordinate_hint");

            if (!mapElement || !latInput || !lngInput || !window.L) {
                return;
            }

            var parsedLat = parseFloat(latInput.value);
            var parsedLng = parseFloat(lngInput.value);
            var hasInitialPin = Number.isFinite(parsedLat) && Number.isFinite(parsedLng);
            var defaultCenter = hasInitialPin ? [parsedLat, parsedLng] : [-6.2, 106.816666];

            var map = window.L.map(mapElement, { scrollWheelZoom: false }).setView(defaultCenter, hasInitialPin ? 15 : 11);

            window.L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; OpenStreetMap contributors"
            }).addTo(map);

            var marker = null;

            function syncPinInputs(lat, lng) {
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                if (coordinateHint) {
                    coordinateHint.textContent = "Pin terpilih: " + lat.toFixed(6) + ", " + lng.toFixed(6);
                }
            }

            function attachMarkerDragHandler(currentMarker) {
                currentMarker.on("dragend", function () {
                    var position = currentMarker.getLatLng();
                    syncPinInputs(position.lat, position.lng);
                });
            }

            function placeMarker(lat, lng) {
                var nextPosition = { lat: lat, lng: lng };

                if (!marker) {
                    marker = window.L.marker(nextPosition, { draggable: true }).addTo(map);
                    attachMarkerDragHandler(marker);
                } else {
                    marker.setLatLng(nextPosition);
                }

                syncPinInputs(lat, lng);
            }

            map.on("click", function (event) {
                placeMarker(event.latlng.lat, event.latlng.lng);
            });

            if (hasInitialPin) {
                placeMarker(parsedLat, parsedLng);
            }

            document.addEventListener("booking:tabChanged", function (event) {
                if (!event || !event.detail || event.detail.tab !== "form") {
                    return;
                }

                setTimeout(function () {
                    map.invalidateSize();
                }, 150);
            });

            window.addEventListener("resize", function () {
                map.invalidateSize();
            });
        }

        initSelect2Group(".booking-select2");
        setupPackageFilter();
        setupLocationCascade();
        setupBookingEstimate();
        setupBookingSubmitConfirmation();
        setupMapPinPicker();
    });
})();
