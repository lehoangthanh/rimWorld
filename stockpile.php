<?php
/**
 * Created by PhpStorm.
 * User: Le.Thanh
 * Date: 4/17/2018
 * Time: 1:28 PM
 */
include_once './constant.php';
$arrStockPile = $arrThingComps = array();

    // The request is using the POST method
    $mode = $_POST['mode'];
    $mode = ($mode) ? $mode : 'read-file';
    $arrHuman = array();
    switch ($mode) {
        case'read-file':{
            $_SESSION['full-thing-compos'] = array();
            if($_FILES['file_save']){
                $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
                $content = file_get_contents($_FILES['file_save']['tmp_name']);
            }else{
                $content = $_SESSION['data-resource'];
            }

            preg_match_all('/\<li Class="Zone_Stockpile">(.*?)<\/settings>(.*?)<\/li>/s', $content, $stockpileMatches);


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
            ksort($arrStockPile[$key]['data']);
            foreach($arrStockPile[$key]['data'] as $width=>$stockPile){
                asort($arrStockPile[$key]['data'][$width]);
            }

//            asort($arrStockPile[$key]['data']);
            preg_match_all('/\<thing Class="ThingWithComps">(.*?)<\/thing>/s', $content, $thingCompsMatches);
            stockpile::proccessThingComps($thingCompsMatches[1], $thingCompsMatches[0],$arrThingComps);


            preg_match_all('/\<thing Class="Medicine">(.*?)<\/thing>/s', $content, $thingCompsMatches);
            stockpile::proccessThingComps($thingCompsMatches[1],$thingCompsMatches[0],$arrThingComps);

//            preg_match_all('/\<thing>(.*?)<def>(Chunk)(.*?)<\/thing>/s', $content, $thingCompsMatches);
//            stockpile::proccessThingComps($thingCompsMatches[0],$thingCompsMatches[0],$arrThingComps);

            $_SESSION['data-resource'] = $content;
            $_SESSION['data-thing-comps'] = $arrThingComps;
            break;
        }
        case 'add-more-resource': {
            $data = $_POST['data'];
            stockpile::addMoreResource($data);
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'success!!!'));
            die;

        }
        case 'reset-resource': {
            $data = $_POST['data'];
            stockpile::resetResource($data);
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'success!!!'));
            die;
        }
        case 'delete-resource': {
            $data = $_POST['data'];
            stockpile::resetResource($data, true);
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'success!!!'));
            die;
        }

        case 'reset-health':{
            $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
            $arrThingComps = ($_SESSION['full-thing-compos']) ? $_SESSION['full-thing-compos'] : array();

            foreach($arrThingComps as $thingComp) {
                $health = $thingComp['health'];
                if ($health == 0) continue;
                $xml = $thingComp['xml'];
                $oldXml = $xml;

                $xml = str_replace("<health>$health</health>", '<health>100000000</health>', $xml);
                $content = str_replace($oldXml, $xml, $content, $countReplace);

            }

            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            $_SESSION['data-resource'] = $content;
            echo json_encode(array('result'=>'success!!!'));
            die;
        }

    }



class stockpile{
    /**
     * @param $pos
     * @param $width
     * @param $height
     * @return void
     */
    public static function getWHbyPos($pos, &$width, &$height){
        $pos = str_replace('(','',$pos);
        $pos = str_replace(')','',$pos);
        $arrPos = explode(',',$pos);
        $height = intval($arrPos[0]);
        $width = intval($arrPos[2]);
    }

    /**
     * @param $data
     * @return void
     */
    public static function addMoreResource($data){
        $id = $data['id'];
        $noWant = intval($data['no']);
        list($width,$height,$def,$id) = explode(',',$id);
        $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
        $arrThingComps = ($_SESSION['data-thing-comps']) ? $_SESSION['data-thing-comps'] : array();

        $thingComps = $arrThingComps[$width][$height][$def];
        $oldXml = $thingComps['xml'];
        preg_match_all('/\<stackCount>(.*?)<\/stackCount>/s', $oldXml, $stackCountMatches);
        $stackCount = $stackCountMatches[1][0];
        $xml = str_replace("<stackCount>$stackCount</stackCount>",'<stackCount>500</stackCount>',$oldXml);

        $idInt = intval(str_replace($def,'',$id));
        $tmpXml = '';
        $max = $idInt+$noWant;
        for($idInt; $idInt <= $max; $idInt++){
            $newId = $def.$idInt;
            $tmpXml .= str_replace("<id>$id</id>","<id>$newId</id>",$xml);
        }
        $content = str_replace($oldXml,$tmpXml,$content, $countReplace);
        $_SESSION['data-resource'] = $content;
    }

