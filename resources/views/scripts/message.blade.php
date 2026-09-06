{{-- A redirect's flash message, shown as a toast in the corner. --}}
@if(\Session::has('message'))
<script type="text/javascript">
    (function () {
        var type = @json(\Session::get('type', 'info'));
        var message = @json(\Session::get('message'));
        var show = function () {
            (window.shoporaToast?.[type] ?? window.shoporaToast?.info)?.(message);
        };

        // The flash partial is included before the toast script on some pages.
        if (window.shoporaToast) show();
        else document.addEventListener('DOMContentLoaded', show);
    })();
</script>
@endif
