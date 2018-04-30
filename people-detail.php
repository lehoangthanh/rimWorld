<?php
/**
 * Created by PhpStorm.
 * User: lehoa
 * Date: 08-Jul-17
 * Time: 1:10 PM
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

    // The request is using the POST method
    $mode = $_GET['mode'];
    $arrHuman = array();
    switch ($mode) {
        case'read-file':{
            $humanId = $_GET['id'];
            $contents = $_SESSION['data-resource'];
            preg_match_all('/\<thing Class="Pawn">(.*?)<\/thing>/s', $contents, $matches);

            foreach($matches[0] as $key=>$match){
//
//                /**
//                 * bản php này bị điên không nhận dc partten này
//                 * /<thing Class="Pawn">(.*)<def>Human<\/def>(.*?)<\/thing>/s
//                 */
                preg_match_all('/\<def>(.*?)<\/def>/s', $match, $defMatches);
                $sDef = $defMatches[1][0];
                if(strcmp($sDef,'Human')!= 0){continue;}

                preg_match_all('/\<id>(.*?)<\/id>/s', $match, $idMatches);
                $id = $idMatches[1][0];

                if($id != $humanId){continue;}

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


                $arrHuman[$id] = array('id'=>$id, 'kindDef'=>$sKindDef, 'name'=>$name);
                preg_match_all('/\<wornApparel>(.*?)<\/wornApparel>/s', $match, $wornApparelMatches);
                preg_match_all('/\<li>(.*?)<\/li>/s', $wornApparelMatches[1][0], $wornApparelItemMatches);
                foreach($wornApparelItemMatches[1] as $keyWornApparelItem=>$wornApparelItem){
                    $xml = $wornApparelItemMatches[0][$keyWornApparelItem];

                    preg_match_all('/\<def>(.*?)<\/def>/s', $wornApparelItem, $wornApparelDefMatches);
                    $wornApparelDef = $wornApparelDefMatches[1][0];

                    preg_match_all('/\<id>(.*?)<\/id>/s', $wornApparelItem, $wornApparelIdMatches);
                    $wornApparelId = $wornApparelIdMatches[1][0];

                    preg_match_all('/\<health>(.*?)<\/health>/s', $wornApparelItem, $wornApparelHealthMatches);
                    $wornApparelHealth = $wornApparelHealthMatches[1][0];

                    preg_match_all('/\<quality>(.*?)<\/quality>/s', $wornApparelItem, $wornApparelQualityMatches);
                    $wornApparelQuality = $wornApparelQualityMatches[1][0];

                    $arrHuman[$id]['worn-apparel'][] = array('def'=>$wornApparelDef, 'id'=>$wornApparelId, 'health'=>$wornApparelHealth, 'quality'=>$wornApparelQuality,'xml'=>$xml);
                }




            }
            $_SESSION['data-resource'] = $contents;

            $_SESSION['data-human'] = $arrHuman;
            break;
        }
        case 'reset':{
            set_time_limit(-1);
            $id = $_GET['id'];

            $arrHuman = isset($_SESSION['data-human']) ? $_SESSION['data-human'] : array();
            $content = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : '';

            $arrApparel = array_key_exists('worn-apparel',$arrHuman[$id]) ? $arrHuman[$id]['worn-apparel'] : array();

            foreach($arrApparel as $apparel){
                $xml = $apparel['xml'];
                $oldXml = $xml;

                preg_match_all('/\<health>(.*?)<\/health>/s', $xml, $wornApparelHealthMatches);
                $wornApparelHealth = $wornApparelHealthMatches[0][0];
                $xml = str_replace($wornApparelHealth,"\n\t\t\t\t\t\t\t\t\t<health>1000000</health>\n\t\t\t\t\t\t\t\t\t",$xml,$countHealth);

                preg_match_all('/\<quality>(.*?)<\/quality>/s', $xml, $wornApparelQualityMatches);
                $wornApparelQuality = $wornApparelQualityMatches[0][0];
                if($wornApparelQuality) {
                    $xml = str_replace($wornApparelQuality, "\n\t\t\t\t\t\t\t\t\t<quality>Legendary</quality>\n\t\t\t\t\t\t\t\t\t", $xml, $countQuality);
                }

                $content = str_replace($oldXml, $xml, $content);
            }
            $_SESSION['data-resource'] = $content;
            echo json_encode(array('result'=>'success!!!'));
            die;


        }
        case 'set-cloth': {

            break;
        }
        case 'save-file':{

            $formFileName = $_SESSION['form-file-name'];
            $content = $_SESSION['data-resource'];
            file_put_contents(fileTMP,$content);

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
?>
<!DOCTYPE html>
<html>
<?php include_once 'header-file-input.php'?>
<body>

<?php include_once 'menu.php'?>

<div class="container">

        <?php
        if($arrHuman){
            $Human = $arrHuman[$humanId];
            ?>
            <form id="f-save-people-detail" method="get">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <label>Humans <?php echo $Human['id'].' - '. $Human['name'] ?></label>
                        </div>
                    </div>
                    <div class="panel-body">
                       <div class="row panel panel-default">
                           <div class="col-md-6 border-left">
                               <div class="apparel-heading">
                                   <label>Apparels</label>
                                   <a class="btn btn-sm btn-primary" data-action="reset">Reset</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                   <div class="set-cloth">
                                       <a class="btn btn-sm btn-warning">Set</a>
                                       <select class="form-control">
                                           <?php foreach($arrQualityConst as $quality){ ?>
                                               <option ><?php echo $quality; ?></option>
                                           <?php } ?>
                                       </select>
                                   </div>
                                   &nbsp;&nbsp;&nbsp;&nbsp;<a class="btn btn-sm btn-primary" data-action="save">Save</a>
                               </div>
                            <?php if($Human['worn-apparel']){?>
                                <div class="apparel">
                                    <?php foreach($Human['worn-apparel'] as $wornApparel){ ?>
                                        <div class="apparel-item">
                                            <div class="apparel-item-header">
                                                <?php echo $wornApparel['id']. ' - '.$wornApparel['def'] ?>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Health: </label>
                                                <div class="col-sm-8">
                                                    <input type="text" class="form-control" value="<?php echo $wornApparel['health'] ?>">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Quality: </label>
                                                <div class="col-sm-8">
                                                    <select class="form-control">
                                                        <?php foreach($arrQualityConst as $quality){ ?>
                                                            <option <?php echo (trim($quality) == trim($wornApparel['quality'])) ? 'selected' : '' ?>><?php echo $quality; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                </div>
                           </div>
                        <?php }?>
                       </div>
                    </div>
                    <div class="panel-footer">
    <!--                    <button class="btn btn-primary">Save File</button>-->
                        <input type="hidden" name="mode" id="mode"/>
                    </div>
                </div>
            </form>
            <?php
        } //end if($arrResource)
        ?>

</div>

<script>
    $(function(){
        $('a[data-action="update-human"]').click(function(e){
           var mode = $(this).data('mode');
            $("#mode").val(mode);
            $("#f-save-new-file-human").submit();
        })

        $('[data-action="reset"]').click(function(){
            var _id = '<?php echo $humanId ?>';
            var _data = {"mode":"reset", id:_id};
            $.get('/people-detail.php',_data, function(response){
                response = JSON.parse(response);
                alertify.success(response.result);
            });
        });

        $('[data-action="save"]').click(function(){
            $('#mode').val('save-file');
            $('#f-save-people-detail').submit();
        });
    })
</script>
</body>
</html>