    /**
     * @param $data
     * @param bool $delResource
     * @return void
     */
    public static function resetResource($data, $delResource = false){
        $content = $_SESSION['data-resource'];
        $id = $data['id'];
        list($width,$height,$def,$id) = explode(',',$id);
        $arrThingComps = ($_SESSION['data-thing-comps']) ? $_SESSION['data-thing-comps'] : array();

        $thingComps = $arrThingComps[$width][$height][$def];
        $oldXml = $thingComps['xml'];
        $fullXml = $thingComps['full-xml'];


        preg_match_all('/\<stackCount>(.*?)<\/stackCount>/s', $oldXml, $stackCountMatches);
        $stackCount = $stackCountMatches[1][0];
        $oldXml = str_replace("<stackCount>$stackCount</stackCount>",'<stackCount>500</stackCount>',$oldXml);

        preg_match_all('/\<thing Class="ThingWithComps">(.*?)<\/thing>/s', $fullXml, $thingComposMatches);
        foreach($thingComposMatches[0] as $key=>$thingCompos){

            if($key == (count($thingComposMatches[0]) -1) && !$delResource){
                $content = str_replace($thingCompos, $oldXml, $content,$countReplace);
            }else{
                $content = str_replace($thingCompos, '', $content,$countReplace);
            }

        }
        $_SESSION['data-resource'] = $content;
        stockPile::addMoreResource($data);
    }

