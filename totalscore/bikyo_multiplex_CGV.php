<?
    session_start();
    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    $Codes    = $_GET['Codes'];     // 선택된 코드들..
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

        // '집계' 버튼 클릭
        function start_click()
        {
            var select = 0;
            var codes = "";

            $("input[name=Code]:checked").each(function()
            {
                select ++ ;

                if  (codes != "") codes += ",";

                codes += $(this).val();
            });

            if  (select > 0) // 선택된 건이 있다면..
            {
                $('input[name=Codes]').val(codes); // 폼안에 히든 값을 설정하고 ..
            }

            frmMain.submit(); // 자기자신을 직접호출한다.
        }

        //
        // 엑셀 출력
        //
        function toexel_click()
        {
           botttomaddr = "<?=$_SERVER['PHP_SELF']?>"
                       + "?ToExel=Yes"
                       + "&Codes=<?=$Codes?>"
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

        <title>CGV 회차 비교표</title>

    </head>
    <body>

    <?
    if  (!session_is_registered("logged_UserId"))
    {
        ?>
        <script type="text/javascript">

            alert('로그인을 해주세요!') ;

            location.href="http://www.mtns7.co.kr/" ;

        </script>
        <?
    }
    else
    {
        if  ($ToExel!="Yes")
        {
            include "inc/Menu.inc";
            ?>
            <form name="frmMain" method="get" action="<?=$_SERVER['PHP_SELF']?>" >
                <input type="hidden" name="Codes" />
                <br>
                년월일: <input type="text" name="PlayDate" class="datepicker" value="<?=$PlayDate?>" onchange="datepicker_change()">
                <button name="start" onclick="start_click()">집계</button>
                <?
                if  ($Codes != null)
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
            echo "CGV 회차 비교표<br>" ;
            echo $PlayDate ;
        }

        $PlayDate = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2); // 2016-06-06 -> 20160606
        $MaxDate  = Get_MaxDate_BoxOffice($connect) ;


        if  ($PlayDate >= $MaxDate) $BaseDate = $MaxDate ;  //  박스오피스 가장최근일자보다 뒤에 날짜를 조회하고자한다면..  -> 박스오피스 가장최근일자
        else                        $BaseDate = $PlayDate ; //  박스오피스 가장최근일자보다 앞에 날짜를 조회한다면.. -> 그대로 그 날짜로...

        if  ($Codes == null) // 영화정보가 없는 경우.. 기본화면..
        {
            ?>
            <div class="t3">
                <table border="1">
                <tr>
                    <th>영화명</th>
                    <th>개봉일</th>
                </tr>
                <?
                $sQuery = "     SELECT *
                                 FROM cgv_movies
                             ORDER BY releasedate desc
                          " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);    // 박스오피스 리스트를 구한다.
                $QryMovie = mysql_query($sQuery,$connect) ; $num_rows = mysql_num_rows($QryMovie);
                while  ($ArrMovie = mysql_fetch_array($QryMovie))
                {
                    $moviecode   = $ArrMovie['moviecode'];
                    $moviename   = iconv("EUC-KR", "UTF-8",$ArrMovie['moviename']);
                    $releasedate = $ArrMovie['releasedate'];
                    ?>
                    <tr>
                        <td id="td<?=$moviecode?>"><label><input name="Code" id="Group" value="<?=$moviecode?>" type="checkbox" />&nbsp;<?=$moviename?></label></td>
                        <td><?=$releasedate?></td>
                    </tr>
                    <?
                }
                ?>
                </table>
            </div>
            <?
        }
        else  // 선택된 영화 자료가 있을 때.... (하나이상 선택하고 집계를 누를때..)
        {
            $sumArray = array(); // 합계를 저장

            ?>
            <div class="t3">
            <table border="1">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">극장명</th>
            <?

            $arrCode = split(",",$Codes);
            for ($i = 0 ; $i < sizeof($arrCode) ; $i++)
            {
                $sQuery = "SELECT moviecode
                                 ,moviename
                             FROM cgv_movies
                            WHERE moviecode = $arrCode[$i]
                          " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.

                $QryMovie = mysql_query($sQuery,$connect) ;
                while  ($ArrMovie = mysql_fetch_array($QryMovie))
                {
                    $moviecode  = $ArrMovie['moviecode'];
                    $moviename  = iconv("EUC-KR", "UTF-8",$ArrMovie['moviename']);
                    ?>
                    <th colspan="24"><?=$moviename?></th>
                    <?
                }
            }
            ?>
            </tr>
            <tr>
            <?
            $arrCode = split(",",$Codes);
            for ($i = 0 ; $i < sizeof($arrCode) ; $i++)
            {
                ?>
                <th>상영관</th>
                <th colspan="20">시간표</th>
                <th>회차수</th>
                <th>총좌석수</th>
                <th>예매율</th>
                <?
            }
            ?>
            </tr>
            <?
            $no = 1 ;
            $sQuery = "     SELECT theatercode
                                  ,regioncode
                                  ,regionname
                                  ,theatername
                              FROM cgv_theater
                          ORDER BY regioncode
                                  ,theatername
                       " ;// echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
            $QryTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrTheather = mysql_fetch_array($QryTheather))
            {
                $theatercode = $ArrTheather["theatercode"] ;
                $regioncode  = $ArrTheather["regioncode"] ;
                $regionname  = iconv("EUC-KR", "UTF-8",$ArrTheather["regionname"]) ;
                $theatername = iconv("EUC-KR", "UTF-8",$ArrTheather["theatername"]) ;

                //if  (($sTheatherCode != "001111")) continue; // CGV압구정

                ?>
                <tr>
                    <td class="ty1"><?=$no++?></td> <!--no-->
                    <td><?=$theatername?></td> <!--극장명-->
                        <?
                        $arrCode = split(",",$Codes);



                        for ($i = 0 ; $i < sizeof($arrCode) ; $i++)
                        {
                            $sumSumSeat   = 0 ;
                            $sumInningTot = 0 ;
                            $sumInningAct = 0 ;
                            $sumTotalSeat = 0 ;

                            array_push($sumArray, 0) ;
                            array_push($sumArray, $sumSumSeat) ;
                            array_push($sumArray, $sumInningTot) ;
                            array_push($sumArray, $sumInningAct) ;
                            array_push($sumArray, $sumTotalSeat) ;

                            $sQuery = "SELECT moviecode
                                             ,moviename
                                         FROM cgv_movies
                                        WHERE moviecode = $arrCode[$i]
                                      " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryMovie = mysql_query($sQuery,$connect) ;
                            while  ($ArrMovie = mysql_fetch_array($QryMovie))
                            {
                                $Lst_ScrnName    = "" ;
                                $Lst_ShowTimes   = "" ;
                                $Lst_CntInning   = "" ;
                                $Lst_TotalSeat   = "" ;
                                $Lst_BookingRate = "" ;

                                $moviecode = $ArrMovie['moviecode'];
                                $moviename = iconv("EUC-KR", "UTF-8",$ArrMovie['moviename']);

                                $numRoom = 0 ;

                                for ($j=0; $j <20 ; $j++)
                                {
                                    $arr_timeslot[$j] = "" ; // 초기화
                                }

                                $sQuery = "    SELECT filmtype
                                                     ,roomfloor
                                                     ,totalseat
                                                     ,gunbunno
                                                 FROM cgv_tickecting2
                                                WHERE 1 = 1
                                                  AND playdate    = '$PlayDate'
                                                  AND theatercode = '$theatercode'
                                                  AND moviecode   = '$moviecode'
                                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                                $QryTickecting2 = mysql_query($sQuery,$connect) ;
                                while  ($ArrTickecting2 = mysql_fetch_array($QryTickecting2))
                                {
                                    $numRoom ++ ;

                                    $filmtype  = $ArrTickecting2['filmtype'];
                                    $roomfloor = $ArrTickecting2['roomfloor'];
                                    $totalseat = preg_replace("/[^0-9]/", "", $ArrTickecting2['totalseat']);
                                    $gunbunno  = $ArrTickecting2['gunbunno'];
                                    $scrnName  = iconv("EUC-KR", "UTF-8",$filmtype . " " . $roomfloor ) ;

                                    $cntInning  = 0 ;
                                    $actInning  = 0 ;
                                    $sumSeat    = 0 ;
                                    $showTimes  = "" ;

                                    $sQuery = "    SELECT playtime
                                                         ,playinfo
                                                         ,playetc
                                                     FROM cgv_tickecting3
                                                    WHERE 1 = 1
                                                      AND playdate    = '$PlayDate'
                                                      AND theatercode = '$theatercode'
                                                      AND moviecode   = '$moviecode'
                                                      AND gunbunno    = '$gunbunno'
                                                 ORDER BY playtime
                                              " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                                    $QryTickecting3 = mysql_query($sQuery,$connect) ;
                                    while  ($ArrTickecting3 = mysql_fetch_array($QryTickecting3))
                                    {
                                        $playtime   = $ArrTickecting3['playtime'] ;
                                        $playinfo   = iconv("EUC-KR", "UTF-8",$ArrTickecting3['playinfo']) ;
                                        $inningseat = preg_replace("/[^0-9]/", "", $playinfo) ;
                                        $playetc    = iconv("EUC-KR", "UTF-8",$ArrTickecting3['playetc']) ;

                                        if  ($inningseat != "")
                                        {
                                            $actInning ++ ; // 유효 회차
                                            $sumSeat += ( $inningseat * 1 ) ;
                                            $playinfo = str_replace('석','',$playinfo);
                                        }

                                        if  ($showTimes != "") $showTimes .= ", ";
                                        $showTimes .= $playtime . "(".$playinfo.")" ;

                                        $arr_timeslot[$cntInning * 2] .= $playtime."<br>" ;
                                        $arr_timeslot[$cntInning * 2 + 1] .= $playinfo."<br>" ;

                                        $cntInning ++ ;
                                    }
                                    $totalSeat = ($actInning * $totalseat) ;

                                    for ($j=$cntInning; $j < 10; $j++) // 나머지 뒷칸은 공백으로 채운다.
                                    {
                                        $arr_timeslot[$j * 2] .= "<br>" ;
                                        $arr_timeslot[$j * 2 + 1] .= "<br>" ;
                                    }

                                    if  ($totalSeat != 0)
                                    {
                                        $bookingRate = number_format(round( ($totalSeat - $sumSeat) / $totalSeat * 100.0, 1),1) ;
                                        $sumBooking = ($totalSeat - $sumSeat) ;
                                    }
                                    else
                                    {
                                        $bookingRate  = "-" ;
                                        $totalSeat    =  "-" ;
                                    }

                                    $Lst_ScrnName    .= $scrnName . "<br>" ;
                                    $Lst_ShowTimes   .= $showTimes . "<br>" ;
                                    $Lst_CntInning   .= $cntInning . "[" . $actInning . "]" . "<br>" ;
                                    $Lst_TotalSeat   .= $totalSeat . "<br>" ;
                                    $Lst_BookingRate .= $bookingRate . "%<br>" ;

                                    $sumSumSeat   += $sumSeat ;
                                    $sumInningTot += $cntInning ;
                                    $sumInningAct += $actInning ;
                                    $sumTotalSeat += $totalSeat ;

                                } // while  ($ArrTickecting2 = mysql_fetch_array($QryTickecting2))

                            } // while  ($ArrMovie = mysql_fetch_array($QryMovie))

                            ?>
                            <td style="vertical-align: top;"><?=$Lst_ScrnName?></td> <!--상영관명-->
                            <!-- td style="vertical-align: top;"><?=$Lst_ShowTimes?></td--> <!--상영시간표들-->
                            <?
                            for ($j=0; $j < 20; $j++)
                            {
                                ?><td text-align="center" style="vertical-align: top;"><?=$arr_timeslot[$j]?></td> <!-- 시간표 --><?
                            }
                            ?>
                            <td style="vertical-align: top;" class="ty2"><?=$Lst_CntInning?></td> <!--회차수-->
                            <td style="vertical-align: top;" class="ty2"><?=$Lst_TotalSeat?></td> <!--총좌석수-->
                            <td style="vertical-align: top;" class="ty2"><?=$Lst_BookingRate?></td><!--예매율-->
                            <?
                            $sumArray[$i*5+0] += $numRoom ;
                            $sumArray[$i*5+1] += $sumSumSeat ;
                            $sumArray[$i*5+2] += $sumInningTot ;
                            $sumArray[$i*5+3] += $sumInningAct ;
                            $sumArray[$i*5+4] += $sumTotalSeat ;

                        } // for ($i = 0 ; $i < sizeof($arrCode) ; $i++)
                        ?>
                </tr>
                <?

                //echo "<tr>".$sTheatherCode."_".$sTheatherName." : 읽기";
            }
            ?>

            <tr>
                <td colspan="2" style="text-align: center;">합계</td> <!--합계-->
                <?
                for ($i = 0 ; $i < sizeof($arrCode) ; $i++)
                {
                    ?>
                    <td><?=$sumArray[$i*5+0]?></td> <!--상영관명-->
                    <td colspan="20"><?=$sumArray[$i*5+1]?></td> <!--상영시간표들-->
                    <td style="vertical-align: top;" class="ty2"><?=$sumArray[$i*5+2]?>[<?=$sumArray[$i*5+3]?>]</td> <!--회차수-->
                    <td style="vertical-align: top;" class="ty2"><?=$sumArray[$i*5+4]?></td> <!--총좌석수-->
                    <?
                    if  ($sumArray[$i*5+4] != 0)
                    {
                        ?><td style="vertical-align: top;" class="ty2"><?=number_format(round( ($sumArray[$i*5+4] - $sumArray[$i*5+1]) / $sumArray[$i*5+4] * 100.0, 1),1)?>%</td><!--예매율--><?
                    }
                    else
                    {

                        ?><td style="vertical-align: top;" class="ty2"></td><!--예매율--><?
                    }
                }
                ?>
            </tr>

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
