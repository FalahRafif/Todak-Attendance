(function () {
    "use strict";

    function formatRupiah(value) {
        var num = String(value).replace(/[^0-9]/g, "");
        if (num === "") {
            return "";
        }
        num = num.replace(/^0+/, "") || "0";
        return num.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseRupiah(formatted) {
        return String(formatted).replace(/[^0-9]/g, "");
    }

    function initPriceFormatter() {
        var $display = $("#price_display");
        var $hidden = $("#price");
        if (!$display.length || !$hidden.length) {
            return;
        }

        $display.on("input", function () {
            var raw = parseRupiah($(this).val());
            var formatted = formatRupiah(raw);
            $(this).val(formatted);
            $hidden.val(raw);
        });

        $display.on("blur", function () {
            var raw = parseRupiah($(this).val());
            if (raw === "" || raw === "0") {
                $(this).val("");
                $hidden.val("");
            }
        });

        $display.on("focus", function () {
            var val = $(this).val();
            if (val === "0") {
                $(this).val("");
            }
        });
    }

    function initThumbnailPreview() {
        var $input = $("#thumbnail");
        var $preview = $("#thumbnail-preview");
        var $previewWrap = $("#thumbnail-preview-wrap");
        var $placeholder = $("#thumbnail-placeholder");
        var $removeCheck = $("#remove_thumbnail");

        if (!$input.length) {
            return;
        }

        $input.on("change", function () {
            var file = this.files && this.files[0];
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $preview.attr("src", e.target.result);
                $previewWrap.removeClass("d-none");
                $placeholder.addClass("d-none");
            };
            reader.readAsDataURL(file);

            if ($removeCheck.length) {
                $removeCheck.prop("checked", false);
            }
        });

        if ($removeCheck.length) {
            $removeCheck.on("change", function () {
                if ($(this).is(":checked")) {
                    $previewWrap.addClass("d-none");
                    $placeholder.removeClass("d-none");
                } else {
                    $previewWrap.removeClass("d-none");
                    $placeholder.addClass("d-none");
                }
            });
        }
    }

    function initBenefitRows() {
        var $container = $("#benefits-container");
        var $addBtn = $("#add-benefit-btn");
        if (!$container.length || !$addBtn.length) {
            return;
        }

        function updateRemoveButtons() {
            var $rows = $container.find(".mp-benefit-row");
            $rows.find(".mp-remove-benefit").toggleClass("d-none", $rows.length <= 1);
        }

        $container.on("click", ".mp-remove-benefit", function (e) {
            e.preventDefault();
            $(this).closest(".mp-benefit-row").remove();
            updateRemoveButtons();
        });

        $addBtn.on("click", function (e) {
            e.preventDefault();
            var html =
                '<div class="input-group mb-2 mp-benefit-row">' +
                    '<span class="input-group-text"><i class="fe fe-check-circle"></i></span>' +
                    '<input type="text" class="form-control" name="benefits[]" maxlength="500" placeholder="Contoh: Bisa melakukan editing foto tanpa batas">' +
                    '<button type="button" class="btn btn-outline-danger mp-remove-benefit" title="Hapus benefit"><i class="fe fe-x"></i></button>' +
                '</div>';
            var $row = $(html);
            $container.append($row);
            $row.find("input[type='text']").focus();
            updateRemoveButtons();
        });

        updateRemoveButtons();
    }

    $(function () {
        initPriceFormatter();
        initThumbnailPreview();
        initBenefitRows();
    });
})();
