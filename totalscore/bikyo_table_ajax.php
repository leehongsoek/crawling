<?
    $Gubun  = $_POST["_Gubun"] ;
    $date   = $_POST["_date"] ;
    $cur    = $_POST["_cur"] ;
    $objval = $_POST["_objval"] ;


    include "inc/config.php";       // {[데이터 베이스]} : 환경설정

    $connect = dbconn() ;           // {[데이터 베이스]} : 연결

    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택


    if  ($Gubun == "1")
    {
        $sQuery = "UPDATE kofic_fix_boxoffice
                      SET Rank = 0
                    WHERE `Date` = '$date'
                      AND Rank   = $cur
                 "; // echo $squery;
        mysql_query($sQuery,$connect) ;

        if  ($cur > $objval)
        {
            $sQuery = "UPDATE kofic_fix_boxoffice
                          SET Rank = Rank + 1
                        WHERE `Date` = '$date'
                          AND Rank   < $cur
                          AND Rank  >= $objval
                     ORDER BY Rank DESC
                     "; // echo $squery;
            mysql_query($sQuery,$connect) ;

            $sQuery = "UPDATE kofic_fix_boxoffice
                          SET Rank = $objval
                        WHERE `Date` = '$date'
                          AND Rank   = 0
                     "; // echo $squery;
            mysql_query($sQuery,$connect) ;
        }
        if  ($cur < $objval)
        {
            $sQuery = "UPDATE kofic_fix_boxoffice
                          SET Rank = Rank - 1
                        WHERE `Date` = '$date'
                          AND Rank   > $cur
                          AND Rank  <= $objval
                     ORDER BY Rank
                     "; // echo $squery;
            mysql_query($sQuery,$connect) ;

            $sQuery = "UPDATE kofic_fix_boxoffice
                          SET Rank = $objval
                        WHERE `Date` = '$date'
                          AND Rank   = 0
                     "; // echo $squery;
            mysql_query($sQuery,$connect) ;
        }
    }

    echo "UPDATE";

    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>