    /**
     * @param $thingCompsMatchesValue
     * @param $thingCompsMatchesFull
     * @param $arrThingComps
     */
    static function proccessThingComps($thingCompsMatchesValue, $thingCompsMatchesFull, &$arrThingComps){

        foreach($thingCompsMatchesValue as $key=>$thing){
            preg_match_all('/\<id>(.*?)<\/id>/s', $thing, $idMatches);
            preg_match_all('/\<def>(.*?)<\/def>/s', $thing, $defMatches);
            preg_match_all('/\<pos>(.*?)<\/pos>/s', $thing, $posMatches);
            preg_match_all('/\<stackCount>(.*?)<\/stackCount>/s', $thing, $stackCountMatches);
            preg_match_all('/\<health>(.*?)<\/health>/s', $thing, $healthMatches);

            $id = $idMatches[1][0];
            $sDef = $defMatches[1][0];
            $pos = $posMatches[1][0];
            $stackCount = $stackCountMatches[1][0];
            $health = intval($healthMatches[1][0]);

            stockpile::getWHbyPos($pos,$width, $height);
            $xml = $thingCompsMatchesFull[$key];
            $fullXml = $xml;
            if(array_key_exists($sDef,$arrThingComps[$width][$height])){
                $oldStackCount = $arrThingComps[$width][$height][$sDef]['stack-count'];
                $oldHealth = $arrThingComps[$width][$height][$sDef]['health'];
                $health = ($health > $oldHealth) ? $health : $oldHealth;
                $arrThingComps[$width][$height][$sDef]['health'] = $health;
                $stackCount+= $oldStackCount;

                $fullXml = $arrThingComps[$width][$height][$sDef]['full-xml'];
                $fullXml .= $xml;
            }else{

                if($health > 0) {
                    $thingComps = array('id' => $id, 'def' => $sDef, 'pos' => $pos, 'stack-count' => $stackCount, 'health' => $health, 'xml' => $xml, 'full-xml' => $fullXml);
                    $_SESSION['full-thing-compos'] []= $thingComps;
                }
            }

            $thingComps = array('id'=>$id,'def'=>$sDef,'pos'=>$pos, 'stack-count'=>$stackCount, 'health'=>$health, 'xml'=>$xml, 'full-xml' => $fullXml);
            $arrThingComps[$width][$height][$sDef]= $thingComps;

        }
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
                <div class="panel-title">Resource Management</div>
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
                        <label>Stockpile List</label>
                        <a href="javascript:void(0);" class="btn btn-warning" data-action="reset-health" >Reset Health</a>
                    </div>
                </div>
                <div class="panel-body">
                    <div style="float: right;padding-bottom: 10px;">
                        <!--                        Up all to-->
<!--                        <input type="text" value="10000" id="all-value-to"/>-->
<!--                        <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up</a>-->

                    </div>
                    <?php foreach($arrStockPile as $stockpile){?>
                    <p class="stockpile-name" style="background-color: <?php echo $stockpile['css-color']?>"> <?php echo $stockpile['label']. ' - '.$stockpile['color'] ?></p>
                    <table class="table table-bordered table-responsive table-hover table-striped">
                        <tbody>

                            <?php foreach($stockpile['data'] as $width=>$arrWidth){?>
                                <tr>
                                <?php foreach($arrWidth as $height){?>
                                    <td class="box-15">
                                        <?php echo "($height, 0, $width) "; ?>
                                        <?php if(array_key_exists($height,$arrThingComps[$width])) { ?>

                                            <?php foreach($arrThingComps[$width][$height] as $thingComps){?>
                                                <div class="thing-comps-item">
                                                    <?php $fileName = "./assets/images/32px-".strtoupper($thingComps['def']).'.png'; ?>
                                                    <?php if (file_exists($fileName)){ ?>
                                                    <img class="img-resource"
                                                         alt="<?php echo $thingComps['def'] ?>"
                                                         title="<?php echo $thingComps['id'] ?>"
                                                         src="<?php echo $fileName ?>"/>
                                                    <?php } else{ ?>
                                                        <label><?php echo"32px-".strtoupper($thingComps['def']) ?></label>
                                                    <?php } ?>
                                                    <span class="badge"><?php echo $thingComps['stack-count'] ?></span>
                                                    <span style="<?php echo ($thingComps['health'] <50 ) ? 'color:red' : '' ?>">
                                                        <?php echo $thingComps['health']?>
                                                    </span>
                                                    <span class="stockpile-add-more" data-action="add-more"
                                                        data-value='<?php echo "$width,$height,".$thingComps['def'].','.$thingComps['id'] ?>'>
                                                        <i class="fas fa-plus-square"></i>
                                                    </span>
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
                    <input type="hidden" name="mode" id="mode" value="download-resource"/>
                </div>
            </div>
        </form>
        <?php
    } //end if($arrResource)
    ?>

</div>

<!-- Modal -->
<div class="modal fade" id="addMoreResourceModal" tabindex="-1" role="dialog" aria-labelledby="addMoreResourceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add More Resource</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1">How many Loop 75 ?</span>
                    <input type="text" class="form-control"  id="add-more-resource-no" aria-describedby="basic-addon1">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="add-more-resource-ajax" class="btn btn-primary">Save changes</button>
                <button type="button" id="reset-resource-ajax" class="btn btn-warning">Reset Resource</button>
                <button type="button" id="delete-resource-ajax" class="btn btn-danger">Delete Resource</button>
            </div>
        </div>
    </div>
</div>

<script src="./assets/jquery/js/jquery-3.2.1.min.js"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="./assets/bootstrap-3.3.7-dist/js/bootstrap.min.js"></script>

<script>
    $(function(){
        var _idChose = '';
        $('tbody').on('click','span[data-action="add-more"]',function(){
            _idChose = $(this).data('value');
            $('#addMoreResourceModal').modal('show');
        });

        $('#add-more-resource-ajax').click(function(){
            sendRequest('add-more-resource');

        });

        $('#reset-resource-ajax').click(function(){
            if(!confirm('Are You Sure ????'))return;
            sendRequest('reset-resource');
        });

        $('#delete-resource-ajax').click(function(){
            if(!confirm('Are You Sure ????'))return;
            sendRequest('delete-resource');
        });

        $('a[data-action="reset-health"]').click(function(){
            sendRequest('reset-health');

        });

        function sendRequest(_mode){
            var _moreResourceNo = $('#add-more-resource-no').val();
            var _data = {id:_idChose,no: _moreResourceNo};
            $.post('/stockpile.php',{data:_data, mode:_mode},function(response){
                response = JSON.parse(response);
                $('#add-more-resource-no').val('');
                $('#addMoreResourceModal').modal('hide');
                alertify.success(response.result);

            });
        }
    })
</script>

</body>
</html>
