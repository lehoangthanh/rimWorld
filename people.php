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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The request is using the POST method
    $mode = $_POST['mode'];
    $arrHuman = array();
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

                $arrHuman[$id] = array('id'=>$id, 'kindDef'=>$sKindDef, 'name'=>$name,'skills'=>array(),'match'=>$match);
                foreach($skillsChildMatches[0] as $keySkill=>$skill){
                    preg_match_all('/\<def>(.*?)<\/def>/s', $skill, $skillDefMatches);

                    $sKillDef = $skillDefMatches[1][0];

                    preg_match_all('/\<level>(.*?)<\/level>/s', $skill, $skillLevelMatches);
                    $sSkillLevel = $skillLevelMatches[1][0];

                    $arrHuman[$id]['skills'][] = array('def'=>$sKillDef,'level'=>$sSkillLevel,'match'=>$skill);
                }

//                $arrResource[$id] = array('id'=>$id, 'def'=>$sDef, 'stackCount'=>$stackCount,'match'=>$match);

            }
            $_SESSION['data-resource'] = $contents;

            $_SESSION['data-human'] = $arrHuman;
            break;
        }
        case 'up-skills':{
            set_time_limit(-1);

            $arrSkilLimit = array('Medicine','Cooking','Artistic','Crafting');
            $formFileName = $_SESSION['form-file-name'];

            $arrHuman = isset($_SESSION['data-human']) ? $_SESSION['data-human'] : array();
            $fileContent = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : '';
//            $human = $arrHuman['Human522'];
            foreach($arrHuman as $key=>$human){
                $humanMatch = $human['match'];
                $humanNewMatch = $humanMatch;

                foreach($human['skills'] as $key=>$skill){

                    $killMatch = $skill['match'];
                    $iLevel = in_array($skill['def'],$arrSkilLimit) ? 20 : 99999;
                    preg_match('/\<level>(.*?)<\/level>/s',$killMatch,$arrLevelMatch);
                   if(count($arrLevelMatch) > 0) {
                       $newMatch = preg_replace('/\<level>(.*?)<\/level>/s', "<level>$iLevel</level>", $killMatch);
                   }else{
                       $newMatch = str_replace('</def>', "</def>\n\t\t\t\t\t\t\t\t\t<level>$iLevel</level>", $killMatch);
                   }
                    $humanNewMatch = str_replace($killMatch,$newMatch,$humanNewMatch);
                }
                $fileContent = str_replace($humanMatch,$humanNewMatch,$fileContent);
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
        case 'reset-health':{

            set_time_limit(-1);

            $arrSkilLimit = array('Medicine','Cooking','Artistic','Crafting');
            $formFileName = $_SESSION['form-file-name'];

            $arrHuman = isset($_SESSION['data-human']) ? $_SESSION['data-human'] : array();
            $content = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : '';

            $healthTemp = '<healthTracker>
							<hediffSet>
								<hediffs />
							</hediffSet>
							<surgeryBills>
								<bills />
							</surgeryBills>
							<immunity>
								<imList />
							</immunity>
						</healthTracker>';
            foreach($arrHuman as $key=>$human){
                $humanMatch = $human['match'];
                $oldMatch = $humanMatch;
                preg_match('/\<healthTracker>(.*?)<\/healthTracker>/s', $humanMatch, $healthMatch);
                $health = $healthMatch[0];
                $humanMatch = str_replace($health,$healthTemp,$humanMatch,$healthCount);
                $content = str_replace($oldMatch,$humanMatch,$content,$contentCount);


            }

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
        if($arrHuman){

            ?>
            <form id="f-save-new-file-human" method="post">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <label>Humans</label>
                            <a href="javascript:void(0);" class="btn btn-primary" data-action="update-human" data-mode="up-skills">Up Skills</a>
                            <a href="javascript:void(0);" class="btn btn-warning" data-action="reset-health-human" >Reset Health</a>
    <!--                        <button class="btn btn-primary">Save File</button>-->
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="float: right;padding-bottom: 10px;">
    <!--                        Up all to-->
    <!--                        <input type="text" value="500" id="all-value-to"/>-->
    <!--                        <a href="javascript:void(0);" class="btn btn-primary" id="btn-up-value-to">Up</a>-->

                        </div>
                        <table class="table table-bordered table-responsive table-hover" id="table-resource">
                            <thead>
                            <th>ID</th>
                            <th>name</th>
                            <th>kindDef</th>
                            <th>Skills</th>
                            </thead>
                            <tbody>
                            <?php foreach($arrHuman as $Human){?>
                                <tr>
                                    <td>
                                        <?php echo $Human['id'] ?>
                                        <a href="<?php echo '/people-detail.php?id='.$Human['id'] ?>&mode=read-file">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                    <td><?php echo $Human['name'] ?></td>
                                    <td><?php echo $Human['kindDef'] ?></td>
                                   <td>
                                       <table class="table table-bordered table-responsive table-hover" id="table-resource">
                                           <tbody>
                                           <?php foreach($Human['skills'] as $skill){?>
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
        });

        $('a[data-action="reset-health-human"]').click(function(){
            $("#mode").val('reset-health');
            $("#f-save-new-file-human").submit();
        });
    })
</script>
</body>
</html>
