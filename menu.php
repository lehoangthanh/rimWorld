<?php
/**
 * Created by PhpStorm.
 * User: thanh.lh
 * Date: 31-Jan-18
 * Time: 14:13
 */

?>
<style>
    .image img {
        -webkit-transition: all 1s ease; /* Safari and Chrome */
        -moz-transition: all 1s ease; /* Firefox */
        -ms-transition: all 1s ease; /* IE 9 */
        -o-transition: all 1s ease; /* Opera */
        transition: all 1s ease;
    }

    .image:hover img {
        -webkit-transform:scale(1.25); /* Safari and Chrome */
        -moz-transform:scale(1.25); /* Firefox */
        -ms-transform:scale(1.25); /* IE 9 */
        -o-transform:scale(1.25); /* Opera */
        transform:scale(1.25);
    }

    .crop-text-by-dots{
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 325px;
    }
</style>
<link rel="stylesheet" href="./assets/bootstrap-3.3.7-dist/css/navbar-fixed-top.css">
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div>
                <a class="navbar-brand" href="/"><img class="image img-responsive center-block" src="assets/images/follow-me-on-fb-01.jpg" style="height: 40px;position: relative;bottom: 10px;"></a>
            </div>

        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="/index.php">Resource</a></li>
                <li><a href="/people.php">People</a></li>
<!--                <li><a href="#contact">Contact</a></li>-->
<!--                <li class="dropdown">-->
<!--                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>-->
<!--                    <ul class="dropdown-menu">-->
<!--                        <li><a href="#">Action</a></li>-->
<!--                        <li><a href="#">Another action</a></li>-->
<!--                        <li><a href="#">Something else here</a></li>-->
<!--                        <li role="separator" class="divider"></li>-->
<!--                        <li class="dropdown-header">Nav header</li>-->
<!--                        <li><a href="#">Separated link</a></li>-->
<!--                        <li><a href="#">One more separated link</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <a class="navbar-brand crop-text-by-dots" href="/" style="">RimWorld Version 0.16.1393 rev533</a>
<!--                <li><a href="../navbar/">Default</a></li>-->
<!--                <li><a href="../navbar-static-top/">Static top</a></li>-->
<!--                <li class="active"><a href="./">Fixed top <span class="sr-only">(current)</span></a></li>-->
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>
