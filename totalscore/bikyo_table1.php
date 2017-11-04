<?
    session_start();
    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    $Ranks    = $_GET['Ranks'];
    $PlayDate = $_GET['PlayDate'];
    $ToExel   = $_GET['ToExel'];

    if  ($ToExel=="Yes")
    {
        header( "Content-type: application/vnd.ms-excel;charset=utf-8");
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0,pre-check=0" );
        header( "Pragma: public" );
        header( "Content-Disposition: attachment; filename=name_".date('Ymd').".xls" );
    }

    include "inc/Library.php" ;

?>

<!DOCTYPE html>
<html lang="kr">
    <head>

        <?if  ($ToExel!="Yes") include "inc/Head.inc"; ?>

        <script type="text/javascript">
        function active_css()
        {
            $('#menu2').attr("class","active has-sub");
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
            frmMain.submit();
        }

        function start_click()
        {
            var select = 0;
            var ranks = "";
            $("input[name=rank]:checked").each(function()
            {
                select ++ ;

                if  (ranks != "") ranks += ",";

                ranks += $(this).val();
            });

            if  (select > 0)
            {
                $('input[name=Ranks]').val(ranks);
            }

            frmMain.submit();
        }

        //
        // 엑셀 출력
        //
        function toexel_click()
        {
           botttomaddr = "<?=$_SERVER['PHP_SELF']?>"
                       + "?ToExel=Yes"
                       + "&Ranks=<?=$Ranks?>"
                       + "&PlayDate=<?=$PlayDate?>"
           //alert(botttomaddr) ;
           location.href = botttomaddr ;
        }

        function chang_rank(baseDate,cur,objval)
        {
            var options = {
                            _Gubun  : '1',
                            _date   : baseDate,
                            _cur    : cur,
                            _objval : objval
                          } ;
            $.post("bikyo_table_ajax.php", options, function(data)
            {
                $('#display').text(data);

                if (data=="UPDATE")
                {
                    if  (cur > objval)
                    {
                        for (i=cur;i>objval;i--)
                        {
                            changeTD(i,i-1) ;
                        }
                    }
                    if  (cur < objval)
                    {
                        for (i=cur;i<objval;i++)
                        {
                            changeTD(i,i+1) ;
                        }
                    }
                }
            });
        }
        function changeTD(a1,a2)
        {
            var $elem1 = $("#td"+a1);
            var $elem2 = $("#td"+a2);
            var $placeholder = $("<td></td>");

            $elem2.after($placeholder);
            $elem1.after($elem2);

            $placeholder.replaceWith($elem1);


            $elem1.attr("id","td"+a2);
            $elem2.attr("id","td"+a1);
        }
        function up_click(baseDate,cur)
        {
            if  (cur==1)
            {
                alert("더이상 이동할 수 없습니다.");
            }
            else
            {
                chang_rank(baseDate,cur,cur-1) ;
            }
        }
        function dn_click(baseDate,cur,end)
        {
            if  (cur==end)
            {
                alert("더이상 이동할 수 없습니다.");
            }
            else
            {
                chang_rank(baseDate,cur,cur+1) ;
            }
        }
        function mv_click(baseDate,cur,end)
        {
            var objval = $("#txt"+cur).val();

            if  (objval == cur) return; // 변경이 없는 경우..

            if  ((objval<1) || (objval>end))
            {
                alert("더이상 이동할 수 없습니다."); // 범위 초과
            }
            else
            {
                chang_rank(baseDate,cur,objval) ;
            }
            $("#txt"+cur).val(cur);
        }

        // 좌석수가 없는 상영관에 자석수 입력을 위한 팝업창을 띄운다..
        function setSeat(TheatherCd,ScrnNm)
        {
            var url='popup_setSeat.php?TheatherCd='+TheatherCd+'&ScrnNm='+ScrnNm;

            cw=screen.availWidth;     //화면 넓이
            ch=screen.availHeight;    //화면 높이

            sw=100;    //띄울 창의 넓이
            sh=60;    //띄울 창의 높이

            ml=(cw-sw)/2;        //가운데 띄우기위한 창의 x위치
            mt=(ch-sh)/2;         //가운데 띄우기위한 창의 y위치

            window.open(url,'_blank','width='+sw+',height='+sh+',top='+mt+',left='+ml+',resizable=no,scrollbars=yes');


            //window.open(url,'_blank', 'menubar=no,location=no,scrollbars=no,width=100,height=60,status=no,resizable=no,top=0,left=0,dependent=no,alwaysRaised=no');
        }


        </script>

        <title>경쟁영화 회차 비교표1</title>
    </head>
    <body>
