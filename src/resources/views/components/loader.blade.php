<style>
    #loader img {
        width: 150px;
    }

    #loader {
        position: fixed;
        width: 100%;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.58);
        z-index: 9999;
        user-select: none;
        justify-content: center;
        align-content: center;
        display: none;
    }

</style>


<div id="loader">
    <img src="{{ asset('assets/dashboard/image/spinner-1.svg') }}" alt="Loader">
</div>

@pushonce('scriptpush')
    <script>
        $("form").submit(function () {
            $("#loader").css('display', 'flex');
        })
    </script>
@endpushonce
