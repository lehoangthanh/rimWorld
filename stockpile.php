<?php
/**
 * Created by PhpStorm.
 * User: Le.Thanh
 * Date: 4/17/2018
 * Time: 1:28 PM
 */

const fileTMP = './assets/file-tmp/file.rws';

if(!file_exists('./assets/file-tmp')){
    mkdir('./assets/file-tmp');
}


if(!file_exists(fileTMP)){
    $myfile = fopen(fileTMP, "w");
    fclose($myfile);
}
if ( ! session_id() ) @ session_start();
$arrStockPile = $arrThingComps = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The request is using the POST method
    $mode = $_POST['mode'];
    $arrHuman = array();
    switch ($mode) {
        case'read-file':{
            $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
            $contents = file_get_contents($_FILES['file_save']['tmp_name']);

            preg_match_all('/\<li Class="Zone_Stockpile">(.*?)<\/settings>(.*?)<\/li>/s', $contents, $stockpileMatches);


            foreach($stockpileMatches[0] as $key=>$stockPile){
                preg_match_all('/\<label>(.*?)<\/label>/s', $stockPile, $labelMatches);
                preg_match_all('/\<color>(.*?)<\/color>/s', $stockPile, $colorMatches);

                $label = $labelMatches[1][0];
                $color = $colorMatches[1][0];
                $tmpColor = $color;
                $tmpColor = str_replace('(','',$tmpColor);
                $tmpColor = str_replace(')','',$tmpColor);
                $tmpColor = str_replace('RGBA','',$tmpColor);
                $arrTmpColor = explode(',',$tmpColor);

                $cssColor = 'rgba('.($arrTmpColor[0]*100).','.(($arrTmpColor[1]*100) -20).','.($arrTmpColor[2]*100).','.($arrTmpColor[3]*10).')';
                $arrStockPile[$key] = array('label'=>$label, 'color'=>$color, 'css-color'=>$cssColor, 'data'=>array());


                preg_match_all('/\<cells>(.*?)<\/cells>/s', $stockPile, $cellsMatches);
                preg_match_all('/\<li>(.*?)<\/li>/s', $cellsMatches[1][0], $posMatches);
                foreach($posMatches[1] as $pos){
                    stockpile::getWHbyPos($pos,$width,$height);
                    $arrStockPile[$key]['data'][$width][] = $height;

                }

            }
            preg_match_all('/\<thing Class="ThingWithComps">(.*?)<\/thing>/s', $contents, $thingCompsMatches);
            foreach($thingCompsMatches[1] as $key=>$thing){
                preg_match_all('/\<id>(.*?)<\/id>/s', $thing, $idMatches);
                preg_match_all('/\<def>(.*?)<\/def>/s', $thing, $defMatches);
                preg_match_all('/\<pos>(.*?)<\/pos>/s', $thing, $posMatches);
                preg_match_all('/\<stackCount>(.*?)<\/stackCount>/s', $thing, $stackCountMatches);

                $id = $idMatches[1][0];
                $sDef = $defMatches[1][0];
                $pos = $posMatches[1][0];
                $stackCount = $stackCountMatches[1][0];

                stockpile::getWHbyPos($pos,$width, $height);
                $xml = $thingCompsMatches[0][$key];
                if(array_key_exists($sDef,$arrThingComps[$width][$height])){

                    $oldStackCount = $arrThingComps[$width][$height][$sDef]['stack-count'];
                    $stackCount+= $oldStackCount;
                    $arrThingComps[$width][$height][$sDef]= array('id'=>$id,'def'=>$sDef,'pos'=>$pos, 'stack-count'=>$stackCount,'xml'=>$xml);

                }else{
                    $arrThingComps[$width][$height][$sDef]= array('id'=>$id,'def'=>$sDef,'pos'=>$pos, 'stack-count'=>$stackCount,'xml'=>$xml);
                }


            }
//            <thing Class="Medicine">

            $_SESSION['data-read-new-file'] = $contents;
            $_SESSION['data-resource'] = $researchValueMatches[1];
            break;
        }
        case 'add-more': {

//            $formFileName = $_SESSION['form-file-name'];
//
//            $arrResource = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
//            $fileContent = isset($_SESSION['data-read-new-file']) ? $_SESSION['data-read-new-file'] : '';
//
//            preg_match_all('/\<researchManager>(.*?)<\/researchManager>/s', $fileContent, $matches);
//            preg_match_all('/\<progress>(.*?)<\/progress>/s', $matches[1][0], $processMatches);
//            preg_match_all('/\<keys>(.*?)<\/keys>/s', $processMatches[1][0], $keyMatches);
//            preg_match_all('/\<values>(.*?)<\/values>/s', $processMatches[1][0], $valueMatches);
//
//
//            foreach($_POST['research-value'] as $key=>$resource){
//                $value .= "<li>$resource</li>";
//                $value .= (($key+1) < count($_POST['research-value'])) ? "\r\n\t\t\t\t\t" : "";
//
//            }
//            $reValue = "<values>\r\n\t\t\t\t\t$value\r\n\t\t\t\t</values>";
//            $fileContent = str_replace($valueMatches[0],$reValue,$fileContent);
//
//            file_put_contents(fileTMP,$fileContent);
//
//            header('Content-Description: File Transfer');
//            header('Content-Type: application/octet-stream');
//            header('Content-Disposition: attachment; filename="'.$formFileName.'"');
//            header('Expires: 0');
//            header('Cache-Control: must-revalidate');
//            header('Pragma: public');
//            header('Content-Length: ' . filesize(fileTMP));
//            readfile(fileTMP);

            unlink(fileTMP);
            break;
        }

    }
}
class stockpile{
    public static function getWHbyPos($pos, &$width, &$height){
        $pos = str_replace('(','',$pos);
        $pos = str_replace(')','',$pos);
        $arrPos = explode(',',$pos);
        $height = intval($arrPos[0]);
        $width = intval($arrPos[2]);
    }
}

