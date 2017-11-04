<?   // "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do"

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

    $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
    $QryKoficTheather = mysql_query($sQuery,$connect) ;
    while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
    {
        $sTheatherCode = $ArrKoficTheather["Code"] ;
        $sTheatherName = $ArrKoficTheather["TheatherName"] ;

        //if  ($sTheatherCode == "005049") // CGV 서산
        //{

        $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);

        echo "<br>".$sTheatherCode."_".$sTheatherName." : 읽기";

        $ShowDate = date("Ymd",time()) ; // 오늘 ...

        for ($day=1 ; $day<=7 ; $day++) // 일주일치를 다 땡긴다...
        {
            ///echo "<br>".$ShowDate."-----------------------------------------------------------------------------";

            $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do";

            $vars = array(
                       "showDt"  => $ShowDate,
                       "theaCd"  => $sTheatherCode
                       );

            $retValue = get_remotefile($url,$vars);
            $retValue = trim($retValue);
            $retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
            $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환

            //echo "<br>".$retValue."<br><br>";

            $json = new Services_JSON();        // create a new instance of Services_JSON
            $jvalue = $json->decode($retValue); // json형태의 스트링을 php 배열로 변환한다.
            //echo "<br>".$jvalue ; // 지우지 말것 ..

            //$theather = $jvalue->{'theater'} ; // 극장정보 - 필요없음
            $schedule = $jvalue->{'schedule'}; // 상영스케줄 정보
            /*
            $size = sizeof($theather) ;
            for ($i=0 ; $i<$size ; $i++)
            {
               foreach($theather[$i] as $key => $value)
               {
                   //echo "<br>".$key.":".$value."<br>";
               }
            }
            */
            $size = sizeof($schedule) ;
            for ($i=0 ; $i<$size ; $i++)
            {
                 foreach($schedule[$i] as $key => $value)
                 {
                     //echo "<br>".$key.":".$value ;

                     if  ($key == "scrnNm")   { $scrnNm = iconv("UTF-8","EUC-KR",$value) ;   }
                     if  ($key == "movieCd")  { $movieCd = $value ;  }
                     if  ($key == "movieNm")  { $movieNm = iconv("UTF-8","EUC-KR",$value) ;  }
                     if  ($key == "showTm")
                     {
                          $showTms = split(",", $value);

                          $sQuery = "DELETE FROM kofic_showtime
                                           WHERE TheatherCd  = '$sTheatherCode'
                                                 `Date`      = '$ShowDate'
                                                 ScrnNm      = '$scrnNm'         " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                          mysql_query($sQuery,$connect) ;

                          $j = 1 ;
                          foreach ($showTms as $showTm)
                          {
                               $sQuery = "INSERT INTO kofic_showtime
                                               VALUES (
                                                       '$sTheatherCode'
                                                      ,'$ShowDate'
                                                      ,'$scrnNm'
                                                      ,$j
                                                      ,'$showTm'
                                                      ,'$movieCd'
                                                      ,'$movieNm'
                                                      )                   " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
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
                                        `Date`     = '$ShowDate'
                                        ScrnNm     = '$scrnNm'          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;

                 $sQuery = "INSERT INTO kofic_showroom
                                VALUES (
                                        '$sTheatherCode'
                                       ,'$ShowDate'
                                       ,'$scrnNm'
                                       ,'$movieCd'
                                       ,'$movieNm'
                                       ,0
                                       )                   " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                 mysql_query($sQuery,$connect) ;

            }

            $ShowDate = date("Ymd",strtotime("$day Day",time())) ;

        }
        //} // if  ($sTheatherCode == "005049") // CGV 서산
    }
?>



    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
