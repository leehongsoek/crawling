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

<html>
    <head>
<?include "inc/Head.inc"; ?>

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


        </script>

        <title>경쟁영화 회차 비교표2</title>
    </head>
    <body>
    <?
    if  ($ToExel!="Yes")
    {
        include "inc/Menu.inc";
    ?>
        <form name="frmMain" method="get" action="<?=$_SERVER['PHP_SELF']?>" >
            <input type="hidden" name="Ranks" />
            <br>
            년월일: <input type="text" name="PlayDate" class="datepicker" value="<?=$PlayDate?>" onchange="datepicker_change()">
            <button name+"start" onclick="start_click()">집계</button>
            <?
            if  ($Ranks != null)
            {
            ?><a href=# onclick="toexel_click();"><img src="../mtnscokr/exel.gif" width="32" height="32" border="0"></a><?
            }
            ?>
            <br>
        </form>
    <?
    }
    else
    {
        echo "경쟁영화 회차 비교표2<br>" ;
        echo $PlayDate ;
    }

    $PlayDate_ = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2); // 2016-06-06 -> 20160606
    $MaxDate   = Get_MaxDate_BoxOffice($connect) ;

    if  ($PlayDate_ >= $MaxDate) $BaseDate = $MaxDate ;   //  박스오피스 가장최근일자보다 뒤에 날짜를 조회하고자한다면..  -> 박스오피스 가장최근일자
    else                         $BaseDate = $PlayDate_ ; //  박스오피스 가장최근일자보다 앞에 날짜를 조회한다면.. -> 그대로 그 날짜로...

    if  ($Ranks == null) // 영화정보가 없는 경우.. 기본화면..
    {
        Make_Fix_Boxoffice($BaseDate,$connect) ;

        $sQuery = "    SELECT *
                         FROM kofic_fix_boxoffice
                        WHERE `Date` = '$BaseDate'
                     ORDER BY Rank
                  " ; // echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.

        $QryBoxOffice = mysql_query($sQuery,$connect) ;
        while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
        {
            $Rank     = $ArrBoxOffice['Rank'];
            $MovieCd  = $ArrBoxOffice['MovieCd'];
            $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
            $MovieName = $Rank.". ".$MovieNm;//."(".$MovieCd.")"
            ?>
            <label><input name="rank" id="Group" value="<?=$Rank?>" type="checkbox" />&nbsp;<?=$MovieName?></label><br>
            <?
            //echo "<br>".$MovieNm."(".$MovieCd.")";
        }
    }
    else  // 선택된 영화 랭킹자료가 있을 때....
    {
        ?>
        <div class="t3">
            <table border="1">
            <tr>
                <th rowspan="2"></th>
                <th colspan="6">스크린</th>
                <th colspan="6">회차</th>
                <th colspan="6">총좌석수</th>
            </tr>
            <tr>
                <th  class="w100">CGV</th>
                <th  class="w100">롯데</th>
                <th  class="w100">메가</th>
                <th  class="w100">프리</th>
                <th  class="w100">기타</th>
                <th  class="w100">합계</th>
                <th  class="w100">CGV</th>
                <th  class="w100">롯데</th>
                <th  class="w100">메가</th>
                <th  class="w100">프리</th>
                <th  class="w100">기타</th>
                <th  class="w100">합계</th>
                <th  class="w100">CGV</th>
                <th  class="w100">롯데</th>
                <th  class="w100">메가</th>
                <th  class="w100">프리</th>
                <th  class="w100">기타</th>
                <th  class="w100">합계</th>
            </tr>
            <?
            $arrRank = split(",",$Ranks);
            for($i=0;$i< sizeof($arrRank);$i++)
            {
                $sQuery = "     SELECT *
                                 FROM kofic_boxoffice
                                WHERE `Date` = '$BaseDate'
                                  AND Rank = $arrRank[$i]
                          " ; // echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                $QryBoxOffice = mysql_query($sQuery,$connect) ;
                if  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                {
                    $Rank     = $ArrBoxOffice['Rank'];
                    $MovieCd  = $ArrBoxOffice['MovieCd'];
                    $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);

    //if  ($MovieCd != "20148048") continue;
                    ?>
                    <tr>
                        <td rowspan=2><?=$MovieNm?></td>
                        <?
                        $stack1 = array();
                        $stack2 = array();
                        $stack3 = array();
                        ?>

                        <?
                            $SumCntScrn = 0 ;
                            $sQuery = "    SELECT tmp.grp
                                                 ,IFNULL(ply.CntScrn, 0) CntScrn
                                             FROM ( SELECT 1 grp
                                                    UNION ALL SELECT 2
                                                    UNION ALL SELECT 3
                                                    UNION ALL SELECT 4
                                                    UNION ALL SELECT 9
                                                  ) tmp
                                        LEFT JOIN (   SELECT IF(fix.`Group`='',9,fix.`Group`) `Group`
                                                            ,count( play.ScrnNm )             CntScrn
                                                        FROM (
                                                                SELECT TheatherCd, `Date` , ScrnNm, MovieCd
                                                                  FROM kofic_showtime
                                                              GROUP BY TheatherCd, `Date` , ScrnNm, MovieCd
                                                             ) play
                                                   LEFT JOIN kofic_fix_theather fix
                                                          ON fix.Code = play.TheatherCd
                                                       WHERE 1 =1
                                                         AND play.`Date`  = '$PlayDate'
                                                         AND play.MovieCd = '$MovieCd'
                                                    GROUP BY fix.`Group`
                                                  ) ply
                                               ON ply.`Group` = tmp.grp
                                         ORDER BY tmp.grp
                                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryKoficPlaying = mysql_query($sQuery,$connect) ;
                            while  ($ArrKoficPlaying = mysql_fetch_array($QryKoficPlaying))
                            {
                                $CntScrn = $ArrKoficPlaying["CntScrn"] ;
                                array_push($stack1, $CntScrn);

                                $SumCntScrn += $CntScrn ;

                                /*if  ($ToExel!="Yes") */ $CntScrn = number_format($CntScrn);

                                ?><td class="ty2"><?=$CntScrn?></td><?
                            }

                            $stack1_sum = $SumCntScrn;

                            /*if  ($ToExel!="Yes") */ $SumCntScrn = number_format($SumCntScrn);

                            ?><td class="ty2"><?=$SumCntScrn?></td><?
                        ?>
                        <?
                            $SumCntShowTm = 0;
                            $sQuery = "    SELECT tmp.grp
                                                 ,IFNULL(ply.CntShowTm, 0) CntShowTm
                                             FROM ( SELECT 1 grp
                                                    UNION ALL SELECT 2
                                                    UNION ALL SELECT 3
                                                    UNION ALL SELECT 4
                                                    UNION ALL SELECT 9
                                                  ) tmp
                                        LEFT JOIN (   SELECT IF( fix.`Group` = '', 9, fix.`Group` ) `Group`
                                                            ,count( play.ShowTm ) CntShowTm
                                                        FROM kofic_showtime  play
                                                   LEFT JOIN kofic_fix_theather fix
                                                          ON fix.Code = play.TheatherCd
                                                       WHERE 1 =1
                                                         AND play.`Date`  = '$PlayDate'
                                                         AND play.MovieCd = '$MovieCd'
                                                    GROUP BY fix.`Group`
                                                  ) ply
                                               ON ply.`Group` = tmp.grp
                                         ORDER BY tmp.grp
                                      " ;// echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryKoficPlaying = mysql_query($sQuery,$connect) ;
                            while  ($ArrKoficPlaying = mysql_fetch_array($QryKoficPlaying))
                            {
                                $CntShowTm = $ArrKoficPlaying["CntShowTm"] ;
                                array_push($stack2, $CntShowTm);
                                $SumCntShowTm += $CntShowTm ;

                                /*if  ($ToExel!="Yes") */ $CntShowTm = number_format($CntShowTm);

                                ?><td class="ty2"><?=$CntShowTm?></td><?
                            }

                            $stack2_sum = $SumCntShowTm;

                            /*if  ($ToExel!="Yes") */ $SumCntShowTm = number_format($SumCntShowTm);

                            ?><td class="ty2"><?=$SumCntShowTm?></td><?
                        ?>
                        <?
                            $SumSumSeat = 0 ;
                            $sQuery = "    SELECT tmp.grp
                                                 ,SumSeat
                                             FROM ( SELECT 1 grp
                                                    UNION ALL SELECT 2
                                                    UNION ALL SELECT 3
                                                    UNION ALL SELECT 4
                                                    UNION ALL SELECT 9
                                                  ) tmp
                                        LEFT JOIN (   SELECT IF( fix.`Group` = '', 9, fix.`Group` ) `Group`
                                                            ,sum(seat.Seat) SumSeat
                                                        FROM kofic_showtime  shtm
                                                   LEFT JOIN kofic_fix_theather fix
                                                          ON fix.Code = shtm.TheatherCd
                                                   LEFT JOIN kofic_seat seat
                                                          ON seat.TheatherCd  = shtm.TheatherCd
                                                         AND seat.ScrnNm      = shtm.ScrnNm
                                                       WHERE 1 =1
                                                         AND shtm.`Date`  = '$PlayDate'
                                                         AND shtm.MovieCd = '$MovieCd'
                                                    GROUP BY fix.`Group`
                                                  ) ply
                                               ON ply.`Group` = tmp.grp
                                         ORDER BY tmp.grp
                                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryKoficPlaying = mysql_query($sQuery,$connect) ;
                            while  ($ArrKoficPlaying = mysql_fetch_array($QryKoficPlaying))
                            {
                                $SumSeat = $ArrKoficPlaying["SumSeat"] ;
                                array_push($stack3, $SumSeat);

                                $SumSumSeat += $SumSeat ;

                                /*if  ($ToExel!="Yes") */ $SumSeat = number_format($SumSeat);

                                ?><td class="ty2"><?=$SumSeat?></td><?
                            }

                            $stack3_sum = $SumSumSeat;

                            /*if  ($ToExel!="Yes") */ $SumSumSeat = number_format($SumSumSeat);

                            ?><td class="ty2"><?=$SumSumSeat?></td><?
                        ?>
                    </tr>
                    <tr>
                        <?
                        foreach($stack1 as $stack)
                        {
                            if  (($stack1_sum>0) && ($stack>0)) echo "<td class=\"ty2\">".number_format(($stack/$stack1_sum)*100.0,1)." %</td>";
                            else echo "<td class=\"ty2\">0 %</td>";
                        }
                        if  ($stack1_sum == 0) echo "<td class=\"ty2\">0 %</td>";
                        else echo "<td class=\"ty2\">100 %</td>";
                        ?>
                        <?
                        foreach($stack2 as $stack)
                        {
                            if  (($stack2_sum>0) && ($stack2>0)) echo "<td class=\"ty2\">".number_format(($stack/$stack2_sum)*100.0,1)." %</td>";
                            else echo "<td class=\"ty2\">0 %</td>";
                        }
                        if  ($stack2_sum == 0) echo "<td class=\"ty2\">0 %</td>";
                        else echo "<td class=\"ty2\">100 %</td>";
                        ?>
                        <?
                        foreach($stack3 as $stack)
                        {
                            if  (($stack3_sum>0) && ($stack>0)) echo "<td class=\"ty2\">".number_format(($stack/$stack3_sum)*100.0,1)." %</td>";
                            else echo "<td class=\"ty2\">0 %</td>";
                        }
                        if  ($stack3_sum == 0) echo "<td class=\"ty2\">0 %</td>";
                        else echo "<td class=\"ty2\">100 %</td>";
                        ?>
                    </tr>
                    <?

                    //echo "<br>".$MovieNm."(".$MovieCd.")";
                }
            }
            ?>
            </table>
        </div>
        <?

    }
    ?>


    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
