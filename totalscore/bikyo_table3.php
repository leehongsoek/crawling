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

        <title>경쟁영화 회차 비교표3</title>
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
                echo "경쟁영화 회차 비교표3<br>" ;
                echo $PlayDate ;
            }

            //$WeekAgoDay = date("Ymd", strtotime($PlayDate."-1day"));

        //    $PlayDate = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2);

        //    $sQuery = " SELECT MAX(Date) MaxDate
        //                  FROM kofic_boxoffice
        //              " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
        //    $QryMaxDate = mysql_query($sQuery,$connect) ;
        //    if  ($ArrMaxDate = mysql_fetch_array($QryMaxDate))
        //    {
        //        $MaxDate = $ArrMaxDate["MaxDate"];
        //    }
        //
            $PlayDate = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2); // 2016-06-06 -> 20160606
            $MaxDate  = Get_MaxDate_BoxOffice($connect) ;

            if  ($PlayDate >= $MaxDate) $BaseDate = $MaxDate ;   //  박스오피스 가장최근일자보다 뒤에 날짜를 조회하고자한다면..  -> 박스오피스 가장최근일자
            else                        $BaseDate = $PlayDate ; //  박스오피스 가장최근일자보다 앞에 날짜를 조회한다면.. -> 그대로 그 날짜로...

            if ($Ranks!="")
            {
                 $MovieCds = "";

                 $arrRank = split(",",$Ranks);
                 for ($i=0;$i< sizeof($arrRank);$i++)
                 {
                     $sQuery = "     SELECT *
                                      FROM kofic_fix_boxoffice
                                     WHERE `Date` = '$BaseDate'
                                       AND Rank   = $arrRank[$i]
                               " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                     $QryBoxOffice = mysql_query($sQuery,$connect) ;
                     if  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                     {
                         if ($MovieCds!="") $MovieCds .= ",";

                         $MovieCds .= "'".$ArrBoxOffice['MovieCd']."'" ;
                     }
                 }
            }

            if  ($Ranks == null)
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
                        <th colspan="4">CGV</th>
                        <th colspan="4">롯데시네마</th>
                        <th colspan="4">메가박스</th>
                        <th colspan="4">프리머스,기타</th>
                        <th colspan="4">총계</th>
                    </tr>
                    <tr>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                    </tr>
                    <?

                    /*
                    SELECT SUM(A_CntScrnNm)      A_CntScrnNm
                          ,SUM(A_SumScrnSeat)    A_SumScrnSeat
                          ,SUM(A_CntShowTm)      A_CntShowTm
                          ,SUM(A_SumShowSeat)    A_SumShowSeat
                          ,SUM(B_CntScrnNm)      B_CntScrnNm
                          ,SUM(B_SumScrnSeat)    B_SumScrnSeat
                          ,SUM(B_CntShowTm)      B_CntShowTm
                          ,SUM(B_SumShowSeat)    B_SumShowSeat
                          ,SUM(C_CntScrnNm)      C_CntScrnNm
                          ,SUM(C_SumScrnSeat)    C_SumScrnSeat
                          ,SUM(C_CntShowTm)      C_CntShowTm
                          ,SUM(C_SumShowSeat)    C_SumShowSeat
                          ,SUM(D_CntScrnNm)      D_CntScrnNm
                          ,SUM(D_SumScrnSeat)    D_SumScrnSeat
                          ,SUM(D_CntShowTm)      D_CntShowTm
                          ,SUM(D_SumShowSeat)    D_SumShowSeat
                          ,SUM(All_CntScrnNm)    All_CntScrnNm
                          ,SUM(All_SumScrnSeat)  All_SumScrnSeat
                          ,SUM(All_CntShowTm)    All_CntShowTm
                          ,SUM(All_SumShowSeat)  All_SumShowSeat
                    FROM
                    (
                        SELECT IF(GCODE='A',CntScrnNm,NULL)       A_CntScrnNm
                              ,IF(GCODE='A',SumScrnSeat,NULL)     A_SumScrnSeat
                              ,IF(GCODE='A',CntShowTm,NULL)       A_CntShowTm
                              ,IF(GCODE='A',SumShowSeat,NULL)     A_SumShowSeat
                              ,IF(GCODE='B',CntScrnNm,NULL)       B_CntScrnNm
                              ,IF(GCODE='B',SumScrnSeat,NULL)     B_SumScrnSeat
                              ,IF(GCODE='B',CntShowTm,NULL)       B_CntShowTm
                              ,IF(GCODE='B',SumShowSeat,NULL)     B_SumShowSeat
                              ,IF(GCODE='C',CntScrnNm,NULL)       C_CntScrnNm
                              ,IF(GCODE='C',SumScrnSeat,NULL)     C_SumScrnSeat
                              ,IF(GCODE='C',CntShowTm,NULL)       C_CntShowTm
                              ,IF(GCODE='C',SumShowSeat,NULL)     C_SumShowSeat
                              ,IF(GCODE='D',CntScrnNm,NULL)       D_CntScrnNm
                              ,IF(GCODE='D',SumScrnSeat,NULL)     D_SumScrnSeat
                              ,IF(GCODE='D',CntShowTm,NULL)       D_CntShowTm
                              ,IF(GCODE='D',SumShowSeat,NULL)     D_SumShowSeat
                              ,IF(ISNULL(GCODE),CntScrnNm,NULL)   All_CntScrnNm
                              ,IF(ISNULL(GCODE),SumScrnSeat,NULL) All_SumScrnSeat
                              ,IF(ISNULL(GCODE),CntShowTm,NULL)   All_CntShowTm
                              ,IF(ISNULL(GCODE),SumShowSeat,NULL) All_SumShowSeat
                        FROM
                        (
                              SELECT GCODE
                                    ,SUM(SHOWROOM.CntScrnNm) CntScrnNm
                                    ,SUM(SHOWROOM.SumSeat) SumScrnSeat
                                    ,SUM(INNING.CntShowTm) CntShowTm
                                    ,SUM(INNING.SumSeat)  SumShowSeat
                                FROM (
                                          SELECT GRP.GCODE, FT.Code
                                            FROM kofic_fix_theather FT
                                      INNER JOIN (        SELECT 'A' GCODE,1 CND1,1 CND2
                                                UNION ALL SELECT 'B',2,2
                                                UNION ALL SELECT 'C',3,3
                                                UNION ALL SELECT 'D',4,''
                                                ) GRP
                                             ON FT.`Group` = GRP.CND1
                                             OR FT.`Group` = GRP.CND2
                                     ) THEATHER
                          INNER JOIN (
                                        SELECT `Date`, MovieCd, TheatherCd, COUNT(ScrnNm) CntScrnNm, SUM(Seat) SumSeat
                                          FROM (
                                                  SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, st.Seat
                                                    FROM kofic_showtime stm
                                              INNER JOIN kofic_seat st
                                                      ON st.TheatherCd = stm.TheatherCd
                                                     AND st.ScrnNm = stm.ScrnNm
                        where stm.`Date` = '20151012'
                          and stm.MovieCd = '20156557'
                                                GROUP BY stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm
                                               ) A
                                      GROUP BY `Date`, MovieCd, TheatherCd
                                     ) SHOWROOM
                                  ON SHOWROOM.TheatherCd =  THEATHER.Code
                          INNER JOIN (
                                        SELECT `Date`, MovieCd, TheatherCd, COUNT(ShowTm) CntShowTm, SUM(Seat) SumSeat
                                          FROM (
                                                    SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, stm.ShowTm, st.Seat
                                                      FROM kofic_showtime stm
                                                INNER JOIN kofic_seat st
                                                        ON st.TheatherCd = stm.TheatherCd
                                                       AND st.ScrnNm = stm.ScrnNm
                        where stm.`Date` = '20151012'
                          and stm.MovieCd = '20156557'
                                               ) A
                                      GROUP BY `Date`, MovieCd, TheatherCd
                                     ) INNING
                                  ON INNING.TheatherCd = THEATHER.Code
                            GROUP BY THEATHER.GCODE WITH ROLLUP
                        )A
                    ) A
                    ;


                    -- 그룹별 극장리스트

                          SELECT GRP.GCODE, FT.Code
                            FROM kofic_fix_theather FT
                      INNER JOIN (        SELECT 'A' GCODE,1 CND1,1 CND2
                                UNION ALL SELECT 'B'      ,2     ,2
                                UNION ALL SELECT 'C'      ,3     ,3
                                UNION ALL SELECT 'D'      ,4     ,''
                                ) GRP
                             ON FT.`Group` = GRP.CND1
                             OR FT.`Group` = GRP.CND2
                    ;


                    -- 일자, 영화, 극장코드 에 해당하는 스크린수 와 스크린당 좌석수
                    SELECT `Date`, MovieCd, TheatherCd, COUNT(ScrnNm) CntScrnNm, SUM(Seat) SumSeat
                      FROM (
                              SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, st.Seat
                                FROM kofic_showtime stm
                          INNER JOIN kofic_seat st
                                  ON st.TheatherCd = stm.TheatherCd
                                 AND st.ScrnNm = stm.ScrnNm
                            GROUP BY stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm
                           ) A
                    where `Date` = '20151012'
                      and MovieCd = '20156557'
                      and TheatherCd = '007025'
                    GROUP BY `Date`, MovieCd, TheatherCd
                    ;

                    -- 일자, 영화, 극장코드 에 해당하는 회차수와 회차별 좌석수
                    SELECT `Date`
                             ,MovieCd
                             ,TheatherCd
                             ,COUNT(ShowTm) CntShowTm
                             ,SUM(Seat) SumSeat
                    FROM
                    (
                          SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, stm.ShowTm, st.Seat
                           FROM kofic_showtime stm
                          INNER JOIN kofic_seat st
                                 ON st.TheatherCd = stm.TheatherCd
                                AND st.ScrnNm = stm.ScrnNm
                    where stm.`Date` = '20151012'
                      and stm.MovieCd = '20156557'
                      and stm.TheatherCd = '007025'
                    ) A
                    GROUP BY `Date`
                             ,MovieCd
                             ,TheatherCd
                    ;
                    */
                    $chgMovie    = "";
                    $optionMovie = "";

                    $Sum_A_CntScrnNm     = 0 ;
                    $Sum_A_SumScrnSeat   = 0 ;
                    $Sum_A_CntShowTm     = 0 ;
                    $Sum_A_SumShowSeat   = 0 ;
                    $Sum_B_CntScrnNm     = 0 ;
                    $Sum_B_SumScrnSeat   = 0 ;
                    $Sum_B_CntShowTm     = 0 ;
                    $Sum_B_SumShowSeat   = 0 ;
                    $Sum_C_CntScrnNm     = 0 ;
                    $Sum_C_SumScrnSeat   = 0 ;
                    $Sum_C_CntShowTm     = 0 ;
                    $Sum_C_SumShowSeat   = 0 ;
                    $Sum_D_CntScrnNm     = 0 ;
                    $Sum_D_SumScrnSeat   = 0 ;
                    $Sum_D_CntShowTm     = 0 ;
                    $Sum_D_SumShowSeat   = 0 ;
                    $Sum_All_CntScrnNm   = 0 ;
                    $Sum_All_SumScrnSeat = 0 ;
                    $Sum_All_CntShowTm   = 0 ;
                    $Sum_All_SumShowSeat = 0 ;

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
                            //$MovieCd = '20161364';
                            //echo "<br><br>".$BaseDate ;
                            //echo "<br><br>".$MovieCd ;


                            $sQuery = "         SELECT IFNULL(SUM(A_CntScrnNm),0)      A_CntScrnNm
                                                      ,IFNULL(SUM(A_SumScrnSeat),0)    A_SumScrnSeat
                                                      ,IFNULL(SUM(A_CntShowTm),0)      A_CntShowTm
                                                      ,IFNULL(SUM(A_SumShowSeat),0)    A_SumShowSeat
                                                      ,IFNULL(SUM(B_CntScrnNm),0)      B_CntScrnNm
                                                      ,IFNULL(SUM(B_SumScrnSeat),0)    B_SumScrnSeat
                                                      ,IFNULL(SUM(B_CntShowTm),0)      B_CntShowTm
                                                      ,IFNULL(SUM(B_SumShowSeat),0)    B_SumShowSeat
                                                      ,IFNULL(SUM(C_CntScrnNm),0)      C_CntScrnNm
                                                      ,IFNULL(SUM(C_SumScrnSeat),0)    C_SumScrnSeat
                                                      ,IFNULL(SUM(C_CntShowTm),0)      C_CntShowTm
                                                      ,IFNULL(SUM(C_SumShowSeat),0)    C_SumShowSeat
                                                      ,IFNULL(SUM(D_CntScrnNm),0)      D_CntScrnNm
                                                      ,IFNULL(SUM(D_SumScrnSeat),0)    D_SumScrnSeat
                                                      ,IFNULL(SUM(D_CntShowTm),0)      D_CntShowTm
                                                      ,IFNULL(SUM(D_SumShowSeat),0)    D_SumShowSeat
                                                      ,IFNULL(SUM(All_CntScrnNm),0)    All_CntScrnNm
                                                      ,IFNULL(SUM(All_SumScrnSeat),0)  All_SumScrnSeat
                                                      ,IFNULL(SUM(All_CntShowTm),0)    All_CntShowTm
                                                      ,IFNULL(SUM(All_SumShowSeat),0)  All_SumShowSeat
                                                FROM
                                                (
                                                    SELECT IF(GCODE='A',CntScrnNm,NULL)       A_CntScrnNm
                                                          ,IF(GCODE='A',SumScrnSeat,NULL)     A_SumScrnSeat
                                                          ,IF(GCODE='A',CntShowTm,NULL)       A_CntShowTm
                                                          ,IF(GCODE='A',SumShowSeat,NULL)     A_SumShowSeat
                                                          ,IF(GCODE='B',CntScrnNm,NULL)       B_CntScrnNm
                                                          ,IF(GCODE='B',SumScrnSeat,NULL)     B_SumScrnSeat
                                                          ,IF(GCODE='B',CntShowTm,NULL)       B_CntShowTm
                                                          ,IF(GCODE='B',SumShowSeat,NULL)     B_SumShowSeat
                                                          ,IF(GCODE='C',CntScrnNm,NULL)       C_CntScrnNm
                                                          ,IF(GCODE='C',SumScrnSeat,NULL)     C_SumScrnSeat
                                                          ,IF(GCODE='C',CntShowTm,NULL)       C_CntShowTm
                                                          ,IF(GCODE='C',SumShowSeat,NULL)     C_SumShowSeat
                                                          ,IF(GCODE='D',CntScrnNm,NULL)       D_CntScrnNm
                                                          ,IF(GCODE='D',SumScrnSeat,NULL)     D_SumScrnSeat
                                                          ,IF(GCODE='D',CntShowTm,NULL)       D_CntShowTm
                                                          ,IF(GCODE='D',SumShowSeat,NULL)     D_SumShowSeat
                                                          ,IF(ISNULL(GCODE),CntScrnNm,NULL)   All_CntScrnNm
                                                          ,IF(ISNULL(GCODE),SumScrnSeat,NULL) All_SumScrnSeat
                                                          ,IF(ISNULL(GCODE),CntShowTm,NULL)   All_CntShowTm
                                                          ,IF(ISNULL(GCODE),SumShowSeat,NULL) All_SumShowSeat
                                                    FROM
                                                    (
                                                          SELECT GCODE
                                                                ,SUM(SHOWROOM.CntScrnNm) CntScrnNm
                                                                ,SUM(SHOWROOM.SumSeat) SumScrnSeat
                                                                ,SUM(INNING.CntShowTm) CntShowTm
                                                                ,SUM(INNING.SumSeat)  SumShowSeat
                                                            FROM (
                                                                      SELECT GRP.GCODE, FT.Code
                                                                        FROM kofic_fix_theather FT
                                                                  INNER JOIN (        SELECT 'A' GCODE,1 CND1,1 CND2
                                                                            UNION ALL SELECT 'B'      ,2     ,2
                                                                            UNION ALL SELECT 'C'      ,3     ,3
                                                                            UNION ALL SELECT 'D'      ,4     ,''
                                                                            ) GRP
                                                                         ON FT.`Group` = GRP.CND1
                                                                         OR FT.`Group` = GRP.CND2
                                                                      WHERE FT.Active = 1
                                                                 ) THEATHER
                                                      INNER JOIN (
                                                                    SELECT `Date`, MovieCd, TheatherCd, COUNT(ScrnNm) CntScrnNm, SUM(Seat) SumSeat
                                                                      FROM (
                                                                              SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, st.Seat
                                                                                FROM kofic_showtime stm
                                                                          INNER JOIN kofic_seat st
                                                                                  ON st.TheatherCd = stm.TheatherCd
                                                                                 AND st.ScrnNm     = stm.ScrnNm
                                                                               WHERE stm.`Date`  = '$BaseDate'
                                                                                 AND stm.MovieCd = '$MovieCd'
                                                                            GROUP BY stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm
                                                                           ) A
                                                                  GROUP BY `Date`, MovieCd, TheatherCd
                                                                 ) SHOWROOM
                                                              ON SHOWROOM.TheatherCd =  THEATHER.Code
                                                      INNER JOIN (
                                                                    SELECT `Date`, MovieCd, TheatherCd, COUNT(ShowTm) CntShowTm, SUM(Seat) SumSeat
                                                                      FROM (
                                                                                SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, stm.ShowTm, st.Seat
                                                                                  FROM kofic_showtime stm
                                                                            INNER JOIN kofic_seat st
                                                                                    ON st.TheatherCd = stm.TheatherCd
                                                                                   AND st.ScrnNm     = stm.ScrnNm
                                                                                 WHERE stm.`Date`  = '$BaseDate'
                                                                                   AND stm.MovieCd = '$MovieCd'
                                                                           ) A
                                                                  GROUP BY `Date`, MovieCd, TheatherCd
                                                                 ) INNING
                                                              ON INNING.TheatherCd = THEATHER.Code
                                                        GROUP BY THEATHER.GCODE WITH ROLLUP
                                                    ) A
                                                ) A
                                      " ; $firephp->fb($sQuery); //  echo "<br><br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                            $QryReport = mysql_query($sQuery,$connect) ;
                            while ($ArrReport = mysql_fetch_array($QryReport))
                            {
                                ?>
                                <tr>
                                <td><?=$MovieNm?></td>
                                <?
                                $A_CntScrnNm      = $ArrReport["A_CntScrnNm"] ;
                                $A_SumScrnSeat    = $ArrReport["A_SumScrnSeat"] ;
                                $A_CntShowTm      = $ArrReport["A_CntShowTm"] ;
                                $A_SumShowSeat    = $ArrReport["A_SumShowSeat"] ;
                                $B_CntScrnNm      = $ArrReport["B_CntScrnNm"] ;
                                $B_SumScrnSeat    = $ArrReport["B_SumScrnSeat"] ;
                                $B_CntShowTm      = $ArrReport["B_CntShowTm"] ;
                                $B_SumShowSeat    = $ArrReport["B_SumShowSeat"] ;
                                $C_CntScrnNm      = $ArrReport["C_CntScrnNm"] ;
                                $C_SumScrnSeat    = $ArrReport["C_SumScrnSeat"] ;
                                $C_CntShowTm      = $ArrReport["C_CntShowTm"] ;
                                $C_SumShowSeat    = $ArrReport["C_SumShowSeat"] ;
                                $D_CntScrnNm      = $ArrReport["D_CntScrnNm"] ;
                                $D_SumScrnSeat    = $ArrReport["D_SumScrnSeat"] ;
                                $D_CntShowTm      = $ArrReport["D_CntShowTm"] ;
                                $D_SumShowSeat    = $ArrReport["D_SumShowSeat"] ;
                                $All_CntScrnNm    = $ArrReport["All_CntScrnNm"] ;
                                $All_SumScrnSeat  = $ArrReport["All_SumScrnSeat"] ;
                                $All_CntShowTm    = $ArrReport["All_CntShowTm"] ;
                                $All_SumShowSeat  = $ArrReport["All_SumShowSeat"] ;

                                $optionMovie .= "\n<option value=\"$Rank\">&nbsp;".$MovieNm ;
                                $chgMovie    .= "\n if  (val==$Rank)
                                                    {
                                                        chartData1 = [];
                                                        chartData1.push({ \"category\": \"CGV\",           \"value\": $A_CntScrnNm });
                                                        chartData1.push({ \"category\": \"롯데시네마\",    \"value\": $B_CntScrnNm });
                                                        chartData1.push({ \"category\": \"메가박스\",      \"value\": $C_CntScrnNm });
                                                        chartData1.push({ \"category\": \"프리머스,기타\", \"value\": $D_CntScrnNm });

                                                        chart1.dataProvider = chartData1;
                                                        chart1.validateData();

                                                        chartData2 = [];
                                                        chartData2.push({ \"category\": \"CGV\",           \"value\": $A_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"롯데시네마\",    \"value\": $B_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"메가박스\",      \"value\": $C_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"프리머스,기타\", \"value\": $D_SumScrnSeat });

                                                        chart2.dataProvider = chartData2;
                                                        chart2.validateData();

                                                        chartData3 = [];
                                                        chartData3.push({ \"category\": \"CGV\",           \"value\": $A_CntShowTm });
                                                        chartData3.push({ \"category\": \"롯데시네마\",    \"value\": $B_CntShowTm });
                                                        chartData3.push({ \"category\": \"메가박스\",      \"value\": $C_CntShowTm });
                                                        chartData3.push({ \"category\": \"프리머스,기타\", \"value\": $D_CntShowTm });

                                                        chart3.dataProvider = chartData3;
                                                        chart3.validateData();

                                                        chartData4 = [];
                                                        chartData4.push({ \"category\": \"CGV\",           \"value\": $A_SumShowSeat });
                                                        chartData4.push({ \"category\": \"롯데시네마\",    \"value\": $B_SumShowSeat });
                                                        chartData4.push({ \"category\": \"메가박스\",      \"value\": $C_SumShowSeat });
                                                        chartData4.push({ \"category\": \"프리머스,기타\", \"value\": $D_SumShowSeat });

                                                        chart4.dataProvider = chartData4;
                                                        chart4.validateData();
                                                    }
                                                 ";
                                ?>
                                <td class="ty2"><?=number_format($A_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($A_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($A_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($A_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($B_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($B_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($B_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($B_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($C_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($C_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($C_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($C_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($D_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($D_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($D_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($D_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($All_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($All_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($All_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($All_SumShowSeat)?></td>
                                </tr>
                                <?

                                $Sum_A_CntScrnNm     += $A_CntScrnNm ;
                                $Sum_A_SumScrnSeat   += $A_SumScrnSeat ;
                                $Sum_A_CntShowTm     += $A_CntShowTm ;
                                $Sum_A_SumShowSeat   += $A_SumShowSeat ;
                                $Sum_B_CntScrnNm     += $B_CntScrnNm ;
                                $Sum_B_SumScrnSeat   += $B_SumScrnSeat ;
                                $Sum_B_CntShowTm     += $B_CntShowTm ;
                                $Sum_B_SumShowSeat   += $B_SumShowSeat ;
                                $Sum_C_CntScrnNm     += $C_CntScrnNm ;
                                $Sum_C_SumScrnSeat   += $C_SumScrnSeat ;
                                $Sum_C_CntShowTm     += $C_CntShowTm ;
                                $Sum_C_SumShowSeat   += $C_SumShowSeat ;
                                $Sum_D_CntScrnNm     += $D_CntScrnNm ;
                                $Sum_D_SumScrnSeat   += $D_SumScrnSeat ;
                                $Sum_D_CntShowTm     += $D_CntShowTm ;
                                $Sum_D_SumShowSeat   += $D_SumShowSeat ;
                                $Sum_All_CntScrnNm   += $All_CntScrnNm ;
                                $Sum_All_SumScrnSeat += $All_SumScrnSeat ;
                                $Sum_All_CntShowTm   += $All_CntShowTm ;
                                $Sum_All_SumShowSeat += $All_SumShowSeat ;
                            }
                            //echo "<br>".$MovieNm."(".$MovieCd.")";
                        }
                    }

                    ?>
                    <tr>
                    <td>합계</td>
                    <td class="ty2"><?=number_format($Sum_A_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_A_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_A_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_A_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_B_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_B_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_B_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_B_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_C_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_C_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_C_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_C_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_D_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_D_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_D_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_D_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_All_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_All_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_All_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_All_SumShowSeat)?></td>
                    </tr>

                    </table>
                </div>
                <?
            }
            ?>


                <script type="text/javascript">
                    var chart;
                    var legend;

                    var chartData1 = [];
                    chartData1.push({ "category": "CGV",           "value": <?=$Sum_A_CntScrnNm?> });
                    chartData1.push({ "category": "롯데시네마",    "value": <?=$Sum_B_CntScrnNm?> });
                    chartData1.push({ "category": "메가박스",      "value": <?=$Sum_C_CntScrnNm?> });
                    chartData1.push({ "category": "프리머스,기타", "value": <?=$Sum_D_CntScrnNm?> });

                    var chartData2 = [];
                    chartData2.push({ "category": "CGV",           "value": <?=$Sum_A_SumScrnSeat?> });
                    chartData2.push({ "category": "롯데시네마",    "value": <?=$Sum_B_SumScrnSeat?> });
                    chartData2.push({ "category": "메가박스",      "value": <?=$Sum_C_SumScrnSeat?> });
                    chartData2.push({ "category": "프리머스,기타", "value": <?=$Sum_D_SumScrnSeat?> });

                    var chartData3 = [];
                    chartData3.push({ "category": "CGV",           "value": <?=$Sum_A_CntShowTm?> });
                    chartData3.push({ "category": "롯데시네마",    "value": <?=$Sum_B_CntShowTm?> });
                    chartData3.push({ "category": "메가박스",      "value": <?=$Sum_C_CntShowTm?> });
                    chartData3.push({ "category": "프리머스,기타", "value": <?=$Sum_D_CntShowTm?> });

                    var chartData4 = [];
                    chartData4.push({ "category": "CGV",           "value": <?=$Sum_A_SumShowSeat?> });
                    chartData4.push({ "category": "롯데시네마",    "value": <?=$Sum_B_SumShowSeat?> });
                    chartData4.push({ "category": "메가박스",      "value": <?=$Sum_C_SumShowSeat?> });
                    chartData4.push({ "category": "프리머스,기타", "value": <?=$Sum_D_SumShowSeat?> });

                    var chart1 = new AmCharts.AmPieChart();
                    var chart2 = new AmCharts.AmPieChart();
                    var chart3 = new AmCharts.AmPieChart();
                    var chart4 = new AmCharts.AmPieChart();

                    AmCharts.ready(function ()
                    {
                        // PIE CHART
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

                    function chgMovie(val)
                    {
                        <?=$chgMovie?>
                    }

                </script>
                <br>

                <select name="Loccation_" style="width:300px;" onchange="chgMovie(this.value);">
                     <option value="0">&nbsp;합계
                     <?=$optionMovie?>
                </select>
                <br><br>

                <div class="t3">
                    <table border="1">
                    <tr>
                        <th>스크린</th>
                        <th>좌석수</th>
                    </tr>
                    <tr>
                        <td><div id="chartdiv1" style="width: 600px; height: 400px;"></div></td>
                        <td><div id="chartdiv2" style="width: 600px; height: 400px;"></div></td>
                    </tr>
                    <tr>
                        <th>총회차</th>
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
