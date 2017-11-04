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
<? include "inc/Menu.inc"; ?>

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

if  ($sTheatherCode != "002127") continue;


        $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);

        echo "<br>".$sTheatherCode."_".$sTheatherName." : 읽기";

        //$StShowDate = date("Y-m-d",strtotime("-1 Day",time())) ;
        //$EtShowDate = date("Y-m-d",strtotime("6 Day",time())) ;
        $StShowDate = $ShowDate1 ;
        $EtShowDate = $ShowDate2 ;

        //$sTheatherCode = "001123";

        $vars = array(
                     "theaCd"      => "$sTheatherCode",
                     "theaArea"    => "Y",
                     "showStartDt" => "$StShowDate",
                     "showEndDt"   => "$EtShowDate",
                     "sWideareaCd" => "",
                     "sBasareaCd"  => "",
                     "sTheaCd"     => "",
                     "choice"      => "2",
                     "sTheaNm"     => "$sTheatherName"
                     );

        $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findShowHistory.do";

        $retValue = get_remotefile($url,$vars);
        //$retValue = trim($retValue);
        //$retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
        $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환
// echo "<br>".$retValue;

        $findShowRoom = false;
        $firstShowRoom = false;
        $CntShowRoom = 0;

        $ShowRooms = array();

        $findInning = false;
        $Innings   = array();

        $offsetShowRoom = -1;
        $nInning = 11 ;

        $pattern = "\n";

        $arrLine1 = split($pattern,$retValue); // 한줄씩 분리..
        for ($i=0;$i< sizeof($arrLine1);$i++)
        {
            $line1 = trim($arrLine1[$i]) ;
 echo $line1."\n" ;
            if  (strpos($line1, "<th scope=\"col\">상영관</th>")!==false)
            {
                if  ($firstShowRoom == true) continue ;
                //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+2]))."]" ;
                //$tmp = trim($arrLine1[$i+2]) ;
                //$Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;
                //echo "<br>"."[".$line1."]" ;

                $findShowRoom = true;
                $firstShowRoom = true;

                continue;
            }
            if  (strpos($line1, "<th scope=\"col\">총")!==false)
            {
                $findShowRoom = false;

                continue;
            }
            if  (strpos($line1, "<th scope=\"col\">")!==false)
            {
                if  ($findShowRoom == true)
                {
                    //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+1]))."]" ;

                    array_push($ShowRooms, trim($arrLine1[$i+1]));
                    $CntShowRoom ++ ;
                }
                //$tmp = trim($arrLine1[$i+2]) ;
                //$Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;
            }

            if  (strpos($line1, "<th>좌석수</th>")!==false)
            {
                 for ($j=1 ; $j<=$CntShowRoom ; $j++)
                 {
                     //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+($j*2)]))."]" ;
                     $sp = strpos($arrLine1[$i+($j*2)], "<td>") + 4;
                     $ep = strpos($arrLine1[$i+($j*2)], "</td>");
                     $Seat = substr($arrLine1[$i+($j*2)],$sp,$ep-$sp);

                     $ScrnNm = iconv("UTF-8", "EUC-KR",$ShowRooms[$j-1]);  // 한글처리

                     // 국장 좌석 수 업데이트
                     $sQuery = "UPDATE kofic_showroom
                                   SET Seat =  $Seat
                                 WHERE TheatherCd = '$sTheatherCode'
                                   AND ScrnNm     = '$ScrnNm'        " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                     mysql_query($sQuery,$connect) ;

                     $sQuery = "DELETE FROM kofic_seat
                                      WHERE TheatherCd = '$sTheatherCode'
                                            ScrnNm     = '$ScrnNm'          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                     mysql_query($sQuery,$connect) ;

                     $sQuery = "INSERT INTO kofic_seat
                                     VALUES (
                                             '$sTheatherCode'
                                            ,'$ScrnNm'
                                            ,$Seat
                                            )                   " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                     mysql_query($sQuery,$connect) ;
                 }
            }

            if  (strpos($line1, "<th scope=\"col\">상영일자</th>")!==false)
            {
                $findInning = true ;

                continue;
            }
            if  (strpos($line1, "<th scope=\"col\">")!==false)
            {
                if  ($findInning == true)
                {
                    $sp = strpos($line1, "<th scope=\"col\">") + 16;
                    $ep = strpos($line1, "회</th>");
                    $Inning = substr($line1,$sp,$ep-$sp);

                    array_push($Innings, $Inning);

                    echo "[".$Inning."]" ;
                }
            }
            if  (strpos($line1, "</tr>")!==false)
            {
                if  ($findInning == true) $findInning = true;
            }

            if  ((strpos($line1, "<tr >")!==false) || (strpos($line1, "<tr class='last-child'>")!==false))
            {
                if  (strpos($arrLine1[$i+1],"<td rowspan='")!==false)
                {
                    $offsetShowRoom  = 2;

                    $sp = strpos($arrLine1[$i+1],"</td>") - 10;
                    $temp =  substr($arrLine1[$i+1],$sp,10);

                    $baseDate = substr($temp,0,4) . substr($temp,5,2) . substr($temp,8,2) ;

                    //echo "<br>"."[".$baseDate."]" ;
                }
                else
                {
                    $offsetShowRoom  = 2;
                }
                $sp = strpos($arrLine1[$i+$offsetShowRoom], "<td>") + 4;
                $ep = strpos($arrLine1[$i+$offsetShowRoom], "</td>");
                $curShowroom = substr($arrLine1[$i+$offsetShowRoom],$sp,$ep-$sp);

                echo "<br>"."[".$curShowroom."]" ;

                $nInning = 0 ;

                $curShowroom = iconv("UTF-8", "EUC-KR",$curShowroom);  // 한글처리
            }
            if  (strpos($line1, "<td class=\"left\">")!==false)
            {
                if  (strpos($arrLine1[$i+1], "</td>")===false)
                {
                    if  (strpos($arrLine1[$i+1], "<font color=\"red\">")!==false) // 발견되고..(가격이 없음)
                    {
                        //$time      = substr($arrLine1[$i+2],0,2) . substr($arrLine1[$i+2],3,2) ;
                        $tmp       = trim($arrLine1[$i+2]) ;
                        $time      = substr($tmp,0,2) .substr($tmp,3,2) ;
                        $unitprice = "0";
                        $movieNm = trim($arrLine1[$i+3]);
                    }
                    else
                    {
                        $ep = strpos($arrLine1[$i+1], "(");
                        $time = substr($arrLine1[$i+1],$ep-6,2) . substr($arrLine1[$i+1],$ep-3,2) ;

                        $sp = strpos($arrLine1[$i+1], "(") + 1;
                        $ep = strpos($arrLine1[$i+1], "원)<br>");
                        $unitprice =  str_replace(",", "", substr($arrLine1[$i+1],$sp,$ep-$sp));

                        $movieNm = trim($arrLine1[$i+2]);
                    }

                    $Inning =  $Innings[$nInning];

                    //$ep = strpos($movieNm, "(");
                    //$movieNm = substr($movieNm,0,$ep) ;

                    $movieNm_ = iconv("UTF-8", "EUC-KR",$movieNm);  // 한글처리

                    $sQuery = "SELECT * FROM kofic_movie
                                WHERE MovieName = '$movieNm_' " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                    $QryMovieName = mysql_query($sQuery,$connect) ;
                    if  ($ArrMovieName = mysql_fetch_array($QryMovieName))
                    {
                        $movieCd = $ArrMovieName["Code"];
                    }
                    else
                    {
                        $movieCd = "";
                    }

                    $sQuery = "INSERT INTO kofic_playing
                                    VALUES (
                                            '$sTheatherCode'
                                           ,'$baseDate'
                                           ,'$curShowroom'
                                           ,$Inning
                                           ,'$time'
                                           ,'$movieCd'
                                           ,'$movieNm_'
                                           ,$unitprice
                                           )
                              " ;//echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                    mysql_query($sQuery,$connect) ;

                    // [회차], [시작시간], [단가], [영화코드], [영화명]
                    echo "<br>"."[".$Inning."][".$time."][".$unitprice."][".$movieCd."][".$movieNm."]" ;
                }


                $nInning ++ ;
            }

        }
    }
}

?>



    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
