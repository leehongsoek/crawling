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

        <title>박스오피스 가져오기</title>
    </head>
    <body>
<? include "inc/Body.inc"; ?>

<?
    include "lib/get_remotefile.php";
    include "lib/JSON.php";

    $vars = array();

    $StShowDate = date("Y-m-d",strtotime("-7 Day",time())) ;
    $EtShowDate = date("Y-m-d",strtotime("-1 Day",time())) ;


    $sQuery = "DELETE FROM kofic_boxoffice
                     WHERE `Date` >= '$StShowDate'
                       AND `Date` <= '$EtShowDate'  " ; echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;


    $url = "http://www.kobis.or.kr/kobis/business/stat/boxs/findDailyBoxOfficeList.do"
          ."?loadEnd=0"
          ."&searchType=search"
          ."&sSearchFrom=$StShowDate"
          ."&sSearchTo=$EtShowDate"
          ."&sMultiMovieYn="
          ."&sRepNationCd="
          ."&sWideAreaCd=";

    $retValue = get_remotefile($url,$vars);
    //$retValue = trim($retValue);
    //$retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
    $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환

    $pattern = "\n";

    $arrLine1 = split($pattern,$retValue); // 한줄씩 분리..
    for ($i=0;$i< sizeof($arrLine1);$i++)
    {
        $line1 = trim($arrLine1[$i]) ;

        if  (strpos($line1, "<div class=\"board_tit\">")!==false)
        {
            //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+2]))."]" ;
            $tmp = trim($arrLine1[$i+2]) ;
            $Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;

            echo "<br>"."/".$Date."/" ;
        }
        if  (strpos($line1, "<td title=\"")!==false)
        {
            $sp = 11 ;
            $ep = strpos($line1, "\">");

            $rank = substr($line1,$sp,$ep-$sp);
            echo "<br/>"."[".htmlspecialchars($line1)."]" ;
            echo "<br>"."[".$rank."]" ;

            //[<a href="#" class="boxMNm" onclick="mstView('movie','20148851');return false;" title="암살">]
        }
        if  (strpos($line1, "<a href=\"#\" class=\"boxMNm\" onclick=\"mstView('movie','")!==false)
        {
            $sp = 53 ;
            $movieCd = substr($line1,$sp,8)  ;

            //echo "<br>"."[".$movieCd."]" ;

            //$ep = strpos($line1, "\">");
        }

        if  (strpos($line1, "');return false;\" title=\"")!==false)
        {
            $sp = strpos($line1, "');return false;\" title=\"") + 25;
            $ep = strpos($line1, "\">");
            $movieNm = substr($line1,$sp,$ep-$sp);

            //echo "<br>"."[".$movieNm."]" ;

            echo "<br>"."[".$rank."]"."[".$movieCd."]"."[".$movieNm."]" ;

            $movieNm_ = iconv("UTF-8", "EUC-KR",$movieNm);  // 한글처리
            $sQuery = "INSERT INTO kofic_boxoffice
                            VALUES (
                                    '$Date'
                                   ,$rank
                                   ,'$movieCd'
                                   ,'$movieNm_'
                                   )                   " ; //echo "<br>".$sQuery;
            mysql_query($sQuery,$connect) ;
        }
    }

?>



    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
