<script type="text/javascript">
    var toastMixin = Swal.mixin({
        toast: true,
        animation: false,
        position: 'top-right',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    @if(\Session::get('type') == 'success')
    toastMixin.fire({
        icon: 'success',
        animation: true,
        title: '{{\Session::get("message")}}'
    });
    @endif
    @if(\Session::get('type') == 'error')
    toastMixin.fire({
        icon: 'error',
        animation: true,
        title: '{{\Session::get("message")}}'
    });
    @endif
</script>