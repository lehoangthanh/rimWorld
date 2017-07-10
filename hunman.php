<?php
/**
 * Created by PhpStorm.
 * User: lehoa
 * Date: 08-Jul-17
 * Time: 1:10 PM
 */
const fileTMP = './assets/file-tmp/file.rws';
if ( ! session_id() ) @ session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The request is using the POST method
    $mode = $_POST['mode'];
    $arrHunman = array();
    switch ($mode) {
        case'read-file':{
            $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
            $contents = file_get_contents($_FILES['file_save']['tmp_name']);

            preg_match_all('/\<thing Class="Pawn">(.*?)<\/thing>/s', $contents, $matches);

            foreach($matches[0] as $key=>$match){

                /**
                 * bản php này bị điên không nhận dc partten này
                 * /<thing Class="Pawn">(.*)<def>Human<\/def>(.*?)<\/thing>/s
                 */
                preg_match_all('/\<def>(.*?)<\/def>/s', $match, $defMatches);
                $sDef = $defMatches[1][0];
                if(strcmp($sDef,'Human')!= 0){continue;}


                preg_match_all('/\<id>(.*?)<\/id>/s', $match, $idMatches);
                $id = $idMatches[1][0];

                preg_match_all('/\<kindDef>(.*?)<\/kindDef>/s', $match, $kindDefMatches);
                $sKindDef = $kindDefMatches[1][0];

                preg_match_all('/\<name Class="NameTriple">(.*?)<\/name>/s', $match, $arrNameMatches);
                $arrName = $arrNameMatches[1][0];

                preg_match_all('/\<first>(.*?)<\/first>/s', $arrName, $firstNameMatches);
                $firstName = $firstNameMatches[1][0];

                preg_match_all('/\<last>(.*?)<\/last>/s', $arrName, $lastNameMatches);
                $lastName = $lastNameMatches[1][0];

                preg_match_all('/\<nick>(.*?)<\/nick>/s', $arrName, $nickNameMatches);
                $nickName = $nickNameMatches[1][0];

                $name = "$firstName $lastName $nickName";

                preg_match_all('/\<skills>(.*?)<\/skills>/s', $match, $skillsMatches);
                $skills = $skillsMatches[1][0];

                preg_match_all('/\<li>(.*?)<\/li>/s', $skills, $skillsChildMatches);

                $arrHunman[$id] = array('id'=>$id, 'kindDef'=>$sKindDef, 'name'=>$name,'skills'=>array());
                foreach($skillsChildMatches[0] as $keySkill=>$skill){
                    preg_match_all('/\<def>(.*?)<\/def>/s', $skill, $skillDefMatches);

                    $sKillDef = $skillDefMatches[1][0];

                    preg_match_all('/\<level>(.*?)<\/level>/s', $skill, $skillLevelMatches);
                    $sSkillLevel = $skillLevelMatches[1][0];

                    $arrHunman[$id]['skills'][] = array('def'=>$sKillDef,'level'=>$sSkillLevel);
                }

//                $arrResource[$id] = array('id'=>$id, 'def'=>$sDef, 'stackCount'=>$stackCount,'match'=>$match);

            }
            $_SESSION['data-read-new-file'] = $contents;

            $_SESSION['data-resource'] = $arrResource;
            break;
        }
        case 'save-new-file': {

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

            file_put_contents('../assets/file-tmp/file.rws','');
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
    if($arrHunman){

        ?>
        <form name="f-save-new-file" method="post">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        <label>Hunmans</label>
<!--                        <button class="btn btn-primary">Save File</button>-->
                    </div>
                </div>
                <div class="panel-body">
                    <div style="float: right;padding-bottom: 10px;">
<!--                        Up all to-->
<!--                        <input type="text" value="500" id="all-value-to"/>-->
<!--                        <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up</a>-->

                        <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up-Skills</a>
                    </div>
                    <table class="table table-bordered table-responsive table-hover" id="table-resource">
                        <thead>
                        <th>ID</th>
                        <th>name</th>
                        <th>kindDef</th>
                        <th>Skills</th>
                        </thead>
                        <tbody>
                        <?php foreach($arrHunman as $hunman){?>
                            <tr>
                                <td><?php echo $hunman['id'] ?></td>
                                <td><?php echo $hunman['name'] ?></td>
                                <td><?php echo $hunman['kindDef'] ?></td>
                               <td>
                                   <table class="table table-bordered table-responsive table-hover" id="table-resource">
                                       <tbody>
                                       <?php foreach($hunman['skills'] as $skill){?>
                                           <tr>
                                               <td><?php echo $skill['def'] ?></td>
                                               <td><input name="<?php echo $skill['def'] ?>" value="<?php echo $skill['level'] ?>"/></td>
                                           </tr>
                                       <?php }?>
                                       </tbody>
                                   </table>
                               </td>

                            </tr>
                        <?php }?>
                        </tbody>
                    </table>

                </div>
                <div class="panel-footer">
<!--                    <button class="btn btn-primary">Save File</button>-->
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
