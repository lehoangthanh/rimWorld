<?php
/**
 * Created by PhpStorm.
 * User: Le.Thanh
 * Date: 4/17/2018
 * Time: 1:28 PM
 */

const HEALTH_MAX = 1000;
include_once './constant.php';
$arrStockPile = $arrThingComps = array();

    // The request is using the POST method
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'read-file';
//    $mode = ($mode) ? $mode : 'read-file';
    $arrHuman = array();
    switch ($mode) {
        case'read-file':{
            $_SESSION['full-thing-compos'] = array();
            if(isset($_FILES['file_save'])){
                $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
                $content = file_get_contents($_FILES['file_save']['tmp_name']);
            }else{
                $content = $_SESSION['data-resource'];
            }
            if($content == null){
                break;
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

                $xml = str_replace("<health>$health</health>", '<health>'.HEALTH_MAX.'</health>', $xml);
                $content = str_replace($oldXml, $xml, $content, $countReplace);

            }

            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            $_SESSION['data-resource'] = $content;
            echo json_encode(array('result'=>'success!!!'));
            die;
        }

        case 'add-new-resource':{
            $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
            $resourceId = $_POST['data']['resource-id'];
            $pos = $_POST['data']['pos'];
            stockpile::addNewResource($resourceId, $pos);
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'success!!!'));
            die;

        }

        case 'add-all-resource':{
            $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
            $pos = $_POST['data']['pos'];
            stockpile::addAllResource($pos);
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'success!!!'));
            die;
        }

        case 'delete-stockpile':{
            $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
            $arrThingComps = ($_SESSION['data-thing-comps']) ? $_SESSION['data-thing-comps'] : array();
            $arrData = $_POST['data'];
            foreach($arrData as $data){
                $data = explode(',', $data);
                $width = $data[0];
                $height = $data[1];
                $thingComps = $arrThingComps[$width][$height];
                if($thingComps){
                    $thingComps = array_pop(array_reverse($thingComps));
                    stockpile::resetResourceWithThingCompos($thingComps,true);
                }
            }
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
            echo json_encode(array('result'=>'Delete Stockpile Success!!!'));
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
        $id = $data['id'];
        list($width,$height,$def,$id) = explode(',',$id);
        $arrThingComps = ($_SESSION['data-thing-comps']) ? $_SESSION['data-thing-comps'] : array();
        $thingComps = $arrThingComps[$width][$height][$def];
        self::resetResourceWithThingCompos($thingComps, $delResource);
        self::addMoreResource($data);
    }

    public static function resetResourceWithThingCompos($thingComps, $delResource){
        $content = $_SESSION['data-resource'];

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
            $health = ($healthMatches[1]) ? intval($healthMatches[1][0]) : 0;

            stockpile::getWHbyPos($pos,$width, $height);
            if($width == 0 || $height == 0){continue;}
            $xml = $thingCompsMatchesFull[$key];
            $fullXml = $xml;

            if(!isset($arrThingComps[$width][$height])){
                $thingComps = array('id'=>$id,'def'=>$sDef,'pos'=>$pos, 'stack-count'=>$stackCount, 'health'=>$health, 'xml'=>$xml, 'full-xml' => $fullXml);
                $arrThingComps[$width][$height][$sDef]= $thingComps;

                if ($health > 0) {
                    $thingComps = array('id' => $id, 'def' => $sDef, 'pos' => $pos, 'stack-count' => $stackCount, 'health' => $health, 'xml' => $xml, 'full-xml' => $fullXml);
                    $_SESSION['full-thing-compos'] [] = $thingComps;
                }
                $allThingResourcePath = "./assets/resource-form/all-things/$sDef.txt";
                if (!file_exists($allThingResourcePath)) {
                    $fp = fopen($allThingResourcePath, "wb");
                    if ($fp) {
                        $xmlTmp = str_replace("<stackCount>$stackCount</stackCount>", '<stackCount>500</stackCount>', $xml);
                        if ($health > 0) {
                            $xmlTmp = str_replace("<health>$health</health>", '<health>'.HEALTH_MAX.'</health>', $xmlTmp);
                        }
                        fwrite($fp, $xmlTmp);
                        fclose($fp);
                    }

                }

            }else {

                $oldStackCount = $arrThingComps[$width][$height][$sDef]['stack-count'];
                $oldHealth = $arrThingComps[$width][$height][$sDef]['health'];
                $health = ($health > $oldHealth) ? $health : $oldHealth;
                $arrThingComps[$width][$height][$sDef]['health'] = $health;
                $stackCount += $oldStackCount;

                $fullXml = $arrThingComps[$width][$height][$sDef]['full-xml'];
                $fullXml .= $xml;

                $thingComps = array('id' => $id, 'def' => $sDef, 'pos' => $pos, 'stack-count' => $stackCount, 'health' => $health, 'xml' => $xml, 'full-xml' => $fullXml);
                $arrThingComps[$width][$height][$sDef] = $thingComps;
            }

        }
    }

    static function addNewResource($resourceId, $pos){
        $content = ($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();
        $filePath = "./assets/resource-form/all-things/$resourceId.txt";
        if(!file_exists($filePath)){
            echo json_encode(array('result'=> "$resourceId Not Found."));
            die;
        }

        $fopen = fopen($filePath,'r');
        $resourceTmp = stream_get_contents($fopen);

        preg_match_all('/\<pos>(.*?)<\/pos>/s', $resourceTmp, $resourceMatches);
        $posTmp = $resourceMatches[1][0];
        $resourceTmp = str_replace($posTmp, $pos,$resourceTmp);
        $resourceTmp .= "\r\n\t\t\t\t</things>";
        $resourceTmp = "\t".$resourceTmp;
        $content = str_replace('</things>', $resourceTmp,$content,$countentReplaceCount);
        $_SESSION['data-resource'] = $content;
    }

    static function addAllResource($pos){
        $allThingsTmp = array_diff(scandir('./assets/resource-form/all-things'), array('..', '.'));
        foreach($allThingsTmp as $resource){
            $resourceId = str_replace('.txt','',$resource);
            self::addNewResource($resourceId,$pos);
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
                    <?php if($arrStockPile){ ?>
                        <?php foreach($arrStockPile as $keyStockpile=>$stockpile){?>
                        <p class="stockpile-name" style="background-color: <?php echo $stockpile['css-color']?>"> <?php echo $stockpile['label']. ' - '.$stockpile['color'] ?></p>
                            <a href="javascript:void(0);" data-action="delete-stockpile" data-value="<?php echo "$keyStockpile" ?>" class="btn btn-danger">
                                Delete Stock Pile
                            </a>
                        <table class="table table-bordered table-responsive table-hover table-striped">
                            <tbody id="stockpile-<?php echo $keyStockpile ?>">

                                <?php foreach($stockpile['data'] as $width=>$arrWidth){?>
                                    <tr>
                                    <?php foreach($arrWidth as $height){?>
                                        <td class="box-15" data-value="<?php echo "$width,$height" ?>">
                                            <?php echo "($height,0,$width) "; ?>
                                            <?php if(!array_key_exists($width,$arrThingComps) || !array_key_exists($height,$arrThingComps[$width])){ ?>

                                                <span class="stockpile-add-more" data-action="add-new"
                                                      data-value='<?php echo "($height,0,$width)" ?>'>
                                                        <i class="fas fa-plus-square"></i>
                                                </span>
                                            <?php continue;
                                            } else{ ?>

                                                <?php foreach($arrThingComps[$width][$height] as $thingComps){?>
                                                    <div class="thing-comps-item col-md-4">
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


<!-- Modal add new réource-->
<section class="modal fade" id="addNewResourceModal" tabindex="-1" role="dialog" aria-labelledby="addNewResourceModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Resource</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row form-group">
                   <?php if($allThingsTmp){ ?>
                        <select id="sel-add-new" class="form-control" data-live-search="true" data-width="100%">
                            <?php foreach($allThingsTmp as $allThingTmp){ ?>
                                <?php $sDef = str_replace('.txt','',$allThingTmp); ?>
                                <?php $fileName = "./assets/images/32px-".strtoupper($sDef).'.png'; ?>
                                <option
                                        data-tokens="<?php echo $allThingTmp ?>"
                                        data-content="
                                        <span class='all-thing-name'>
                                            <img src='<?php echo $fileName; ?>' style='width:32px; height:32px;'/>
                                            <?php echo $sDef; ?>
                                        </span>"
                                        value="<?php echo $sDef ?>" >
                                    <?php echo $allThingTmp ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" id="add-new-resource" class="btn btn-primary">Save changes</button>
                <button type="button" id="add-all-resource" class="btn btn-primary">Add All</button>
            </div>
        </div>
    </div>
</section>


<!-- Latest compiled and minified JavaScript -->
<link rel="stylesheet" href="./assets/bootstrap-select-1.12.4/css/bootstrap-select.css">
<script src="./assets/bootstrap-select-1.12.4/js/bootstrap-select.js"></script>

<script>
    $(function(){
        var _idChose, _pos = '';
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

        $('#add-new-resource').click(function(){

            var _resourceId = $('#sel-add-new').val();
            sendRequest('add-new-resource',_resourceId, _pos);
        });

        $('#add-all-resource').click(function(){
            sendRequest('add-all-resource',0, _pos);
        });


        $('tbody').on('click','span[data-action="add-new"]',function(){
            _pos = $(this).data('value');
            $('#addNewResourceModal').modal('show');
        });


        function sendRequest(_mode,_resourceId, _pos){
            var _moreResourceNo = $('#add-more-resource-no').val();
            _resourceId = typeof _resourceId === undefined ? '' : _resourceId;
            _pos = typeof _pos === undefined ? '' : _pos;

            var _data = {id:_idChose,no: _moreResourceNo, "resource-id":_resourceId, pos:_pos};
            $.post('/stockpile.php',{data:_data, mode:_mode},function(response){
                response = JSON.parse(response);
                $('#add-more-resource-no').val('');
                $('#addMoreResourceModal').modal('hide');
                alertify.success(response.result);

            });
        }
    })

    $('a[data-action="delete-stockpile"]').click(function(){
        var _this = $(this);
        var _stockpileId = _this.data('value');
        var _table = $('#stockpile-'+_stockpileId);
        var _data = [];
        _table.find('td').each(function(){
            var _td = $(this);
            _data.push(_td.data('value'));
        })

        $.ajax({
            url: '/stockpile.php',
            type: 'post',
            dataType: 'json',
            data: {data: _data, mode: 'delete-stockpile'}
        }).done(function(response){
            alertify.success(response.result);
        })
    })
    $('#sel-add-new').selectpicker('refresh',{
        // selectOnTab: true,
        // showContent: true,
        // showIcon: true,
        // showSubtext: true,
        // showTick: true
    });
</script>

</body>
</html>
