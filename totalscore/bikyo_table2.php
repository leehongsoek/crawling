<?
    require('FirePHPCore/FirePHP.class.php');
    ob_start();

    $firephp = FirePHP::getInstance(true);

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

    function GetSign($val)
    {
        if  ($val == 0)  return "□" ;
        else
        {
            if  ($val < 0)  return "▽" ;
            if  ($val > 0)  return "△" ;
        }
    }

    include "inc/Library.php" ;

?>

<!DOCTYPE html>
<html lang="kr">
    <head>

        <?if  ($ToExel!="Yes") include "inc/Head.inc"; ?>

        <script src="/amcharts/amcharts.js" type="text/javascript"></script>
        <script src="/amcharts/pie.js" type="text/javascript"></script>

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

        </script>

        <title>경쟁영화 회차 비교표2</title>
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
            echo "경쟁영화 회차 비교표2<br>" ;
            echo $PlayDate ;
        }

        //$WeekAgoDay = date("Ymd", strtotime($PlayDate."-1day"));

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
                        " ; // echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
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
                    <th rowspan="2" style="width: 200px;">영화명</th>
                    <th colspan="6">극장</th>
                    <th colspan="6">스크린</th>
                    <th colspan="6">회차</th>
                    <th colspan="6">총좌석수</th>
                </tr>
                <tr>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <th class="w100">프리</th>
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <th class="w100">프리</th>
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <th class="w100">프리</th>
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <th class="w100">프리</th>
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                </tr>
                <?

                $Script1 = "" ;
                $Script2 = "" ;
                $Script3 = "" ;
                $Script4 = "" ;

                $arrRank = split(",",$Ranks);
                for ($i=0;$i< sizeof($arrRank);$i++)
                {
                    $sQuery = "     SELECT *
                                     FROM kofic_fix_boxoffice
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

                            <?
                            $sQuery = "        SELECT '$MovieCd'                                    MovieCd
                                                      ,rpt.`Date`                                   `Date`
                                                      ,if(isnull(CntTheather1),0,CntTheather1)      CntTheather1
                                                      ,if(isnull(CntTheather2),0,CntTheather2)      CntTheather2
                                                      ,if(isnull(CntTheather3),0,CntTheather3)      CntTheather3
                                                      ,if(isnull(CntTheather4),0,CntTheather4)      CntTheather4
                                                      ,if(isnull(CntTheather9),0,CntTheather9)      CntTheather9
                                                      ,if(isnull(CntTheatherSum),0,CntTheatherSum)  CntTheatherSum
                                                      ,if(isnull(CntScrn1),0,CntScrn1)              CntScrn1
                                                      ,if(isnull(CntScrn2),0,CntScrn2)              CntScrn2
                                                      ,if(isnull(CntScrn3),0,CntScrn3)              CntScrn3
                                                      ,if(isnull(CntScrn4),0,CntScrn4)              CntScrn4
                                                      ,if(isnull(CntScrn9),0,CntScrn9)              CntScrn9
                                                      ,if(isnull(CntScrnSum),0,CntScrnSum)          CntScrnSum
                                                      ,if(isnull(CntShowTm1),0,CntShowTm1)          CntShowTm1
                                                      ,if(isnull(CntShowTm2),0,CntShowTm2)          CntShowTm2
                                                      ,if(isnull(CntShowTm3),0,CntShowTm3)          CntShowTm3
                                                      ,if(isnull(CntShowTm4),0,CntShowTm4)          CntShowTm4
                                                      ,if(isnull(CntShowTm9),0,CntShowTm9)          CntShowTm9
                                                      ,if(isnull(CntShowTmSum),0,CntShowTmSum)      CntShowTmSum
                                                      ,if(isnull(SumSeat1),0,SumSeat1)              SumSeat1
                                                      ,if(isnull(SumSeat2),0,SumSeat2)              SumSeat2
                                                      ,if(isnull(SumSeat3),0,SumSeat3)              SumSeat3
                                                      ,if(isnull(SumSeat4),0,SumSeat4)              SumSeat4
                                                      ,if(isnull(SumSeat9),0,SumSeat9)              SumSeat9
                                                      ,if(isnull(SumSeatSum),0,SumSeatSum)          SumSeatSum
                                                 FROM ( SELECT A.MovieCd
                                                              ,A.`Date`
                                                              ,SUM(if (C.X = 0 and A.grp=1, A.CntTheather,0)) CntTheather1
                                                              ,SUM(if (C.X = 0 and A.grp=2, A.CntTheather,0)) CntTheather2
                                                              ,SUM(if (C.X = 0 and A.grp=3, A.CntTheather,0)) CntTheather3
                                                              ,SUM(if (C.X = 0 and A.grp=4, A.CntTheather,0)) CntTheather4
                                                              ,SUM(if (C.X = 0 and A.grp=9, A.CntTheather,0)) CntTheather9
                                                              ,SUM(if (C.X = 0 and A.grp=1, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=2, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=3, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=4, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=9, A.CntTheather,0)) CntTheatherSum
                                                              ,SUM(if (C.X = 1 and A.grp=1, A.CntScrn,0)) CntScrn1
                                                              ,SUM(if (C.X = 1 and A.grp=2, A.CntScrn,0)) CntScrn2
                                                              ,SUM(if (C.X = 1 and A.grp=3, A.CntScrn,0)) CntScrn3
                                                              ,SUM(if (C.X = 1 and A.grp=4, A.CntScrn,0)) CntScrn4
                                                              ,SUM(if (C.X = 1 and A.grp=9, A.CntScrn,0)) CntScrn9
                                                              ,SUM(if (C.X = 1 and A.grp=1, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=2, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=3, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=4, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=9, A.CntScrn,0)) CntScrnSum
                                                              ,SUM(if (C.X = 2 and A.grp=1, A.CntShowTm,0)) CntShowTm1
                                                              ,SUM(if (C.X = 2 and A.grp=2, A.CntShowTm,0)) CntShowTm2
                                                              ,SUM(if (C.X = 2 and A.grp=3, A.CntShowTm,0)) CntShowTm3
                                                              ,SUM(if (C.X = 2 and A.grp=4, A.CntShowTm,0)) CntShowTm4
                                                              ,SUM(if (C.X = 2 and A.grp=9, A.CntShowTm,0)) CntShowTm9
                                                              ,SUM(if (C.X = 2 and A.grp=1, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=2, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=3, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=4, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=9, A.CntShowTm,0)) CntShowTmSum
                                                              ,SUM(if (C.X = 3 and A.grp=1, A.SumSeat,0)) SumSeat1
                                                              ,SUM(if (C.X = 3 and A.grp=2, A.SumSeat,0)) SumSeat2
                                                              ,SUM(if (C.X = 3 and A.grp=3, A.SumSeat,0)) SumSeat3
                                                              ,SUM(if (C.X = 3 and A.grp=4, A.SumSeat,0)) SumSeat4
                                                              ,SUM(if (C.X = 3 and A.grp=9, A.SumSeat,0)) SumSeat9
                                                              ,SUM(if (C.X = 3 and A.grp=1, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=2, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=3, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=4, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=9, A.SumSeat,0)) SumSeatSum
                                                          FROM (
                                                                  SELECT a.MovieCd
                                                                        ,a.`Date`
                                                                        ,a.grp
                                                                        ,CntTheather
                                                                        ,CntScrn
                                                                        ,CntShowTm
                                                                        ,SumSeat
                                                                    FROM (
                                                                                SELECT tmp.grp
                                                                                      ,ply.`Date`
                                                                                      ,ply.MovieCd
                                                                                      ,IFNULL(ply.CntTheather, 0) CntTheather
                                                                                  FROM ( SELECT 1 grp
                                                                                          UNION ALL SELECT 2
                                                                                          UNION ALL SELECT 3
                                                                                          UNION ALL SELECT 4
                                                                                          UNION ALL SELECT 9
                                                                                        ) tmp
                                                                              LEFT JOIN (   SELECT IF(fix.`Group`='',9,fix.`Group`) `Group`
                                                                                                  ,play.`Date`
                                                                                                  ,play.MovieCd
                                                                                                  ,count( play.TheatherCd ) CntTheather
                                                                                              FROM (
                                                                                                      SELECT stm.TheatherCd, stm.`Date`, stm.MovieCd
                                                                                                        FROM kofic_showtime stm
                                                                                                  INNER JOIN kofic_seat st
                                                                                                          ON st.TheatherCd = stm.TheatherCd
                                                                                                         AND st.ScrnNm     = stm.ScrnNm
                                                                                                       WHERE `Date`  = '$BaseDate'
                                                                                                         AND MovieCd = '$MovieCd'
                                                                                                    GROUP BY TheatherCd, `Date` , MovieCd
                                                                                                  ) play
                                                                                       INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                               ON fix.Code = play.TheatherCd
                                                                                            WHERE 1 =1
                                                                                              AND play.MovieCd = '$MovieCd'
                                                                                         GROUP BY fix.`Group`
                                                                                                 ,play.`Date`
                                                                                        ) ply
                                                                                    ON ply.`Group` = tmp.grp
                                                                              ORDER BY tmp.grp
                                                                        ) c,
                                                                        (
                                                                                SELECT tmp.grp
                                                                                      ,ply.`Date`
                                                                                      ,ply.MovieCd
                                                                                      ,IFNULL(ply.CntScrn, 0) CntScrn
                                                                                  FROM ( SELECT 1 grp
                                                                                          UNION ALL SELECT 2
                                                                                          UNION ALL SELECT 3
                                                                                          UNION ALL SELECT 4
                                                                                          UNION ALL SELECT 9
                                                                                        ) tmp
                                                                              LEFT JOIN (   SELECT IF(fix.`Group`='',9,fix.`Group`) `Group`
                                                                                                  ,play.`Date`
                                                                                                  ,play.MovieCd
                                                                                                  ,count( play.ScrnNm ) CntScrn
                                                                                              FROM (
                                                                                                      SELECT stm.TheatherCd, stm.`Date`, stm.ScrnNm, stm.MovieCd
                                                                                                        FROM kofic_showtime stm
                                                                                                  INNER JOIN kofic_seat st
                                                                                                          ON st.TheatherCd = stm.TheatherCd
                                                                                                         AND st.ScrnNm     = stm.ScrnNm
                                                                                                       WHERE `Date`  = '$BaseDate'
                                                                                                         AND MovieCd = '$MovieCd'
                                                                                                    GROUP BY stm.TheatherCd, stm.`Date`, stm.ScrnNm, stm.MovieCd
                                                                                                  ) play
                                                                                       INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                               ON fix.Code = play.TheatherCd
                                                                                            WHERE 1 =1
                                                                                              AND play.MovieCd = '$MovieCd'
                                                                                         GROUP BY fix.`Group`
                                                                                                 ,play.`Date`
                                                                                        ) ply
                                                                                    ON ply.`Group` = tmp.grp
                                                                              ORDER BY tmp.grp
                                                                        ) a,
                                                                        (
                                                                              SELECT tmp.grp
                                                                                    ,`Date`
                                                                                    ,ply.MovieCd
                                                                                    ,IFNULL(ply.CntShowTm, 0) CntShowTm
                                                                                    ,IFNULL(ply.SumSeat, 0) SumSeat
                                                                                FROM ( SELECT 1 grp
                                                                                      UNION ALL SELECT 2
                                                                                      UNION ALL SELECT 3
                                                                                      UNION ALL SELECT 4
                                                                                      UNION ALL SELECT 9
                                                                                    ) tmp
                                                                          LEFT JOIN (   SELECT IF( fix.`Group` = '', 9, fix.`Group` ) `Group`
                                                                                              ,play.`Date`
                                                                                              ,play.MovieCd
                                                                                              ,count( play.ShowTm ) CntShowTm
                                                                                              ,sum(seat.Seat)       SumSeat
                                                                                          FROM kofic_showtime  play
                                                                                    INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                            ON fix.Code = play.TheatherCd
                                                                                    INNER JOIN kofic_seat seat
                                                                                            ON seat.TheatherCd  = play.TheatherCd
                                                                                           AND seat.ScrnNm      = play.ScrnNm
                                                                                         WHERE 1 =1
                                                                                           AND play.`Date`  = '$BaseDate'
                                                                                           AND play.MovieCd = '$MovieCd'
                                                                                      GROUP BY fix.`Group`
                                                                                              ,play.`Date`
                                                                                    ) ply
                                                                                  ON ply.`Group` = tmp.grp
                                                                            ORDER BY tmp.grp
                                                                        ) b
                                                                  WHERE a.grp    = b.grp
                                                                    AND a.`Date` = b.`Date`
                                                                    And c.grp    = b.grp
                                                                    AND c.`Date` = b.`Date`
                                                               ) A
                                                              ,(
                                                                  SELECT 0 X
                                                                  UNION ALL
                                                                  SELECT 1
                                                                  UNION ALL
                                                                  SELECT 2
                                                                  UNION ALL
                                                                  SELECT 3
                                                               )  C
                                                         WHERE 1=1
                                                      GROUP BY A.MovieCd
                                                             ,A.`Date`
                                                      ) rpt
                                      " ; $firephp->fb($sQuery); // echo "<br><br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                            $QryReport = mysql_query($sQuery,$connect) ; $chkOne = 0;
                            while ($ArrReport = mysql_fetch_array($QryReport))
                            {
                              $chkOne ++ ;
                              if  ($ArrReport["Date"] == $BaseDate)
                              {
                                ?>
                                <tr>
                                <td rowspan=2><?=$MovieNm?></td>
                                <?
                                $CntTheather1  = $ArrReport["CntTheather1"] ;
                                $CntTheather2  = $ArrReport["CntTheather2"] ;
                                $CntTheather3  = $ArrReport["CntTheather3"] ;
                                $CntTheather4  = $ArrReport["CntTheather4"] ;
                                $CntTheather9  = $ArrReport["CntTheather9"] ;
                                $CntTheatherSum= $ArrReport["CntTheatherSum"] ;
                                $CntScrn1      = $ArrReport["CntScrn1"] ;
                                $CntScrn2      = $ArrReport["CntScrn2"] ;
                                $CntScrn3      = $ArrReport["CntScrn3"] ;
                                $CntScrn4      = $ArrReport["CntScrn4"] ;
                                $CntScrn9      = $ArrReport["CntScrn9"] ;
                                $CntScrnSum    = $ArrReport["CntScrnSum"] ;
                                $CntShowTm1    = $ArrReport["CntShowTm1"] ;
                                $CntShowTm2    = $ArrReport["CntShowTm2"] ;
                                $CntShowTm3    = $ArrReport["CntShowTm3"] ;
                                $CntShowTm4    = $ArrReport["CntShowTm4"] ;
                                $CntShowTm9    = $ArrReport["CntShowTm9"] ;
                                $CntShowTmSum  = $ArrReport["CntShowTmSum"] ;
                                $SumSeat1      = $ArrReport["SumSeat1"] ;
                                $SumSeat2      = $ArrReport["SumSeat2"] ;
                                $SumSeat3      = $ArrReport["SumSeat3"] ;
                                $SumSeat4      = $ArrReport["SumSeat4"] ;
                                $SumSeat9      = $ArrReport["SumSeat9"] ;
                                $SumSeatSum    = $ArrReport["SumSeatSum"] ;

                                $Script1 .= "chartData1.push({ \"category\": \"$MovieNm\",  \"value\": $CntTheatherSum }); \n" ;
                                $Script2 .= "chartData2.push({ \"category\": \"$MovieNm\",  \"value\": $CntScrnSum }); \n" ;
                                $Script3 .= "chartData3.push({ \"category\": \"$MovieNm\",  \"value\": $CntShowTmSum }); \n" ;
                                $Script4 .= "chartData4.push({ \"category\": \"$MovieNm\",  \"value\": $SumSeatSum }); \n" ;

                                ?><td class="ty2"><?=number_format($CntTheather1)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather2)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather3)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather4)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather9)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheatherSum)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn1)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn2)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn3)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn4)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn9)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrnSum)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm1)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm2)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm3)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm4)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm9)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTmSum)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat1)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat2)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat3)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat4)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat9)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeatSum)?></td><?
                                ?>
                                </tr>
                                <?
                              }


                              if  ($chkOne==1)
                              {
                                ?>
                                <tr>
                                <?

                                $RtCntTheather1  = (($CntTheather1>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather1 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather2  = (($CntTheather2>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather2 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather3  = (($CntTheather3>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather3 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather4  = (($CntTheather4>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather4 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather9  = (($CntTheather9>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather9 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheatherSum= ($CntTheatherSum>0) ? "100 %" : "0.0 %" ;
                                $RtCntScrn1      = (($CntScrn1>0) && ($CntScrnSum>0))  ? number_format(($CntScrn1 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn2      = (($CntScrn2>0) && ($CntScrnSum>0))  ? number_format(($CntScrn2 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn3      = (($CntScrn3>0) && ($CntScrnSum>0))  ? number_format(($CntScrn3 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn4      = (($CntScrn4>0) && ($CntScrnSum>0))  ? number_format(($CntScrn4 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn9      = (($CntScrn9>0) && ($CntScrnSum>0))  ? number_format(($CntScrn9 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrnSum    = ($CntScrnSum>0) ? "100 %" : "0.0 %" ;
                                $RtCntShowTm1    = (($CntShowTm1>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm1 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm2    = (($CntShowTm2>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm2 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm3    = (($CntShowTm3>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm3 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm4    = (($CntShowTm4>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm4 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm9    = (($CntShowTm9>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm9 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTmSum  = ($CntShowTmSum>0) ? "100 %" : "0.0 %" ;
                                $RtSumSeat1      = (($SumSeat1>0) && ($SumSeatSum>0))  ? number_format(($SumSeat1 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat2      = (($SumSeat2>0) && ($SumSeatSum>0))  ? number_format(($SumSeat2 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat3      = (($SumSeat3>0) && ($SumSeatSum>0))  ? number_format(($SumSeat3 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat4      = (($SumSeat4>0) && ($SumSeatSum>0))  ? number_format(($SumSeat4 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat9      = (($SumSeat9>0) && ($SumSeatSum>0))  ? number_format(($SumSeat9 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeatSum    = ($SumSeatSum>0) ? "100 %" : "0.0 %" ;


                                ?><td class="ty2"><?=$RtCntTheather1?></td><?
                                ?><td class="ty2"><?=$RtCntTheather2?></td><?
                                ?><td class="ty2"><?=$RtCntTheather3?></td><?
                                ?><td class="ty2"><?=$RtCntTheather4?></td><?
                                ?><td class="ty2"><?=$RtCntTheather9?></td><?
                                ?><td class="ty2"><?=$RtCntTheatherSum?></td><?
                                ?><td class="ty2"><?=$RtCntScrn1?></td><?
                                ?><td class="ty2"><?=$RtCntScrn2?></td><?
                                ?><td class="ty2"><?=$RtCntScrn3?></td><?
                                ?><td class="ty2"><?=$RtCntScrn4?></td><?
                                ?><td class="ty2"><?=$RtCntScrn9?></td><?
                                ?><td class="ty2"><?=$RtCntScrnSum?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm1?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm2?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm3?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm4?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm9?></td><?
                                ?><td class="ty2"><?=$RtCntShowTmSum?></td><?
                                ?><td class="ty2"><?=$RtSumSeat1?></td><?
                                ?><td class="ty2"><?=$RtSumSeat2?></td><?
                                ?><td class="ty2"><?=$RtSumSeat3?></td><?
                                ?><td class="ty2"><?=$RtSumSeat4?></td><?
                                ?><td class="ty2"><?=$RtSumSeat9?></td><?
                                ?><td class="ty2"><?=$RtSumSeatSum?></td><?
                                ?>
                                </tr>
                                <?
                              }

                              /*
                              if  ($ArrReport["Date"] == $WeekAgoDay)
                              {
                                $GapCntScrn1      = $CntScrn1 - $ArrReport["CntScrn1"] ;
                                $GapCntScrn2      = $CntScrn2 - $ArrReport["CntScrn2"] ;
                                $GapCntScrn3      = $CntScrn3 - $ArrReport["CntScrn3"] ;
                                $GapCntScrn4      = $CntScrn4 - $ArrReport["CntScrn4"] ;
                                $GapCntScrn9      = $CntScrn9 - $ArrReport["CntScrn9"] ;
                                $GapCntScrnSum    = $CntScrnSum - $ArrReport["CntScrnSum"] ;
                                $GapCntShowTm1    = $CntShowTm1 - $ArrReport["CntShowTm1"] ;
                                $GapCntShowTm2    = $CntShowTm2 - $ArrReport["CntShowTm2"] ;
                                $GapCntShowTm3    = $CntShowTm3 - $ArrReport["CntShowTm3"] ;
                                $GapCntShowTm4    = $CntShowTm4 - $ArrReport["CntShowTm4"] ;
                                $GapCntShowTm9    = $CntShowTm9 - $ArrReport["CntShowTm9"] ;
                                $GapCntShowTmSum  = $CntShowTmSum - $ArrReport["CntShowTmSum"] ;
                                $GapSumSeat1      = $SumSeat1 - $ArrReport["SumSeat1"] ;
                                $GapSumSeat2      = $SumSeat2 - $ArrReport["SumSeat2"] ;
                                $GapSumSeat3      = $SumSeat3 - $ArrReport["SumSeat3"] ;
                                $GapSumSeat4      = $SumSeat4 - $ArrReport["SumSeat4"] ;
                                $GapSumSeat9      = $SumSeat9 - $ArrReport["SumSeat9"] ;
                                $GapSumSeatSum    = $SumSeatSum - $ArrReport["SumSeatSum"] ;

                                $SgnCntScrn1     = GetSign($GapCntScrn1) ;
                                $SgnCntScrn2     = GetSign($GapCntScrn2) ;
                                $SgnCntScrn3     = GetSign($GapCntScrn3) ;
                                $SgnCntScrn4     = GetSign($GapCntScrn4) ;
                                $SgnCntScrn9     = GetSign($GapCntScrn9) ;
                                $SgnCntScrnSum   = GetSign($GapCntScrnSum) ;
                                $SgnCntShowTm1   = GetSign($GapCntShowTm1) ;
                                $SgnCntShowTm2   = GetSign($GapCntShowTm2) ;
                                $SgnCntShowTm3   = GetSign($GapCntShowTm3) ;
                                $SgnCntShowTm4   = GetSign($GapCntShowTm4) ;
                                $SgnCntShowTm9   = GetSign($GapCntShowTm9) ;
                                $SgnCntShowTmSum = GetSign($GapCntShowTmSum) ;
                                $SgnSumSeat1     = GetSign($GapSumSeat1) ;
                                $SgnSumSeat2     = GetSign($GapSumSeat2) ;
                                $SgnSumSeat3     = GetSign($GapSumSeat3) ;
                                $SgnSumSeat4     = GetSign($GapSumSeat4) ;
                                $SgnSumSeat9     = GetSign($GapSumSeat9) ;
                                $SgnSumSeatSum   = GetSign($GapSumSeatSum) ;


                                ?>
                                <tr>
                                <td class="ty2">전일대비</td>
                                <?
                                ?><td class="ty2"><?=number_format($GapCntScrn1)." ".$SgnCntScrn1?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn2)." ".$SgnCntScrn2?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn3)." ".$SgnCntScrn3?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn4)." ".$SgnCntScrn4?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn9)." ".$SgnCntScrn9?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrnSum)." ".$SgnCntScrnSum?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm1)." ".$SgnCntShowTm1?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm2)." ".$SgnCntShowTm2?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm3)." ".$SgnCntShowTm3?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm4)." ".$SgnCntShowTm4?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm9)." ".$SgnCntShowTm9?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTmSum)." ".$SgnCntShowTmSum?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat1)." ".$SgnSumSeat1?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat2)." ".$SgnSumSeat2?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat3)." ".$SgnSumSeat3?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat4)." ".$SgnSumSeat4?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat9)." ".$SgnSumSeat9?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeatSum)." ".$SgnSumSeatSum?></td><?
                                ?>
                                </tr>
                                <?
                              }
                              */
                            }


                            //echo "<br>".$MovieNm."(".$MovieCd.")";
                    }
                }
                ?>
                </table>
            </div>
            <?

        }
        ?>

        <script type="text/javascript">
            var chartData1 = [];
            var chartData2 = [];
            var chartData3 = [];
            var chartData4 = [];

            <?=$Script1?>

            <?=$Script2?>

            <?=$Script3?>

            <?=$Script4?>



            AmCharts.ready(function ()
                {
                    // PIE CHART
                    chart1 = new AmCharts.AmPieChart();
                    chart1.dataProvider = chartData1;
                    chart1.titleField = "category";
                    chart1.valueField = "value";

                    chart1.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                    chart1.depth3D = 15;
                    chart1.angle = 30;
                    chart1.labelRadius = 5;
                    chart1.legend = {  "position": "right"  };
                    chart1.labelText = "[[percents]]%";

                    chart1.write("chartdiv1");

                    // PIE CHART
                    chart2 = new AmCharts.AmPieChart();
                    chart2.dataProvider = chartData2;
                    chart2.titleField = "category";
                    chart2.valueField = "value";
                    chart2.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                    chart2.depth3D = 15;
                    chart2.angle = 30;
                    chart2.labelRadius = 5;
                    chart2.legend = {  "position": "right"  };
                    chart2.labelText = "[[percents]]%";

                    chart2.write("chartdiv2");


                    // PIE CHART
                    chart3 = new AmCharts.AmPieChart();
                    chart3.dataProvider = chartData3;
                    chart3.titleField = "category";
                    chart3.valueField = "value";
                    chart3.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                    chart3.depth3D = 15;
                    chart3.angle = 30;
                    chart3.labelRadius = 5;
                    chart3.legend = {  "position": "right"  };
                    chart3.labelText = "[[percents]]%";

                    chart3.write("chartdiv3");

                    // PIE CHART
                    chart4 = new AmCharts.AmPieChart();
                    chart4.dataProvider = chartData4;
                    chart4.titleField = "category";
                    chart4.valueField = "value";
                    chart4.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                    chart4.depth3D = 15;
                    chart4.angle = 30;
                    chart4.labelRadius = 5;
                    chart4.legend = {  "position": "right"  };
                    chart4.labelText = "[[percents]]%";

                    chart4.write("chartdiv4");
                });


        </script>

        <br>
        <div class="t3">
            <table border="1">
            <tr>
                <th>극장</th>
                <th>스크린</th>
            </tr>
            <tr>
                <td><div id="chartdiv1" style="width: 600px; height: 400px;"></div></td>
                <td><div id="chartdiv2" style="width: 600px; height: 400px;"></div></td>
            </tr>
            <tr>
                <th>회차</th>
                <th>총좌석수</th>
            </tr>
            <tr>
                <td><div id="chartdiv3" style="width: 600px; height: 400px;"></div></td>
                <td><div id="chartdiv4" style="width: 600px; height: 400px;"></div></td>
            </tr>
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
