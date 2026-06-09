<script>
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            const keyword = this.value.toLowerCase();
            document.querySelectorAll('#' + this.dataset.tableSearch + ' tbody tr').forEach(function (row) {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        });
    });
</script>
