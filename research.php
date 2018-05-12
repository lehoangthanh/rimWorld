<?php
/**
 * Created by PhpStorm.
 * User: Le.Thanh
 * Date: 4/17/2018
 * Time: 1:28 PM
 */
include_once './constant.php';

    // The request is using the POST method
    $mode = $_POST['mode'];
    $mode = ($mode) ? $mode : 'read-file';
    $arrHuman = array();
    switch ($mode) {
        case'read-file':{
            if($_FILES['file_save']){
                $_SESSION['form-file-name'] = $_FILES['file_save']['name'];
                $content = file_get_contents($_FILES['file_save']['tmp_name']);
            }else{
                $content = $_SESSION['data-resource'];
            }
            preg_match_all('/\<researchManager>(.*?)<\/researchManager>/s', $content, $matches);
            preg_match_all('/\<progress>(.*?)<\/progress>/s', $matches[1][0], $processMatches);
            preg_match_all('/\<keys>(.*?)<\/keys>/s', $processMatches[1][0], $keyMatches);
            preg_match_all('/\<values>(.*?)<\/values>/s', $processMatches[1][0], $valueMatches);

            foreach($keyMatches[1] as $key=>$match){

                preg_match_all('/\<li>(.*?)<\/li>/s', $match, $researchKeyMatches);
                preg_match_all('/\<li>(.*?)<\/li>/s', $valueMatches[1][$key], $researchValueMatches);

            }

            $arrResearch = array();
            foreach($researchKeyMatches[1] as $key=>$researchKey){
                $arrResearch[$researchKey] = $researchValueMatches[1][$key];
            }

            $_SESSION['data-resource'] = $content;
            break;
        }
        case 'save-new-file': {

            $formFileName = $_SESSION['form-file-name'];
            $content = isset($_SESSION['data-resource']) ? $_SESSION['data-resource'] : array();

            preg_match_all('/\<researchManager>(.*?)<\/researchManager>/s', $content, $matches);
            preg_match_all('/\<progress>(.*?)<\/progress>/s', $matches[1][0], $processMatches);
            preg_match_all('/\<keys>(.*?)<\/keys>/s', $processMatches[1][0], $keyMatches);
            preg_match_all('/\<values>(.*?)<\/values>/s', $processMatches[1][0], $valueMatches);


            foreach($_POST['research-value'] as $key=>$resource){
                $value .= "<li>$resource</li>";
                $value .= (($key+1) < count($_POST['research-value'])) ? "\r\n\t\t\t\t\t" : "";

            }
            $reValue = "<values>\r\n\t\t\t\t\t$value\r\n\t\t\t\t</values>";
            $content = str_replace($valueMatches[0],$reValue,$content);

            $_SESSION['data-resource'] = $content;
            $_SESSION['token'] = md5($_SESSION['form-file-name']);
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
        if($arrResearch){

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
                        <table class="table table-bordered table-responsive table-hover" id="table-resource">
                            <thead>
                            <th>ID</th>
                            <th>name</th>
                            </thead>
                            <tbody>
                            <?php foreach($arrResearch as $key=>$Research){?>
                                <tr>
                                    <td><?php echo $key ?></td>
                                    <td><input name="research-value[]" value="<?php echo $Research ?>"/></td>


                                </tr>
                            <?php }?>
                            </tbody>
                        </table>

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
