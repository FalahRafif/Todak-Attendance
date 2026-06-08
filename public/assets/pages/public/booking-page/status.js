(function () {
    "use strict";

    document.addEventListener("DOMContentLoaded", function () {
        var form = document.getElementById("booking_status_lookup_form");
        if (!form) return;

        var lookupUrl = String(form.getAttribute("data-status-lookup-url") || "").trim();
        var bookingCodeInput = document.getElementById("booking_code");
        var lookupError = document.getElementById("booking_status_lookup_error");
        var openVerifyButton = document.getElementById("booking_status_lookup_button");

        var modal = document.getElementById("booking_status_verify_modal");
        var modalCloseButtons = Array.from(document.querySelectorAll("[data-booking-status-verify-close]"));
        var verifyInput = document.getElementById("booking_status_phone_last4");
        var verifySubmitButton = document.getElementById("booking_status_verify_submit");
        var verifyError = document.getElementById("booking_status_verify_error");

        var statusResultPanel = document.getElementById("booking_status_result");
        var statusState = document.getElementById("booking_status_state");
        var statusStateLabel = document.getElementById("booking_status_state_label");
        var statusStateSubtitle = document.getElementById("booking_status_state_subtitle");
        var downloadProofLink = document.getElementById("status_download_proof");
        var historyList = document.getElementById("booking_status_history");
        var mapsPinLink = document.getElementById("status_google_maps_pin_link");

        var uploadPaymentModal = document.getElementById("upload_payment_modal");
        var uploadPaymentForm = document.getElementById("upload_payment_form");
        var uploadPaymentCloseButtons = Array.from(document.querySelectorAll("[data-upload-payment-close]"));
        var uploadPaymentError = document.getElementById("upload_payment_error");
        var uploadPaymentSubmitBtn = document.getElementById("upload_payment_submit_btn");
        var uploadPaymentAmountInfo = document.getElementById("upload_payment_amount_info");
        var uploadPaymentInstallmentId = document.getElementById("upload_payment_installment_id");

        var isSubmitting = false;
        var lastFocusedElement = null;
        var currentPayload = null;

        function setText(id, value) {
            var node = document.getElementById(id);
            if (!node) return;
            var normalized = String(value || "").trim();
            node.textContent = normalized !== "" ? normalized : "-";
        }

        function setLookupError(message) {
            if (!lookupError) return;
            var normalized = String(message || "").trim();
            lookupError.textContent = normalized;
            lookupError.hidden = normalized === "";
        }

        function setVerifyError(message) {
            if (!verifyError) return;
            var normalized = String(message || "").trim();
            verifyError.textContent = normalized;
            verifyError.hidden = normalized === "";
        }

        function sanitizeLastFour(value) {
            return String(value || "").replace(/\D+/g, "").slice(0, 4);
        }

        function normalizeTone(tone) {
            var t = String(tone || "").toLowerCase();
            return ["success", "warning", "info", "danger", "neutral"].indexOf(t) !== -1 ? t : "neutral";
        }

        function openVerifyModal() {
            if (!modal || !verifyInput) return;
            modal.hidden = false;
            document.body.classList.add("booking-confirm-open");
            verifyInput.value = "";
            setVerifyError("");
            verifySubmitButton.disabled = false;
            verifySubmitButton.textContent = "Verifikasi & Tampilkan";
            lastFocusedElement = document.activeElement;
            verifyInput.focus();
        }

        function closeVerifyModal() {
            if (!modal) return;
            modal.hidden = true;
            document.body.classList.remove("booking-confirm-open");
            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        function buildWhatsappUrl(phone, message) {
            var p = String(phone || "").replace(/[^0-9]/g, "");
            if (p === "") return "#";
            if (p.startsWith("0")) p = "62" + p.substring(1);
            return "https://wa.me/" + p + "?text=" + encodeURIComponent(String(message || ""));
        }

        function renderHistory(items) {
            if (!historyList) return;
            historyList.innerHTML = "";
            if (!Array.isArray(items) || items.length === 0) {
                var emptyItem = document.createElement("li");
                emptyItem.className = "booking-status-history-item";
                emptyItem.textContent = "Riwayat status belum tersedia.";
                historyList.appendChild(emptyItem);
                return;
            }
            items.forEach(function (item) {
                var li = document.createElement("li");
                li.className = "booking-status-history-item";
                var statusSpan = document.createElement("span");
                statusSpan.className = "booking-status-history-status";
                statusSpan.textContent = String(item && item.status ? item.status : "-");
                var timeSpan = document.createElement("span");
                timeSpan.className = "booking-status-history-time";
                timeSpan.textContent = String(item && item.time ? item.time : "-");
                li.appendChild(statusSpan);
                li.appendChild(timeSpan);
                if (item && item.description) {
                    var descSpan = document.createElement("span");
                    descSpan.className = "booking-status-history-desc";
                    descSpan.textContent = item.description;
                    li.appendChild(descSpan);
                }
                historyList.appendChild(li);
            });
        }

        function renderBillingDetails(details) {
            var wrap = document.getElementById("billing_details_wrap");
            var list = document.getElementById("billing_details_list");
            if (!wrap || !list) return;
            list.innerHTML = "";
            if (!Array.isArray(details) || details.length === 0) {
                wrap.hidden = true;
                return;
            }
            wrap.hidden = false;
            var grid = document.createElement("div");
            grid.className = "booking-status-grid";
            details.forEach(function (d) {
                var item = document.createElement("div");
                item.className = "booking-status-item";
                var k = document.createElement("p");
                k.className = "booking-status-key";
                k.textContent = String(d.type || "-") + ": " + String(d.name || "-");
                var v = document.createElement("p");
                v.className = "booking-status-value";
                v.textContent = String(d.amount_label || "-");
                item.appendChild(k);
                item.appendChild(v);
                grid.appendChild(item);
            });
            list.appendChild(grid);
        }

        function renderInstallments(installments) {
            var wrap = document.getElementById("billing_installments_wrap");
            var list = document.getElementById("billing_installments_list");
            if (!wrap || !list) return;
            list.innerHTML = "";
            if (!Array.isArray(installments) || installments.length === 0) {
                wrap.hidden = true;
                return;
            }
            wrap.hidden = false;
            installments.forEach(function (inst) {
                var card = document.createElement("div");
                card.className = "billing-installment-card";

                var header = document.createElement("div");
                header.className = "billing-installment-header";

                var typeEl = document.createElement("span");
                typeEl.className = "billing-installment-type";
                typeEl.textContent = String(inst.type || "-");

                var statusEl = document.createElement("span");
                statusEl.className = "billing-installment-status";
                statusEl.textContent = String(inst.status || "-");

                header.appendChild(typeEl);
                header.appendChild(statusEl);
                card.appendChild(header);

                var grid = document.createElement("div");
                grid.className = "booking-status-grid";

                var fields = [
                    { key: "Tagihan", val: inst.amount_label },
                    { key: "Dibayar", val: inst.paid_label },
                    { key: "Sisa", val: inst.remaining_label },
                    { key: "Jatuh Tempo", val: inst.due_date }
                ];
                fields.forEach(function (f) {
                    var item = document.createElement("div");
                    item.className = "booking-status-item";
                    var k = document.createElement("p");
                    k.className = "booking-status-key";
                    k.textContent = f.key;
                    var v = document.createElement("p");
                    v.className = "booking-status-value";
                    v.textContent = f.val || "-";
                    item.appendChild(k);
                    item.appendChild(v);
                    grid.appendChild(item);
                });
                card.appendChild(grid);

                if (Array.isArray(inst.payments) && inst.payments.length > 0) {
                    var payTitle = document.createElement("p");
                    payTitle.className = "booking-status-key mt-2";
                    payTitle.textContent = "Riwayat Pembayaran";
                    card.appendChild(payTitle);

                    inst.payments.forEach(function (p) {
                        var payRow = document.createElement("div");
                        payRow.className = "booking-status-item booking-status-item-full";
                        var k = document.createElement("p");
                        k.className = "booking-status-key";
                        k.textContent = String(p.paid_at || "-") + " \u2022 " + String(p.method || "-");
                        var v = document.createElement("p");
                        v.className = "booking-status-value";
                        var statusLabel = String(p.status || "-");
                        var pCode = String(p.status_code || "");
                        if (pCode === "PYS_PEDING") {
                            statusLabel = "\u23F3 Menunggu Verifikasi";
                        } else if (pCode === "PYS_FAILED") {
                            statusLabel = "\u274C Ditolak";
                        } else if (pCode === "PYS_SUCCESS") {
                            statusLabel = "\u2705 Terverifikasi";
                        }
                        v.textContent = String(p.amount_label || "-") + " (" + statusLabel + ")";
                        payRow.appendChild(k);
                        payRow.appendChild(v);

                        if (pCode === "PYS_FAILED" && p.rejection_reason) {
                            var reasonEl = document.createElement("p");
                            reasonEl.className = "booking-disclaimer text-danger mt-1 mb-0";
                            reasonEl.textContent = "Alasan: " + p.rejection_reason;
                            payRow.appendChild(reasonEl);
                        }

                        card.appendChild(payRow);
                    });
                }

                list.appendChild(card);
            });
        }

        function renderCustomerActions(actions, billing, waPhone, waTemplates) {
            var wrap = document.getElementById("customer_actions_list");
            if (!wrap) return;
            wrap.innerHTML = "";
            if (!Array.isArray(actions) || actions.length === 0) {
                var empty = document.createElement("p");
                empty.className = "booking-disclaimer";
                empty.textContent = "Tidak ada aksi yang tersedia saat ini.";
                wrap.appendChild(empty);
                return;
            }

            actions.forEach(function (action) {
                if (action === "upload_dp" || action === "upload_dp_pending") {
                    var dpInst = findPayableDpInstallment(billing);
                    if (action === "upload_dp_pending" || (dpInst && dpInst.has_pending_payment)) {
                        renderPendingInfo(wrap, "DP", waPhone, waTemplates.dp_paid);
                    } else if (dpInst) {
                        var btn = document.createElement("button");
                        btn.className = "cta mb-2";
                        btn.innerHTML = '<i class="ri-upload-line me-1"></i> Upload Bukti DP';
                        btn.setAttribute("data-installment-id", dpInst.id);
                        btn.setAttribute("data-installment-remaining", dpInst.remaining_label);
                        btn.addEventListener("click", function () {
                            openUploadPaymentModal(dpInst.id, "DP", dpInst.remaining_label, dpInst.amount_label);
                        });
                        wrap.appendChild(btn);
                    }
                }
                if (action === "upload_final" || action === "upload_final_pending") {
                    var finalInsts = findPayableNonDpInstallments(billing);
                    finalInsts.forEach(function (fi) {
                        if (action === "upload_final_pending" || fi.has_pending_payment) {
                            renderPendingInfo(wrap, fi.type, waPhone, waTemplates.final_paid);
                        } else {
                            var btn = document.createElement("button");
                            btn.className = "cta cta-outline mb-2";
                            btn.innerHTML = '<i class="ri-upload-line me-1"></i> Upload Bukti ' + String(fi.type || "Pembayaran") + " (" + String(fi.remaining_label || "-") + ")";
                            btn.addEventListener("click", function () {
                                openUploadPaymentModal(fi.id, fi.type, fi.remaining_label, fi.amount_label);
                            });
                            wrap.appendChild(btn);
                        }
                    });
                }
                if (action === "reschedule_request") {
                    var info = document.createElement("div");
                    info.className = "estimate-box mb-2";
                    info.innerHTML = '<p class="estimate-note mb-0">Ingin mengajukan reschedule? Silakan hubungi tim kami via WhatsApp.</p>';
                    wrap.appendChild(info);
                }
            });
        }

        function renderPendingInfo(wrap, type, phone, template) {
            var box = document.createElement("div");
            box.className = "estimate-box mb-2";
            box.innerHTML = '<p class="estimate-note mb-1">\u23F3 Bukti pembayaran ' + String(type || '') + ' sudah dikirim dan sedang menunggu verifikasi tim kami.</p><p class="estimate-note mb-0" style="font-size:0.85em;">Jika bukti ditolak, Anda bisa mengirim ulang dari halaman ini.</p>';
            wrap.appendChild(box);
            if (phone && template) {
                var waLink = document.createElement("a");
                waLink.className = "cta cta-outline mb-2";
                waLink.href = buildWhatsappUrl(phone, template);
                waLink.target = "_blank";
                waLink.rel = "noopener";
                waLink.innerHTML = "Konfirmasi via WhatsApp";
                wrap.appendChild(waLink);
            }
        }

        function findPayableDpInstallment(billing) {
            if (!billing || !Array.isArray(billing.installments)) return null;
            return billing.installments.find(function (i) {
                if (i.type_code !== "INS_DP") return false;
                if ((i.remaining_amount || 0) > 0) return true;
                if (Array.isArray(i.payments)) {
                    var hasFailed = i.payments.some(function (p) { return String(p.status_code || "") === "PYS_FAILED"; });
                    if (hasFailed) return true;
                }
                return false;
            }) || null;
        }

        function findPayableNonDpInstallments(billing) {
            if (!billing || !Array.isArray(billing.installments)) return [];
            return billing.installments.filter(function (i) {
                if (i.type_code === "INS_DP" || i.type_code === "INS_REFUND") return false;
                if ((i.remaining_amount || 0) > 0) return true;
                if (Array.isArray(i.payments)) {
                    var hasFailed = i.payments.some(function (p) { return String(p.status_code || "") === "PYS_FAILED"; });
                    if (hasFailed) return true;
                }
                return false;
            });
        }

        function openUploadPaymentModal(installmentId, type, remainingLabel, amountLabel) {
            if (!uploadPaymentModal) return;
            uploadPaymentInstallmentId.value = String(installmentId);
            uploadPaymentAmountInfo.textContent = "Tipe: " + String(type || "-") + " | Nominal Tagihan: " + String(amountLabel || "-") + " | Sisa: " + String(remainingLabel || "-");
            uploadPaymentError.hidden = true;
            uploadPaymentSubmitBtn.disabled = false;
            uploadPaymentSubmitBtn.textContent = "Kirim Bukti Pembayaran";
            var fileInput = document.getElementById("upload_payment_receipt");
            if (fileInput) fileInput.value = "";
            uploadPaymentModal.hidden = false;
            document.body.classList.add("booking-confirm-open");
        }

        function closeUploadPaymentModal() {
            if (!uploadPaymentModal) return;
            uploadPaymentModal.hidden = true;
            document.body.classList.remove("booking-confirm-open");
        }

        function submitPaymentProof() {
            if (isSubmitting || !uploadPaymentForm || !currentPayload) return;
            uploadPaymentError.hidden = true;

            var fileInput = document.getElementById("upload_payment_receipt");
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                uploadPaymentError.textContent = "Pilih file bukti transfer terlebih dahulu.";
                uploadPaymentError.hidden = false;
                return;
            }

            var formData = new FormData();
            formData.append("booking_code", String(bookingCodeInput.value || "").trim());
            formData.append("phone_last4", sanitizeLastFour(verifyInput ? verifyInput.value : ""));
            formData.append("billing_installment_id", String(uploadPaymentInstallmentId.value || ""));
            if (fileInput.files && fileInput.files.length > 0) {
                formData.append("transfer_receipt", fileInput.files[0]);
            }

            isSubmitting = true;
            uploadPaymentSubmitBtn.disabled = true;
            uploadPaymentSubmitBtn.textContent = "Mengirim...";

            fetch(uploadPaymentUrl, {
                method: "POST",
                credentials: "same-origin",
                body: formData,
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    if (!response.ok) {
                        var msg = data.message || "Gagal mengirim bukti pembayaran.";
                        if (data.errors) {
                            var fieldErrors = [];
                            for (var field in data.errors) {
                                if (data.errors.hasOwnProperty(field)) {
                                    fieldErrors = fieldErrors.concat(data.errors[field]);
                                }
                            }
                            if (fieldErrors.length > 0) msg = fieldErrors.join(" ");
                        }
                        throw new Error(msg);
                    }
                    return data;
                });
            })
            .then(function (data) {
                closeUploadPaymentModal();
                submitLookup();
            })
            .catch(function (err) {
                var msg = err instanceof Error ? err.message : "Terjadi kendala saat mengirim bukti pembayaran.";
                uploadPaymentError.textContent = msg;
                uploadPaymentError.hidden = false;
            })
            .finally(function () {
                isSubmitting = false;
                uploadPaymentSubmitBtn.disabled = false;
                uploadPaymentSubmitBtn.textContent = "Kirim Bukti Pembayaran";
            });
        }

        function renderPayload(payload) {
            currentPayload = payload;
            var status = payload && typeof payload.status === "object" ? payload.status : {};
            var customer = payload && typeof payload.customer === "object" ? payload.customer : {};
            var eventData = payload && typeof payload.event === "object" ? payload.event : {};
            var packageData = payload && typeof payload.package === "object" ? payload.package : {};
            var billing = payload && typeof payload.billing === "object" ? payload.billing : null;

            var tone = normalizeTone(status.tone);
            if (statusState) {
                statusState.classList.remove("success", "warning", "info", "danger", "neutral");
                statusState.classList.add(tone);
            }
            if (statusStateLabel) statusStateLabel.textContent = "Status: " + String(status.label || "-");
            if (statusStateSubtitle) statusStateSubtitle.textContent = "Diajukan pada " + String(eventData.submitted_at || "-");

            setText("status_case_id", payload.booking_case_id);
            setText("status_request_code", payload.request_code);
            setText("status_customer_name", customer.name);
            setText("status_customer_phone", customer.phone_masked);
            setText("status_event_date", eventData.date_label);
            setText("status_event_session", eventData.session);
            setText("status_package_name", packageData.name);
            setText("status_package_type", packageData.type);
            setText("status_package_price", packageData.price);
            setText("status_package_address", packageData.address);
            setText("status_location", payload.location_label);
            setText("status_event_detail", eventData.detail);

            var mapsPinUrl = String(payload.google_maps_pin || "").trim();
            if (mapsPinLink) {
                if (mapsPinUrl !== "" && mapsPinUrl !== "-") {
                    mapsPinLink.href = mapsPinUrl;
                    mapsPinLink.textContent = "Lihat pin lokasi";
                    mapsPinLink.removeAttribute("aria-disabled");
                } else {
                    mapsPinLink.href = "#";
                    mapsPinLink.textContent = "Pin lokasi belum tersedia";
                    mapsPinLink.setAttribute("aria-disabled", "true");
                }
            }

            if (billing) {
                setText("status_billing_status", "Status: " + String(billing.status || "-"));
                setText("status_billing_total", String(billing.total || "-"));
                setText("status_billing_paid", String(billing.paid || "-"));
                setText("status_billing_remaining", String(billing.remaining || "-"));
                renderBillingDetails(billing.details || []);
                renderInstallments(billing.installments || []);
            } else {
                setText("status_billing_status", "Belum ada data pembayaran.");
                setText("status_billing_total", "-");
                setText("status_billing_paid", "-");
                setText("status_billing_remaining", "-");
                renderBillingDetails([]);
                renderInstallments([]);
            }

            renderHistory(payload.history || []);
            var adminWa = String(payload.admin_whatsapp || "").trim();
            var waTemplates = payload.whatsapp_templates || {};
            renderCustomerActions(payload.customer_actions || [], billing, adminWa, waTemplates);

            var waSupportLink = document.getElementById("btn_wa_support");
            if (waSupportLink) {
                if (adminWa !== "") {
                    waSupportLink.href = buildWhatsappUrl(adminWa, waTemplates.support || "");
                    waSupportLink.hidden = false;
                } else {
                    waSupportLink.href = "#";
                    waSupportLink.hidden = true;
                }
            }

            if (downloadProofLink) {
                var proofUrl = String(payload.proof_download_url || "").trim();
                if (proofUrl !== "") {
                    downloadProofLink.href = proofUrl;
                    downloadProofLink.hidden = false;
                } else {
                    downloadProofLink.href = "#";
                    downloadProofLink.hidden = true;
                }
            }

            if (statusResultPanel) {
                statusResultPanel.hidden = false;
                statusResultPanel.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }

        function submitLookup() {
            if (isSubmitting || !bookingCodeInput || !verifyInput) return;
            var bookingCode = String(bookingCodeInput.value || "").trim();
            var phoneLast4 = sanitizeLastFour(verifyInput.value);
            setVerifyError("");
            setLookupError("");

            if (phoneLast4.length !== 4) {
                setVerifyError("Masukkan tepat 4 digit terakhir nomor WhatsApp.");
                verifyInput.focus();
                return;
            }
            if (lookupUrl === "") {
                setVerifyError("Endpoint cek status belum tersedia.");
                return;
            }

            isSubmitting = true;
            verifySubmitButton.disabled = true;
            verifySubmitButton.textContent = "Memverifikasi...";

            var requestUrl = new URL(lookupUrl, window.location.origin);
            requestUrl.searchParams.set("booking_code", bookingCode);
            requestUrl.searchParams.set("phone_last4", phoneLast4);

            fetch(requestUrl.toString(), {
                method: "GET",
                credentials: "same-origin",
                headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
            })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (payload) {
                    if (!response.ok) throw new Error(String(payload.message || "Data booking tidak ditemukan atau verifikasi tidak sesuai."));
                    return payload;
                });
            })
            .then(function (payload) {
                closeVerifyModal();
                renderPayload(payload);
            })
            .catch(function (error) {
                var message = error instanceof Error ? error.message : "Terjadi kendala saat mengambil data booking.";
                setVerifyError(message);
            })
            .finally(function () {
                isSubmitting = false;
                verifySubmitButton.disabled = false;
                verifySubmitButton.textContent = "Verifikasi & Tampilkan";
            });
        }

        var uploadPaymentUrl = String(form.getAttribute("data-upload-payment-url") || "").trim();

        if (bookingCodeInput) {
            bookingCodeInput.addEventListener("input", function () { setLookupError(""); });
        }

        if (verifyInput) {
            verifyInput.addEventListener("input", function () {
                verifyInput.value = sanitizeLastFour(verifyInput.value);
                setVerifyError("");
            });
            verifyInput.addEventListener("keydown", function (e) {
                if (e.key === "Enter") { e.preventDefault(); submitLookup(); }
            });
        }

        modalCloseButtons.forEach(function (btn) {
            btn.addEventListener("click", closeVerifyModal);
        });

        uploadPaymentCloseButtons.forEach(function (btn) {
            btn.addEventListener("click", closeUploadPaymentModal);
        });

        if (openVerifyButton) {
            openVerifyButton.addEventListener("click", function (e) { e.preventDefault(); openVerifyModal(); });
        }

        form.addEventListener("submit", function (e) {
            e.preventDefault();
            if (typeof form.reportValidity === "function" && !form.reportValidity()) return;
            openVerifyModal();
        });

        if (verifySubmitButton) {
            verifySubmitButton.addEventListener("click", submitLookup);
        }

        if (uploadPaymentForm) {
            uploadPaymentForm.addEventListener("submit", function (e) {
                e.preventDefault();
                submitPaymentProof();
            });
        }

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                if (modal && !modal.hidden) closeVerifyModal();
                if (uploadPaymentModal && !uploadPaymentModal.hidden) closeUploadPaymentModal();
            }
        });

        var statusTabs = document.querySelector("[data-status-tabs]");
        if (statusTabs) {
            var tabBtns = statusTabs.querySelectorAll("[data-status-tab]");
            var tabPanels = statusTabs.querySelectorAll("[data-status-panel]");

            function setActiveStatusTab(tabName) {
                tabBtns.forEach(function (btn) {
                    var isActive = btn.getAttribute("data-status-tab") === tabName;
                    btn.classList.toggle("is-active", isActive);
                });
                tabPanels.forEach(function (panel) {
                    var isActive = panel.getAttribute("data-status-panel") === tabName;
                    panel.classList.toggle("is-active", isActive);
                    panel.hidden = !isActive;
                });
            }

            tabBtns.forEach(function (btn) {
                btn.addEventListener("click", function () {
                    setActiveStatusTab(btn.getAttribute("data-status-tab"));
                });
            });

            setActiveStatusTab("info");
        }

        var refreshBtn = document.getElementById("btn_refresh_status");
        if (refreshBtn) {
            refreshBtn.addEventListener("click", function () {
                if (currentPayload) {
                    refreshBtn.disabled = true;
                    refreshBtn.textContent = "Memuat ulang...";
                    submitLookup();
                    setTimeout(function () {
                        refreshBtn.disabled = false;
                        refreshBtn.textContent = "Refresh Data Booking";
                    }, 1500);
                } else {
                    alert("Data booking belum dimuat. Cari booking terlebih dahulu.");
                }
            });
        }
    });
})();
