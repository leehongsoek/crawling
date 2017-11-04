<?
    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정

    $connect = dbconn() ;           // {[데이터 베이스]} : 연결

    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

      $DelDate = date("Ymd",strtotime("-7 Day",time())) ;

        $sQuery = "DELETE FROM kofic_boxoffice
                         WHERE Date <= '$DelDate'
                  " ;echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        //mysql_query($sQuery,$connect) ;

        $sQuery = "DELETE FROM kofic_fix_boxoffice
                         WHERE Date <= '$DelDate'
                  " ;echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        //mysql_query($sQuery,$connect) ;

        $sQuery = "DELETE FROM kofic_playing
                         WHERE Date <= '$DelDate'
                  " ;echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        //mysql_query($sQuery,$connect) ;

        $sQuery = "DELETE FROM kofic_showtime
                         WHERE Date <= '$DelDate'
                  " ;echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        //mysql_query($sQuery,$connect) ;


    mysql_close($connect) ;      // {[데이터 베이스]} : 단절

?>