<?
    if  (!session_is_registered("logged_UserId"))
    {
        ?>로그인을 해주세요!<?
    }
    else
    {
        if  ($ToExel!="Yes")
        {
            include "inc/Menu.inc";
            ?>
            <form name="frmMain" method="get" action="<?=$_SERVER['PHP_SELF']?>" >
                <input type="hidden" name="Ranks" />
                <br>
                년월일: <input type="text" name="PlayDate" class="datepicker" value="<?=$PlayDate?>" onchange="datepicker_change()">
                <button name="start" onclick="start_click()">집계</button>
                <?
                if  ($Ranks != null)
                {
                ?><a href=# onclick="toexel_click();"><img src="../mtnscokr/exel.gif" width="32" height="32" border="0"></a><?
                }
                ?>
                <br>
            </form>
            <br>
            <?
        }
        else
        {
            echo "경쟁영화 회차 비교표1<br>" ;
            echo $PlayDate ;
        }

        $PlayDate = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2); // 2016-06-06 -> 20160606
        $MaxDate  = Get_MaxDate_BoxOffice($connect) ;


        if  ($PlayDate >= $MaxDate) $BaseDate = $MaxDate ;   //  박스오피스 가장최근일자보다 뒤에 날짜를 조회하고자한다면..  -> 박스오피스 가장최근일자
        else                        $BaseDate = $PlayDate ; //  박스오피스 가장최근일자보다 앞에 날짜를 조회한다면.. -> 그대로 그 날짜로...

        if  ($Ranks == null) // 영화정보가 없는 경우.. 기본화면..
        {
            Make_Fix_Boxoffice($BaseDate,$connect) ;

            ?>
            <div class="t3">
              <table border="1">
              <tr>
                  <th>순위</th>
                  <th>영화명</th>
                  <th></th>
              </tr>
              <?
              $sQuery = "     SELECT *
                               FROM kofic_fix_boxoffice
                              WHERE `Date` = '$BaseDate'
                           ORDER BY Rank
                        " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);    // 박스오피스 리스트를 구한다.
              $QryBoxOffice = mysql_query($sQuery,$connect) ; $num_rows = mysql_num_rows($QryBoxOffice);
              while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
              {
                  $Rank     = $ArrBoxOffice['Rank'];
                  $MovieCd  = $ArrBoxOffice['MovieCd'];
                  $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                  $MovieName = $Rank.". ".$MovieNm;//."(".$MovieCd.")"
                  ?>
                  <tr>
                      <td class="ty2"><?=$Rank?></td>
                      <td id="td<?=$Rank?>"><label><input name="rank" id="Group" value="<?=$Rank?>" type="checkbox" />&nbsp;<?=$MovieNm?></label></td>
                      <td>

                          <button name="start" onclick="up_click('<?=$BaseDate?>',<?=$Rank?>)">↑</button>
                          <button name="start" onclick="dn_click('<?=$BaseDate?>',<?=$Rank?>,<?=$num_rows?>)">↓</button>

                          <input id="txt<?=$Rank?>" type="text" style="text-align:right;width: 40px;" size="3" value="<?=$Rank?>">
                          <button name="start" onclick="mv_click('<?=$BaseDate?>',<?=$Rank?>,<?=$num_rows?>)">이동</button>

                      </td>
                  </tr>
                  <?
                  //echo "<br>".$MovieNm."(".$MovieCd.")";
              }
              ?>
              </table>
            </div>
            <?
        }
        else  // 선택된 영화 랭킹자료가 있을 때....
        {
            ?>
            <div class="t3">
            <table border="1">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">극장명</th>
            <?

            $arrRank = split(",",$Ranks);
            for ($i=0;$i< sizeof($arrRank);$i++)
            {
                $sQuery = "SELECT *
                             FROM kofic_fix_boxoffice
                            WHERE `Date` = '$PlayDate'
                              AND Rank = $arrRank[$i]
                          " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.

                $QryBoxOffice = mysql_query($sQuery,$connect) ;
                while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                {
                    $Rank     = $ArrBoxOffice['Rank'];
                    $MovieCd  = $ArrBoxOffice['MovieCd'];
                    $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                    $MovieName = $Rank.". ".$MovieNm."(".$MovieCd.")";
                    ?>
                    <th colspan="5"><?=$MovieNm?></th>
                    <?
                }
            }
            ?>
            </tr>
            <tr>
            <?
            $arrRank = split(",",$Ranks);
            for ($i=0;$i< sizeof($arrRank);$i++)
            {
                ?>
                <th>상영관</th>
                <th>좌석수</th>
                <th>시간표</th>
                <th>회차수</th>
                <th>총좌석수</th>
                <?
            }
            ?>
            </tr>
            <?
            $no = 1 ;
            $sQuery = "     SELECT *
                              FROM kofic_theather t
                        INNER JOIN (SELECT * FROM kofic_fix_theather WHERE Active = 1) f
                                ON t.Code = f.Code
                          ORDER BY f.`Group`
                                  ,f.Location
                                  ,t.TheatherName
                       " ;// echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sTheatherCode = $ArrKoficTheather["Code"] ;
                $sTheatherName = $ArrKoficTheather["TheatherName"] ;
    //if  (($sTheatherCode != "001111")) continue; // CGV압구정

                $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);
                ?>
                <tr>
                    <td class="ty1"><?=$no++?></td>
                    <td><?=$sTheatherName/*."(".$sTheatherCode.")"*/?></td>
                    <?
                        $arrRank = split(",",$Ranks);

                        $sQuery = "SELECT *
                                     FROM kofic_fix_boxoffice
                                    WHERE `Date` = '$PlayDate'
                                      AND Rank  IN (
                                  ";
                                for ($i=0;$i< sizeof($arrRank);$i++)
                                {
                                    $sQuery .= "$arrRank[$i]" ;
                                    if  ($i< sizeof($arrRank)-1)
                                        $sQuery .= ", " ;
                                }
                        $sQuery .= " )" ;
                        $sQuery .= " ORDER BY Rank" ;     //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 박스오피스 리스트를 구한다.
                        $QryBoxOffice = mysql_query($sQuery,$connect) ;
                        while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                        {
                            $Rank      = $ArrBoxOffice['Rank'];
                            $MovieCd   = $ArrBoxOffice['MovieCd'];
                            $MovieNm   = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                            $MovieName = $Rank.". ".$MovieNm;//."(".$MovieCd.")";

                            $ScrnNames = "";
                            $Seats     = "";
                            $ShowTmss  = "";
                            $cntInning = "";
                            $sumSeats  = "";


                            $sQuery = "    SELECT a.ScrnNm
                                                 ,b.Seat
                                             FROM kofic_showtime a
                                        LEFT JOIN kofic_seat     b
                                               ON b.TheatherCd = a.TheatherCd
                                              AND b.ScrnNm     = a.ScrnNm
                                            WHERE 1 = 1
                                              AND a.TheatherCd = '$sTheatherCode'
                                              AND a.`Date`     = '$PlayDate'
                                              AND a.MovieCd    = '$MovieCd'
                                         GROUP BY a.ScrnNm
                                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryKoficPlaying = mysql_query($sQuery,$connect) ;
                            while  ($ArrKoficPlaying = mysql_fetch_array($QryKoficPlaying))
                            {
                                $ScrnNm  = $ArrKoficPlaying['ScrnNm'];
                                $ScrnNm_ = iconv("EUC-KR", "UTF-8",$ArrKoficPlaying['ScrnNm']);

                                $Seat    = $ArrKoficPlaying['Seat'];

                                $ShowTms = "";

                                $cntSeat = 0;

                                $sQuery = "    SELECT ShowTm
                                                 FROM kofic_showtime
                                                WHERE 1 = 1
                                                  AND TheatherCd = '$sTheatherCode'
                                                  AND `Date`     = '$PlayDate'
                                                  AND ScrnNm     = '$ScrnNm'
                                                  AND MovieCd    = '$MovieCd'
                                             ORDER BY Seq
                                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                                $QryShowTime = mysql_query($sQuery,$connect) ;
                                while  ($ArrShowTime = mysql_fetch_array($QryShowTime))
                                {
                                    $ShowTm = $ArrShowTime['ShowTm'] ;
                                    $ShowTm = substr($ShowTm,0,2) .":". substr($ShowTm,2,2);

                                    if  ($ShowTms != "") $ShowTms .= ",";

                                    $ShowTms .= $ShowTm;

                                    $cntSeat++;
                                }
                                $ShowTms .= " ";

                                if  ($Seat==null)
                                {
                                  $ScrnNames .= "<a href=\"#\" onclick=\"setSeat('".$sTheatherCode."','".$ScrnNm_."')\">".$ScrnNm_."</a><br>";
                                  $Seats     .= "없음<br>";
                                }
                                else
                                {
                                  $ScrnNames .= $ScrnNm_."<br>";
                                  $Seats     .= $Seat."<br>";
                                }

                                $cntInning .= $cntSeat."<br>";
                                if  ($ToExel=="Yes")  $sumSeats .= ($cntSeat*$Seat)."<br>";
                                else                  $sumSeats .= number_format($cntSeat*$Seat)."<br>";
                                $ShowTmss .= $ShowTms."<br>";
                            }
                            ?>
                            <td><?=$ScrnNames?></td>
                            <td class="ty2"><?=$Seats?></td>
                            <td style = "mso-number-format:\@"><?=$ShowTmss?></td>
                            <td class="ty2"><?=$cntInning?></td>
                            <td class="ty2"><?=$sumSeats?></td>
                            <?

                        }

                    ?>
                </tr>
                <?
                //echo "<tr>".$sTheatherCode."_".$sTheatherName." : 읽기";
            }
            ?>
            </table>
            </div>
            <?
        }
        ?>

        <table width="90%">
        <tr>
              <td><div id="display"></div></td>
        </tr>
        </table>
<?
    }
?>
    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
