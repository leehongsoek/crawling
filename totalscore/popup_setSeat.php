<?
     set_time_limit(0) ;

     include "inc/config.php";       // {[데이터 베이스]} : 환경설정

     $connect = dbconn() ;           // {[데이터 베이스]} : 연결

     mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

     $TheatherCd = $_GET['TheatherCd'];
     $ScrnNm     = $_GET['ScrnNm'];

?>
<html lang="kr">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="application/vnd.ms-excel;charset=utf-8">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="./inc/styles.css">

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>

        <title>상영관 자석수 입력</title>

        <script type="text/javascript">
         //
         //   숫자만 입력 받도록 제한한다.
         //
         //
         //

         function input_check()
         {
            edit = $('input[name=seat]').val() ;

            if (edit !="")
            {
                if (edit.search(/\D/) != -1)
                {
                    alert("숫자만 입력시오!") ;

                    $('input[name=seat]').val("") ;

                    edit = edit.replace(/\D/g, "")

                    $('input[name=seat]').focus() ;

                    return false ;
                }
                else
                {
                    return true ;
                }
            }
            else
            {
                alert("좌석수를 입력시오!") ;

                return false ;
            }
         }


         function setSeat()
         {
            if  (input_check()==true)
            {
                $.post("popup_setSeat_ajax.php"
                       ,{ theatherCd  : '<?=$TheatherCd?>'
                         ,scrnNm      : '<?=$ScrnNm?>'
                         ,seat        : $('input[name=seat]').val()
                        }
                       ,function(data)
                        {
                            if (data == "UPDATE")  alert("갱신이 완료되었습니다."); //
                            if (data == "INSERT")  alert("추가가 완료되었습니다."); //

                            self.close();
                            opener.location.reload();
                        });
            }
         }

        </script>
    </head>
    <body>
<?
        $sQuery = "     SELECT *
                          FROM kofic_theather t
                         WHERE Code = '$TheatherCd'
                   " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
        $QryKoficTheather = mysql_query($sQuery,$connect) ;
        if  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
        {
            echo iconv("EUC-KR", "UTF-8", $ArrKoficTheather["TheatherName"]);
            echo "<br>";
            echo $ScrnNm;
            ?>
            <input name="seat" type="text" value="" style="text-align:right;width: 40px;"  size="3">
            <button name="setSeat" onclick="setSeat()">추가</button>
            <?
        }

?>
    </body>
</html>
<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
