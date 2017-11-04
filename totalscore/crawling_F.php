<?
     set_time_limit(0) ;

     include "inc/config.php";       // {[데이터 베이스]} : 환경설정

     $connect = dbconn() ;           // {[데이터 베이스]} : 연결

     mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

     $ShowDate1 = $_GET['ShowDate1'];
     $ShowDate2 = $_GET['ShowDate2'];
?>
<html lang="kr">
    <head>
<? include "inc/Head.inc"; ?>

        <script type="text/javascript">
        function active_css()
        {
            $('#menu1').attr("class","active has-sub");
        };

        $(function() {
          $( ".datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            dayNames: ['일','월','화','수','목','금','토'],
            dayNamesShort: ['일','월','화','수','목','금','토'],
            dayNamesMin: ['일','월','화','수','목','금','토'],
            showMonthAfterYear: true,
            yearSuffix: '년'
          });
        });

        function datepicker_change()
        {
            //frmMain.submit();
        }
        function start_click()
        {
            $("input[name=start_flag]").val("true");
            frmMain.submit();
        }

        </script>


        <title>상영내역 가져오기</title>
    </head>
    <body>
<? include "inc/menu1   .inc"; ?>

<?
if  ($start_flag !== "true")
{
?>
    <form name="frmMain" method="get" action="<?=$_SERVER['PHP_SELF']?>" >
        <input type="hidden" name="start_flag" value="false"/>
        <br>
        부터: <input type="text" name="ShowDate1" class="datepicker" value="<?=$ShowDate1?>" onchange="datepicker_change()">
        까지: <input type="text" name="ShowDate2" class="datepicker" value="<?=$ShowDate2?>" onchange="datepicker_change()">
        <button name+"start" onclick="start_click()">가져오기</button>
        <br>
    </form>

<?
}
else
{
    include "lib/get_remotefile.php";
    include "lib/JSON.php";

    $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
    $QryKoficTheather = mysql_query($sQuery,$connect) ;
    while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
    {
        $sTheatherCode = $ArrKoficTheather["Code"] ;
        $sTheatherName = $ArrKoficTheather["TheatherName"] ;

        $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);
//if  ($sTheatherCode != "001123") continue;

        echo "<br>".$sTheatherCode."_".$sTheatherName." : 읽기";

        $date = $ShowDate1 ;
        $StShowDate = $ShowDate1 ;
        $EtShowDate = $ShowDate2 ;

        while (strtotime($date) <= strtotime($EtShowDate))
        {
            echo "<br>"."$date";
            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));

            $baseDate = substr($date,0,4) . substr($date,5,2) . substr($date,8,2) ;

            $vars = array(
                         "theaCd" => "$sTheatherCode",
                         "showDt" => "$baseDate"
                         );

            $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do";

            $retValue = get_remotefile($url,$vars);
            $retValue = trim($retValue);
            $retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
            $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환
    // echo "<br>".$retValue;

            $json = new Services_JSON();        // create a new instance of Services_JSON
            $jvalue = $json->decode($retValue); // json형태의 스트링을 php 배열로 변환한다.
            //echo "<br>".$jvalue ; // 지우지 말것 ..

            $schedule = $jvalue->{'schedule'}; // 상영스케줄 정보

            $size = sizeof($schedule) ;
            for ($i=0 ; $i<$size ; $i++)
            {
                 foreach($schedule[$i] as $key => $value)
                 {
                     echo "<br>".$key.":".$value ;
                     if  ($key == "scrnNm")   { $scrnNm = iconv("UTF-8","EUC-KR",$value) ;   }
                     if  ($key == "movieCd")  { $movieCd = $value ;  }
                     if  ($key == "movieNm")  { $movieNm = iconv("UTF-8","EUC-KR",$value) ;  }
                     if  ($key == "showTm")
                     {
                          $showTms = split(",", $value);

                          $sQuery = "DELETE FROM kofic_showtime
                                           WHERE TheatherCd  = '$sTheatherCode'
                                             AND `Date`      = '$baseDate'
                                             AND ScrnNm      = '$scrnNm'
                                             AND MovieCd     = '$movieCd'
                                    " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                          mysql_query($sQuery,$connect) ;

                          $j = 1 ;
                          foreach ($showTms as $showTm)
                          {
                               $sQuery = "INSERT INTO kofic_showtime
                                               VALUES (
                                                       '$sTheatherCode'
                                                      ,'$baseDate'
                                                      ,'$scrnNm'
                                                      ,$j
                                                      ,'$showTm'
                                                      ,'$movieCd'
                                                      ,'$movieNm'
                                                      )
                                         " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                               mysql_query($sQuery,$connect) ;

                               $j ++;
                          }
                     }
                 }

                 $sQuery = "DELETE FROM kofic_movie
                                  WHERE Code = '$movieCd'   " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;

                 $sQuery = "INSERT INTO kofic_movie
                                 VALUES (
                                         '$movieCd'
                                        ,'$movieNm'
                                        )                   " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;


                 $sQuery = "DELETE FROM kofic_showroom
                                  WHERE TheatherCd = '$sTheatherCode'
                                        `Date`     = '$baseDate'
                                        ScrnNm     = '$scrnNm'          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;

                 $sQuery = "INSERT INTO kofic_showroom
                                VALUES (
                                        '$sTheatherCode'
                                       ,'$baseDate'
                                       ,'$scrnNm'
                                       ,'$movieCd'
                                       ,'$movieNm'
                                       ,0
                                       )                   " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;
            }

/*
            $pattern = "\n";

            $arrLine1 = split($pattern,$retValue); // 한줄씩 분리..
            for ($i=0;$i< sizeof($arrLine1);$i++)
            {
                $line1 = trim($arrLine1[$i]) ;
                echo "<br>".$line1 ;
            }*/
        }
    }
}

?>



    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
