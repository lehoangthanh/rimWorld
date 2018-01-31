<?php
/**
 * Created by PhpStorm.
 * User: lehoa
 * Date: 08-Jul-17
 * Time: 1:10 PM
 */
const fileTMP = './assets/file-tmp/file.rws';
if(!file_exists(fileTMP)){
    $myfile = fopen(fileTMP, "w");
    fclose($myfile);
}
if ( ! session_id() ) @ session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The request is using the POST method
    $mode = $_POST['mode'];
    $arrResource = array();
    switch ($mode) {
        case'read-file':{
            $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
            $contents = file_get_contents($_FILES['file_save']['tmp_name']);

            preg_match_all('/\<thing Class="ThingWithComps">(.*?)<\/thing>/s', $contents, $matches);
            foreach($matches[0] as $key=>$match){

                preg_match_all('/\<id>(.*?)<\/id>/s', $match, $idMatches);
                $id = $idMatches[1][0];

                preg_match_all('/\<def>(.*?)<\/def>/s', $match, $defMatches);
                $sDef = $defMatches[1][0];
                if(preg_match('/^Raw*/',$sDef) === 1){continue;}
//                if(preg_match('/^Meal*/',$sDef) === 1){continue;}
                if(preg_match('/^MeleeWeapon*/',$sDef) === 1){continue;}
                if(preg_match('/^WoodLog*/',$sDef) === 1){continue;}
                if(preg_match('/^Bow*/',$sDef) === 1){continue;}

                preg_match_all('/\<stackCount>(.*?)<\/stackCount>/s', $match, $stackCountMatches);
                $stackCount = $stackCountMatches[1][0];

                $arrResource[$id] = array('id'=>$id, 'def'=>$sDef, 'stackCount'=>$stackCount,'match'=>$match);

            }
            $_SESSION['data-read-new-file'] = $contents;

            $_SESSION['data-resource'] = $arrResource;
            break;
        }
        case 'save-new-file': {
            set_time_limit(-1);
            $formFileName = $_SESSION['form-file-name'];

            $arrResource = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
            $fileContent = isset($_SESSION['data-read-new-file']) ? $_SESSION['data-read-new-file'] : '';

            foreach($arrResource as $key=>$resource){
                if(!isset($_POST[$key])){continue;}
                if(!array_key_exists('match',$resource)){continue;}

                $value = intval($_POST[$key]);
                $match = $resource['match'];
                $newMatch = preg_replace('/\<stackCount>(.*?)<\/stackCount>/s',"<stackCount>$value</stackCount>",$match);
                $fileContent = str_replace($match,$newMatch,$fileContent);

            }

            file_put_contents(fileTMP,$fileContent);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$formFileName.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize(fileTMP));
            readfile(fileTMP);
            unlink(fileTMP);
            break;
        }

    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rimworld</title>
    <meta charset="UTF-8">

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="./assets/bootstrap-3.3.7-dist/css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="./assets/bootstrap-3.3.7-dist/css/bootstrap-theme.min.css">



</head>
<body>

<?php include_once 'menu.php'?>

<div class="container">
    <form enctype="multipart/form-data" method="post">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <div class="panel-title">Rim World Cheat Save File</div>
        </div>
        <div class="panel-body">
            <label for="basic-url">Choose your file save</label>
            <p >C:\Users\***\AppData\LocalLow\Ludeon Studios\RimWorld by Ludeon Studios\Saves</p>
            <div class="input-group">
                <input type="file" name="file_save" class="form-control" aria-describedby="file-save">
            </div>
        </div>
        <div class="panel-footer">
            <button class="btn btn-primary">Read File</button>
            <input type="hidden" name="mode" value="read-file"/>
        </div>
    </div>
    </form>

    <?php
    if($arrResource){

    ?>
    <form name="f-save-new-file" method="post">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">
                    <label>Resource</label>
                    <button class="btn btn-primary">Save File</button>
                </div>
            </div>
            <div class="panel-body">
                <div style="float: right;padding-bottom: 10px;">
                    Up all to
                    <input type="text" value="500" id="all-value-to"/>
                    <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up</a>
                </div>
                <table class="table table-bordered table-responsive table-hover" id="table-resource">
                    <thead>
                        <th>ID</th>
                        <th>Def</th>
                        <th>Stack Count</th>
                    </thead>
                    <tbody>
                    <?php foreach($arrResource as $resource){?>
                        <tr>
                            <td><?php echo $resource['id'] ?></td>
                            <td><?php echo $resource['def'] ?></td>
                            <td><input name="<?php echo $resource['id'] ?>" value="<?php echo $resource['stackCount'] ?>"/></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>

            </div>
            <div class="panel-footer">
                <button class="btn btn-primary">Save File</button>
                <input type="hidden" name="mode" value="save-new-file"/>
            </div>
        </div>
    </form>
    <?php
    } //end if($arrResource)
    ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="./assets/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>

<script>
    $(function(){
        $('#btn-up-value-to').click(function(e){
            var val = $('#all-value-to').val();
            $('#table-resource tbody input').val(val);
        })
    })
</script>
</body>
</html>
