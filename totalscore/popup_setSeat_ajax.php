<?

     $theatherCd  = $_POST["theatherCd"] ;
     $scrnNm      = $_POST["scrnNm"] ;
     $seat        = $_POST["seat"] ;

     include "inc/config.php";       // {[데이터 베이스]} : 환경설정

     $connect = dbconn() ;           // {[데이터 베이스]} : 연결

     mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

	 $ScrnNm = iconv("UTF-8","EUC-KR",$scrnNm);  // 한글처리

     $sQuery = "    INSERT INTO kofic_seat
                                (TheatherCd,ScrnNm,Seat)
                         VALUES ('$theatherCd','$ScrnNm',$seat)
               ON DUPLICATE KEY UPDATE Seat = $seat
              "; //echo $sQuery;
    mysql_query($sQuery,$connect) ;

    echo "INSERT";


    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>