<?

     $active       = $_POST["active"] ;
     $theatherCd   = $_POST["theatherCd"] ;
     $Location     = $_POST["Location"] ;
     $theatherName = $_POST["theatherName"] ;
     $Group        = $_POST["Group"] ;

     $Location     = iconv("UTF-8", "EUC-KR",$Location) ;
     $theatherName = iconv("UTF-8", "EUC-KR",$theatherName) ;

     include "inc/config.php";       // {[데이터 베이스]} : 환경설정

     $connect = dbconn() ;           // {[데이터 베이스]} : 연결

     mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

     $sQuery = " SELECT count(*) cnt
                   FROM kofic_fix_theather b
                  WHERE Code = '$theatherCd'
              "; //echo $sQuery;
    $QryCntTheather = mysql_query($sQuery,$connect) ;
    if ($ArrCntTheather = mysql_fetch_array($QryCntTheather))
    {
        if  ($ArrCntTheather["cnt"] == 0)
        {
             $sQuery = "INSERT INTO kofic_fix_theather
                                    (Code
                                    ,Location
                                    ,TheatherName
                                    ,`Group`
                                    ,Active
                                    )
                             VALUES ('$theatherCd'
                                    ,'$Location'
                                    ,'$theatherName'
                                    ,'$Group'
                                    ,$active
                                    )
                       "; echo "INSERT"; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        }
        else
        {
             $sQuery = "UPDATE kofic_fix_theather
                           SET Location = '$Location'
                              ,`Group`  = '$Group'
                              ,Active   = $active
                         WHERE Code = '$theatherCd'
                      "; echo "UPDATE"; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        }
        mysql_query($sQuery,$connect) ;
    }

    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>