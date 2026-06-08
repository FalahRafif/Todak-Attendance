(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('profile_image');
        var preview = document.getElementById('profile-image-preview');

        if (!(fileInput instanceof HTMLInputElement) || !(preview instanceof HTMLImageElement)) {
            return;
        }

        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

            if (!file) {
                return;
            }

            var objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.onload = function () {
                URL.revokeObjectURL(objectUrl);
            };
        });
    });
})();