?>
<!DOCTYPE html>
<html>
<?php include_once 'header-file-input.php'?>
<body>

<?php include_once 'menu.php'?>

<div class="container">

    <form enctype="multipart/form-data" method="post">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="panel-title">Cheat People Skills</div>
            </div>
            <div class="panel-body">
                <label for="basic-url">Choose your file save</label>
                <?php include_once 'tool-tip-copy-path-name.php'?>
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
    if($arrStockPile){

        ?>
        <form id="f-save-new-file-human" method="post">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        <label>Humans</label>
                        <!--                            <a href="javascript:void(0);" class="btn btn-primary" data-action="update-human" data-mode="up-skills">Up Skills</a>-->
                        <button class="btn btn-primary">Up Skills</button>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="float: right;padding-bottom: 10px;">
                        <!--                        Up all to-->
                        <input type="text" value="10000" id="all-value-to"/>
                        <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up</a>

                    </div>
                    <?php foreach($arrStockPile as $stockpile){?>
                    <p class="stockpile-name" style="background-color: <?php echo $stockpile['css-color']?>"> <?php echo $stockpile['label']. ' - '.$stockpile['color'] ?></p>
                    <table class="table table-bordered table-responsive table-hover table-striped" id="table-resource">
                        <tbody>

                            <?php foreach($stockpile['data'] as $width=>$arrWidth){?>
                                <tr>
                                <?php foreach($arrWidth as $height){?>
                                    <td class="box-15">
                                        <?php if(array_key_exists($height,$arrThingComps[$width])) { ?>

                                            <?php foreach($arrThingComps[$width][$height] as $thingComps){?>
                                                <div>
                                                    <img class="img-resource"
                                                         alt="<?php echo $thingComps['def'] ?>"
                                                         title="<?php echo $thingComps['id'] ?>"
                                                         src="./assets/images/32px-<?php echo strtoupper($thingComps['def']).'.png' ?>"/>
                                                    <span class="badge"><?php echo $thingComps['stack-count'] ?></span>
                                                </div>

                                            <?php }?>

                                        <?php }?>

                                    </td>
                                <?php }?>
                                </tr>
                            <?php }?>
                        </tbody>
                    </table>
                    <?php }?>
                </div>
                <div class="panel-footer">
                    <!--                    <button class="btn btn-primary">Save File</button>-->
                    <input type="hidden" name="mode" id="mode" value="save-new-file"/>
                </div>
            </div>
        </form>
        <?php
    } //end if($arrResource)
    ?>

</div>

<script src="./assets/jquery/js/jquery-3.2.1.min.js"></script>
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
