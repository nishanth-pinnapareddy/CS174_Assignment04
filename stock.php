
<?php
/**
 * Created by PhpStorm.
 * User: Nishanth Reddy
 * Date: 4/1/2017
 * Time: 9:02 PM
 */
    $markitLookUpAPI = 'http://dev.markitondemand.com/MODApis/Api/v2/Lookup/xml?input=';
    $markitGetQuoteAPI = 'http://dev.markitondemand.com/MODApis/Api/v2/Quote/json?symbol=';

    //Form submitted
    if(isset($_POST['submit'])) {
        //Error checking
        if(!$_POST['comapanyname']) {
            $message =  "Please enter Name or Symbol.";
            echo "<script type='text/javascript'>alert('$message');</script>";
        }
        else {
            $comapanyname = $_POST['comapanyname'];
            $comapanyname = trim($comapanyname);
            $uri =  $markitLookUpAPI . $comapanyname;
            $response = file_get_contents($uri);
            $xml = simplexml_load_string($response);
        }
    }

    //check if the set variable exists
    else if (isset($_GET['quoteuri']))
    {
        if (!isset($_REQUEST['hiddenbox']) || (isset($_REQUEST['hiddenbox']) && $_REQUEST['hiddenbox']== 'unclear')) {
            $quoteUri = $_GET['quoteuri'];
            $comapanyname = $_GET['companyname'];
            $quoteResponse = file_get_contents($quoteUri);;
            $jsonbody = json_decode($quoteResponse, true);
        }
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
        <style>
            .formdiv {
                text-align: center;
                border: 2px solid;
                background-color: #F3F3F3;
                width: 500px;
                height: 200px;
                padding-left: -2%;
                margin-left: 32%;
            }

            .submit {
                padding: 5px 10px 5px 10px;
                margin-top: 2%;
                margin-left: 32%;
            }

            .clear {
                padding: 5px 10px 5px 10px;
            }

            .lookupresultsdiv {
                text-align: center;
                margin-left: 31%;
                padding-top: 1%;
            }

            .quoteresultsdiv{
                text-align: center;
                margin-left: 30%;
                margin-top: 1%;
            }

            .norecordsfound, .nostockfound{
                text-align: center;
                border: 2px solid;
                background-color: #F3F3F3;
                width: 500px;
                height: 50px;
                margin-left: 32%;
                margin-top: 1%;
            }

        </style>
        <script>
            function remove(id) {
                var elem = document.getElementById(id);
                if (elem) {
                    elem.parentNode.removeChild(elem);
                }
            }

            function clearData(){
                remove('lookupdiv');
                remove('lookuperror');
                remove('quotediv');
                remove('quoteerror');
                document.getElementById('inputText').value = "";
                var myhidden = document.getElementById("hiddenbox");
                myhidden.value = "clear";
            }
        </script>
    </head>
    <body>
        <div class="formdiv">
            <h1>Stock Search</h1>
            <form action="" method="post">
                <label for="company">Company Name or Symbol:  </label><input id="inputText" type="text" name="comapanyname" value="<?php if(isset($comapanyname)){echo $comapanyname;}?>">
                <br/>
                <input class="submit" type="submit" name="submit" value="Submit" />
                <input class="clear" type="submit" name="clear" onClick="clearData();" value="Clear" />
                <input type="hidden" id="hiddenbox" name="hiddenbox" value="unclear"/>
                <a href="http://www.markit.com/product/markit-on-demand"><h4 style="padding-left: 25%;">Powered by Markit on Demand</h4></a>
            </form>
        </div>
        <?php if (isset($xml) && count($xml->children()) > 0): ?>
            <div id="lookupdiv" class="lookupresultsdiv">
                <table border="2" style="background-color: #F3F3F3;">
                    <tr>
                        <th style=\"width:200px;\">Name</th>
                        <th>Symbol</th>
                        <th>Exchange</th>
                        <th>Details</th>
                    </tr>
                    <?php
                        foreach ($xml->LookupResult  as $lookupresult) {
                            echo "<tr>";
                            echo "<td style='width:60%'>" . $lookupresult->Name  . "</td>";
                            echo "<td>" . $lookupresult->Symbol  . "</td>";
                            echo "<td>" . $lookupresult->Exchange  . "</td>";
                            echo "<td><a href='stock.php?quoteuri=" . $markitGetQuoteAPI . $lookupresult->children()[0]. "&companyname=" . $comapanyname ."'>More Info</a></td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if (isset($xml) && count($xml->children()) == 0): ?>
            <div id="lookuperror" class="norecordsfound">
                <h3> No Records has been found </h3>
            </div>
        <?php endif; ?>

        <?php if (isset($jsonbody) && $jsonbody['Status'] == 'SUCCESS'): ?>
            <div id="quotediv" class="quoteresultsdiv">
                <table border="2" style="background-color: #F3F3F3;">
                    <?php
                        foreach($jsonbody as $key => $val) {
                            if ($key != 'Status') {
                                if ($key == 'Change') {
                                    $val = number_format((float)$val, 2, '.', '');
                                    if ($val > 0){
                                        $val = $val . '<img src="http://i.imgur.com/rJUQgvp.jpg" width="10px" height="12px">';
                                    } elseif ($val < 0){
                                        $val = $val . '<img src="http://i.imgur.com/hGgD7yM.jpg" width="10px" height="12px">';
                                    }
                                }

                                if ($key == 'ChangePercent'){
                                    $val = number_format((float)$val, 2, '.', '');
                                    if ($val > 0){
                                        $val = $val . '%' . '<img src="http://i.imgur.com/rJUQgvp.jpg" width="10px" height="12px">';
                                    } elseif ($val < 0){
                                        $val = $val . '%' . '<img src="http://i.imgur.com/hGgD7yM.jpg" width="10px" height="12px">';
                                    } else {
                                        $val = $val . '%';
                                    }
                                }

                                if ($key == 'Timestamp'){
                                    date_default_timezone_set('America/Los_Angeles');
                                    $old_date_timestamp = strtotime($val);
                                    $val = date('Y-m-d h:i A', $old_date_timestamp);
                                }

                                if ($key == 'MarketCap'){
                                    $val  = $val/ 1000000000;
                                    $val = number_format((float)$val, 2, '.', ''). 'B';
                                }

                                if ($key == 'Volume') {
                                    $val = number_format($val);
                                }

                                if ($key == 'ChangeYTD') {
                                    $val = $jsonbody['LastPrice'] - $val;
                                    $val = number_format((float)$val, 2, '.', '');
                                    if ($val > 0){
                                        $val = $val . '<img src="http://i.imgur.com/rJUQgvp.jpg" width="10px" height="12px">';
                                    } elseif ($val < 0){
                                        $val = $val . '<img src="http://i.imgur.com/hGgD7yM.jpg" width="10px" height="12px">';
                                    }
                                }

                                if ($key == 'ChangePercentYTD') {
                                    $val = number_format((float)$val, 2, '.', '');
                                    if ($val > 0){
                                        $val = $val . '%' . '<img src="http://i.imgur.com/rJUQgvp.jpg" width="10px" height="12px">';
                                    } elseif ($val < 0){
                                        $val = $val . '%' . '<img src="http://i.imgur.com/hGgD7yM.jpg" width="10px" height="12px">';
                                    } else {
                                        $val = $val . '%';
                                    }
                                }

                                echo "<tr>";
                                echo "<td style='width: 300px;'><b>" . $key . "</b></td>";
                                echo "<td  style='width: 300px;'>" . $val . "</td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if (isset($jsonbody) && $jsonbody['Status'] != 'SUCCESS'): ?>
            <div id="quoteerror" class="nostockfound">
                <h3> There is no stock information available. </h3>
            </div>
        <?php endif; ?>
    </body>
</html>
