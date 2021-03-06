<head>
    <title>Rimworld Ver 0.16</title>
    <meta charset="UTF-8">
<style>
    .fa-copy:hover{
        color: #2e6da4;
        cursor: pointer;
    }
</style>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="./assets/AlertifyJS/build/css/alertify.css">
<link rel="stylesheet" href="./assets/bootstrap-3.3.7-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="./assets/css/style.css">

<!-- Optional theme -->
<link rel="stylesheet" href="./assets/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">

    <script src="./assets/jquery/js/jquery-3.2.1.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="./assets/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>
    <script src="./assets/AlertifyJS/build/alertify.js"></script>

<script defer src="./assets/fontawesome/v5.0.6-all.js"></script>

<script type="text/javascript">
    $(function(){
        $('[data-toggle="tooltip"]').hover(
            // hover on
            function(){
                $(this).tooltip('show');
            },
            // hover off
            function(){
                $(this).tooltip('hide');
            }
        );

        $('[data-action="copy-path-save"]').click(function(){

            $(this).attr('title','Copied')
                .tooltip('fixTitle')
                .tooltip('show');

            $('#path-name-content').css({color:'#2e6da4','font-style': 'italic'});

            var _pathName = $(this).data('value');

            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(_pathName).select();
            document.execCommand("copy");
            $temp.remove();


        });
    })
</script>
</head>