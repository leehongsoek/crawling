<?
    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    $sQuery = "    SELECT JobCode
                         ,Status
                         ,Percent
                     FROM wrk_job
                 ORDER BY JobCode
                    LIMIT 0,1
              " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
    $QryWrkJob = mysql_query($sQuery,$connect) ;
    if  ($ArrWrkJob = mysql_fetch_array($QryWrkJob))
    {
        $JobCode = $ArrWrkJob["JobCode"] ;
        $Status  = $ArrWrkJob["Status"] ;
        $Percent = $ArrWrkJob["Percent"] ;

        echo iconv("EUC-KR", "UTF-8",$JobCode.'|'.$Status.'|'.$Percent.' %');
    }
    else
    {
        echo "|The End!|" ;
    }

    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>