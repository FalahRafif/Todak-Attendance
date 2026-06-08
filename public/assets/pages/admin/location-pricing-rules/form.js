(function () {
    "use strict";

    function initSelect2() {
        var $selects = $(".select2");
        if (!$selects.length) {
            return;
        }

        $selects.select2({
            width: "100%",
            allowClear: true,
            placeholder: function () {
                return $(this).data("placeholder") || "";
            }
        });
    }

    function setSelectOptions($select, options, selectedId) {
        $select.empty();
        $select.append($("<option>", { value: "", text: "" }));

        options.forEach(function (option) {
            $select.append($("<option>", {
                value: option.id,
                text: option.name
            }));
        });

        if (selectedId && String(selectedId) !== "0") {
            $select.val(String(selectedId));
        } else {
            $select.val("");
        }

        $select.trigger("change.select2");
    }

    function resetSelect($select) {
        setSelectOptions($select, [], null);
        $select.prop("disabled", true);
    }

    function enableSelect($select) {
        $select.prop("disabled", false);
    }

    function toggleSelects(levelCode, order, $wrappers) {
        var levelIndex = order.indexOf(levelCode);
        if (levelIndex === -1) {
            levelIndex = 0;
        }

        order.forEach(function (code, index) {
            var $wrapper = $wrappers[code];
            if (!$wrapper || !$wrapper.length) {
                return;
            }

            if (index <= levelIndex) {
                $wrapper.removeClass("d-none");
            } else {
                $wrapper.addClass("d-none");
            }
        });
    }

    function updateLocationId(levelCode, selects, $locationId) {
        var value = "";

        if (levelCode === "LL_PV") {
            value = selects.province.val() || "";
        } else if (levelCode === "LL_CT") {
            value = selects.city.val() || "";
        } else if (levelCode === "LL_KC") {
            value = selects.district.val() || "";
        } else if (levelCode === "LL_KL") {
            value = selects.village.val() || "";
        }

        $locationId.val(value);
    }

    function loadOptions(url, levelCode, parentId, $select, selectedId) {
        var payload = { level: levelCode };
        if (parentId) {
            payload.parent_id = parentId;
        }

        return $.getJSON(url, payload).then(
            function (response) {
                var options = (response && response.options) ? response.options : [];
                setSelectOptions($select, options, selectedId);
                enableSelect($select);
                return options;
            },
            function () {
                resetSelect($select);
                return [];
            }
        );
    }

    function initCascadingLocations() {
        var $form = $(".lpr-form-card");
        if (!$form.length) {
            return;
        }

        var url = $form.data("locationOptionsUrl");
        if (!url) {
            return;
        }

        var $level = $("#location_level");
        var selects = {
            province: $("#location_province_id"),
            city: $("#location_city_id"),
            district: $("#location_district_id"),
            village: $("#location_village_id")
        };
        var $locationId = $("#location_id");
        var order = ["LL_PV", "LL_CT", "LL_KC", "LL_KL"];
        var wrappers = {
            LL_PV: $("[data-location-wrapper='LL_PV']"),
            LL_CT: $("[data-location-wrapper='LL_CT']"),
            LL_KC: $("[data-location-wrapper='LL_KC']"),
            LL_KL: $("[data-location-wrapper='LL_KL']")
        };

        var initialSelection = {
            province: String(selects.province.data("selected") || ""),
            city: String(selects.city.data("selected") || ""),
            district: String(selects.district.data("selected") || ""),
            village: String(selects.village.data("selected") || "")
        };

        var isInitializing = true;

        function clearBelow(levelCode) {
            var idx = order.indexOf(levelCode);
            for (var i = idx + 1; i < order.length; i++) {
                resetSelect(selects[getSelectKey(order[i])]);
            }
        }

        function getSelectKey(code) {
            var map = {
                LL_PV: "province",
                LL_CT: "city",
                LL_KC: "district",
                LL_KL: "village"
            };
            return map[code] || "";
        }

        async function initChain() {
            var levelCode = $level.val();
            if (!levelCode) {
                $locationId.val("");
                return;
            }

            toggleSelects(levelCode, order, wrappers);

            resetSelect(selects.city);
            resetSelect(selects.district);
            resetSelect(selects.village);

            await loadOptions(url, "LL_PV", null, selects.province, initialSelection.province);

            if (levelCode === "LL_PV") {
                updateLocationId(levelCode, selects, $locationId);
                return;
            }

            var provinceId = selects.province.val();
            if (!provinceId) {
                $locationId.val("");
                return;
            }

            await loadOptions(url, "LL_CT", provinceId, selects.city, initialSelection.city);

            if (levelCode === "LL_CT") {
                updateLocationId(levelCode, selects, $locationId);
                return;
            }

            var cityId = selects.city.val();
            if (!cityId) {
                $locationId.val("");
                return;
            }

            await loadOptions(url, "LL_KC", cityId, selects.district, initialSelection.district);

            if (levelCode === "LL_KC") {
                updateLocationId(levelCode, selects, $locationId);
                return;
            }

            var districtId = selects.district.val();
            if (!districtId) {
                $locationId.val("");
                return;
            }

            await loadOptions(url, "LL_KL", districtId, selects.village, initialSelection.village);
            updateLocationId(levelCode, selects, $locationId);
        }

        $level.on("change", function () {
            if (isInitializing) {
                return;
            }

            var levelCode = $(this).val();
            if (!levelCode) {
                order.forEach(function (code) {
                    var $w = wrappers[code];
                    if ($w && $w.length) {
                        $w.addClass("d-none");
                    }
                });
                resetSelect(selects.province);
                resetSelect(selects.city);
                resetSelect(selects.district);
                resetSelect(selects.village);
                $locationId.val("");
                return;
            }

            toggleSelects(levelCode, order, wrappers);
            clearBelow("LL_PV");
            $locationId.val("");

            loadOptions(url, "LL_PV", null, selects.province, null);
        });

        selects.province.on("change", function () {
            if (isInitializing) {
                return;
            }

            var levelCode = $level.val() || "";
            clearBelow("LL_PV");

            var provinceId = $(this).val();
            if (levelCode !== "LL_PV" && provinceId) {
                loadOptions(url, "LL_CT", provinceId, selects.city, null);
            }

            updateLocationId(levelCode, selects, $locationId);
        });

        selects.city.on("change", function () {
            if (isInitializing) {
                return;
            }

            var levelCode = $level.val() || "";
            clearBelow("LL_CT");

            var cityId = $(this).val();
            if ((levelCode === "LL_KC" || levelCode === "LL_KL") && cityId) {
                loadOptions(url, "LL_KC", cityId, selects.district, null);
            }

            updateLocationId(levelCode, selects, $locationId);
        });

        selects.district.on("change", function () {
            if (isInitializing) {
                return;
            }

            var levelCode = $level.val() || "";
            clearBelow("LL_KC");

            var districtId = $(this).val();
            if (levelCode === "LL_KL" && districtId) {
                loadOptions(url, "LL_KL", districtId, selects.village, null);
            }

            updateLocationId(levelCode, selects, $locationId);
        });

        selects.village.on("change", function () {
            if (isInitializing) {
                return;
            }

            var levelCode = $level.val() || "";
            updateLocationId(levelCode, selects, $locationId);
        });

        initChain().finally(function () {
            isInitializing = false;
        });
    }

    $(function () {
        initSelect2();
        initCascadingLocations();
    });
})();
