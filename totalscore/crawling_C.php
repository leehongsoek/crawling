<?
     set_time_limit(0) ;

     include "inc/config.php";       // {[데이터 베이스]} : 환경설정

     $connect = dbconn() ;           // {[데이터 베이스]} : 연결

     mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택
?>
<html lang="kr">
    <head>
<? include "inc/Head.inc"; ?>

        <script type="text/javascript">
        function active_css()
        {
            $('#menu1').attr("class","active has-sub");
        };

        </script>

        <title>상영관별 시간표 가져오기</title>
    </head>
    <body>
<? include "inc/Body.inc"; ?>

<?
    include "lib/get_remotefile.php";
    include "lib/JSON.php";

    $pattern = "\n";

    $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
    $QryKoficTheather = mysql_query($sQuery,$connect) ;
    while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
    {
        $sTheatherCode = $ArrKoficTheather["Code"] ;
        $sTheatherName = $ArrKoficTheather["TheatherName"] ;

        $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);

        echo "<br>".$sTheatherName." : 읽기";

            $swScrnCd = false ;
            $swScrnNm = false ;
            $swSeat   = false ;

            $arrScrnCd = array();
            $arrScrnNm = array();
            $arrSeat   = array();

            $vars = array();
            $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterCodeLayer.do?theaCd=".$sTheatherCode;
            $retValue = get_remotefile($url,$vars);
//echo "<br>".$retValue;
            $arrLine2 = split($pattern,$retValue); // 한줄씩 분리..
            for ($j=0;$j< sizeof($arrLine2);$j++)
            {
                //echo  "<br>".$arrLine2[$j];

                $trm = trim($arrLine2[$j]);

                if  (strpos($trm, '&lt;th&gt;스크린코드&lt;/th&gt;') !== false)  {  $swScrnCd = true ; continue; }
                if  (strpos($trm, '&lt;th&gt;스크린&lt;/th&gt;') !== false)  {  $swScrnNm = true ; continue; }
                if  (strpos($trm, '&lt;th scope=&quot;row&quot; class=&quot;sty3 ct&quot;&gt;좌석수&lt;/th&gt;') !== false)  {  $swSeat   = true ; continue; }

                if  (  (strpos($trm, '&lt;/colgroup&gt;') !== false)
                    || (strpos($trm, '&lt;/tr&gt;')       !== false))
                {
                    $swScrnCd = false ;
                    $swScrnNm = false ;
                    $swSeat   = false ;
                }

                if  (($swScrnCd == true) && (strpos($trm, '&lt;th&gt;') !== false))
                {
                    $sp = strpos($trm, '&lt;th&gt;') + 10;
                    $ep = strpos($trm, '&lt;/th&gt;');

                    $ScrnCd = substr($trm,$sp,$ep-$sp) ;

                    array_push($arrScrnCd , $ScrnCd);

                    continue ;
                }

                if  (($swScrnNm == true) && (strpos($trm, '&lt;th&gt;') !== false))
                {
                    $sp = strpos($trm, '&lt;th&gt;') + 10;
                    $ep = strpos($trm, '&lt;/th&gt;');

                    $ScrnNm = substr($trm,$sp,$ep-$sp) ;

                    array_push($arrScrnNm , $ScrnNm);

                    continue ;
                }

                if  (($swSeat == true) && (strpos($trm, '&lt;td class=&quot;ct&quot;&gt;') !== false))
                {
                    $sp = strpos($trm, '&lt;td class=&quot;ct&quot;&gt;') + 31;
                    $ep = strpos($trm, '&lt;/td&gt;');

                    $Seat = substr($trm,$sp,$ep-$sp) ;

                    array_push($arrSeat , $Seat);

                    continue ;
                }
                //echo "<br>"."[".$ScrnCd."]"."[".$ScrnNm."]"."[".$Seat."] ";
            }

            for($k=0;$k< count($arrScrnCd);$k++)
            {
                //echo "<br>".$arrScrnCd[$k]."/".$arrScrnNm[$k]."/". (int)$arrSeat[$k];
                $ScrnNm = iconv("UTF-8", "EUC-KR",$arrScrnNm[$k]);  // 한글처리

                $sQuery = "INSERT INTO kofic_screen           ".
                          "     VALUES (                      ".
                          "             '".$sTheatherCode."'  ".
                          "            ,'".$arrScrnCd[$k]."'  ".
                          "            ,'".$ScrnNm."'         ".
                          "            ,".(int)$arrSeat[$k]." ".
                          "            )                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;
            }
    }

    $sQuery = "UPDATE kofic_theather tht                        ".
              "   SET Seat =  (                                 ".
              "                   SELECT sum( Seat )            ".
              "                    FROM kofic_screen            ".
              "                   WHERE TheatherCd = tht.Code   ".
              "               )                                 " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;

?>



    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
